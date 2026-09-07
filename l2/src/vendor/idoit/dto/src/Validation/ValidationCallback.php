<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidationCallback implements ValidatorInterface
{
    public function validate(mixed $value): array
    {
        return [];
    }
}
