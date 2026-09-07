<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

class ObjectTypeId extends AbstractPlaceholder implements ApplyOnceInterface
{
    /**
     * @var string
     */
    protected static string $pattern = '%OBJTYPEID%';

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        if ($config->getObjectTypeId() === null) {
            return $value;
        }
        return str_replace($this->getPattern(), $config->getObjectTypeId(), $value);
    }
}
