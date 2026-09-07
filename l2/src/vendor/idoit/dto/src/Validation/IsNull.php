<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsNull extends SimpleValidator
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'The value must be a null.');
    }

    protected function matches(mixed $value): bool
    {
        return $value === null;
    }
}
