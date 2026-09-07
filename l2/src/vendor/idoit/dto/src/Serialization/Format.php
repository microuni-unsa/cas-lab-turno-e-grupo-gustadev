<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use ReflectionAttribute;
use ReflectionProperty;

abstract class Format
{
    public abstract function toJson(mixed $object): mixed;

    public abstract function fromJson(mixed $string): mixed;

    public static function fromProperty(ReflectionProperty $property): ?Format
    {
        $attribute = $property->getAttributes(Format::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        if ($attribute === null) {
            return null;
        }

        $instance = $attribute->newInstance();
        if (!$instance instanceof Format) {
            return null;
        }
        return $instance;
    }
}
