<?php

namespace idoit\Component\Helper;

use Exception;
use isys_application;
use isys_exception_general;

/**
 * i-doit Unserialize helper
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     https://www.i-doit.com/
 */
class Unserialize
{
    public const EMPTY_ARRAY = 'a:0:{}';
    public const EMPTY_OBJECT = 'O:8:"stdClass":0:{}';
    public const EMPTY_STRING = 's:0:"";';
    public const BOOL_FALSE = 'b:0;';
    public const BOOL_TRUE = 'b:1;';
    public const DOUBLE_ZERO = 'd:0;';
    public const INT_ZERO = 'i:0;';
    public const NULL = 'N;';

    /**
     * @param string|null $serialized
     *
     * @return bool|float|int|string
     * @throws \Exception
     */
    public static function toScalar(?string $serialized) // :bool|float|int|string
    {
        try {
            // d = double / float, b = bool, i = int, s = string.
            if (!is_string($serialized) || !preg_match('~^[dbis]:(\d+)~', $serialized)) {
                throw new isys_exception_general('The given value is not unserializable!');
            }

            $unserialized = unserialize($serialized, ['allowed_classes' => false]);

            if (is_scalar($unserialized)) {
                return $unserialized;
            }

            throw new isys_exception_general('The given string could not be unserialized to an scalar!');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')
                ->warning('Your given configuration is malformed!');
        }

        return false;
    }

    /**
     * @param string|null $serialized
     *
     * @return array
     * @throws \Exception
     */
    public static function toArray(?string $serialized): array
    {
        // return an empty array for Null value
        if ($serialized === Unserialize::NULL) {
            return [];
        }
        try {
            if (!is_string($serialized) || !preg_match('~^a:(\d+):~', $serialized)) {
                throw new isys_exception_general('The given value is not unserializable!');
            }

            $unserialized = unserialize($serialized, ['allowed_classes' => false]);

            if (is_array($unserialized)) {
                return $unserialized;
            }

            throw new isys_exception_general('The given string could not be unserialized to an array!');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')
                ->warning('Your given configuration is malformed!');
        }

        return [];
    }

    /**
     * @param string|null $serialized
     * @param array       $allowedClasses
     *
     * @return object
     * @throws \Exception
     */
    public static function toObject(?string $serialized, array $allowedClasses): object
    {
        try {
            if (!is_string($serialized) || !preg_match('~^O:(\d+):~', $serialized)) {
                throw new isys_exception_general('The given value is not unserializable!');
            }

            $unserialized = unserialize($serialized, ['allowed_classes' => $allowedClasses]);

            if (is_object($unserialized)) {
                return $unserialized;
            }

            throw new isys_exception_general('The given string could not be unserialized to an object');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')
                ->warning('Your given configuration is malformed!');
        }

        return new \stdClass();
    }
}
