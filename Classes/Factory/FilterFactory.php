<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Factory;

use T3G\Querybuilder\Entity\Filter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterFactory
{
    public function create(array $properties): Filter
    {
        $entity = GeneralUtility::makeInstance(Filter::class);
        foreach ($properties as $property => $value) {
            $method = 'set' . GeneralUtility::underscoredToUpperCamelCase($property);
            if (method_exists($entity, $method)) {
                $entity->$method($value);
            }
        }
        return $entity;
    }
}
