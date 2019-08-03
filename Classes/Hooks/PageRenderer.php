<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Hooks;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
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
    public function renderPreProcess(array $params): void
    {
        //    TODO Check this. TYPO3_REQUEST is not supported anymore
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $queryParams = $request->getQueryParams();
        $table = $queryParams['table'] ?? '';
        $route = $queryParams['route'] ?? '';
        if (!empty($table) && $route === '/web/list/') {
            $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

            $pageRenderer->addInlineLanguageLabelFile('EXT:querybuilder/Resources/Private/Language/querybuilder-js.xlf');
            $pageRenderer->addCssFile('EXT:querybuilder/Resources/Public/Css/query-builder.default.css');
            $pageRenderer->addCssFile('EXT:querybuilder/Resources/Public/Css/custom-query-builder.css');

            $pageRenderer->addRequireJsConfiguration([
                'paths' => [
                    'query-builder' => PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/JavaScript/query-builder.standalone'),
                    'query-builder/lang' => PathUtility::getAbsoluteWebPath('../typo3conf/ext/querybuilder/Resources/Public/JavaScript/Language'),
                ],
            ]);
            $languageModule = 'query-builder/lang/query-builder.en';
            $languageFile = 'EXT:querybuilder/Resources/Public/JavaScript/Language/query-builder.' . $GLOBALS['BE_USER']->uc['lang'] . '.js';
            if (file_exists(GeneralUtility::getFileAbsFileName($languageFile))) {
                $languageModule = 'query-builder/lang/query-builder.' . $GLOBALS['BE_USER']->uc['lang'];
            }

            $query = json_decode($queryParams['query'] ?? '');
            $pageRenderer->addJsInlineCode('tx_querybuilder_query', 'var tx_querybuilder_query = ' . json_encode($query) . ';');

            $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class);

            $pageId = (int)$queryParams['id'];
            $filter = $queryBuilder->buildFilterFromTca($table, $pageId);
            $pageRenderer->addJsInlineCode('tx_querybuilder_filter', 'var tx_querybuilder_filter = ' . json_encode($filter) . ';');

            $pageRenderer->loadRequireJsModule($languageModule);
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Querybuilder/QueryBuilder', 'function(QueryBuilder) {
                QueryBuilder.initialize(tx_querybuilder_query, tx_querybuilder_filter);
            }');
        }
    }
}
