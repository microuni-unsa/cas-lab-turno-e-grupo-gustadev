<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\DateTime;

use idoit\Component\PlaceholderReplacer\Config;
use idoit\Component\PlaceholderReplacer\Placeholder\AbstractPlaceholder;

abstract class AbstractDateTime extends AbstractPlaceholder
{
    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        return str_replace($this->getPattern(), date(trim($this->getPattern(), '%')), $value);
    }
}
