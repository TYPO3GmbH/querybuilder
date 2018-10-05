<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder;

use InvalidArgumentException;
use stdClass;
use T3G\Querybuilder\Backend\Form\FormDataGroup\TcaOnly;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataProvider\SiteResolving;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Class QueryParser.
 */
class QueryBuilder
{
    private const FORMAT_DATETIME = 'YYYY-MM-DD HH:mm';
    private const FORMAT_DATE = 'YYYY-MM-DD';
    private const FORMAT_TIME = 'HH:mm';

    /**
     * Build the filter configuration from TCA
     *
     * @param string $table
     *
     * @return array
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function buildFilterFromTca($table, $pageId) : array
    {
        $dataProviderResult = $this->prepareTca($table, $pageId);
        $TCA = $dataProviderResult['processedTca'];
        $filters = [];
        $filterFields = !empty($TCA['ctrl']['queryFilterFields']) ? $TCA['ctrl']['queryFilterFields'] : $TCA['ctrl']['searchFields'];
        $filterFields = GeneralUtility::trimExplode(',', $filterFields);
        foreach ($filterFields as $filterField) {
            $fieldConfig = $TCA['columns'][$filterField];
            if (!is_array($fieldConfig)) {
                // if a filter field has no column declaration continue...
                continue;
            }
            // Filter:Types: string, integer, double, date, time, datetime and boolean.
            // Filter:Required: id, type, values*
            $filter = new stdClass();
            $filter->id = $filterField;
            $filter->type = $this->determineFilterType($fieldConfig);
            $filter->input = $this->determineFilterInput($fieldConfig);
            $filter->values = $this->determineFilterValues($fieldConfig);
            $filter->label = $fieldConfig['label'];
            $filter->description = !empty($fieldConfig['description']) ? $fieldConfig['description'] : '';
            $this->determineAndAddExtras($filter);
            $filters[] = $filter;
        }

        return $filters;
    }
    /**
     * Returns one of the possible filter types:
     * [string, integer, double, date, time, datetime and boolean].
     *
     * @param array $fieldConfig
     *
     * @return string
     */
    protected function determineFilterType(array $fieldConfig) : string
    {
        $type = 'string';
        switch ($fieldConfig['config']['type']) {
            case 'check':
                $type = 'boolean';
                break;
            case 'select':
            case 'input':
                if (isset($fieldConfig['config']['eval'])) {
                    if (strpos($fieldConfig['config']['eval'], 'double2') !== false) {
                        $type = 'double';
                        break;
                    }
                    if (strpos($fieldConfig['config']['eval'], 'datetime') !== false) {
                        $type = 'datetime';
                        break;
                    }
                    if (strpos($fieldConfig['config']['eval'], 'date') !== false) {
                        $type = 'date';
                        break;
                    }
                    if (strpos($fieldConfig['config']['eval'], 'time') !== false) {
                        $type = 'time';
                        break;
                    }
                    if (strpos($fieldConfig['config']['eval'], 'int') !== false) {
                        $type = 'integer';
                        break;
                    }
                    if (strpos($fieldConfig['config']['eval'], 'num') !== false) {
                        $type = 'integer';
                        break;
                    }
                }
                break;
        }

        return $type;
    }

    /**
     * @param array $fieldConfig
     *
     * @return string
     */
    protected function determineFilterInput(array $fieldConfig) : string
    {
        $input = 'text';
        switch ($fieldConfig['config']['type']) {
            case 'check':
                $input = 'checkbox';
                break;
            case 'select':
                $input = 'select';
                break;
            case 'input':
                if (isset($fieldConfig['config']['eval'])) {
                    if (strpos($fieldConfig['config']['eval'], 'double2') !== false) {
                        $input = 'number';
                    }
                    if (strpos($fieldConfig['config']['eval'], 'int') !== false) {
                        $input = 'number';
                    }
                }
                break;
        }

        return $input;
    }

    /**
     * @param array $fieldConfig
     * @return array
     */
    protected function determineFilterValues(array $fieldConfig) : array
    {
        $values = [];
        switch ($fieldConfig['config']['type']) {
            case 'select':
                if (!empty($fieldConfig['config']['items'])) {
                    foreach ($fieldConfig['config']['items'] as $item) {
                        $tmp = new stdClass();
                        $tmp->{$item[1]} = $item[0];
                        $values[] = $tmp;
                    }
                }
                break;
            case 'check':
                $values[] = 1;
                break;
        }

        return $values;
    }

    /**
     * @param stdClass $filter
     */
    protected function determineAndAddExtras(&$filter)
    {
        if ($filter->type === 'date'
            || $filter->type === 'datetime'
            || $filter->type === 'time') {
            $filter->validation = new stdClass();
            $filter->plugin = 'datetimepicker';
            $filter->plugin_config = new stdClass();
            $filter->plugin_config->sideBySide = true;
            $filter->plugin_config->icons = new stdClass();
            $filter->plugin_config->icons->time = 'fa fa-clock-o';
            $filter->plugin_config->icons->date = 'fa fa-calendar';
            $filter->plugin_config->icons->up = 'fa fa-chevron-up';
            $filter->plugin_config->icons->down = 'fa fa-chevron-down';
            $filter->plugin_config->icons->previous = 'fa fa-chevron-left';
            $filter->plugin_config->icons->next = 'fa fa-chevron-right';
            $filter->plugin_config->icons->today = 'fa fa-calendar-o';
            $filter->plugin_config->icons->clear = 'fa fa-trash';
            switch ($filter->type) {
                case 'datetime':
                    $filter->plugin_config->format = self::FORMAT_DATETIME;
                    break;
                case 'date':
                    $filter->plugin_config->format = self::FORMAT_DATE;
                    break;
                case 'time':
                    $filter->plugin_config->format = self::FORMAT_TIME;
                    break;
            }
            $filter->validation->format = $filter->plugin_config->format;
        }
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    protected function prepareTca($tableName, $pageId) : array
    {
        $formDataGroup = GeneralUtility::makeInstance(TcaOnly::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);
        $siteResolver = GeneralUtility::makeInstance(SiteResolving::class);

        $formDataCompilerInput = [
            'tableName' => $tableName,
            'command' => 'new',
            'effectivePid' => (int)$pageId
        ];
        $formDataCompilerInput = $siteResolver->addData($formDataCompilerInput);

        return $formDataCompiler->compile($formDataCompilerInput);
    }
}
