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

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DOUBLE = 'double';
    const TYPE_DATE = 'date';
    const TYPE_TIME= 'time';
    const TYPE_DATETIME = 'datetime';

    /**
     * @param stdClass $queryObject
     * @param string $table
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function parse(stdClass $queryObject, string $table) : string
    {
        $condition = $queryObject->condition === static::CONDITION_AND
            ? static::CONDITION_AND
            : static::CONDITION_OR;
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

        return empty($whereParts) ? '' :
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

        // determine the correct database type for quoting
        switch ($rule->type) {
            case static::TYPE_INTEGER:
                $databaseType = \PDO::PARAM_INT;
                break;
            case static::TYPE_BOOLEAN:
                $databaseType = \PDO::PARAM_BOOL;
                break;
            case static::TYPE_DOUBLE:
                $unQuotedValue = str_replace(',', '.', $unQuotedValue);
                $databaseType = \PDO::PARAM_STR;
                break;
            case static::TYPE_DATE:
            case static::TYPE_TIME:
            case static::TYPE_DATETIME:
                $databaseType = \PDO::PARAM_INT;
                break;
            case static::TYPE_STRING:
            default:
                $databaseType = \PDO::PARAM_STR;
                break;
        }

        // Field is a date string and must be converted
        if ($rule->type === static::TYPE_DATETIME || $rule->type === static::TYPE_DATE) {
            // @TODO: TCA supports dbType = date and dbType = datetime, this must be handled different.
            if (is_array($unQuotedValue)) {
                if ($unQuotedValue[0] !== null) {
                    $unQuotedValue[0] = (new \DateTime($unQuotedValue[0]))->getTimestamp();
                    $unQuotedValue[0] -= date('Z', $unQuotedValue[0]);
                }
                if ($unQuotedValue[1] !== null) {
                    $unQuotedValue[1] = (new \DateTime($unQuotedValue[1]))->getTimestamp();
                    $unQuotedValue[1] -= date('Z', $unQuotedValue[1]);
                }
            } else {
                if ($unQuotedValue !== null) {
                    $unQuotedValue = (new \DateTime($unQuotedValue))->getTimestamp();
                    $unQuotedValue -= date('Z', $unQuotedValue);
                }
            }
        }

        // Field is a date string and must be converted
        if ($rule->type === static::TYPE_TIME) {
            if (is_array($unQuotedValue)) {
                if ($unQuotedValue[0] !== null) {
                    list($hours, $minutes) = GeneralUtility::intExplode(':', $unQuotedValue[0]);
                    $unQuotedValue[0] = ($hours * 60 * 60) + ($minutes * 60);
                }
                if ($unQuotedValue[1] !== null) {
                    list($hours, $minutes) = GeneralUtility::intExplode(':', $unQuotedValue[1]);
                    $unQuotedValue[1] = ($hours * 60 * 60) + ($minutes * 60);
                }
            } else {
                if ($unQuotedValue !== null) {
                    list($hours, $minutes) = GeneralUtility::intExplode(':', $unQuotedValue);
                    $unQuotedValue = ($hours * 60 * 60) + ($minutes * 60);
                }
            }
        }

        // Quote all values
        if (is_array($unQuotedValue)) {
            if ($databaseType === \PDO::PARAM_INT) {
                $quotedValue[0] = (int)$unQuotedValue[0];
                $quotedValue[1] = (int)$unQuotedValue[1];
                if ($rule->type === static::TYPE_BOOLEAN) {
                    $quotedValue = (int)$unQuotedValue[0];
                }
            } else {
                $quotedValue[0] = $queryBuilder->quote($unQuotedValue[0], $databaseType);
                $quotedValue[1] = $queryBuilder->quote($unQuotedValue[1], $databaseType);
                if ($rule->type === static::TYPE_BOOLEAN) {
                    $quotedValue = $queryBuilder->quote($unQuotedValue[0], $databaseType);
                }
            }
        } else {
            if ($databaseType === \PDO::PARAM_INT) {
                $quotedValue = (int)$unQuotedValue;
            } else {
                $quotedValue = $queryBuilder->quote($unQuotedValue, $databaseType);
            }
        }

        switch ($rule->operator) {
            case static::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $quotedValue);
                break;
            case static::OPERATOR_NOT_EQUAL:
                $where = $queryBuilder->expr()->neq($field, $quotedValue);
                break;
            case static::OPERATOR_IN:
                $values = [$unQuotedValue];
                if (is_string($unQuotedValue)) {
                    $values = $this->splitString($unQuotedValue);
                }
                $escapedValues = [];
                foreach ($values as $singleValue) {
                    $escapedValues[] = $queryBuilder->quote($singleValue);
                }
                $where = $queryBuilder->expr()->in($field, implode(',', $escapedValues));
                break;
            case static::OPERATOR_NOT_IN:
                $values = [$unQuotedValue];
                if (is_string($unQuotedValue)) {
                    $values = $this->splitString($unQuotedValue);
                }
                $escapedValues = [];
                foreach ($values as $singleValue) {
                    $escapedValues[] = $queryBuilder->quote($singleValue);
                }
                $where = $queryBuilder->expr()->notIn($field, implode(',', $escapedValues));
                break;
            case static::OPERATOR_BEGINS_WITH:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal($this->quoteLikeValue($unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_NOT_BEGINS_WITH:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal($this->quoteLikeValue($unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_CONTAINS:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal('%' . $this->quoteLikeValue($unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_NOT_CONTAINS:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal('%' . $this->quoteLikeValue($unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_ENDS_WITH:
                $where = $queryBuilder->expr()->like(
                    $field,
                    $queryBuilder->expr()->literal('%' . $this->quoteLikeValue($unQuotedValue))
                );
                break;
            case static::OPERATOR_NOT_ENDS_WITH:
                $where = $queryBuilder->expr()->notLike(
                    $field,
                    $queryBuilder->expr()->literal('%' . $this->quoteLikeValue($unQuotedValue))
                );
                break;
            case static::OPERATOR_IS_EMPTY:
                $where = (string)$queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq($field, $queryBuilder->expr()->literal('')),
                    $queryBuilder->expr()->isNull($field)
                );
                break;
            case static::OPERATOR_IS_NOT_EMPTY:
                $where = (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->neq($field, $queryBuilder->expr()->literal('')),
                    $queryBuilder->expr()->isNotNull($field)
                );
                break;
            case static::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);
                break;
            case static::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);
                break;
            case static::OPERATOR_LESS:
                $where = $queryBuilder->expr()->lt($field, $quotedValue);
                break;
            case static::OPERATOR_LESS_OR_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $quotedValue);
                break;
            case static::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $quotedValue);
                break;
            case static::OPERATOR_GREATER_OR_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $quotedValue);
                break;
            case static::OPERATOR_BETWEEN:
                $where = (string)$queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt($field, $quotedValue[0]),
                    $queryBuilder->expr()->lt($field, $quotedValue[1])
                );
                break;
            case static::OPERATOR_NOT_BETWEEN:
                $where = (string)$queryBuilder->expr()->orX(
                    $queryBuilder->expr()->lt($field, $quotedValue[0]),
                    $queryBuilder->expr()->gt($field, $quotedValue[1])
                );
                break;
        }

        return $where;
    }

    /**
     * This method split the given string into chunks and return
     * it as array. As delimiter a set of special character is used:
     * - ; (semicolon)
     * - + (plus)
     * - # (hash)
     * - | (pipe)
     * - ! (exlamationmark)
     *
     * @param string $string
     * @param string $pattern
     *
     * @return array
     */
    protected function splitString(string $string, string $pattern = '/[;+#|!]/') : array
    {
        return array_map('trim', preg_split($pattern, $string));
    }

    /**
     * @param $unQuotedValue
     *
     * @return string
     */
    protected function quoteLikeValue($unQuotedValue) : string
    {
        return addcslashes($unQuotedValue, '%_');
    }
}
