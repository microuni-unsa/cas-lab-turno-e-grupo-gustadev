<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

abstract class AbstractPlaceholder implements PlaceholderInterface
{
    /**
     * @var string
     */
    protected static string $pattern = '';

    /**
     * @var string
     */
    protected static string $description = '';

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return static::$pattern;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return static::$description;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isApplicable(string $value): bool
    {
        return strpos($value, $this->getPattern()) !== false;
    }

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        return $value;
    }
}
