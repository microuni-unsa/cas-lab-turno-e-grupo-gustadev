<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ReadonlyFormat extends Format
{
    public function toJson(mixed $object): mixed
    {
        if (!is_object($object) || $object === null) {
            return null;
        }
        return Serializer::toJson($object);
    }

    public function fromJson(mixed $string): mixed
    {
        return null;
    }
}
