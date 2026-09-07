<?php declare(strict_types = 1);

namespace Idoit\Dto\Validation;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Validation
{
    /**
     * @param object $object
     * @param string[] $path
     *
     * @return ValidationMessage[]
     *
     * @throws ReflectionException
     */
    public static function validate(object $object, array $path = []): array
    {
        $errors = [];
        $class = new ReflectionClass($object);

        $methods = array_filter($class->getMethods(), fn (ReflectionMethod $method) => !empty($method->getAttributes(ValidatorInterface::class, ReflectionAttribute::IS_INSTANCEOF)));

        foreach ($methods as $method) {
            $result = $method->invoke($object, $path);
            if (!is_array($result)) {
                continue;
            }
            foreach ($result as $error) {
                if ($error instanceof ValidationMessage) {
                    $errors[] = $error;
                }
            }
        }

        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $validators = $property->getAttributes(ValidatorInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $subpath = [...$path, $property->getName()];
            foreach ($validators as $validator) {
                $instance = $validator->newInstance();
                if (!$instance instanceof ValidatorInterface) {
                    continue;
                }
                foreach ($instance->validate($value) as $message) {
                    $errors[] = (ValidationMessage::fromValidation($message))->prependPath($subpath);
                }
            }
            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    if (!is_object($item)) {
                        continue;
                    }
                    foreach (self::validate($item, [...$subpath, "$key"]) as $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (is_object($value)) {
                foreach (self::validate($value, $subpath) as $message) {
                    $errors[] = $message;
                }
            }
        }

        if (empty($errors)) {
            return [];
        }
        return $errors;
    }
}
