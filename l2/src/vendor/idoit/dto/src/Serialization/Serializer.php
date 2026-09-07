<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

class Serializer
{
    protected static function convertToJson(ReflectionProperty $property, mixed $value): mixed
    {
        $attribute = Format::fromProperty($property);
        if ($attribute) {
            return $attribute->toJson($value);
        }

        if (is_object($value)) {
            return self::toJson($value);
        }

        return $value;
    }

    public static function toJson(object $object): array
    {
        $initial = [];
        $class = new ReflectionClass($object);
        $discriminators = self::getClassDiscriminators(get_class($object));
        foreach ($discriminators as $discriminator) {
            if (!$discriminator instanceof Discriminator) {
                continue;
            }
            $type = $discriminator->getType($object);
            if (!$type) {
                continue;
            }
            $key = $discriminator->getTypeKey();
            $initial[$key] = $type;
        }
        return array_reduce(
            $class->getProperties(),
            function (array $result, ReflectionProperty $property) use ($object) {
                $property->setAccessible(true);
                $value = $property->getValue($object);
                $result[$property->getName()] = self::convertToJson($property, $value);
                return $result;
            }, $initial);
    }

    protected static function convertFromJson(ReflectionProperty $property, mixed $value): mixed
    {
        $attribute = Format::fromProperty($property);
        if ($attribute) {
            return $attribute->fromJson($value);
        }

        $type = $property->getType();
        if ($type instanceof ReflectionNamedType && class_exists($type->getName())) {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            if (is_array($value)) {
                return self::fromJson($type->getName(), $value);
            }
        }

        return $value;
    }

    public static function fromJson(string $class, array $json): object
    {
        $discriminators = self::getClassDiscriminators($class);
        $targetClass = new ReflectionClass($class);

        foreach ($discriminators as $discriminator) {
            $key = $discriminator->getTypeKey();
            if (!isset($json[$key])) {
                continue;
            }
            $mappedClass = $discriminator->getClass($json[$key]);
            if (!$mappedClass) {
                continue;
            }
            $targetClass = new ReflectionClass($mappedClass);
            if (!$targetClass->isAbstract()) {
                break;
            }
        }
        if ($targetClass->isAbstract()) {
            return self::fromJson($targetClass->getName(), $json);
        }

        $instance = $targetClass->newInstanceWithoutConstructor();
        return array_reduce($targetClass->getProperties(), function (object $result, \ReflectionProperty $property) use ($json) {
            $property->setAccessible(true);
            $property->setValue($result, self::convertFromJson($property, $json[$property->getName()] ?? null));
            return $result;
        }, $instance);
    }

    /**
     * @param string $class
     * @return Discriminator[]|array
     * @throws \ReflectionException
     */
    private static function getClassDiscriminators(string $class): array
    {
        $discriminators = [];
        $class = new ReflectionClass($class);
        $parent = $class;
        do {
            foreach ($parent->getAttributes(Discriminator::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $instance = $attribute->newInstance();
                if (!$instance instanceof Discriminator) {
                    continue;
                }
                $discriminators[] = $instance;
            }
        } while ($parent = $parent->getParentClass());

        return array_reverse($discriminators);
    }
}
