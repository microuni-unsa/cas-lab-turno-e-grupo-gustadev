<?php

namespace idoit\Api\Parameter;

abstract class Parameter
{
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_ARRAY = 'array';
    public const TYPE_BOOLEAN = 'boolean';

    private string $name;

    private array $types;

    private string $description;

    private $validation;

    public function __construct(string $name, string|array $types, ?string $description = null, ?callable $validation = null)
    {
        $this->name = $name;
        $this->types = (array)$types;
        $this->description = $description;
        $this->validation = $validation;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValidation(): ?callable
    {
        return $this->validation;
    }
}
