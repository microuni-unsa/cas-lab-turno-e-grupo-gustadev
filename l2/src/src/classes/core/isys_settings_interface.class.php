<?php

/**
 * i-doit settings interface
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_settings_interface
{
    /**
     * @return array
     */
    public static function get_definition(): array;

    /**
     * @param array $settings
     *
     * @return void
     */
    public static function extend(array $settings = []): void;

    /**
     * @param isys_component_database $database
     *
     * @return void
     */
    public static function initialize(isys_component_database $database): void;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function set(string $key, mixed $value): void;

    /**
     * @param string $key
     *
     * @return void
     */
    public static function remove(string $key): void;

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public static function get(string|null $key = null, mixed $default = ''): mixed;

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool;

    /**
     * @return array
     */
    public static function regenerate(): array;

    /**
     * @return void
     */
    public static function force_save(): void;
}
