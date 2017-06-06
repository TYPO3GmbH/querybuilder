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

use stdClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Backend\Utility\BackendUtility;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Main script class for saving query
 */
class QuerybuilderController
{

    /**
     * @param $table
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return string
     */
    public function ajaxSaveQuery(ServerRequestInterface $request, ResponseInterface $response)
    {

//        var_dump(get_defined_vars());die();

        $requestParams = $request->getQueryParams();
//        $completedAddition = empty($whereParts) ? '' : ' ( ' . implode(' ' . $condition . ' ', $whereParts) . ' ) ';
        $fields = [
            'where_parts' => $requestParams['query'],
            'user' => $GLOBALS['BE_USER']->user['uid'],
            'affected_table' => $requestParams['table'],
//            'queryname' => $requestParams['queryname']
        ];
//        if (!empty($completedAddition)) {
            $saveQuery = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_querybuilder');
            $saveQuery->insert('sys_querybuilder', $fields);
//        }
    }
}

