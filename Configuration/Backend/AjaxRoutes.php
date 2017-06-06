<?php
use T3G\Querybuilder\Controller;

/**
 * Definitions for routes provided by EXT:querybuilder
 * Contains all AJAX-based routes for entry points
 *
 * Currently the "access" property is only used so no token creation + validation is made
 * but will be extended further.
 */
return [

    // Save query
    'querybuilder_save_query' => [
        'path' => '/querybuilder/query/save',
        'target' => Controller\QuerybuilderController::class . '::ajaxSaveQuery'
    ],
];
