<?php

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// PageRenderer Hook to add CSS and JS modules
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
    \T3G\Querybuilder\Hooks\PageRenderer::class . '->renderPreProcess';

// DatabaseRecordList hook to process the query
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class]['modifyQuery'][] =
    \T3G\Querybuilder\Hooks\DatabaseRecordList::class;

// Create DataProviderGroup
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaOnly'] = [
    \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
        ],
    ],
//    TODO Check this
    \TYPO3\CMS\Backend\Form\FormDataProvider\ParentPageTca::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseSystemLanguageRows::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseLanguageRows::class,
            // As the ctrl.type can hold a nested key we need to resolve all relations
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaGroup::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsOverrides::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsOverrides::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineExpandCollapseState::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessRecordTitle::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessPlaceholders::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessShowitem::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseSystemLanguageRows::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessFieldLabels::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\UserTsConfig::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessFieldLabels::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRadioItems::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaCheckboxItems::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRadioItems::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabasePageRootline::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfigMerged::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaTypesShowitem::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsRemoveUnused::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaCheckboxItems::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectTreeItems::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectTreeItems::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInline::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInputPlaceholders::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineIsOnSymmetricSide::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInputPlaceholders::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\TcaRecordTitle::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInline::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineIsOnSymmetricSide::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordOverrideValues::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ],
    ],
    \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class => [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDateTimeFields::class,
        ],
    ],
];