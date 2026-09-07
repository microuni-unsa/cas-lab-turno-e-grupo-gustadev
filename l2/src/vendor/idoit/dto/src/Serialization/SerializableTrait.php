<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

trait SerializableTrait
{
    function jsonSerialize(): array
    {
        return Serializer::toJson($this);
    }
}
