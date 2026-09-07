<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayFormat extends Format
{
    public function __construct(private string $class)
    {
    }

    public function toJson(mixed $object): mixed
    {
        if (!is_array($object)) {
            return [];
        }

        return array_map(function (mixed $entry) {
            if (is_object($entry)) {
                return Serializer::toJson($entry);
            }
            return json_encode($entry);
        }, $object);
    }

    public function fromJson(mixed $string): mixed
    {
        if (!is_array($string)) {
            return [];
        }
        return array_map(fn ($data) => is_array($data) ? Serializer::fromJson($this->class, $data) : null, $string);
    }
}
