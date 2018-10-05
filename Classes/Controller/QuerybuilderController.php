<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $result = new \stdClass();
        $result->status = 'ok';

        $requestParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_querybuilder');
        $uid = (int)$requestParams['uid'];
        if ((int)$requestParams['override'] === 1 && $uid > 0) {
            $result->uid = $uid;
            $queryBuilder->update('sys_querybuilder')
                ->set('where_parts', $requestParams['query'])
                ->set('queryname', $requestParams['queryName'])
                ->where($queryBuilder->expr()->eq('uid', $uid))
                ->andWhere($queryBuilder->expr()->eq('user', (int)$GLOBALS['BE_USER']->user['uid']))
                ->execute();
        } else {
            $data = [
                'where_parts' => $requestParams['query'],
                'user' => (int)$GLOBALS['BE_USER']->user['uid'],
                'affected_table' => $requestParams['table'],
                'queryname' => $requestParams['queryName'],
            ];
            $queryBuilder
                ->insert('sys_querybuilder')
                ->values($data)
                ->execute();
            $uid = $queryBuilder->getConnection()->lastInsertId('sys_querybuilder');
            $result->uid = $uid;
        }

        $response->getBody()->write(json_encode($result));
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
            ->select('uid', 'queryname', 'where_parts')
            ->from('sys_querybuilder')
            ->where(
                $queryBuilder->expr()->eq('affected_table', $queryBuilder->createNamedParameter($requestParams['table'])),
                $queryBuilder->expr()->eq('user', (int)$GLOBALS['BE_USER']->user['uid'])
            )
            ->execute()
            ->fetchAll();

        $response->getBody()->write(json_encode($results));
        return $response;
    }
}
