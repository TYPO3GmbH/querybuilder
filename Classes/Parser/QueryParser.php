<?php
declare(strict_types=1);

namespace T3G\Querybuilder\Parser;

use stdClass;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    const OPERATOR_IS_EMPTY = 'is_empty';
    const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    const OPERATOR_IS_NULL = 'is_null';
    const OPERATOR_IS_NOT_NULL = 'is_not_null';
    const OPERATOR_LESS = 'less';
    const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_NOT_BETWEEN = 'not_between';

    const CONDITION_AND = 'AND';
    const CONDITION_OR = 'OR';

    const FORMAT_DATETIME = 'Y-m-d H:i';
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_TIME = 'H:i';
    const FORMAT_TIMESEC = 'H:i:s';
    const FORMAT_YEAR = 'Y';

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOlEAN = 'boolean';
    const TYPE_DOUBLE = 'double';
    const TYPE_DATE = 'date';
    const TYPE_TIME= 'time';
    const TYPE_DATETIME = 'datetime';

    /**
     * @param stdClass $queryObject
     * @param string $table
     *
     * @return string
     */
    public function parse($queryObject, $table) : string
    {
        $condition = $queryObject->condition ===
        self::CONDITION_AND ?
            self::CONDITION_AND :
            self::CONDITION_OR;
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

        return empty($whereParts) ?
            '' :
            ' ( ' .
            implode(' ' .
                $condition .
                ' ', $whereParts) .
            ' ) ';
    }

    /**
     * @param stdClass $rule
     * @param string $table
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getWhereClause(stdClass $rule, string $table) : string
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        $where = '';
        $field = $rule->field;
        $unQuotedValue = $rule->value;

        switch ($rule->type) {
            case self::TYPE_INTEGER:
                $databaseType = \PDO::PARAM_INT;
                break;
            case self::TYPE_BOOlEAN:
                $databaseType = \PDO::PARAM_BOOL;
                break;
            case self::TYPE_DATE:
            case self::TYPE_TIME:
            case self::TYPE_DATETIME:
            case self::TYPE_DOUBLE:
            case self::TYPE_STRING:
                $databaseType = \PDO::PARAM_STR;
                break;
            default:
                $databaseType = \PDO::PARAM_STR;
                break;
        }
        if ($rule->operator !== self::OPERATOR_BETWEEN &&  $rule->operator !== self::OPERATOR_NOT_BETWEEN) {
            $quotedValue = $queryBuilder->quote($unQuotedValue, $databaseType);
        }

        switch ($rule->operator) {
            case self::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $quotedValue);
                break;
            case self::OPERATOR_NOT_EQUAL:
                $where = $queryBuilder->expr()->neq($field, $quotedValue);
                break;
            case self::OPERATOR_IN:
                $values = GeneralUtility::trimExplode(',', $unQuotedValue);
                $escapedValues = [];
                foreach ($values as $quotedValue) {
                    $escapedValues[] = $queryBuilder->quote($quotedValue);
                }
                $where = $queryBuilder->expr()->in($field, implode(',', $escapedValues));
                break;
            case self::OPERATOR_NOT_IN:
                $values = GeneralUtility::trimExplode(',', $unQuotedValue);
                $escapedValues = [];
                foreach ($values as $quotedValue) {
                    $escapedValues[] = $queryBuilder->quote($quotedValue);
                }
                $where = $queryBuilder->expr()->notIn($field, implode(',', $escapedValues));
                break;
            case self::OPERATOR_BEGINS_WITH:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal($unQuotedValue . '%')
                );
                break;
            case self::OPERATOR_NOT_BEGINS_WITH:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal($unQuotedValue . '%')
                );
                break;
            case self::OPERATOR_CONTAINS:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal('%' . $unQuotedValue . '%')
                );
                break;
            case self::OPERATOR_NOT_CONTAINS:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal('%' . $unQuotedValue . '%')
                );
                break;
            case self::OPERATOR_ENDS_WITH:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal('%' . $unQuotedValue)
                );
                break;
            case self::OPERATOR_NOT_ENDS_WITH:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal('%' . $unQuotedValue)
                );
                break;
            case self::OPERATOR_IS_EMPTY:
                $where = (string)$queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq($field, $queryBuilder->expr()->literal('')),
                    $queryBuilder->expr()->isNull($field)
                );
                break;
            case self::OPERATOR_IS_NOT_EMPTY:
                $where = (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->neq($field, $queryBuilder->expr()->literal('')),
                    $queryBuilder->expr()->isNotNull($field)
                );
                break;
            case self::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);
                break;
            case self::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);
                break;
            case self::OPERATOR_LESS:
                $where = $queryBuilder->expr()->lt($field, $quotedValue);
                break;
            case self::OPERATOR_LESS_OR_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $quotedValue);
                break;
            case self::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $quotedValue);
                break;
            case self::OPERATOR_GREATER_OR_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $quotedValue);
                break;
            case self::OPERATOR_BETWEEN:
//                $values = GeneralUtility::trimExplode(',', $unQuotedValue);
                $quotedValue1 = $queryBuilder->quote($unQuotedValue[0], $databaseType);
                $quotedValue2 = $queryBuilder->quote($unQuotedValue[1], $databaseType);
                $where = (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt($field, $quotedValue1),
                    $queryBuilder->expr()->lt($field, $quotedValue2)
                );
                break;
            case self::OPERATOR_NOT_BETWEEN:
//                $values = GeneralUtility::trimExplode(',', (string)$unQuotedValue);
                $quotedValue1 = $queryBuilder->quote($unQuotedValue[0], $databaseType);
                $quotedValue2 = $queryBuilder->quote($unQuotedValue[1], $databaseType);
                $where = (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->lt($field, $quotedValue1),
                    $queryBuilder->expr()->gt($field, $quotedValue2)
                );
                break;
        }

        return $where;
    }

    /**
     * Prepare incoming values. E.g. parse date string into timestamp.
     *
     * @param stdClass $rule
     *
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
            if ($format !==
                null
            ) {
                $date = \DateTime::createFromFormat($format, $value);
                $value = $date->getTimestamp();
            }
        }

        return count($values) ===
        1 ?
            $values[0] :
            $values;
    }
}
