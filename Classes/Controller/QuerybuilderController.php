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

/**
 * Main script class for saving query
 */
class QuerybuilderController
{

    public function ajaxSafeQuery()
    {
        die('here I am');

//        var_dump($GLOBALS['BE_USER']->user['uid']);die();
//        $completedAddition = empty($whereParts) ? '' : ' ( ' . implode(' ' . $condition . ' ', $whereParts) . ' ) ';
//        $fields = [
//            'where_parts' => $completedAddition,
//            'user' => $GLOBALS['BE_USER']->user['uid'],
//            'affected_table' => $table
//        ];
//        if (!empty($completedAddition)) {
//            $safeQuery = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_querybuilder');
//            $safeQuery->insert('sys_querybuilder', $fields);
//            return $completedAddition;
//        }
//        return '';
//        return $completedAddition;
    }
}

