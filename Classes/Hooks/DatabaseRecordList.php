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
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $route = $parsedBody['route'] ?? $queryParams['route'] ?? '';
        if ($table !== null && $route === '/web/list/') {
            $query = $parsedBody['query'] ?? $queryParams['query'] ?? '';
            if ($query !== null) {
                $filter = json_decode($query);
                if ($filter) {
                    $queryBuilder = GeneralUtility::makeInstance(QueryParser::class)
                        ->parse($filter, $queryBuilder);
                }
            }
        }
        return $queryBuilder;
    }
}
