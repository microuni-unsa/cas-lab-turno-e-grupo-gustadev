<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required extends SimpleValidator
{
    public function __construct(?string $errorMessage = null)
    {
        parent::__construct($errorMessage ?? 'The value is required.');
    }

    protected function matches(mixed $value): bool
    {
        if (is_string($value) && strlen($value) > 0) {
            return true;
        }
        return !empty($value);
    }
}
