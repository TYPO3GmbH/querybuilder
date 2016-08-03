<?php

namespace T3G\Querybuilder\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class PageRenderer {

    /**
     * @param array $params
     *
     * @throws \InvalidArgumentException
     */
    public function renderPreProcess($params)
    {
        if (GeneralUtility::_GP('M') === 'web_list' && GeneralUtility::_GP('table') !== null) {
            $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

            $pageRenderer->addCssFile(
                PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/Css/query-builder.default.min.css')
            );
            $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'query-builder' => PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/JavaScript/query-builder.standalone'),
                ]
            ]);
            $query = GeneralUtility::_GP('query');
            $query = json_decode($query);
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Querybuilder/QueryBuilder', 'function(QueryBuilder) {
                QueryBuilder.initialize('. json_encode($query) .');
            }');
        }
    }
}