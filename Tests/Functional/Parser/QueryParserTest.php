<?php

namespace T3G\Querybuilder\Tests\Functional\Parser;

use T3G\Querybuilder\Parser\QueryParser;
use T3G\Querybuilder\QueryBuilder;
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
                            "condition": "AND",
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
                ' ( `input_1` = \'foo\' AND `input_1` = \'bar\' AND  ( `input_9` = 42 AND `inputdatetime_2` = 1498420800 )  ) '
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
                ' ( `input_8` = 42.42 AND  ( `inputdatetime_5` = 59400 AND `checkbox_2` = \'1\' )  AND  ( `inputdatetime_4` = 1498653000 )  ) '
            ]
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForMultipleEqualsQueryDataProvider
     *
     * @param $multipleRules
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForMultipleEqualsQuery($multipleRules, $expectedResult)
    {
        $query = json_decode($multipleRules);
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotEqualQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', ' ( `title` <> \'42\' ) '],
            'string as number value as type string' => ['42', 'string', ' ( `title` <> \'42\' ) '],
            'float value as type string' => [42.5, 'string', ' ( `title` <> \'42.5\' ) '],
            'string float value as type string' => ['42.5', 'string', ' ( `title` <> \'42.5\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` <> \'42,5\' ) '],
            'string as string value as type string' => ['foo', 'string', ' ( `title` <> \'foo\' ) '],

            'integer value as type integer' => [42, 'integer', ' ( `title` <> 42 ) '],
            'string as number value as type integer' => ['42', 'integer', ' ( `title` <> 42 ) '],
            'integer(negative) value as type integer' => [-5, 'integer', ' ( `title` <> -5 ) '],
            'string(negative) as number value as type integer' => ['-5', 'integer', ' ( `title` <> -5 ) '],

            'integer(1) value as type boolean' => [[1], 'boolean', ' ( `title` <> \'1\' ) '],
            'string(1) as number value as type boolean' => [['1'], 'boolean', ' ( `title` <> \'1\' ) '],
            'integer(0) value as type boolean' => [[0], 'boolean', ' ( `title` <> \'0\' ) '],
            'string(0) as number value as type boolean' => [['0'], 'boolean', ' ( `title` <> \'0\' ) '],

            'integer value as type double' => [42, 'double', ' ( `title` <> 42 ) '],
            'string as number value as type double' => ['42', 'double', ' ( `title` <> 42 ) '],
            'integer(negative)value as type double' => [-5, 'double', ' ( `title` <> -5 ) '],
            'string(negative) as number value as type double' => ['-5', 'double', ' ( `title` <> -5 ) '],
            'float value as type double' => [42.5, 'double', ' ( `title` <> 42.5 ) '],
            'string float value as type double' => ['42.5', 'double', ' ( `title` <> 42.5 ) '],
            'float value (2 decimal w 00) as type double' => [42.00, 'double', ' ( `title` <> 42 ) '],
            'float value (2 decimal w 50) as type double' => [42.50, 'double', ' ( `title` <> 42.5 ) '],
            'float value (2 decimal w 55) as type double' => [42.55, 'double', ' ( `title` <> 42.55 ) '],
            'string float value (2 decimal) as type double' => ['42.50', 'double', ' ( `title` <> 42.5 ) '],
            'comma value as type double' => ['42,50', 'double', ' ( `title` <> 42.5 ) '],
            'comma value (2 decimal) as type double' => ['42,50', 'double', ' ( `title` <> 42.5 ) '],

            'comma value as type date' => ['2017-06-26', 'date', ' ( `title` <> 1498420800 ) '],

            'comma value as type time' => ['18:30', 'time', ' ( `title` <> 66600 ) '],

            'string as number value as type datetime' => ['2017-01-21 00:00', 'datetime', ' ( `title` <> 1484949600 ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotEqualQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }


    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleInQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', ' ( `title` IN (\'42\') ) '],
            'string as number value as type string' => ['42', 'string', ' ( `title` IN (\'42\') ) '],
            'float value as type string' => [42.5, 'string', ' ( `title` IN (\'42.5\') ) '],
            'two float values as type string' => ['42.5;50.5', 'string', ' ( `title` IN (\'42.5\',\'50.5\') ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` IN (\'42,5\') ) '],
            'two comma values as type string with ; as delimiter' => ['42,5;5,5', 'string', ' ( `title` IN (\'42,5\',\'5,5\') ) '],
            'two comma values as type string with # as delimiter' => ['42,5#5,5', 'string', ' ( `title` IN (\'42,5\',\'5,5\') ) '],
            'two comma values as type string with | as delimiter' => ['42,5|5,5', 'string', ' ( `title` IN (\'42,5\',\'5,5\') ) '],
            'multiple comma values as type string with mixed delimiters' => ['42,5;5,5#6,6|7,7', 'string', ' ( `title` IN (\'42,5\',\'5,5\',\'6,6\',\'7,7\') ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` IN (\'foo\') ) '],
            'string(2 words) as string value as type string' => ['foo;bar', 'string', ' ( `title` IN (\'foo\',\'bar\') ) '],
            'string(3 words) as string value as type string' => ['foo;bar;dong', 'string', ' ( `title` IN (\'foo\',\'bar\',\'dong\') ) '],
            'mixed values as type string' => ['foo;42,5;dong', 'string', ' ( `title` IN (\'foo\',\'42,5\',\'dong\') ) '],

            'integer value as type integer' => [42, 'integer', ' ( `title` IN (\'42\') ) '],
            'string as number value as type integer' => ['42', 'integer', ' ( `title` IN (\'42\') ) '],

            'float value as type double' => [42.5, 'double', ' ( `title` IN (\'42.5\') ) '],
            'string float value as type double' => ['42.5', 'double', ' ( `title` IN (\'42.5\') ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` IN (\'42.5\') ) '],

            'comma value as type date' => ['2017-06-26', 'date', ' ( `title` IN (\'1498420800\') ) '],

            'comma value as type time' => ['18:30', 'time', ' ( `title` IN (\'66600\') ) '],

            'string as number value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` IN (\'1483221600\') ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleInQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleInQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotInQueryDataProvider() : array
    {
        return [
            'integer value as type string' => [42, 'string', ' ( `title` NOT IN (\'42\') ) '],
            'string as number value as type string' => ['42', 'string', ' ( `title` NOT IN (\'42\') ) '],
            'float value as type string' => [42.5, 'string', ' ( `title` NOT IN (\'42.5\') ) '],
            'two float values as type string' => ['42.5;50.5', 'string', ' ( `title` NOT IN (\'42.5\',\'50.5\') ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` NOT IN (\'42,5\') ) '],
            'two comma values as type string with ; as delimiter' => ['42,5;5,5', 'string', ' ( `title` NOT IN (\'42,5\',\'5,5\') ) '],
            'two comma values as type string with # as delimiter' => ['42,5#5,5', 'string', ' ( `title` NOT IN (\'42,5\',\'5,5\') ) '],
            'two comma values as type string with | as delimiter' => ['42,5|5,5', 'string', ' ( `title` NOT IN (\'42,5\',\'5,5\') ) '],
            'multiple comma values as type string with mixed delimiters' => ['42,5;5,5#6,6|7,7', 'string', ' ( `title` NOT IN (\'42,5\',\'5,5\',\'6,6\',\'7,7\') ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` NOT IN (\'foo\') ) '],
            'string(2 words) as string value as type string' => ['foo;bar', 'string', ' ( `title` NOT IN (\'foo\',\'bar\') ) '],
            'string(3 words) as string value as type string' => ['foo;bar;dong', 'string', ' ( `title` NOT IN (\'foo\',\'bar\',\'dong\') ) '],
            'mixed values as type string' => ['foo;42,5;dong', 'string', ' ( `title` NOT IN (\'foo\',\'42,5\',\'dong\') ) '],

            'integer value as type integer' => [42, 'integer', ' ( `title` NOT IN (\'42\') ) '],
            'string as number value as type integer' => ['42', 'integer', ' ( `title` NOT IN (\'42\') ) '],

            'float value as type double' => [42.5, 'double', ' ( `title` NOT IN (\'42.5\') ) '],
            'string float value as type double' => ['42.5', 'double', ' ( `title` NOT IN (\'42.5\') ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` NOT IN (\'42.5\') ) '],

            'comma value as type date' => ['2017-06-26', 'date', ' ( `title` NOT IN (\'1498420800\') ) '],

            'comma value as type time' => ['18:30', 'time', ' ( `title` NOT IN (\'66600\') ) '],

            'string as number value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` NOT IN (\'1483221600\') ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotInQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotInQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleBeginsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` LIKE \'42%\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` LIKE \'42,5%\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` LIKE \'foo%\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` LIKE \'foo bar%\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` LIKE \'foo\\\\%bar%\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` LIKE \'foo\\\\_bar%\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleBeginsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleBeginsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotBeginsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` NOT LIKE \'42%\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` NOT LIKE \'42,5%\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` NOT LIKE \'foo%\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` NOT LIKE \'foo bar%\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` NOT LIKE \'foo\\\\%bar%\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` NOT LIKE \'foo\\\\_bar%\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotBeginsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotBeginsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleContainsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` LIKE \'%42%\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` LIKE \'%42,5%\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` LIKE \'%foo%\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` LIKE \'%foo bar%\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` LIKE \'%foo\\\\%bar%\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` LIKE \'%foo\\\\_bar%\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleContainsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleContainsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotContainsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` NOT LIKE \'%42%\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` NOT LIKE \'%42,5%\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` NOT LIKE \'%foo%\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` NOT LIKE \'%foo bar%\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` NOT LIKE \'%foo\\\\%bar%\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` NOT LIKE \'%foo\\\\_bar%\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotContainsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotContainsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleEndsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` LIKE \'%42\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` LIKE \'%42,5\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` LIKE \'%foo\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` LIKE \'%foo bar\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` LIKE \'%foo\\\\%bar\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` LIKE \'%foo\\\\_bar\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleEndsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleEndsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotEndsQueryDataProvider() : array
    {
        return [
            'string as number value as type string' => ['42', 'string', ' ( `title` NOT LIKE \'%42\' ) '],
            'comma value as type string' => ['42,5', 'string', ' ( `title` NOT LIKE \'%42,5\' ) '],
            'string(1 words) as string value as type string' => ['foo', 'string', ' ( `title` NOT LIKE \'%foo\' ) '],
            'string(2 words) as string value as type string' => ['foo bar', 'string', ' ( `title` NOT LIKE \'%foo bar\' ) '],
            'string(2 words) as string value as type string with %' => ['foo%bar', 'string', ' ( `title` NOT LIKE \'%foo\\\\%bar\' ) '],
            'string(2 words) as string value as type string with _' => ['foo_bar', 'string', ' ( `title` NOT LIKE \'%foo\\\\_bar\' ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotEndsQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotEndsQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
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
        $expectedResult = ' ( (`title` = \'\') OR (`title` IS NULL) ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
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
        $expectedResult = ' ( (`title` <> \'\') AND (`title` IS NOT NULL) ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
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
        $expectedResult = ' ( `title` IS NULL ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
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
        $expectedResult = ' ( `title` IS NOT NULL ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleLessQueryDataProvider() : array
    {
        return [
            'integer value as type integer' => [42, 'integer', ' ( `title` < 42 ) '],
            'float value as type integer' => [42.5, 'integer', ' ( `title` < 42 ) '],
            'comma value as type integer' => ['42,5', 'integer', ' ( `title` < 42 ) '],
            'string as number value as type integer' => ['42', 'integer', ' ( `title` < 42 ) '],
            'string as string value as type integer' => ['foo', 'integer', ' ( `title` < 0 ) '],

            'integer value as type double' => [42, 'double', ' ( `title` < 42 ) '],
            'float value as type double' => [42.5, 'double', ' ( `title` < 42.5 ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` < 42.5 ) '],
            'string as number value as type double' => ['42', 'double', ' ( `title` < 42 ) '],

            'string as date value as type date' => ['2017-01-01', 'date', ' ( `title` < 1483221600 ) '],

            'string as type time' => ['16:30', 'time', ' ( `title` < 59400 ) '],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` < 1483221600 ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleLessQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleLessQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleLessOrEqualQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', ' ( `title` <= 42 ) '],
            'float value as integer' => [42.5, 'integer', ' ( `title` <= 42 ) '],
            'comma value as integer' => ['42,5', 'integer', ' ( `title` <= 42 ) '],
            'string as number value as integer' => ['42', 'integer', ' ( `title` <= 42 ) '],
            'string as string value as integer' => ['foo', 'integer', ' ( `title` <= 0 ) '],

            'integer value as type double' => [42, 'double', ' ( `title` <= 42 ) '],
            'float value as type double' => [42.5, 'double', ' ( `title` <= 42.5 ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` <= 42.5 ) '],
            'string as number value as type double' => ['42', 'double', ' ( `title` <= 42 ) '],

            'string as date value as type date' => ['2017-01-01', 'date', ' ( `title` <= 1483221600 ) '],

            'string as type time' => ['16:30', 'time', ' ( `title` <= 59400 ) '],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` <= 1483221600 ) '],
        ];
    }


    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleLessOrEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleLessOrEqualQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', ' ( `title` > 42 ) '],
            'float value as integer' => [42.5, 'integer', ' ( `title` > 42 ) '],
            'comma value as integer' => ['42,5', 'integer', ' ( `title` > 42 ) '],
            'string as number value as integer' => ['42', 'integer', ' ( `title` > 42 ) '],
            'string as string value as integer' => ['foo', 'integer', ' ( `title` > 0 ) '],

            'integer value as type double' => [42, 'double', ' ( `title` > 42 ) '],
            'float value as type double' => [42.5, 'double', ' ( `title` > 42.5 ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` > 42.5 ) '],
            'string as number value as type double' => ['42', 'double', ' ( `title` > 42 ) '],

            'string as date value as type date' => ['2017-01-01', 'date', ' ( `title` > 1483221600 ) '],

            'string as type time' => ['16:30', 'time', ' ( `title` > 59400 ) '],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` > 1483221600 ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleGreaterQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterOrEqualQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [42, 'integer', ' ( `title` >= 42 ) '],
            'float value as integer' => [42.5, 'integer', ' ( `title` >= 42 ) '],
            'comma value as integer' => ['42,5', 'integer', ' ( `title` >= 42 ) '],
            'string as number value as integer' => ['42', 'integer', ' ( `title` >= 42 ) '],
            'string as string value as integer' => ['foo', 'integer', ' ( `title` >= 0 ) '],

            'integer value as type double' => [42, 'double', ' ( `title` >= 42 ) '],
            'float value as type double' => [42.5, 'double', ' ( `title` >= 42.5 ) '],
            'comma value as type double' => ['42,5', 'double', ' ( `title` >= 42.5 ) '],
            'string as number value as type double' => ['42', 'double', ' ( `title` >= 42 ) '],

            'string as date value as type date' => ['2017-01-01', 'date', ' ( `title` >= 1483221600 ) '],

            'string as type time' => ['16:30', 'time', ' ( `title` >= 59400 ) '],

            'string as datetime value as type datetime' => ['2017-01-01 00:00', 'datetime', ' ( `title` >= 1483221600 ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleGreaterOrEqualQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleGreaterOrEqualQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleBetweenQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [[42,62], 'integer', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'float value as integer' => [[42.5, 62.5], 'integer', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'comma value as integer' => [['42,5','62,5'], 'integer', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'string as number value as integer' => [['42', '62'], 'integer', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'string as string value as integer' => [['foo','bar'], 'integer', ' ( (`title` > 0) AND (`title` < 0) ) '],

            'integer value as type double' => [[42,62], 'double', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'float value as type double' => [[42.5,62.5], 'double', ' ( (`title` > 42.5) AND (`title` < 62.5) ) '],
            'comma value as type double' => [['42,5','62,5'], 'double', ' ( (`title` > 42.5) AND (`title` < 62.5) ) '],
            'string as number value as type double' => [['42','62'], 'double', ' ( (`title` > 42) AND (`title` < 62) ) '],
            'string as string value as type double' => [['foo','bar'], 'double', ' ( (`title` > 0) AND (`title` < 0) ) '],

            'string as date value as type date' => [['2017-01-01', '2017-06-30'], 'datetime', ' ( (`title` > 1483221600) AND (`title` < 1498766400) ) '],

            'string as type time' => [['16:30', '18:30'], 'time', ' ( (`title` > 59400) AND (`title` < 66600) ) '],

            'string as datetime value as type datetime' => [['2017-01-01 00:00', '2017-06-30 23:59'], 'datetime', ' ( (`title` > 1483221600) AND (`title` < 1498852740) ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleBetweenQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleBetweenQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }

    /**
     * @return array
     */
    public function parseReturnsValidWhereClauseForSimpleNotBetweenQueryDataProvider() : array
    {
        return [
            'integer value as integer' => [[42,62], 'integer', ' ( (`title` < 42) OR (`title` > 62) ) '],
            'float value as integer' => [[42.5, 62.5], 'integer', ' ( (`title` < 42) OR (`title` > 62) ) '],
            'comma value as integer' => [['42,5','62,5'], 'integer', ' ( (`title` < 42) OR (`title` > 62) ) '],
            'string as number value as integer' => [['42', '62'], 'integer', ' ( (`title` < 42) OR (`title` > 62) ) '],
            'string as string value as integer' => [['foo','bar'], 'integer', ' ( (`title` < 0) OR (`title` > 0) ) '],

            'integer value as type double' => [[42,62], 'double', ' ( (`title` < 42) OR (`title` > 62) ) '],
            'float value as type double' => [[42.5,62.5], 'double', ' ( (`title` < 42.5) OR (`title` > 62.5) ) '],
            'comma value as type double' => [['42,5','62,5'], 'double', ' ( (`title` < 42.5) OR (`title` > 62.5) ) '],
            'string as number value as type double' => [['42','62'], 'double', ' ( (`title` < 42) OR (`title` > 62) ) '],

            'string as date value as type date' => [['2017-01-01', '2017-06-30'], 'date', ' ( (`title` < 1483221600) OR (`title` > 1498766400) ) '],

            'string as type time' => [['16:30', '18:30'], 'time', ' ( (`title` < 59400) OR (`title` > 66600) ) '],

            'string as datetime value as type datetime' => [['2017-01-01 00:00', '2017-06-30 23:59'], 'datetime', ' ( (`title` < 1483221600) OR (`title` > 1498852740) ) '],
        ];
    }

    /**
     * @test
     * @dataProvider parseReturnsValidWhereClauseForSimpleNotBetweenQueryDataProvider
     *
     * @param $number
     * @param $type
     * @param $expectedResult
     */
    public function parseReturnsValidWhereClauseForSimpleNotBetweenQuery($number, $type, $expectedResult)
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
        self::assertEquals($expectedResult, $this->subject->parse($query, $this->table));
    }
}
