<?php

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF['querybuilder'] = [
    'title' => 'QueryBuilder',
    'description' => 'Backend extension for a QueryBuilder in list module.',
    'category' => 'be',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 GmbH',
    'author_email' => 'info@typo3.com',
    'version' => '11.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0 - 11.0.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
