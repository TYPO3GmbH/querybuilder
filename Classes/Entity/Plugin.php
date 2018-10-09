<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Entity;

class Plugin implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $configuration;

    public function jsonSerialize()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'configuration' => $this->getConfiguration()
        ];
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }
}
