<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsArrayOf extends Validator
{
    public function __construct(private readonly ValidatorInterface $validator, private readonly string $notArrayMessage = 'The value must be an array.')
    {
    }

    public function validate(mixed $value): array
    {
        $validationErrors = [];

        if (!is_array($value)) {
            return [$this->notArrayMessage];
        }

        foreach ($value as $index => $item) {
            $errors = $this->validator->validate($item);

            foreach ($errors as $error) {
                $validationErrors[] = (ValidationMessage::fromValidation($error))->prependPath(["$index"]);
            }
        }

        return $validationErrors;
    }
}
