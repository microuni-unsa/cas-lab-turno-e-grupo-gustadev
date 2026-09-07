<?php declare(strict_types = 1);

namespace idoit\Component\Processor\Validation;

use Attribute;
use idoit\Component\Helper\Purify;
use Idoit\Dto\Validation\ValidatorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsValidConstantString implements ValidatorInterface
{
    /**
     * @param string|null $startsWith
     */
    public function __construct(private string|null $startsWith = null)
    {
    }

    public function validate(mixed $value): array
    {
        if (!is_string($value)) {
            return ['Value needs to be a string.'];
        }

        if ($this->startsWith !== null && !str_starts_with($value, $this->startsWith)) {
            return ['Value needs to start with "' . $this->startsWith . '".'];
        }

        $formattedConstant = Purify::formatConstant($value);

        if ($value !== $formattedConstant) {
            return ['Value needs to contain a valid constant string, try "' . $formattedConstant . '".'];
        }

        return [];
    }
}
