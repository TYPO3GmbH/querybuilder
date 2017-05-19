<?php
return [
    'ctrl' => [
        'title' => 'Querybuilder Queries',
        'label' => 'uid',
        'delete' => 'deleted',
    ],
    'types' => [
        '1' => [
            'showitem' => 'where_parts, affected_table, user'
        ],
    ],
    'palettes' => [
        '1' => [
            'showitem' => 'where_parts, affected_table, user'
        ],
    ],
    'columns' => [
        'where_parts' => [
            'exclude' => true,
            'label' => 'where_parts',
            'config' => [
                'type' => 'text',
            ],
        ],
        'affected_table' => [
            'exclude' => true,
            'label' => 'affected_table',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 50,
                'eval' => ''
            ]
        ],
        'user' => [
            'exclude' => true,
            'label' => 'userid',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'be_users',
                'foreign_table_field' => 'uid',
                'maxitems' => 1,
            ],
        ]
    ],
];
