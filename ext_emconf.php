<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'QueryBuilder',
    'description' => 'Backend extension for querybuilder in list module.',
    'category' => 'be',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 GmbH',
    'author_email' => 'info@typo3.com',
    'version' => '9.0.x-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '9.0.0-9.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
