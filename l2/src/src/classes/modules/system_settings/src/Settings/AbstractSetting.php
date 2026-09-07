<?php

namespace idoit\Module\SystemSettings\Settings;

abstract class AbstractSetting implements SettingInterface
{
    protected const SETTING_KEY = '';

    /**
     * @return string
     */
    public static function getSettingKey(): string
    {
        return static::SETTING_KEY;
    }
}
