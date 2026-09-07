<?php

namespace idoit\Api\Parameter;

final class OptionalParameter extends Parameter
{
    private $defaultValue;

    public function __construct(string $name, string|array $type, ?string $description = null, ?callable $validation = null, $defaultValue = null)
    {
        parent::__construct($name, $type, $description, $validation);

        $this->defaultValue = $defaultValue;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
