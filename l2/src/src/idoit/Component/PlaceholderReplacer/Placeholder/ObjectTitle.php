<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

class ObjectTitle extends AbstractPlaceholder implements ApplyOnceInterface
{
    /**
     * @var string
     */
    protected static string $pattern = '%OBJTITLE%';

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        if ($config->getObjectTitle() === '') {
            return $value;
        }

        return str_replace($this->getPattern(), $config->getObjectTitle(), $value);
    }
}
