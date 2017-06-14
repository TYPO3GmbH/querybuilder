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
     *
     */
    protected function setUp()
    {
        $this->subject = new QueryParser();
        parent::setUp();
    }

    /**
     * @test
     */
    public function parseReturnsValidWhereClauseForSimpleEqualQuery()
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
        $expectedResult = ' ( `title` = \'foo\' ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, 'demo'));
    }

    /**
     * @test
     */
    public function parseReturnsValidWhereClauseForSimpleNotEqualQuery()
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
        $expectedResult = ' ( `title` <> \'foo\' ) ';
        self::assertEquals($expectedResult, $this->subject->parse($query, 'demo'));
    }
}
