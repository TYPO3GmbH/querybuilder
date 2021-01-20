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
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;

    public function __construct(\TYPO3\CMS\Core\Page\PageRenderer $pageRenderer, QueryBuilder $queryBuilder)
    {
        $this->pageRenderer = $pageRenderer;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param array $params
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function renderPreProcess(array $params): void
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];
        $queryParams = $request->getQueryParams();
        $table = $queryParams['table'] ?? '';
        $route = $queryParams['route'] ?? '';
        if (!empty($table) && $route === '/module/web/list') {
            $this->pageRenderer->addInlineLanguageLabelFile('EXT:querybuilder/Resources/Private/Language/querybuilder-js.xlf');
            $this->pageRenderer->addCssFile('EXT:querybuilder/Resources/Public/Css/query-builder.default.css');
            $this->pageRenderer->addCssFile('EXT:querybuilder/Resources/Public/Css/custom-query-builder.css');

            $this->pageRenderer->addRequireJsConfiguration([
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
            $this->pageRenderer->addJsInlineCode('tx_querybuilder_query', 'var tx_querybuilder_query = ' . json_encode($query) . ';');

            $pageId = (int)$queryParams['id'];
            $filter = $this->queryBuilder->buildFilterFromTca($table, $pageId);
            $this->pageRenderer->addJsInlineCode('tx_querybuilder_filter', 'var tx_querybuilder_filter = ' . json_encode($filter) . ';');

            $this->pageRenderer->loadRequireJsModule($languageModule);
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Querybuilder/QueryBuilder', 'function(QueryBuilder) {
                QueryBuilder.initialize(tx_querybuilder_query, tx_querybuilder_filter);
            }');
        }
    }
}
