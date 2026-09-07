<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder;

use idoit\Component\PlaceholderReplacer\Config;

interface PlaceholderInterface
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isApplicable(string $value): bool;

    /**
     * @param string $value
     * @param Config $config
     *
     * @return string
     */
    public function replacePlaceholder(string $value, Config $config): string;
}
