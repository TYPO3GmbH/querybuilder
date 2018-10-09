<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Factory;

use T3G\Querybuilder\Entity\Validation;

class ValidationFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->entityClass = Validation::class;
    }

    public function create(array $properties): Validation
    {
        return parent::create($properties);
    }
}
