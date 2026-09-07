<?php

namespace idoit\Module\SystemSettings\Settings;

interface SettingInterface
{
    public static function getSettingKey(): string;

    public static function execute($value): void;
}
