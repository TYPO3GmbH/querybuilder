<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Backend\Form\FormDataGroup;

use TYPO3\CMS\Backend\Form\FormDataGroupInterface;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A data provider group for casual database records.
 */
class TcaOnly implements FormDataGroupInterface
{
    /**
     * Compile form data.
     *
     * @param array $result Initialized result array
     *
     * @return array Result filled with data
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function compile(array $result) : array
    {
        $dataProvider = $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaOnly'];
        $orderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
        $orderedDataProvider = $orderingService->orderByDependencies($dataProvider, 'before', 'depends');

        foreach ($orderedDataProvider as $providerClassName => $_) {
            $provider = GeneralUtility::makeInstance($providerClassName);

            if (!$provider instanceof FormDataProviderInterface) {
                throw new \UnexpectedValueException(
                    'Data provider ' . $providerClassName . ' must implement FormDataProviderInterface',
                    1437906440
                );
            }

            $result = $provider->addData($result);
        }

        return $result;
    }
}
