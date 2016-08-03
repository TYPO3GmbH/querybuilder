<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// PageRenderer Hook to add CSS and JS modules
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
    \T3G\Querybuilder\Hooks\PageRenderer::class . '->renderPreProcess';

// DatabaseRecordList hook to process the query
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] =
    \T3G\Querybuilder\Hooks\DatabaseRecordList::class;
