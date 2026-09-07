<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\Counter;

use idoit\Component\PlaceholderReplacer\Config;
use isys_tenantsettings;

class CustomCounter extends AbstractCounter
{
    use CounterTrait;

    private const CUSTOM_COUNTER_WITH_LENGTH = 2;

    /**
     * @var string
     */
    protected static string $pattern = "/(%COUNTER_[a-zA-Z0-9_-]+#(\d+)%)|(%COUNTER_[a-zA-Z0-9_-]+%)/";

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        preg_match_all($this->getPattern(), $value, $matches, PREG_SET_ORDER);

        foreach ($matches as $index => $matchData) {
            $match = array_values(array_unique(array_filter($matchData, fn ($item) => !empty($item))));
            $key = $match[0];
            $length = null;

            if (count($match) === self::CUSTOM_COUNTER_WITH_LENGTH) {
                $length = $match[1];

                // @see ID-11073 Remove the length from the counter key.
                if (str_contains($key, '#')) {
                    $key = strstr($key, '#', true) . '%';
                }
            }
            $valueExploded = explode($matchData[0], $value);
            $value = array_reduce($valueExploded, function ($prev, $current) use ($key, $config, $length) {
                if ($prev === null) {
                    return $current;
                }
                $counter = static::getCounter($key . ':' . $config->getTable());
                if ($length !== null) {
                    $counter = str_pad($counter, $length, '0', STR_PAD_LEFT);
                }

                $return = $prev . $counter . $current;

                // @see ID-10888 Only update the counter in 'non-preview' mode, so that the counter won't increase falsely.
                if ($config->isPreview() === false) {
                    static::updateCounter($key . ':' . $config->getTable());
                }

                return $return;
            });

            // @see ID-10763 On duplication we do not update the counter for a preview
            if ($config->isPreview() === false) {
                isys_tenantsettings::set('cmdb.counter.' . trim(strtolower($key), '%'), static::getCounter($key . ':' . $config->getTable()));
            }
        }
        return $value;
    }

    /**
     * @param string $value
     * @param Config $config
     *
     * @return void
     */
    public static function setCounters(string $value, Config $config): void
    {
        preg_match_all(static::$pattern, $value, $matches, PREG_SET_ORDER);

        foreach ($matches as $index => $matchData) {
            $match = array_values(array_unique(array_filter($matchData, fn ($item) => !empty($item))));
            $key = $match[0];

            // @see ID-11073 Remove the length from the counter key.
            if (str_contains($key, '#')) {
                $key = strstr($key, '#', true) . '%';
            }

            if (isset(self::$currentCounters[$key . ':' . $config->getTable()])) {
                continue;
            }

            self::$currentCounters[$key . ':' . $config->getTable()] = isys_tenantsettings::get('cmdb.counter.' . trim(strtolower($key), '%'), 1);
        }
    }

    /**
     * @param string|null $key
     *
     * @return void
     */
    public static function decreaseCounter(?string $key = null): void
    {
        if ($key !== null && self::$currentCounters[$key] && self::$currentCounters[$key] > 0) {
            [$placeholder,] = explode(':', $key);
            self::$currentCounters[$key]--;
            isys_tenantsettings::set('cmdb.counter.' . trim(strtolower($placeholder), '%'), self::$currentCounters[$key]);
        }

        foreach (self::$currentCounters as $key => $value) {
            if ($value < 1) {
                continue;
            }
            [$placeholder,] = explode(':', $key);
            self::$currentCounters[$key]--;
            isys_tenantsettings::set('cmdb.counter.' . trim(strtolower($placeholder), '%'), self::$currentCounters[$key]);
        }
    }
}
