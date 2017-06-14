<?php

namespace T3G\Querybuilder\Hooks;

use InvalidArgumentException;
use T3G\Querybuilder\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use UnexpectedValueException;

/**
 * Class PageRenderer
 *
 */
class PageRenderer
{
    /**
     * @param array $params
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function renderPreProcess(array $params)
    {
        $table = GeneralUtility::_GP('table');
        if (!empty($table) && GeneralUtility::_GP('M') === 'web_list') {
            $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

            $pageRenderer->addInlineLanguageLabelFile('EXT:querybuilder/Resources/Private/Language/querybuilder-js.xlf', 'tx_querybuilder_js');
            $pageRenderer->addCssFile(
                PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/Css/query-builder.default.css')
            );
            $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'query-builder' => PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/JavaScript/query-builder.standalone'),
                ],
            ]);

            $query = GeneralUtility::_GP('query');
            $query = json_decode($query);
            $pageRenderer->addJsInlineCode('tx_querybuilder_query', 'var tx_querybuilder_query = ' . json_encode($query) . ';');

            $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class);
            $filter = $queryBuilder->buildFilterFromTca($table);
            $pageRenderer->addJsInlineCode('tx_querybuilder_filter', 'var tx_querybuilder_filter = ' . json_encode($filter) . ';');

            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Querybuilder/QueryBuilder', 'function(QueryBuilder) {
                QueryBuilder.initialize(tx_querybuilder_query, tx_querybuilder_filter);
            }');
        }
    }
}
