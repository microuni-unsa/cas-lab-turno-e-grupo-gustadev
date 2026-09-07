<?php

/**
 * i-doit
 *
 * @package   synetics_admin
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_synetics_admin extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_synetics_admin
     */
    private static $instance;

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     */
    public function get_auth_methods()
    {
        return [
            'admin-center'         => [
                'title'  => 'LC__SYNETICS_ADMIN__AUTH__OPEN_ADMIN_CENTER',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW
                ]
            ],
            'manage-addons' => [
                'title'  => 'LC__SYNETICS_ADMIN__AUTH__MANAGE_ADDONS',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::EXECUTE
                ]
            ],
            'manage-license' => [
                'title'  => 'LC__SYNETICS_ADMIN__AUTH__MANAGE_LICENSE',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::SUPERVISOR
                ]
            ],
            'addon-info' => [
                'title'  => 'LC__SYNETICS_ADMIN__AUTH__ADDON_INFO',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW
                ]
            ],
        ];
    }

    /**
     * Get ID of related module
     *
     * @return int
     */
    public function get_module_id()
    {
        return C__MODULE__SYNETICS_ADMIN;
    }

    /**
     * Get title of related module
     *
     * @return string
     */
    public function get_module_title()
    {
        return 'LC__SYNETICS_ADMIN';
    }

    /**
     * @return bool
     */
    public function canAccessAdminCenter(): bool
    {
        return $this->is_allowed_to(isys_auth::VIEW, 'admin-center');
    }

    /**
     * @return bool
     */
    public function canViewAddons(): bool
    {
        return $this->is_allowed_to(isys_auth::VIEW, 'addon-info');
    }

    /**
     * @return bool
     */
    public function canManageAddons(): bool
    {
        return $this->is_allowed_to(isys_auth::EXECUTE, 'manage-addons');
    }

    /**
     * @return bool
     */
    public function canManageLicense(): bool
    {
        return $this->is_allowed_to(isys_auth::SUPERVISOR, 'manage-license');
    }

    /**
     * @return bool
     */
    public function hasAddonRights()
    {
        return $this->canViewAddons() || $this->canManageAddons();
    }

    /**
     * Retrieve singleton instance of authorization class
     *
     * @return isys_auth_synetics_admin
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
