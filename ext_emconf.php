<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'QueryBuilder',
    'description' => 'Backend extension for query builder in list module.',
    'category' => 'be',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 GmbH',
    'author_email' => 'info@typo3.com',
    'version' => '8.7.1-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '8.3.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
