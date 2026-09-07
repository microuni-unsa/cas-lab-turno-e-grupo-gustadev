<?php

namespace idoit\Component\FeatureManager;

interface FeatureCheckInterface
{
    /**
     * @return bool
     */
    public static function isFeatureEnabled(): bool;
}
