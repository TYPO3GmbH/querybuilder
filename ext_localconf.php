<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// PageRenderer Hook to add CSS and JS modules
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
    \T3G\Querybuilder\Hooks\PageRenderer::class . '->renderPreProcess';

// DatabaseRecordList hook to process the query
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['TYPO3\\CMS\\Recordlist\\RecordList\\DatabaseRecordList']['buildQueryParameters'][] =
    \T3G\Querybuilder\Hooks\DatabaseRecordList::class;

// Create DataProviderGroup
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaOnly'] = array(
    \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\ParentPageTca::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseSystemLanguageRows::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseLanguageRows::class,
            // As the ctrl.type can hold a nested key we need to resolve all relations
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsOverrides::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsOverrides::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseSystemLanguageRows::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessFieldLabels::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexFetch::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\UserTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessFieldLabels::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexFetch::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRadioItems::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaCheckboxItems::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRadioItems::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabasePageRootline::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaCheckboxItems::class,
            // GeneralUtility::getFlexFormDS() needs unchanged databaseRow values as string
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexFetch::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectTreeItems::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectTreeItems::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInline::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInputPlaceholders::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineIsOnSymmetricSide::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInputPlaceholders::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRecordTitle::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInline::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineIsOnSymmetricSide::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRecordTitle::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ),
    ),
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class => array(
        'depends' => array(
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDateTimeFields::class,
        ),
    ),
);
