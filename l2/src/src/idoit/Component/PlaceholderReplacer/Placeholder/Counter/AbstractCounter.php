<?php

namespace idoit\Component\PlaceholderReplacer\Placeholder\Counter;

use idoit\Component\PlaceholderReplacer\Placeholder\AbstractPlaceholder;

abstract class AbstractCounter extends AbstractPlaceholder
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isApplicable(string $value): bool
    {
        $matches = [];
        preg_match($this->getPattern(), $value, $matches);

        return !empty($matches);
    }
}
