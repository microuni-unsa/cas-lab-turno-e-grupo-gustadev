<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

interface ValidatorInterface
{
    /**
     * @param mixed $value
     *
     * @return ValidationMessage[]|string[]
     */
    public function validate(mixed $value): array;
}
