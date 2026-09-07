<?php declare(strict_types = 1);

namespace idoit\Component\Processor\Validation;

use Attribute;
use Idoit\Dto\Validation\ValidatorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsValidHexColor implements ValidatorInterface
{
    public function validate(mixed $value): array
    {
        if (!is_string($value)) {
            return ['Value needs to contain a hex color string (for example "#ffffff").'];
        }

        $formattedColor = \isys_helper_color::unifyHexColor($value);

        if (strtolower($value) !== $formattedColor) {
            return ['Value does not contain a valid hex color string, try: "' . $formattedColor . '".'];
        }

        return [];
    }
}
