<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Entity;

class Validation implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $format;

    public function jsonSerialize()
    {
        return [
            'format' => $this->getFormat()
        ];
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }
}
