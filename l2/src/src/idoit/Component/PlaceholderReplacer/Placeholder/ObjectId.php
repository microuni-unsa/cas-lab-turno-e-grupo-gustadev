<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

class ObjectId extends AbstractPlaceholder implements ApplyOnceInterface
{
    /**
     * @var string
     */
    protected static string $pattern = '%OBJID%';

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        if ($config->getObjectId() === null) {
            return $value;
        }
        return str_replace($this->getPattern(), $config->getObjectId(), $value);
    }
}
