<?php

namespace idoit\Component\Settings;

/**
 * i-doit Tenant setting component
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class Tenant extends Settings
{
    /**
     * @param \isys_component_database $db
     * @param \isys_component_session  $session
     *
     * @return Tenant
     */
    public static function factory(\isys_component_database $db, \isys_component_session $session): Tenant
    {
        if ($session->is_logged_in()) {
            \isys_tenantsettings::initialize($db, $session->get_mandator_id());
        }

        return new self();
    }

    /**
     * @param null   $key
     * @param string $default
     *
     * @return mixed
     */
    public function get($key = null, $default = ''): mixed
    {
        return \isys_tenantsettings::get($key, $default);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getLike(string $key): array
    {
        return \isys_tenantsettings::getLike($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value): static
    {
        \isys_tenantsettings::set($key, $value);

        return $this;
    }

    /**
     * @param array $setting
     */
    public function extend($setting): void
    {
        \isys_tenantsettings::extend($setting);
    }

    /**
     * @param string $key
     */
    public function remove($key): void
    {
        \isys_tenantsettings::remove($key);
    }

    /**
     * @return void
     */
    public function save(): void
    {
        \isys_tenantsettings::force_save();
    }
}
