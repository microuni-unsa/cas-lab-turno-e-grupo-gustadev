<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneOf extends Validator
{
    /**
     * @param string[] $values
     */
    public function __construct(private readonly array $values)
    {
    }

    public function validate(mixed $value): array
    {
        if (!in_array($value, $this->values, true)) {
            $values = join(', ', $this->values);
            return ['Value should be one of: ' . $values . '.'];
        }
        return [];
    }
}
