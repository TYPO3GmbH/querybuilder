<?php

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'ctrl' => [
        'title' => 'Querybuilder Queries',
        'label' => 'queryname',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:querybuilder/Resources/Public/Icons/search.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'queryname, affected_table, where_parts, user'
        ],
    ],
    'columns' => [
        'where_parts' => [
            'exclude' => true,
            'label' => 'Conditions (saved ad JSON)',
            'config' => [
                'type' => 'text',
            ],
        ],
        'affected_table' => [
            'exclude' => true,
            'label' => 'Affected Table',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 50,
                'eval' => ''
            ]
        ],
        'queryname' => [
            'exclude' => true,
            'label' => 'Queryname',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 50,
                'eval' => ''
            ]
        ],
        'user' => [
            'exclude' => true,
            'label' => 'User',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'be_users',
                'foreign_table_field' => 'uid',
                'maxitems' => 1,
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'Disable',
                    ],
                ],
            ],
        ]
    ],
];
