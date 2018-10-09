<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Parser;

use stdClass;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QueryParser.
 */
class QueryParser
{
    private const OPERATOR_EQUAL = 'equal';
    private const OPERATOR_NOT_EQUAL = 'not_equal';
    private const OPERATOR_IN = 'in';
    private const OPERATOR_NOT_IN = 'not_in';
    private const OPERATOR_BEGINS_WITH = 'begins_with';
    private const OPERATOR_NOT_BEGINS_WITH = 'not_begins_with';
    private const OPERATOR_CONTAINS = 'contains';
    private const OPERATOR_NOT_CONTAINS = 'not_contains';
    private const OPERATOR_ENDS_WITH = 'ends_with';
    private const OPERATOR_NOT_ENDS_WITH = 'not_ends_with';
    private const OPERATOR_IS_EMPTY = 'is_empty';
    private const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    private const OPERATOR_IS_NULL = 'is_null';
    private const OPERATOR_IS_NOT_NULL = 'is_not_null';
    private const OPERATOR_LESS = 'less';
    private const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    private const OPERATOR_GREATER = 'greater';
    private const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';
    private const OPERATOR_BETWEEN = 'between';
    private const OPERATOR_NOT_BETWEEN = 'not_between';

    private const CONDITION_AND = 'AND';

    private const TYPE_STRING = 'string';
    private const TYPE_INTEGER = 'integer';
    private const TYPE_BOOLEAN = 'boolean';
    private const TYPE_DOUBLE = 'double';
    private const TYPE_DATE = 'date';
    private const TYPE_TIME= 'time';
    private const TYPE_DATETIME = 'datetime';

    /**
     * @param stdClass $filterObject
     * @param QueryBuilder $queryBuilderObject
     *
     * @param int $iteration
     *
     * @return QueryBuilder
     */
    public function parse(stdClass $filterObject, QueryBuilder $queryBuilderObject, int $iteration = 1) : QueryBuilder
    {
        $whereParts = [];
        if (!empty($filterObject->rules)) {
            foreach ($filterObject->rules as $rule) {
                if ($rule->condition && $rule->rules) {
                    $queryBuilderObject = $this->parse($rule, $queryBuilderObject, $iteration++);
                } else {
                    $whereParts[] = $this->getWhereClause($rule, $queryBuilderObject);
                }
            }
        }
        if (!empty($whereParts)) {
            if ($iteration === 1) {
                $filterObject->condition === static::CONDITION_AND
                    ? $queryBuilderObject->andWhere($queryBuilderObject->expr()->andX($queryBuilderObject->expr()->andX(...$whereParts)))
                    : $queryBuilderObject->andWhere($queryBuilderObject->expr()->andX($queryBuilderObject->expr()->orX(...$whereParts)));
            } else {
                $filterObject->condition === static::CONDITION_AND
                    ? $queryBuilderObject->andWhere($queryBuilderObject->expr()->andX(...$whereParts))
                    : $queryBuilderObject->orWhere($queryBuilderObject->expr()->orX(...$whereParts));
            }
        }
        return $queryBuilderObject;
    }

    /**
     * @param stdClass $rule
     * @param QueryBuilder $queryBuilderObject
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getWhereClause(stdClass $rule, QueryBuilder $queryBuilderObject) : string
    {
        $where = '';
        $field = $rule->field;
        $unQuotedValue = ($rule->type === static::TYPE_DOUBLE)
            ? str_replace(',', '.', $rule->value)
            : $rule->value;

        $databaseType = $this->determineDatabaseType($rule->type);

        // @TODO: This method is very long and complex
        // @TODO: Think about refactoring this method and split it up into
        // @TODO: smaller methods likes $this->determineDatabaseType()
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
            } elseif ($unQuotedValue !== null) {
                $unQuotedValue = (new \DateTime($unQuotedValue))->getTimestamp();
                $unQuotedValue -= date('Z', $unQuotedValue);
            }
        }

        // Field is a date string and must be converted
        if ($rule->type === static::TYPE_TIME) {
            if (is_array($unQuotedValue)) {
                if ($unQuotedValue[0] !== null) {
                    [$hours, $minutes] = GeneralUtility::intExplode(':', $unQuotedValue[0]);
                    $unQuotedValue[0] = ($hours * 60 * 60) + ($minutes * 60);
                }
                if ($unQuotedValue[1] !== null) {
                    [$hours, $minutes] = GeneralUtility::intExplode(':', $unQuotedValue[1]);
                    $unQuotedValue[1] = ($hours * 60 * 60) + ($minutes * 60);
                }
            } elseif ($unQuotedValue !== null) {
                [$hours, $minutes] = GeneralUtility::intExplode(':', $unQuotedValue);
                $unQuotedValue = ($hours * 60 * 60) + ($minutes * 60);
            }
        }

        // Quote all values
        if (is_array($unQuotedValue)) {
            if ($databaseType === \PDO::PARAM_INT) {
                $quotedValue[0] = (int)$unQuotedValue[0];
                $quotedValue[1] = (int)$unQuotedValue[1];
            } elseif ($rule->type === static::TYPE_DOUBLE) {
                $quotedValue[0] = (double)$unQuotedValue[0];
                $quotedValue[1] = (double)$unQuotedValue[1];
            } else {
                $quotedValue[0] = $queryBuilderObject->createNamedParameter($unQuotedValue[0], $databaseType);
                $quotedValue[1] = $queryBuilderObject->createNamedParameter($unQuotedValue[1], $databaseType);
                if ($rule->type === static::TYPE_BOOLEAN) {
                    $quotedValue = $queryBuilderObject->createNamedParameter($unQuotedValue[0], $databaseType);
                }
            }
        } elseif ($databaseType === \PDO::PARAM_INT) {
            $quotedValue = (int)$unQuotedValue;
        } elseif ($rule->type === static::TYPE_DOUBLE) {
            $quotedValue = (double)$unQuotedValue;
        } else {
            $quotedValue = $queryBuilderObject->createNamedParameter($unQuotedValue, $databaseType);
        }

        switch ($rule->operator) {
            case static::OPERATOR_EQUAL:
                $where = $queryBuilderObject->expr()->eq($field, $quotedValue);
                break;
            case static::OPERATOR_NOT_EQUAL:
                $where = $queryBuilderObject->expr()->neq($field, $quotedValue);
                break;
            case static::OPERATOR_IN:
                $values = [$unQuotedValue];
                is_string($unQuotedValue) ?  $values = $this->splitString($unQuotedValue) : null;
                $escapedValues = [];
                foreach ($values as $singleValue) {
                    $escapedValues[] = $queryBuilderObject->createNamedParameter($singleValue);
                }
                $where = $queryBuilderObject->expr()->in($field, implode(',', $escapedValues));
                break;
            case static::OPERATOR_NOT_IN:
                $values = [$unQuotedValue];
                is_string($unQuotedValue) ? $values = $this->splitString($unQuotedValue) : null;
                $escapedValues = [];
                foreach ($values as $singleValue) {
                    $escapedValues[] = $queryBuilderObject->createNamedParameter($singleValue);
                }
                $where = $queryBuilderObject->expr()->notIn($field, implode(',', $escapedValues));
                break;
            case static::OPERATOR_BEGINS_WITH:
                $where = $queryBuilderObject->expr()->like(
                    $field,
                    $queryBuilderObject->expr()->literal($this->quoteLikeValue((string)$unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_NOT_BEGINS_WITH:
                $where = $queryBuilderObject->expr()->notLike(
                    $field,
                    $queryBuilderObject->expr()->literal($this->quoteLikeValue((string)$unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_CONTAINS:
                $where = $queryBuilderObject->expr()->like(
                    $field,
                    $queryBuilderObject->expr()->literal('%' . $this->quoteLikeValue((string)$unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_NOT_CONTAINS:
                $where = $queryBuilderObject->expr()->notLike(
                    $field,
                    $queryBuilderObject->expr()->literal('%' . $this->quoteLikeValue((string)$unQuotedValue) . '%')
                );
                break;
            case static::OPERATOR_ENDS_WITH:
                $where = $queryBuilderObject->expr()->like(
                    $field,
                    $queryBuilderObject->expr()->literal('%' . $this->quoteLikeValue((string)$unQuotedValue))
                );
                break;
            case static::OPERATOR_NOT_ENDS_WITH:
                $where = $queryBuilderObject->expr()->notLike(
                    $field,
                    $queryBuilderObject->expr()->literal('%' . $this->quoteLikeValue((string)$unQuotedValue))
                );
                break;
            case static::OPERATOR_IS_EMPTY:
                $where = (string)$queryBuilderObject->expr()->orX(
                    $queryBuilderObject->expr()->eq($field, $queryBuilderObject->expr()->literal('')),
                    $queryBuilderObject->expr()->isNull($field)
                );
                break;
            case static::OPERATOR_IS_NOT_EMPTY:
                $where = (string)$queryBuilderObject->expr()->andX(
                    $queryBuilderObject->expr()->neq($field, $queryBuilderObject->expr()->literal('')),
                    $queryBuilderObject->expr()->isNotNull($field)
                );
                break;
            case static::OPERATOR_IS_NULL:
                $where = $queryBuilderObject->expr()->isNull($field);
                break;
            case static::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilderObject->expr()->isNotNull($field);
                break;
            case static::OPERATOR_LESS:
                $where = $queryBuilderObject->expr()->lt($field, $quotedValue);
                break;
            case static::OPERATOR_LESS_OR_EQUAL:
                $where = $queryBuilderObject->expr()->lte($field, $quotedValue);
                break;
            case static::OPERATOR_GREATER:
                $where = $queryBuilderObject->expr()->gt($field, $quotedValue);
                break;
            case static::OPERATOR_GREATER_OR_EQUAL:
                $where = $queryBuilderObject->expr()->gte($field, $quotedValue);
                break;
            case static::OPERATOR_BETWEEN:
                $where = (string)$queryBuilderObject->expr()->andX(
                    $queryBuilderObject->expr()->gt($field, $quotedValue[0]),
                    $queryBuilderObject->expr()->lt($field, $quotedValue[1])
                );
                break;
            case static::OPERATOR_NOT_BETWEEN:
                $where = (string)$queryBuilderObject->expr()->orX(
                    $queryBuilderObject->expr()->lt($field, $quotedValue[0]),
                    $queryBuilderObject->expr()->gt($field, $quotedValue[1])
                );
                break;
            default:
        }

        return $where;
    }

    protected function determineDatabaseType(string $ruleType): int
    {
        switch ($ruleType) {
            case static::TYPE_INTEGER:
            case static::TYPE_DATE:
            case static::TYPE_TIME:
            case static::TYPE_DATETIME:
                $databaseType = \PDO::PARAM_INT;
                break;
            case static::TYPE_BOOLEAN:
                $databaseType = \PDO::PARAM_BOOL;
                break;
            case static::TYPE_DOUBLE:
                $databaseType = \PDO::PARAM_STR;
                break;
            case static::TYPE_STRING:
            default:
                $databaseType = \PDO::PARAM_STR;
                break;
        }
        return $databaseType;
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

    protected function quoteLikeValue(string $unQuotedValue) : string
    {
        return addcslashes($unQuotedValue, '%_');
    }
}
