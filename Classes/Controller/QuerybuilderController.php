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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
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
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
     */
    public function ajaxSaveQuery(ServerRequestInterface $request): ResponseInterface
    {
        $result = new \stdClass();
        $result->status = 'ok';

        $requestParams = $request->getQueryParams();
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_querybuilder');
        $uid = (int)$requestParams['uid'];
        if ($uid > 0 && (int)$requestParams['override'] === 1) {
            $result->uid = $uid;
            $queryBuilder->update('sys_querybuilder')
                ->set('where_parts', $requestParams['query'])
                ->set('queryname', $requestParams['queryName'])
                ->where($queryBuilder->expr()->eq('uid', $uid))
                ->andWhere($queryBuilder->expr()->eq('user', (int)$this->getBackendUserAspect()->get('id')))
                ->execute();
        } else {
            $data = [
                'where_parts' => $requestParams['query'],
                'user' => (int)$this->getBackendUserAspect()->get('id'),
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

//        @TODO: This does not feel right but generates a response like in earlier version
        $response = new Response('php://temp', 200,
            [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-JSON' => [true]
            ]);
        $response->getBody()->write(json_encode($result));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
     */
    public function ajaxGetRecentQueries(ServerRequestInterface $request): ResponseInterface
    {
        $requestParams = $request->getQueryParams();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_querybuilder');

        $results = $queryBuilder
            ->select('uid', 'queryname', 'where_parts')
            ->from('sys_querybuilder')
            ->where(
                $queryBuilder->expr()->eq('affected_table',
                    $queryBuilder->createNamedParameter($requestParams['table'])),
                $queryBuilder->expr()->eq('user', (int)$this->getBackendUserAspect()->get('id'))
            )
            ->execute()
            ->fetchAll();

        return new JsonResponse($results, !empty($results) ? 200 : 204);
    }

    /**
     * @return UserAspect
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getBackendUserAspect(): UserAspect
    {
        return GeneralUtility::makeInstance(Context::class)->getAspect('backend.user');
    }
}
