<?php

namespace idoit\Module\SystemSettings\Settings\Tenant;

use idoit\Module\SystemSettings\Settings\AbstractSetting;
use idoit\Module\SystemSettings\Settings\SettingInterface;
use isys_application;
use isys_tenantsettings;

class LastLogin extends AbstractSetting implements SettingInterface
{
    protected const SETTING_KEY = 'logging.user.last-login';

    /**
     * @param $value
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function execute($value): void
    {
        $isInactive = (bool)$value === false;

        if ($isInactive) {
            $updateQuery = 'UPDATE isys_cats_person_list SET isys_cats_person_list__last_login = NULL;';

            $cmdb = isys_application::instance()->container->get('cmdb_dao');
            $cmdb->update($updateQuery) && $cmdb->apply_update();
        }
    }
}
