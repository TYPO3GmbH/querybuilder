<?php
declare(strict_types=1);

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\Querybuilder\Entity;

class Filter implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Validation
     */
    protected $validation;

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'input' => $this->getInput(),
            'values' => $this->getValues(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'plugin' => $this->getPlugin() ? $this->getPlugin()->getIdentifier(): '',
            'plugin_config' => $this->getPlugin() ? $this->getPlugin()->getConfiguration(): []
        ];
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setInput(string $input): self
    {
        $this->input = $input;
        return $this;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setPlugin(Plugin $plugin): self
    {
        $this->plugin = $plugin;
        return $this;
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    public function getValidation(): Validation
    {
        return $this->validation;
    }

    public function setValidation(Validation $validation): self
    {
        $this->validation = $validation;
        return $this;
    }
}
