<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Hooks;

use Psr\Http\Message\ServerRequestInterface;
use T3G\Querybuilder\Parser\QueryParser;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DatabaseRecordList.
 */
class DatabaseRecordList
{
    /**
     * @param array $parameters parameters
     * @param string $table the current database table
     * @param int $pageId the records' page ID
     * @param array $additionalConstraints additional constraints
     * @param array $fieldList field list
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     * @throws \InvalidArgumentException
     */
    public function modifyQuery(
        array &$parameters,
        string $table,
        int $pageId,
        array $additionalConstraints,
        array $fieldList,
        QueryBuilder $queryBuilder
    ): QueryBuilder {
        //    TODO Check this. TYPO3_REQUEST is not supported anymore
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $queryParams = $request->getQueryParams();
        $route = $queryParams['route'] ?? '';
        if (!empty($table) && $route === '/web/list/') {
            $query = $queryParams['query'] ?? '';
            if ($query !== null) {
                $filter = json_decode($query);
                $filter ? $queryBuilder = GeneralUtility::makeInstance(QueryParser::class)->parse($filter, $queryBuilder) : null;
            }
        }
        return $queryBuilder;
    }
}
