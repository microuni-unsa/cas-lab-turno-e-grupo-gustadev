<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsInstanceOf extends Validator
{
    /**
     * @param class-string $className
     */
    public function __construct(private readonly string $className)
    {
    }

    public function validate(mixed $value): array
    {
        if (!is_a($value, $this->className)) {
            return ["The value must be an instance of $this->className."];
        }
        return [];
    }
}
