<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

class SysId extends AbstractPlaceholder implements ApplyOnceInterface
{
    /**
     * @var string
     */
    protected static string $pattern = '%SYSID%';

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        if ($config->getSysId() === '') {
            return $value;
        }
        return str_replace($this->getPattern(), $config->getSysId(), $value);
    }
}
