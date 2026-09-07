<?php declare(strict_types=1);

namespace idoit\Component\Processor\Exception;

class ValidationException extends \Exception
{
    private array $errors = [];

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
