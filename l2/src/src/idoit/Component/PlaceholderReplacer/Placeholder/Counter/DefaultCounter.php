<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\Counter;

use idoit\Component\PlaceholderReplacer\Config;

class DefaultCounter extends AbstractCounter
{
    use CounterTrait;

    /**
     * @var string
     */
    protected static string $pattern = "/%COUNTER%/";

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        $trimmedPattern = trim(static::$pattern, '/');
        $currentCount = static::getCounter($trimmedPattern . ':' . $config->getTable());

        // @see ID-10888 Only update the counter in 'non-preview' mode, so that the counter won't increase falsely.
        if ($config->isPreview() === false) {
            static::updateCounter($trimmedPattern . ':' . $config->getTable());
        }

        return preg_replace($this->getPattern(), $currentCount, $value);
    }
}
