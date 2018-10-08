<?php
declare(strict_types = 1);

namespace T3G\Querybuilder\Backend;

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use T3G\Querybuilder\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Creates queryBuilder
 */
class QueryBuilderFactory
{
    /**
     * @return QueryBuilder
     */
    public function create(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class);
        return $queryBuilder;
    }
}
