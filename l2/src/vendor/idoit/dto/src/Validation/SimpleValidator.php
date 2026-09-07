<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

abstract class SimpleValidator extends Validator
{
    public function __construct(protected readonly string $message)
    {
    }

    abstract protected function matches(mixed $value): bool;

    public function validate(mixed $value): array
    {
        if (!$this->matches($value)) {
            return [$this->message];
        }

        return [];
    }
}
