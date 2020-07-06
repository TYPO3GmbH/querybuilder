<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder;

use stdClass;
use T3G\Querybuilder\Entity\Filter;
use T3G\Querybuilder\Factory\FilterFactory;
use T3G\Querybuilder\Factory\PluginFactory;
use T3G\Querybuilder\Factory\ValidationFactory;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param int $pageId
     * @return Filter[]
     */
    public function buildFilterFromTca(string $table, int $pageId) : array
    {
        $dataProviderResult = $this->prepareTca($table, $pageId);
        $TCA = $dataProviderResult['processedTca'];
        $filters = [];
        $filterFields = !empty($TCA['ctrl']['queryFilterFields']) ? $TCA['ctrl']['queryFilterFields'] : $TCA['ctrl']['searchFields'];
        $filterFields = GeneralUtility::trimExplode(',', $filterFields);
        foreach ($filterFields as $filterField) {
            $fieldConfig = $TCA['columns'][$filterField];
            if (!\is_array($fieldConfig)) {
                // if a filter field has no column declaration continue...
                continue;
            }
            // Filter:Types: string, integer, double, date, time, datetime and boolean.
            // Filter:Required: id, type, values*
            $filter = GeneralUtility::makeInstance(FilterFactory::class)
                ->create([
                    'id' => $filterField,
                    'type' => $this->determineFilterType($fieldConfig),
                    'input' => $this->determineFilterInput($fieldConfig),
                    'values' => $this->determineFilterValues($fieldConfig),
                    'label' => $fieldConfig['label'],
                    'description' => $fieldConfig['description'] ?? '',
                ]);
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
                    if (strpos($fieldConfig['config']['eval'], 'int') !== false
                        || strpos($fieldConfig['config']['eval'], 'num') !== false) {
                        $type = 'integer';
                        break;
                    }
                }
                break;
            default:
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
                if (!empty($fieldConfig['config']['eval'])
                    && (strpos($fieldConfig['config']['eval'], 'double2') !== false
                        || strpos($fieldConfig['config']['eval'], 'int') !== false)
                ) {
                    $input = 'number';
                }
                break;
            default:
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
        $fieldConfigType = $fieldConfig['config']['type'];
        $fieldConfigItems = $fieldConfig['config']['items'];
        if ($fieldConfigType === 'select') {
            if (!empty($fieldConfigItems)) {
                foreach ($fieldConfigItems as $item) {
                    $tmp = new stdClass();
                    $tmp->{$item[1]} = $item[0];
                    $values[] = $tmp;
                }
            }
        } elseif ($fieldConfigType === 'check') {
            $values[] = 1;
        }

        return $values;
    }

    /**
     * @param Filter $filter
     */
    protected function determineAndAddExtras(Filter $filter): void
    {
        if (\in_array($filter->getType(), ['date', 'datetime', 'time'], true)) {
            $pluginConfiguration = [
                'sideBySide' => true,
                'icons' => [
                    'time' => 'fa fa-clock-o',
                    'date' => 'fa fa-calendar',
                    'up' => 'fa fa-chevron-up',
                    'down' => 'fa fa-chevron-down',
                    'previous' => 'fa fa-chevron-left',
                    'next' => 'fa fa-chevron-right',
                    'today' => 'fa fa-calendar-o',
                    'clear' => 'fa fa-trash',
                ]
            ];
            switch ($filter->getType()) {
                case 'datetime':
                    $pluginConfiguration['format'] = self::FORMAT_DATETIME;
                    break;
                case 'date':
                    $pluginConfiguration['format'] = self::FORMAT_DATE;
                    break;
                case 'time':
                    $pluginConfiguration['format'] = self::FORMAT_TIME;
                    break;
                default:
            }
            $filter->setPlugin(
                GeneralUtility::makeInstance(PluginFactory::class)
                    ->create([
                        'identifier' => 'datetimepicker',
                        'configuration' => $pluginConfiguration,
                    ])
            );

            if (!empty($pluginConfiguration['format'])) {
                $filter->setValidation(
                    GeneralUtility::makeInstance(ValidationFactory::class)
                        ->create([
                            'format' => $pluginConfiguration['format'],
                        ])
                );
            }
        }
    }

    /**
     * @param string $tableName
     * @param int $pageId
     * @return array
     */
    protected function prepareTca(string $tableName, int $pageId) : array
    {
        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class, $formDataGroup);

        $formDataCompilerInput = [
            'tableName' => $tableName,
            'command' => 'new',
            'effectivePid' => $pageId,
            'vanillaUid' => $pageId
        ];

        return $formDataCompiler->compile($formDataCompilerInput);
    }
}
