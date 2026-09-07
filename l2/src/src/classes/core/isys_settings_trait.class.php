<?php

use idoit\Event\System\Settings\ExtendSystemSettings;
use idoit\Event\System\Settings\ExtendTenantSettings;
use idoit\Event\System\Settings\ExtendUserSettings;

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait isys_settings_trait
{
    protected static bool $isInitialized = false;
    protected static array $settings = [];
    private static bool $hasChanged = false;
    private static bool $isExtended = false;

    public static function get_definition(): array
    {
        if (!self::$isExtended) {
            self::extendByEvents();
        }

        return self::$definition;
    }

    public static function getRawDefinition(): array
    {
        $definitions = [];
        $settingDefinitions = self::get_definition();

        foreach ($settingDefinitions as $definition) {
            $definitions = array_merge($definitions, $definition);
        }

        return $definitions;
    }

    /**
     * @deprecated Please use '\idoit\Event\System\Settings\ExtendTenantSettings' event with event dispatcher.
     */
    public static function extend(array $settings = []): void
    {
        self::$definition = array_merge_recursive(self::$definition, $settings);
    }

    /**
     * @deprecated This is an empty alias for compatibility.
     */
    public static function is_initialized(): bool
    {
        return true;
    }

    /**
     * @deprecated This is an empty alias for compatibility.
     */
    public static function load_cache(string $cacheDirectory): void
    {
    }

    public static function set(string $key, mixed $value): void
    {
        self::$hasChanged = true;

        if (!isset(self::$settings[$key])) {
            self::$dao->set($key, $value)
                ->apply_update();
        }

        self::$settings[$key] = $value;
    }

    /**
     * @throws isys_exception_dao
     */
    public static function remove(string $key): void
    {
        unset(self::$settings[$key]);

        self::$dao->remove($key);
    }

    public static function get(string|null $key = null, mixed $default = ''): mixed
    {
        if ($key === null) {
            return self::$settings;
        }

        return isset(self::$settings[$key]) && self::$settings[$key] !== '' ? self::$settings[$key] : $default;
    }

    public static function has(string $key): bool
    {
        return isset(self::$settings[$key]);
    }

    /**
     * @deprecated This is an empty alias for compatibility.
     */
    public static function override(array $settings): void
    {
    }

    /**
     * Dispatch events to extend the settings.
     *
     * @throws Exception
     * @see ID-11818
     */
    private static function extendByEvents(): void
    {
        if (str_contains(strtolower(self::class), 'user')) {
            $event = new ExtendUserSettings();
        } elseif (str_contains(strtolower(self::class), 'tenant')) {
            $event = new ExtendTenantSettings();
        } else {
            $event = new ExtendSystemSettings();
        }

        $settings = [];
        isys_application::instance()->container->get('event_dispatcher')->dispatch($event, $event::NAME);

        foreach ($event->getSettings() as $settingsCollection) {
            $settings[$settingsCollection->getName()] = $settings[$settingsCollection->getName()] ?? [];

            foreach ($settingsCollection->getSettings() as $key => $setting) {
                $settings[$settingsCollection->getName()][$key] = $setting->toArray();
            }
        }

        self::$definition = array_merge_recursive(self::$definition, $settings);
        self::$isExtended = true;
    }

    public static function force_save(): void
    {
        // Save to database.
        self::$dao->save(self::$settings);
    }

    /**
     * Before destructing the usersettings we want to save the data.
     */
    public static function shutdown(): void
    {
        if (self::$hasChanged) {
            self::force_save();
        }
    }
}
