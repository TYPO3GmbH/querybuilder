<?php
declare(strict_types=1);
namespace T3G\Querybuilder\Hooks;

use T3G\Querybuilder\Parser\QueryParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\RecordList\AbstractDatabaseRecordList;

/**
 * Class DatabaseRecordList.
 */
class DatabaseRecordList
{

    /**
     * @param array $parameters parameters
     * @param string $table the current database table
     * @param int $pageId the records' page ID
     * @param array $additionalConstraints additional constraints
     * @param array $fieldList field list
     * @param AbstractDatabaseRecordList $parentObject
     */
    public function buildQueryParametersPostProcess(array &$parameters,
                                   string $table,
                                   int $pageId,
                                   array $additionalConstraints,
                                   array $fieldList,
                                   AbstractDatabaseRecordList $parentObject)
    {
        if (GeneralUtility::_GP('M') === 'web_list' && $parentObject->table !== null) {
            $query = GeneralUtility::_GP('query');
            if ($query !== null) {
                $query = json_decode($query);
                if ($query) {
                    $queryParser = GeneralUtility::makeInstance(QueryParser::class);
                    $parameters['where'][] = $queryParser->parse($query, $table);
                }
            }
        }
    }
}
