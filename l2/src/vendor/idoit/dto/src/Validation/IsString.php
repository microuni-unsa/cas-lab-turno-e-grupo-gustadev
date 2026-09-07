<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsString extends SimpleValidator
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'The value must be a string.');
    }

    protected function matches(mixed $value): bool
    {
        return is_string($value);
    }
}
