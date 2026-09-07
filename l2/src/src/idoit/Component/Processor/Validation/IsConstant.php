<?php declare(strict_types = 1);

namespace idoit\Component\Processor\Validation;

use Attribute;
use Idoit\Dto\Validation\ValidatorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsConstant implements ValidatorInterface
{
    /**
     * @param string|null $startsWith
     */
    public function __construct(private string|null $startsWith = null)
    {
    }

    public function validate(mixed $value): array
    {
        if (!is_string($value) || !defined($value)) {
            return ['Value contains no defined constant.'];
        }

        if ($this->startsWith !== null && !str_starts_with($value, $this->startsWith)) {
            return ['Value should start with: ' . $this->startsWith . '.'];
        }

        return [];
    }
}
