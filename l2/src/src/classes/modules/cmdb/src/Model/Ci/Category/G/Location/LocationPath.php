<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Location;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;
use isys_tenantsettings;

/**
 * i-doit
 *
 * Callback renderer for location path.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class LocationPath implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param $data
     * @param $extra
     *
     * @return mixed|string
     * @throws \Exception
     */
    public static function render($data, $extra = null)
    {
        if (!$data || !is_numeric($data)) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $path = isys_application::instance()->container->get('idoit.cmdb.locationpath')->getLocationPathForObjectList((int)$data);

        // @see ID-10992 Do not process objects without set location.
        if ($path === null) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        return $path;
    }
}
