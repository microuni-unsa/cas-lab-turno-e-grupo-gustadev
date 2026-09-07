<?php

namespace idoit\Component\Helper;

/**
 * i-doit array helper.
 *
 * Use this class for common- and convenience array functions.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ArrayHelper
{
    /**
     * Flatten nested arrays to only one level.
     *
     * @param array $source
     *
     * @return array
     * @see ID-9968
     */
    public static function flatten(array $source): array
    {
        $flat = [];

        foreach ($source as $item) {
            if (is_array($item)) {
                $flat = array_merge($flat, self::flatten($item));
            } else {
                $flat[] = $item;
            }
        }

        return $flat;
    }

    /**
     * @param string[] $source
     * @param string[] $replacement
     *
     * @return array
     */
    public static function replace(array $source, array $replacement): array
    {
        foreach ($replacement as $search => $replace) {
            $index = array_search($search, $source);
            if ($index) {
                $source[$index] = $replace;
            }
        }

        return $source;
    }

    /**
     * @param string[] $source
     * @param string   $value
     *
     * @return string[]
     */
    public static function without(array $source, string $value): array
    {
        if (in_array($value, $source, true)) {
            return array_values(
                array_filter(
                    $source,
                    static function ($sourceValue) use ($value) {
                        return $sourceValue !== $value;
                    }
                )
            );
        }

        return $source;
    }

    /**
     * @param string[] $source
     * @param string   $value
     *
     * @return string[]
     */
    public static function with(array $source, string $value): array
    {
        if (!in_array($value, $source, true)) {
            $source[] = $value;
        }

        return $source;
    }

    /**
     * @param array $source
     * @param mixed $element
     *
     * @return bool
     */
    public static function hasElement(array $source, $element): bool
    {
        return in_array($element, $source, true);
    }

    /**
     * @param array $source
     * @param array $exclude
     *
     * @return array
     */
    public static function difference(array $source, array $exclude): array
    {
        return array_diff($source, $exclude);
    }

    /**
     * @param array $source
     * @param array $target
     *
     * @return array
     */
    public static function delta(array $source, array $target): array
    {
        return [
            'removed' => array_diff($source, $target),
            'added'   => array_diff($target, $source)
        ];
    }

    /**
     * @param array $target
     * @param array $add
     *
     * @return array
     */
    public static function merge(array $target, array $add): array
    {
        return array_replace($target, $add);
    }

    /**
     * @param array $target
     * @param array $add
     *
     * @return array
     */
    public static function concat(array $target, array $add): array
    {
        return array_merge($target, $add);
    }

    /**
     * @param array    $haystack
     * @param callable $callback
     * @param mixed    $default
     *
     * @return mixed|null
     */
    public static function find(array $haystack, callable $callback, $default = null): mixed
    {
        foreach ($haystack as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * @param array $source
     * @return bool
     */
    public static function isList(array $source): bool
    {
        if ($source === []) {
            return true;
        }

        return array_keys($source) === range(0, count($source) - 1);
    }
}
