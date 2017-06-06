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

    // Safe query
    'querybuilder_safe_query' => [
        'path' => '/querybuilder/query/safe',
        'target' => Controller\QuerybuilderController::class . '::ajaxSafeQuery'
    ],
];
