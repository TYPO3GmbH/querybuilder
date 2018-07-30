<?php

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Hooks;

use T3G\Querybuilder\Parser\QueryParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList;

/**
 * Class DatabaseRecordList.
 */
class DatabaseRecordList
{

    /**
     * @param array $parameters parameters
     * @param string $table the current database table
     * @param int $pageId the records' page ID
     * @param array $additionalConstraints additional constraints
     * @param array $fieldList field list
     * @param AbstractDatabaseRecordList $parentObject
     *
     * @throws \InvalidArgumentException
     */
    public function buildQueryParametersPostProcess(
        array &$parameters,
                                   string $table,
                                   int $pageId,
                                   array $additionalConstraints,
                                   array $fieldList,
                                   AbstractDatabaseRecordList $parentObject
    ) {
        if ($parentObject->table !== null && GeneralUtility::_GP('M') === 'web_list') {
            $query = GeneralUtility::_GP('query');
            if ($query !== null) {
                $query = json_decode($query);
                if ($query) {
                    $parameters['where'][] = GeneralUtility::makeInstance(QueryParser::class)
                        ->parse($query, $table);
                }
            }
        }
    }
}
