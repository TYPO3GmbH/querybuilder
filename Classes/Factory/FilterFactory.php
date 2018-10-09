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

class FilterFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->entityClass = Filter::class;
    }

    public function create(array $properties): Filter
    {
        return parent::create($properties);
    }
}
