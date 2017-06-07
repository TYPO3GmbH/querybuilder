<?php
declare(strict_types=1);
namespace T3G\Querybuilder\Controller;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Main script class for saving query
 */
class QuerybuilderController
{

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function ajaxSaveQuery(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $requestParams = $request->getQueryParams();
        $data = [
            // 'pid' => ??
            'where_parts' => $requestParams['query'],
            'user' => (int)$GLOBALS['BE_USER']->user['uid'],
            'affected_table' => $requestParams['table'],
            'queryname' => $requestParams['queryName']
        ];
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_querybuilder')
            ->insert('sys_querybuilder')
            ->values($data)
            ->execute();

        $response->getBody()->write('{"status": "ok"}');
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function ajaxGetRecentQueries(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $requestParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_querybuilder');
        $results = $queryBuilder
            ->select('uid','queryname')
            ->from('sys_querybuilder')
            ->where(
                $queryBuilder->expr()->eq('affected_table', $queryBuilder->createNamedParameter($requestParams['table']))
            )
            ->execute()
            ->fetchAll();
        $response->getBody()->write(json_encode($results));
        return $response;
    }
}

