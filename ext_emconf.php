<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'QueryBuilder',
    'description' => 'Backend extension for querybuilder in list module.',
    'category' => 'be',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'author' => 'Frank Naegler, Henrik Elsner',
    'author_company' => 'TYPO3 GmbH',
    'version' => '9.0.x-dev',
    'constraints' => array(
        'depends' => array(
            'typo3' => '9.0.0-9.9.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
