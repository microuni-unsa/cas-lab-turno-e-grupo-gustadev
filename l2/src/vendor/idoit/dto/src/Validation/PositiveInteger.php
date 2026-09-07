<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PositiveInteger extends SimpleValidator
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'The value must be positive number.');
    }

    protected function matches(mixed $value): bool
    {
        return $value && $value > 0;
    }
}
