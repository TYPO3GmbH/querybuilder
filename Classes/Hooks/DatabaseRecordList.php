<?php

namespace T3G\Querybuilder\Hooks;

use T3G\Querybuilder\Parser\QueryParser;
use TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DatabaseRecordList.
 */
class DatabaseRecordList implements RecordListGetTableHookInterface
{
    /**
     * modifies the DB list query.
     *
     * @param string                                              $table                 The current database table
     * @param int                                                 $pageId                The record's page ID
     * @param string                                              $additionalWhereClause An additional WHERE clause
     * @param string                                              $selectedFieldsList    Comma separated list of selected fields
     * @param \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList $parentObject          Parent \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList object
     *
     * @throws \InvalidArgumentException
     */
    public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject)
    {
        if (GeneralUtility::_GP('M') === 'web_list' && GeneralUtility::_GP('table') !== null) {
            $query = GeneralUtility::_GP('query');
            if ($query !== null) {
                $query = json_decode($query);
                $queryParser = GeneralUtility::makeInstance(QueryParser::class);
                $additionalWhereClause .= ' AND ' . $queryParser->parse($query, $table);
            }
        }
    }
}
