<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringArrayFormat extends Format
{
    public function toJson(mixed $object): mixed
    {
        if (!is_array($object)) {
            return [];
        }

        return array_reduce($object, function (array $result, mixed $entry) {
            if (is_string($entry)) {
                return [...$result, $entry];
            }
            return $result;
        }, []);
    }

    public function fromJson(mixed $string): mixed
    {
        if (is_string($string)) {
            $string = json_decode($string);
        }
        if (!is_array($string)) {
            return [];
        }
        return array_reduce($string, function (array $result, mixed $entry) {
            if (is_string($entry)) {
                return [...$result, $entry];
            }
            return $result;
        }, []);
    }
}
