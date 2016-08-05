<?php

namespace T3G\Querybuilder;

use T3G\Querybuilder\Backend\Form\FormDataGroup\TcaOnly;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class QueryParser.
 */
class QueryBuilder
{

    /**
     * Build the filter configuration from TCA
     *
     * @param string $table
     *
     * @return array
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function buildFilterFromTca($table)
    {
        $dataProviderResult = $this->prepareTca($table);
        $TCA = $dataProviderResult['processedTca'];
        $filters = [];

        $filterFields = !empty($TCA['ctrl']['queryFilterFields']) ? $TCA['ctrl']['queryFilterFields'] : $TCA['ctrl']['searchFields'];
        $filterFields = GeneralUtility::trimExplode(',', $filterFields);
        $languageService = $this->getLanguageService();
        foreach ($filterFields as $filterField) {
            $fieldConfig = $TCA['columns'][$filterField];
            if (!is_array($fieldConfig)) {
                // if a filter field has no column declaration continue...
                continue;
            }
            // Filter:Types: string, integer, double, date, time, datetime and boolean.
            // Filter:Required: id, type, values*
            $filter = new \stdClass();
            $filter->id = $filterField;
            $filter->type = $this->determineFilterType($fieldConfig);
            $filter->input = $this->determineFilterInput($fieldConfig);
            $filter->values = $this->determineFilterValues($fieldConfig);
            $filter->label = $languageService->sL($fieldConfig['label']);
            $this->determineAndAddExtras($filter, $fieldConfig);
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
    protected function determineFilterType(array $fieldConfig)
    {
        $type = 'string';
        switch ($fieldConfig['config']['type']) {
            case 'check':
                $type = 'boolean';
                break;
            case 'select':
            case 'input':
                if (strpos($fieldConfig['config']['eval'], 'double2') !== false) {
                    $type = 'double';
                }
                //if (strpos($fieldConfig['config']['eval'], 'date') !== false) {
                //    $type = 'date';
                //}
                //if (strpos($fieldConfig['config']['eval'], 'datetime') !== false) {
                //    $type = 'datetime';
                //}
                break;
        }

        return $type;
    }

    /**
     * @param array $fieldConfig
     *
     * @return string
     */
    protected function determineFilterInput(array $fieldConfig)
    {
        $input = 'text';
        switch ($fieldConfig['config']['type']) {
            case 'check':
                $input = 'checkbox';
                break;
            case 'select':
                $input = 'select';
                break;
        }

        return $input;
    }

    /**
     * @param array $fieldConfig
     *
     * @return string
     */
    protected function determineFilterValues(array $fieldConfig)
    {
        $languageService = $this->getLanguageService();
        $values = [];
        switch ($fieldConfig['config']['type']) {
            case 'select':
                if (!empty($fieldConfig['config']['items'])) {
                    foreach ($fieldConfig['config']['items'] as $item) {
                        $tmp = new \stdClass();
                        $tmp->{$item[1]} = $languageService->sL($item[0]);
                        $values[] = $tmp;
                    }
                }
                break;
        }

        return $values;
    }

    /**
     * @param \stdClass $filter
     * @param array $fieldConfig
     */
    protected function determineAndAddExtras(&$filter, $fieldConfig)
    {
        if ($filter->type === 'date' || $filter->type === 'datetime') {
            $filter->validation = new \stdClass();
            $filter->plugin = 'datetimepicker';
            $filter->plugin_config = new \stdClass();
            $filter->plugin_config->sideBySide = true;
            $filter->plugin_config->icons = new \stdClass();
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
                    $filter->plugin_config->format = 'YYYY-MM-DD HH:mm';
                    break;
                case 'date':
                    $filter->plugin_config->format = 'YYYY-MM-DD';
                    break;
                case 'time':
                    $filter->plugin_config->format = 'HH:mm';
                    break;
                case 'timesec':
                    $filter->plugin_config->format = 'HH:mm:ss';
                    break;
                case 'year':
                    $filter->plugin_config->format = 'YYYY';
                    break;
            }
            $filter->validation->format = $filter->plugin_config->format;
        }
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws \UnexpectedValueException
     *
     * @throws \InvalidArgumentException
     */
    protected function prepareTca($tableName)
    {
        $formDataGroup = GeneralUtility::makeInstance(TcaOnly::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);

        $formDataCompilerInput = [
            'tableName' => $tableName,
            'command' => 'edit',
        ];

        return $formDataCompiler->compile($formDataCompilerInput);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}