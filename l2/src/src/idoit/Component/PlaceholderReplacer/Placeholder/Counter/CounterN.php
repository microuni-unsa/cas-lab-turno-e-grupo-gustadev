<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\Counter;

use idoit\Component\PlaceholderReplacer\Config;

class CounterN extends AbstractCounter
{
    use CounterTrait;

    /**
     * @var string
     */
    protected static string $pattern = "/%COUNTER#(\d+)%/";

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string
    {
        preg_match_all($this->getPattern(), $value, $matches);

        foreach ($matches[0] as $index => $search) {
            $length = (int)$matches[1][$index];
            $value = str_replace($search, str_pad(static::getCounter($search . ':' . $config->getTable()), $length, '0', STR_PAD_LEFT), $value);

            // @see ID-10888 Only update the counter in 'non-preview' mode, so that the counter won't increase falsely.
            if ($config->isPreview() === false) {
                static::updateCounter($search . ':' . $config->getTable());
            }
        }

        return $value;
    }
}
