<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsInt extends SimpleValidator
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'The value must be an integer.');
    }

    protected function matches(mixed $value): bool
    {
        return is_int($value);
    }
}
