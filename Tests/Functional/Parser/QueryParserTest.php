<?php

namespace T3G\Querybuilder\Tests\Functional\Parser;

use T3G\Querybuilder\Parser\QueryParser;
use T3G\Querybuilder\Tests\Functional\FunctionalTestCase;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 */
class QueryParserTest extends FunctionalTestCase
{
    /** @var  QueryParser */
    protected $subject;

    /**
     * @var string the database table
     */
    protected $table = 'pages';

    /**
     * @var \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $originalTimeZone;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->originalTimeZone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');
        $this->subject = new QueryParser();
        $this->queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->table);
    }

    /**
     *
     */
    protected function tearDown()
    {
        date_default_timezone_set($this->originalTimeZone);
        parent::tearDown();
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleEqualQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => 42]],
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => 42]],
            'float value as type string' => [42.5, 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => 42.5]],
            'string float value as type string' => ['42.5', 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => 42.5]],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => '42,5']],
            'string as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` = :dcValue1', ['dcValue1' => 'foo']],

            'integer value as type integer' => [42, 'integer', 'SELECT  WHERE `title` = 42', []],
            'string as number value as type integer' => ['42', 'integer', 'SELECT  WHERE `title` = 42', []],
            'integer(negative) value as type integer' => [-5, 'integer', 'SELECT  WHERE `title` = -5', []],
            'string(negative) as number value as type integer' => ['-5', 'integer', 'SELECT  WHERE `title` = -5', []],

            'integer(1) value as type boolean' => [[1], 'boolean', 'SELECT  WHERE `title` = :dcValue3', ['dcValue1' => 1, 'dcValue2' => null, 'dcValue3' => 1]],
            'string(1) as number value as type boolean' => [['1'], 'boolean', 'SELECT  WHERE `title` = :dcValue3', ['dcValue1' => '1', 'dcValue2' => null, 'dcValue3' => '1']],
            'integer(0) value as type boolean' => [[0], 'boolean', 'SELECT  WHERE `title` = :dcValue3', ['dcValue1' => 0, 'dcValue2' => null, 'dcValue3' => 0]],
            'string(0) as number value as type boolean' => [['0'], 'boolean', 'SELECT  WHERE `title` = :dcValue3', ['dcValue1' => '0', 'dcValue2' => null, 'dcValue3' => '0']],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` = 42', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` = 42', []],
            'integer(negative)value as type double' => [-5, 'double', 'SELECT  WHERE `title` = -5', []],
            'string(negative) as number value as type double' => ['-5', 'double', 'SELECT  WHERE `title` = -5', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` = 42.5', []],
            'string float value as type double' => ['42.5', 'double', 'SELECT  WHERE `title` = 42.5', []],
            'float value (2 decimal w 00) as type double' => [42.00, 'double', 'SELECT  WHERE `title` = 42', []],
            'float value (2 decimal w 50) as type double' => [42.50, 'double', 'SELECT  WHERE `title` = 42.5', []],
            'float value (2 decimal w 55) as type double' => [42.55, 'double', 'SELECT  WHERE `title` = 42.55', []],
            'string float value (2 decimal) as type double' => ['42.50', 'double', 'SELECT  WHERE `title` = 42.5', []],
            'comma value as type double' => ['42,50', 'double', 'SELECT  WHERE `title` = 42.5', []],
            'comma value (2 decimal) as type double' => ['42,50', 'double', 'SELECT  WHERE `title` = 42.5', []],
            'string as type double' => ['foo', 'double', 'SELECT  WHERE `title` = 0', []],

            'comma value as type date' => ['2017-06-26', 'date', 'SELECT  WHERE `title` = 1498420800', []],

            'comma value as type time' => ['18:30', 'time', 'SELECT  WHERE `title` = 66600', []],

            'string as number value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` = 1483221600', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleEqualQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "equal",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForMultipleEqualsQueryDataProvider() : array
    {
        return [
            'foo, bar, int and date' => [
                '{
                    "condition": "AND",
                    "rules": [
                        {
                            "id": "input_1",
                            "field": "input_1",
                            "type": "string",
                            "input": "text",
                            "operator": "equal",
                            "value": "foo"
                        },
                        {
                            "id": "input_1",
                            "field": "input_1",
                            "type": "string",
                            "input": "text",
                            "operator": "equal",
                            "value": "bar"
                        },
                        {
                            "condition": "OR",
                            "rules": [
                                {
                                    "id": "input_9",
                                    "field": "input_9",
                                    "type": "integer",
                                    "input": "number",
                                    "operator": "equal",
                                    "value": "42"
                                },
                                {
                                    "id": "inputdatetime_2",
                                    "field": "inputdatetime_2",
                                    "type": "date",
                                    "input": "text",
                                    "operator": "equal",
                                    "value": "2017-06-26"
                                }
                            ]
                        }
                    ],
                    "valid": true
                }',
                'SELECT  WHERE ((`input_9` = 42) OR (`inputdatetime_2` = 1498420800)) AND ((`input_1` = :dcValue1) AND (`input_1` = :dcValue2))',
                ['dcValue1' => 'foo', 'dcValue2' => 'bar']
            ],

            'double, time, boolean, datetime' => [
                '{
                    "condition": "AND",
                    "rules": [
                        {
                            "id": "input_8",
                            "field": "input_8",
                            "type": "double",
                            "input": "number",
                            "operator": "equal",
                            "value": "42.42"
                        },
                        {
                            "condition": "AND",
                            "rules": [
                                {
                                    "id": "inputdatetime_5",
                                    "field": "inputdatetime_5",
                                    "type": "time",
                                    "input": "text",
                                    "operator": "equal",
                                    "value": "16:30"
                                },
                                {
                                    "id": "checkbox_2",
                                    "field": "checkbox_2",
                                    "type": "boolean",
                                    "input": "checkbox",
                                    "operator": "equal",
                                    "value": ["1"]
                                }
                            ]
                        },
                        {
                            "condition": "AND",
                            "rules": [
                            {
                                "id": "inputdatetime_4",
                                "field": "inputdatetime_4",
                                "type": "datetime",
                                "input": "text",
                                "operator": "equal",
                                "value": "2017-06-28 16:30"
                            }
                          ]
                        }
                    ],
                    "valid": true
                }',
                'SELECT  WHERE ((`inputdatetime_5` = 59400) AND (`checkbox_2` = :dcValue3)) AND (`inputdatetime_4` = 1498653000) AND (`input_8` = 42.42)',
                ['dcValue1' => '1', 'dcValue2' => null, 'dcValue3' => '1']
            ],

            'simple group' => [
                '{
                  "condition": "AND",
                  "rules": [
                    {
                      "condition": "AND",
                      "rules": [
                        {
                          "id": "header",
                          "field": "header",
                          "type": "string",
                          "input": "text",
                          "operator": "equal",
                          "value": "humbel"
                        },
                        {
                          "id": "header",
                          "field": "header",
                          "type": "string",
                          "input": "text",
                          "operator": "equal",
                          "value": "bumbel"
                        }
                      ]
                    }
                  ],
                  "valid": true
                }',
                'SELECT  WHERE (`header` = :dcValue1) AND (`header` = :dcValue2)',
                ['dcValue1' => 'humbel', 'dcValue2' => 'bumbel']
            ],

            'or condition' => [
                '{
                  "condition": "OR",
                  "rules": [
                    {
                        "id": "header",
                        "field": "header",
                        "type": "string",
                        "input": "text",
                        "operator": "equal",
                        "value": "humbel"
                        },
                        {
                          "id": "header",
                          "field": "header",
                          "type": "string",
                          "input": "text",
                          "operator": "equal",
                          "value": "bumbel"
                    }
                  ],
                  "valid": true
                }',
                'SELECT  WHERE (`header` = :dcValue1) OR (`header` = :dcValue2)',
                ['dcValue1' => 'humbel', 'dcValue2' => 'bumbel']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForMultipleEqualsQueryDataProvider
     *
     * @param $multipleRules
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForMultipleEqualsQuery($multipleRules, $expectedSQL, $expectedParameters)
    {
        $query = json_decode($multipleRules);
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotEqualQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => 42]],
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => 42]],
            'float value as type string' => [42.5, 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => 42.5]],
            'string float value as type string' => ['42.5', 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => 42.5]],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => '42,5']],
            'string as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` <> :dcValue1', ['dcValue1' => 'foo']],

            'integer value as type integer' => [42, 'integer', 'SELECT  WHERE `title` <> 42', []],
            'string as number value as type integer' => ['42', 'integer', 'SELECT  WHERE `title` <> 42', []],
            'integer(negative) value as type integer' => [-5, 'integer', 'SELECT  WHERE `title` <> -5', []],
            'string(negative) as number value as type integer' => ['-5', 'integer', 'SELECT  WHERE `title` <> -5', []],

            'integer(1) value as type boolean' => [[1], 'boolean', 'SELECT  WHERE `title` <> :dcValue3',['dcValue1' => 1, 'dcValue2' => null,'dcValue3' => 1]],
            'string(1) as number value as type boolean' => [['1'], 'boolean', 'SELECT  WHERE `title` <> :dcValue3',['dcValue1' => 1, 'dcValue2' => null,'dcValue3' => 1]],
            'integer(0) value as type boolean' => [[0], 'boolean', 'SELECT  WHERE `title` <> :dcValue3',['dcValue1' => 0, 'dcValue2' => null,'dcValue3' => 0]],
            'string(0) as number value as type boolean' => [['0'], 'boolean', 'SELECT  WHERE `title` <> :dcValue3',['dcValue1' => 0, 'dcValue2' => null,'dcValue3' => 0]],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` <> 42', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` <> 42', []],
            'integer(negative)value as type double' => [-5, 'double', 'SELECT  WHERE `title` <> -5', []],
            'string(negative) as number value as type double' => ['-5', 'double', 'SELECT  WHERE `title` <> -5', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` <> 42.5', []],
            'string float value as type double' => ['42.5', 'double', 'SELECT  WHERE `title` <> 42.5', []],
            'float value (2 decimal w 00) as type double' => [42.00, 'double', 'SELECT  WHERE `title` <> 42', []],
            'float value (2 decimal w 50) as type double' => [42.50, 'double', 'SELECT  WHERE `title` <> 42.5', []],
            'float value (2 decimal w 55) as type double' => [42.55, 'double', 'SELECT  WHERE `title` <> 42.55', []],
            'string float value (2 decimal) as type double' => ['42.50', 'double', 'SELECT  WHERE `title` <> 42.5', []],
            'comma value as type double' => ['42,50', 'double', 'SELECT  WHERE `title` <> 42.5', []],
            'comma value (2 decimal) as type double' => ['42,50', 'double', 'SELECT  WHERE `title` <> 42.5', []],

            'comma value as type date' => ['2017-06-26', 'date', 'SELECT  WHERE `title` <> 1498420800', []],

            'comma value as type time' => ['18:30', 'time', 'SELECT  WHERE `title` <> 66600', []],

            'string as number value as type datetime' => ['2017-01-21 00:00', 'datetime', 'SELECT  WHERE `title` <> 1484949600', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotEqualQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "not_equal",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }


    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleInQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', 'SELECT  WHERE `title` IN (:dcValue2)', ['dcValue2' => 42, 'dcValue1' => '42']],
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` IN (:dcValue2)', ['dcValue2' => 42, 'dcValue1' => '42']],
            'float value as type string' => [42.5, 'string', 'SELECT  WHERE `title` IN (:dcValue2)', ['dcValue2' => 42.5, 'dcValue1' => 42.5]],
            'two float values as type string' => ['42.5;50.5', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3)', ['dcValue2' => '42.5', 'dcValue3' => '50.5', 'dcValue1' => '42.5;50.5']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` IN (:dcValue2)', ['dcValue2' => '42,5', 'dcValue1' => '42,5']],
            'two comma values as type string with ; as delimiter' => ['42,5;5,5', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5;5,5']],
            'two comma values as type string with # as delimiter' => ['42,5#5,5', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5#5,5']],
            'two comma values as type string with | as delimiter' => ['42,5|5,5', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5|5,5']],
            'multiple comma values as type string with mixed delimiters' => ['42,5;5,5#6,6|7,7', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3,:dcValue4,:dcValue5)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue4' => '6,6', 'dcValue5' => '7,7', 'dcValue1' => '42,5;5,5#6,6|7,7']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` IN (:dcValue2)', ['dcValue2' => 'foo', 'dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo;bar', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3)', ['dcValue2' => 'foo', 'dcValue3' => 'bar', 'dcValue1' => 'foo;bar']],
            'string(3 words) as string value as type string' => ['foo;bar;dong', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3,:dcValue4)', ['dcValue2' => 'foo', 'dcValue3' => 'bar', 'dcValue4' => 'dong', 'dcValue1' => 'foo;bar;dong']],
            'mixed values as type string' => ['foo;42,5;dong', 'string', 'SELECT  WHERE `title` IN (:dcValue2,:dcValue3,:dcValue4)', ['dcValue2' => 'foo', 'dcValue3' => '42,5', 'dcValue4' => 'dong', 'dcValue1' => 'foo;42,5;dong']],

            'integer value as type integer' => [42, 'integer', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => '42']],
            'string as number value as type integer' => ['42', 'integer', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => '42']],

            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => '42.5']],
            'string float value as type double' => ['42.5', 'double', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => '42.5']],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => '42.5']],

            'comma value as type date' => ['2017-06-26', 'date', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => 1498420800]],

            'comma value as type time' => ['18:30', 'time', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => 66600]],

            'string as number value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` IN (:dcValue1)', ['dcValue1' => 1483221600]],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleInQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleInQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "in",
              "value": "foo, bar"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotInQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2)', ['dcValue2' => 42, 'dcValue1' => 42]],
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2)', ['dcValue2' => 42, 'dcValue1' => '42']],
            'float value as type string' => [42.5, 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2)', ['dcValue2' => 42.5, 'dcValue1' => 42.5]],
            'two float values as type string' => ['42.5;50.5', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3)', ['dcValue2' => '42.5', 'dcValue3' => '50.5', 'dcValue1' => '42.5;50.5']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2)', ['dcValue2' => '42,5', 'dcValue1' => '42,5']],
            'two comma values as type string with ; as delimiter' => ['42,5;5,5', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5;5,5']],
            'two comma values as type string with # as delimiter' => ['42,5#5,5', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5#5,5']],
            'two comma values as type string with | as delimiter' => ['42,5|5,5', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue1' => '42,5|5,5']],
            'multiple comma values as type string with mixed delimiters' => ['42,5;5,5#6,6|7,7', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3,:dcValue4,:dcValue5)', ['dcValue2' => '42,5', 'dcValue3' => '5,5', 'dcValue4' => '6,6', 'dcValue5' => '7,7', 'dcValue1' => '42,5;5,5#6,6|7,7']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2)', ['dcValue2' => 'foo', 'dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo;bar', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3)', ['dcValue2' => 'foo', 'dcValue3' => 'bar', 'dcValue1' => 'foo;bar']],
            'string(3 words) as string value as type string' => ['foo;bar;dong', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3,:dcValue4)', ['dcValue2' => 'foo', 'dcValue3' => 'bar', 'dcValue4' => 'dong', 'dcValue1' => 'foo;bar;dong']],
            'mixed values as type string' => ['foo;42,5;dong', 'string', 'SELECT  WHERE `title` NOT IN (:dcValue2,:dcValue3,:dcValue4)', ['dcValue2' => 'foo', 'dcValue3' => '42,5', 'dcValue4' => 'dong', 'dcValue1' => 'foo;42,5;dong']],

            'integer value as type integer' => [42, 'integer', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => '42']],
            'string as number value as type integer' => ['42', 'integer', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => '42']],

            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => '42.5']],
            'string float value as type double' => ['42.5', 'double', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => '42.5']],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => '42.5']],

            'comma value as type date' => ['2017-06-26', 'date', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => 1498420800]],

            'comma value as type time' => ['18:30', 'time', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => 66600]],

            'string as number value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` NOT IN (:dcValue1)', ['dcValue1' => 1483221600]],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotInQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotInQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "not_in",
              "value": "foo, bar"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleBeginsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` LIKE \'42%\'', ['dcValue1' => 42]],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` LIKE \'42,5%\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` LIKE \'foo%\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` LIKE \'foo bar%\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` LIKE \'foo\\\\%bar%\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` LIKE \'foo\\\\_bar%\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleBeginsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleBeginsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "begins_with",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotBeginsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` NOT LIKE \'42%\'', ['dcValue1' => '42']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` NOT LIKE \'42,5%\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` NOT LIKE \'foo%\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'foo bar%\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'foo\\\\%bar%\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'foo\\\\_bar%\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotBeginsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotBeginsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "not_begins_with",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleContainsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` LIKE \'%42%\'', ['dcValue1' => '42']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` LIKE \'%42,5%\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` LIKE \'%foo%\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo bar%\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo\\\\%bar%\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo\\\\_bar%\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleContainsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleContainsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "contains",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotContainsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` NOT LIKE \'%42%\'', ['dcValue1' => '42']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` NOT LIKE \'%42,5%\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo%\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo bar%\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo\\\\%bar%\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo\\\\_bar%\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotContainsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotContainsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "not_contains",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleEndsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` LIKE \'%42\'', ['dcValue1' => '42']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` LIKE \'%42,5\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` LIKE \'%foo\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo bar\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo\\\\%bar\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` LIKE \'%foo\\\\_bar\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleEndsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleEndsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "ends_with",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotEndsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', 'SELECT  WHERE `title` NOT LIKE \'%42\'', ['dcValue1' => '42']],
            'comma value as type string' => ['42,5', 'string', 'SELECT  WHERE `title` NOT LIKE \'%42,5\'', ['dcValue1' => '42,5']],
            'string(1 words) as string value as type string' => ['foo', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo\'', ['dcValue1' => 'foo']],
            'string(2 words) as string value as type string' => ['foo bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo bar\'', ['dcValue1' => 'foo bar']],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo\\\\%bar\'', ['dcValue1' => 'foo%bar']],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', 'SELECT  WHERE `title` NOT LIKE \'%foo\\\\_bar\'', ['dcValue1' => 'foo_bar']],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotEndsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotEndsQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "not_ends_with",
              "value": "foo"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @test
     */
    public function parseReturnsValidWhereClauseForSimpleEmptyQuery()
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "is_empty"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $expectedResult = 'SELECT  WHERE (`title` = \'\') OR (`title` IS NULL)';
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedResult, $queryBuilder->getSQL());
    }

    /**
     * @test
     */
    public function parseReturnsValidWhereClauseForSimpleNotEmptyQuery()
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "is_not_empty"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $expectedResult = 'SELECT  WHERE (`title` <> \'\') AND (`title` IS NOT NULL)';
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedResult, $queryBuilder->getSQL());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNullQueryDataProvider() : array
    {
        return [
            'type string' => ['string'],
            'type integer' => ['integer'],
            'type double' => ['double'],
            'type date' => ['date'],
            'type datetime' => ['datetime'],
            'type time' => ['time'],
            'type boolean' => ['boolean'],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNullQueryDataProvider
     *
     * @param $type
     */
    public function parseReturnsValidWhereClauseForSimpleNullQuery($type)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "is_null"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->type = $type;
        $expectedResult = 'SELECT  WHERE `title` IS NULL';
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedResult, $queryBuilder->getSQL());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotNullQueryDataProvider() : array
    {
        return [
            'type string' => ['string'],
            'type integer' => ['integer'],
            'type double' => ['double'],
            'type date' => ['date'],
            'type datetime' => ['datetime'],
            'type time' => ['time'],
            'type boolean' => ['boolean'],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotNullQueryDataProvider
     *
     * @param $type
     */
    public function parseReturnsValidWhereClauseForSimpleNotNullQuery($type)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "text",
              "operator": "is_not_null"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->type = $type;
        $expectedResult = 'SELECT  WHERE `title` IS NOT NULL';
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedResult, $queryBuilder->getSQL());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleLessQueryDataProvider() : array
    {
        return [
            'integer value as type integer' => [42, 'integer', 'SELECT  WHERE `title` < 42', []],
            'float value as type integer' => [42.5, 'integer', 'SELECT  WHERE `title` < 42', []],
            'comma value as type integer' => ['42,5', 'integer', 'SELECT  WHERE `title` < 42', []],
            'string as number value as type integer' => ['42', 'integer', 'SELECT  WHERE `title` < 42', []],
            'string as string value as type integer' => ['foo', 'integer', 'SELECT  WHERE `title` < 0', []],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` < 42', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` < 42.5', []],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` < 42.5', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` < 42', []],

            'string as date value as type date' => ['2017-01-01', 'date', 'SELECT  WHERE `title` < 1483221600', []],

            'string as type time' => ['16:30', 'time', 'SELECT  WHERE `title` < 59400', []],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` < 1483221600', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleLessQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleLessQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "less",
              "value": "42"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleLessOrEqualQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', 'SELECT  WHERE `title` <= 42', []],
            'float value as integer' => [42.5, 'integer', 'SELECT  WHERE `title` <= 42', []],
            'comma value as integer' => ['42,5', 'integer', 'SELECT  WHERE `title` <= 42', []],
            'string as number value as integer' => ['42', 'integer', 'SELECT  WHERE `title` <= 42', []],
            'string as string value as integer' => ['foo', 'integer', 'SELECT  WHERE `title` <= 0', []],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` <= 42', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` <= 42.5', []],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` <= 42.5', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` <= 42', []],

            'string as date value as type date' => ['2017-01-01', 'date', 'SELECT  WHERE `title` <= 1483221600', []],

            'string as type time' => ['16:30', 'time', 'SELECT  WHERE `title` <= 59400', []],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` <= 1483221600', []],
        ];
    }


    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleLessOrEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleLessOrEqualQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "less_or_equal",
              "value": "42"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', 'SELECT  WHERE `title` > 42', []],
            'float value as integer' => [42.5, 'integer', 'SELECT  WHERE `title` > 42', []],
            'comma value as integer' => ['42,5', 'integer', 'SELECT  WHERE `title` > 42', []],
            'string as number value as integer' => ['42', 'integer', 'SELECT  WHERE `title` > 42', []],
            'string as string value as integer' => ['foo', 'integer', 'SELECT  WHERE `title` > 0', []],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` > 42', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` > 42.5', []],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` > 42.5', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` > 42', []],

            'string as date value as type date' => ['2017-01-01', 'date', 'SELECT  WHERE `title` > 1483221600', []],

            'string as type time' => ['16:30', 'time', 'SELECT  WHERE `title` > 59400', []],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` > 1483221600', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleGreaterQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "greater",
              "value": "42"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterOrEqualQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', 'SELECT  WHERE `title` >= 42', []],
            'float value as integer' => [42.5, 'integer', 'SELECT  WHERE `title` >= 42', []],
            'comma value as integer' => ['42,5', 'integer', 'SELECT  WHERE `title` >= 42', []],
            'string as number value as integer' => ['42', 'integer', 'SELECT  WHERE `title` >= 42', []],
            'string as string value as integer' => ['foo', 'integer', 'SELECT  WHERE `title` >= 0', []],

            'integer value as type double' => [42, 'double', 'SELECT  WHERE `title` >= 42', []],
            'float value as type double' => [42.5, 'double', 'SELECT  WHERE `title` >= 42.5', []],
            'comma value as type double' => ['42,5', 'double', 'SELECT  WHERE `title` >= 42.5', []],
            'string as number value as type double' => ['42', 'double', 'SELECT  WHERE `title` >= 42', []],

            'string as date value as type date' => ['2017-01-01', 'date', 'SELECT  WHERE `title` >= 1483221600', []],

            'string as type time' => ['16:30', 'time', 'SELECT  WHERE `title` >= 59400', []],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', 'SELECT  WHERE `title` >= 1483221600', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleGreaterOrEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterOrEqualQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "greater_or_equal",
              "value": "42"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleBetweenQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [[42,62], 'integer', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],
            'float value as integer' => [[42.5, 62.5], 'integer', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],
            'comma value as integer' => [['42,5','62,5'], 'integer', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],
            'string as number value as integer' => [['42', '62'], 'integer', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],
            'string as string value as integer' => [['foo','bar'], 'integer', 'SELECT  WHERE (`title` > 0) AND (`title` < 0)', []],

            'integer value as type double' => [[42,62], 'double', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],
            'float value as type double' => [[42.5,62.5], 'double', 'SELECT  WHERE (`title` > 42.5) AND (`title` < 62.5)', []],
            'comma value as type double' => [['42,5','62,5'], 'double', 'SELECT  WHERE (`title` > 42.5) AND (`title` < 62.5)', []],
            'string as number value as type double' => [['42','62'], 'double', 'SELECT  WHERE (`title` > 42) AND (`title` < 62)', []],

            'string as date value as type date' => [['2017-01-01', '2017-06-30'], 'datetime', 'SELECT  WHERE (`title` > 1483221600) AND (`title` < 1498766400)', []],

            'string as type time' => [['16:30', '18:30'], 'time', 'SELECT  WHERE (`title` > 59400) AND (`title` < 66600)', []],

            'string as datetime value as type datetime' => [['2017-01-01 00:00', '2017-06-30 23:59'], 'datetime', 'SELECT  WHERE (`title` > 1483221600) AND (`title` < 1498852740)', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleBetweenQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleBetweenQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "between",
              "value": "42,62"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotBetweenQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [[42,62], 'integer', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],
            'float value as integer' => [[42.5, 62.5], 'integer', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],
            'comma value as integer' => [['42,5','62,5'], 'integer', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],
            'string as number value as integer' => [['42', '62'], 'integer', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],
            'string as string value as integer' => [['foo','bar'], 'integer', 'SELECT  WHERE (`title` < 0) OR (`title` > 0)', []],

            'integer value as type double' => [[42,62], 'double', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],
            'float value as type double' => [[42.5,62.5], 'double', 'SELECT  WHERE (`title` < 42.5) OR (`title` > 62.5)', []],
            'comma value as type double' => [['42,5','62,5'], 'double', 'SELECT  WHERE (`title` < 42.5) OR (`title` > 62.5)', []],
            'string as number value as type double' => [['42','62'], 'double', 'SELECT  WHERE (`title` < 42) OR (`title` > 62)', []],

            'string as date value as type date' => [['2017-01-01', '2017-06-30'], 'date', 'SELECT  WHERE (`title` < 1483221600) OR (`title` > 1498766400)', []],

            'string as type time' => [['16:30', '18:30'], 'time', 'SELECT  WHERE (`title` < 59400) OR (`title` > 66600)', []],

            'string as datetime value as type datetime' => [['2017-01-01 00:00', '2017-06-30 23:59'], 'datetime', 'SELECT  WHERE (`title` < 1483221600) OR (`title` > 1498852740)', []],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotBetweenQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedSQL
     * @param $expectedParameters
     */
    public function parseReturnsValidWhereClauseForSimpleNotBetweenQuery($number, $type, $expectedSQL, $expectedParameters)
    {
        $query = '{
          "condition": "AND",
          "rules": [
            {
              "id": "title",
              "field": "title",
              "type": "string",
              "input": "integer",
              "operator": "not_between",
              "value": "42,62"
            }
          ],
          "valid": true
        }';
        $query = json_decode($query);
        $query->rules[0]->value = $number;
        $query->rules[0]->type = $type;
        $queryBuilder = $this->subject->parse($query, $this->getConnectionPool()->getQueryBuilderForTable($this->table));
        self::assertEquals($expectedSQL, $queryBuilder->getSQL());
        self::assertEquals($expectedParameters, $queryBuilder->getParameters());
    }
}
