<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use JsonSerializable;
use Idoit\Dto\Serialization\SerializableTrait;

class ValidationMessage implements JsonSerializable
{
    use SerializableTrait;

    public function __construct(private array $path, private string $message)
    {
    }

    public static function fromValidation(string|ValidationMessage $message): self
    {
        if (is_string($message)) {
            return new ValidationMessage([], $message);
        }
        return $message;
    }

    public function prependPath(array $path): self
    {
        return new ValidationMessage([
            ...$path,
            ...$this->path
        ], $this->message);
    }

    public function getPath(): array
    {
        return $this->path;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
