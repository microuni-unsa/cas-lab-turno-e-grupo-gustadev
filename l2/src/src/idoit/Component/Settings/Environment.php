<?php

namespace idoit\Component\Settings;

use isys_application;
use isys_tenantsettings;

class Environment
{
    /**
     * @return string[]
     */
    public static function getAllowedVariables(): array
    {
        return [
            'IDOIT_APP_URL' => 'Definition of applications default url.',
        ];
    }

    /**
     * @param string $ident
     *
     * @return string|null
     */
    public static function get(string $ident): ?string
    {
        $container = isys_application::instance()->container;

        return isset(self::getAllowedVariables()[$ident]) ? ($container->hasParameter($ident) ? $container->getParameter($ident) :  null) : null;
    }

    /**
     * @return string
     */
    public static function getBaseUri(): string
    {
        return (string) (self::get('IDOIT_APP_URL') ?? isys_tenantsettings::get('system.base.uri') ?? null);
    }
}
