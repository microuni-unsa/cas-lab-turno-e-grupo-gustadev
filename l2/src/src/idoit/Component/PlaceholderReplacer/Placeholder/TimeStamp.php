<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

class TimeStamp extends AbstractPlaceholder
{
    /**
     * @var string
     */
    protected static string $pattern = '%TIMESTAMP%';

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        return str_replace($this->getPattern(), time(), $value);
    }
}
