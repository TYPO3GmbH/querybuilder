<?php

namespace T3G\Querybuilder\Hooks;

use TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DatabaseRecordList implements RecordListGetTableHookInterface {

    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_NOT_EQUAL = 'not_equal';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'not_in';
    const OPERATOR_BEGINS_WITH = 'begins_with';
    const OPERATOR_NOT_BEGINS_WITH = 'not_begins_with';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_NOT_CONTAINS = 'not_contains';
    const OPERATOR_ENDS_WITH = 'ends_with';
    const OPERATOR_NOT_ENDS_WITH = 'not_ends_with';
    const OPERATOR_IS_EMPTY = 'is_empty';
    const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    const OPERATOR_IS_NULL= 'is_null';
    const OPERATOR_IS_NOT_NULL = 'is_not_null';

    /**
     * modifies the DB list query
     *
     * @param string $table The current database table
     * @param int $pageId The record's page ID
     * @param string $additionalWhereClause An additional WHERE clause
     * @param string $selectedFieldsList Comma separated list of selected fields
     * @param \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList $parentObject Parent \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList object
     *
     * @return void
     */
    public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject)
    {
        if (GeneralUtility::_GP('M') === 'web_list' && GeneralUtility::_GP('table') !== null) {
            $query = GeneralUtility::_GP('query');
            if ($query !== null) {
                $query = json_decode($query);
            }
            $condition = $query->condition === 'AND' ? 'AND' : 'OR';
            if (!empty($query->rules)) {
                foreach ($query->rules as $rule) {
                    $additionalWhereClause .= ' ' . $condition . ' ' . $this->getWhereClause($rule, $table);
                }
            }
        }
    }

    /**
     * @param \stdClass $rule
     * @param string $table
     *
     * @return string
     */
    protected function getWhereClause($rule, $table)
    {
        $dbConnection = $this->getDatabaseConnection();
        $where = '';
        $field = $dbConnection->quoteStr($rule->field, $table) . ' ';
        $value = $dbConnection->fullQuoteStr($rule->value, $table);
        switch ($rule->operator) {
            case self::OPERATOR_EQUAL:
                $where = $field . '=' . $value;
                break;
            case self::OPERATOR_NOT_EQUAL:
                $where = $field . '!=' . $value;
                break;
            case self::OPERATOR_IN:
            case self::OPERATOR_NOT_IN:
                $values = GeneralUtility::trimExplode(',', $rule->value);
                $escapedValues = [];
                foreach ($values as $value) {
                    $escapedValues[] = $dbConnection->fullQuoteStr($value, $table);
                }
                $negation = $rule->operator === self::OPERATOR_NOT_IN ? 'NOT ' : '';
                $where = $field . $negation . 'IN (' . implode(',', $escapedValues) . ')';
                break;
            case self::OPERATOR_BEGINS_WITH:
            case self::OPERATOR_NOT_BEGINS_WITH:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_BEGINS_WITH ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr($value . '%', $table);
                break;
            case self::OPERATOR_CONTAINS:
            case self::OPERATOR_NOT_CONTAINS:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_CONTAINS ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr('%' . $value . '%', $table);
                break;
            case self::OPERATOR_ENDS_WITH:
            case self::OPERATOR_NOT_ENDS_WITH:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_ENDS_WITH ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr('%' . $value, $table);
                break;
            case self::OPERATOR_IS_EMPTY:
            case self::OPERATOR_IS_NOT_EMPTY:
                $negation = $rule->operator === self::OPERATOR_IS_NOT_EMPTY ? '!' : '';
                $where = $field . $negation . '= \'\'';
                break;
            case self::OPERATOR_IS_NULL:
            case self::OPERATOR_IS_NOT_NULL:
                $negation = $rule->operator === self::OPERATOR_IS_NOT_NULL ? 'NOT' : '';
                $where = $field . 'IS ' . $negation . ' NULL';
                break;
        }
        return $where;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}