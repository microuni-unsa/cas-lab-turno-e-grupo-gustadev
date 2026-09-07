<?php

namespace idoit\Component\Settings;

/**
 * i-doit User setting component
 *
 * @package     idoit\Component
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

class User extends Settings
{
    /**
     * @param \isys_component_database $db
     * @param \isys_component_session  $session
     *
     * @return User
     */
    public static function factory(\isys_component_database $db, \isys_component_session $session): User
    {
        if ($session->is_logged_in()) {
            \isys_usersettings::initialize($db);
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
        return \isys_usersettings::get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value): static
    {
        \isys_usersettings::set($key, $value);

        return $this;
    }

    /**
     * @param array $setting
     *
     * @return void
     */
    public function extend($setting): void
    {
        \isys_usersettings::extend($setting);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key): void
    {
        \isys_usersettings::remove($key);
    }

    /**
     * @return void
     */
    public function save(): void
    {
        \isys_usersettings::force_save();
    }
}
