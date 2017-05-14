<?php

namespace T3G\Querybuilder\Parser;

use stdClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class QueryParser.
 */
class QueryParser
{
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
    const OPERATOR_IS_NULL = 'is_null';
    const OPERATOR_IS_NOT_NULL = 'is_not_null';
    const OPERATOR_LESS = 'less';
    const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';

    const CONDITION_AND = 'AND';
    const CONDITION_OR = 'OR';

    const FORMAT_DATETIME = 'Y-m-d H:i';
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_TIME = 'H:i';
    const FORMAT_TIMESEC = 'H:i:s';
    const FORMAT_YEAR = 'Y';

    /**
     * @param stdClass $queryObject
     * @param string $table

     *
     * @return string
     */
    public function parse($queryObject, $table)
    {
        $condition = $queryObject->condition === self::CONDITION_AND ? self::CONDITION_AND : self::CONDITION_OR;
        $whereParts = [];
        if (!empty($queryObject->rules)) {
            foreach ($queryObject->rules as $rule) {
                if ($rule->condition && $rule->rules) {
                    $whereParts[] = $this->parse($rule, $table);
                } else {
                    $whereParts[] = $this->getWhereClause($rule, $table);
                }
            }
        }

        return ' ( ' . implode(' ' . $condition . ' ', $whereParts) . ' ) ';
    }

    /**
     * @param stdClass $rule
     * @param string $table
     *
     * @return string
     */
    protected function getWhereClause(stdClass $rule, string $table) : string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $where = '';
        $field = $rule->field;
        $value = $queryBuilder->quote($rule->value);
        switch ($rule->operator) {
            case self::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq(
                                $field,
                                $value
                        );
                break;
            case self::OPERATOR_NOT_EQUAL:
                $where = $queryBuilder->expr()->neq(
                                $field,
                                $value
                        );
                break;
            case self::OPERATOR_IN:
                $values = GeneralUtility::trimExplode(',', $rule->value);
                $escapedValues = [];
                foreach ($values as $value) {
                    // todo delete/change
                    $escapedValues[] = $queryBuilder->createNamedParameter($value);
                }
                $where = $queryBuilder->expr()->in(
                        $field,
                        implode(',', $escapedValues)
                );
                break;
            case self::OPERATOR_NOT_IN:
                $values = GeneralUtility::trimExplode(',', $rule->value);
                $escapedValues = [];
                foreach ($values as $value) {
                    // todo delete/change
                    $escapedValues[] = $queryBuilder->createNamedParameter($value);
                }
                $where = $queryBuilder->expr()->notIn(
                        $field,
                        implode(',', $escapedValues)
                );
                break;
            case self::OPERATOR_BEGINS_WITH:
                $where = $queryBuilder->expr()->like(
                        $field,
                        $queryBuilder->literal($rule->value . '%')
                );
                break;
            case self::OPERATOR_NOT_BEGINS_WITH:
                $where = $queryBuilder->expr()->notLike(
                        $field,
                        $queryBuilder->literal($rule->value . '%')
                );
                break;
            case self::OPERATOR_CONTAINS:
                $where = $queryBuilder->expr()->like(
                        $field,
                        $queryBuilder->literal('%' . $rule->value . '%')
                );
                break;
            case self::OPERATOR_NOT_CONTAINS:
                $where = $queryBuilder->expr()->notLike(
                        $field,
                        $queryBuilder->literal('%' . $rule->value . '%')
                );
                break;
            case self::OPERATOR_ENDS_WITH:
                $where = $queryBuilder->expr()->like(
                        $field,
                        $queryBuilder->literal('%' . $rule->value)
                );
                break;
            case self::OPERATOR_NOT_ENDS_WITH:
                $where = $queryBuilder->expr()->notLike(
                        $field,
                        $queryBuilder->literal('%' . $rule->value)
                );
                break;
            case self::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull(
                        $field
                );
                break;
            case self::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull(
                        $field
                );
                break;
            case self::OPERATOR_LESS:
                $where = $queryBuilder->expr()->lt(
                                $field,
                                $value
                        );
                break;
            case self::OPERATOR_LESS_OR_EQUAL:
                $where = $queryBuilder->expr()->lte(
                                $field,
                                $value
                        );
                break;
            case self::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt(
                                $field,
                                $value
                        );
                break;
            case self::OPERATOR_GREATER_OR_EQUAL:
                $where = $queryBuilder->expr()->gte(
                                $field,
                                $value
                        );
                break;
        }

        return $where;
    }

    /**
     * Prepare incoming values. E.g. parse date string into timestamp.
     *
     * @param stdClass $rule
     * @return mixed
     */
    protected function getValue($rule)
    {
        $values = $rule->value;
        if (!is_array($values)) {
            $values = [$values];
        }
        $format = null;
        switch ($rule->type) {
            case 'datetime':
                $format = self::FORMAT_DATETIME;
                break;
            case 'date':
                $format = self::FORMAT_DATE;
                break;
            case 'time':
                $format = self::FORMAT_TIME;
                break;
            case 'timesec':
                $format = self::FORMAT_TIMESEC;
                break;
            case 'year':
                $format = self::FORMAT_YEAR;
                break;
        }
        foreach ($values as &$value) {
            if ($format !== null) {
                $date = \DateTime::createFromFormat($format, $value);
                $value = $date->getTimestamp();
            }
        }
        return count($values) === 1 ? $values[0] : $values;
    }
}
