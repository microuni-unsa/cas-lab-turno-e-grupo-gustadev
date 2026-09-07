<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\Counter;

use idoit\Component\PlaceholderReplacer\Config;

trait CounterTrait
{
    /**
     * @var array
     */
    protected static array $currentCounters = [];

    /**
     * @param string $key
     * @param int    $initialValue
     *
     * @return void
     */
    public static function updateCounter(string $key, int $initialValue = 0): void
    {
        if (!isset(self::$currentCounters[$key])) {
            self::$currentCounters[$key] = $initialValue;
            return;
        }
        self::$currentCounters[$key]++;
    }

    /**
     * @param string|null $key
     *
     * @return void
     */
    public static function decreaseCounter(?string $key = null): void
    {
        if ($key !== null && self::$currentCounters[$key] && self::$currentCounters[$key] > 0) {
            self::$currentCounters[$key]--;
        }

        foreach (self::$currentCounters as $key => $value) {
            if ($value < 1) {
                continue;
            }
            self::$currentCounters[$key]--;
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function getCounter($key): mixed
    {
        return self::$currentCounters[$key] ?? null;
    }

    /**
     * @param string|null $key
     *
     * @return array|mixed
     */
    public static function getCurrentCounters(?string $key = null): mixed
    {
        if ($key && isset(self::$currentCounters[$key])) {
            return self::$currentCounters[$key];
        }

        return self::$currentCounters;
    }

    /**
     * @param string $value
     * @param Config $config
     *
     * @return void
     */
    public static function setCounters(string $value, Config $config): void
    {
        preg_match_all(static::$pattern, $value, $matches);

        foreach ($matches[0] as $search) {
            if (isset(self::$currentCounters[$search . ':' . $config->getTable()])) {
                continue;
            }
            self::$currentCounters[$search . ':' . $config->getTable()] = $config->getTableInitialCount();
        }
    }
}
