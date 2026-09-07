<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Cable;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_ajax_handler_quick_info;
use isys_application;
use isys_tenantsettings;

class Connection implements DynamicCallbackInterface
{
    /**
     * @param $data
     * @param $extra
     *
     * @return mixed|string
     * @throws \Exception
     */
    public static function render($data, $extra = null)
    {
        $data = (int)$data;
        if ($data <= 0) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $fields = [
            'isys_obj__id as objId',
            'isys_obj__title as objTitle',
            'isys_catg_connector_list__id as connectorId',
            'isys_catg_connector_list__title as connectorTitle',
        ];

        $query = "SELECT " . implode(',', $fields) . " FROM isys_catg_connector_list
         INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
         INNER JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
         WHERE isys_cable_connection__isys_obj__id = {$dao->convert_sql_id($data)};";

        $result = $dao->retrieve($query);
        if ($result->count() === 0) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }
        $connectors = [];
        $quickinfo = isys_ajax_handler_quick_info::instance();

        while ($row = $result->get_row()) {
            $connectors[] = $quickinfo->get_quick_info(
                $row['objId'],
                "{$row['objTitle']} ({$row['connectorTitle']})",
                C__LINK__OBJECT
            );
        }

        return implode(' &#x2194; ', $connectors);
    }
}
