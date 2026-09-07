<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OrX extends Validator
{
    private readonly array $conditions;

    public function __construct(ValidatorInterface ...$conditions)
    {
        $this->conditions = $conditions;
    }

    public function validate(mixed $value): array
    {
        $issues = [];
        foreach ($this->conditions as $condition) {
            if (!($condition instanceof ValidatorInterface)) {
                continue;
            }
            $errors = $condition->validate($value);
            // At least one condition resulted without errors.
            if (empty($errors)) {
                return [];
            }
            $issues = [...$issues, ...$errors];
        }
        return $issues;
    }
}
