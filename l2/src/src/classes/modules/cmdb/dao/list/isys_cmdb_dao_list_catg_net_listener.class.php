<?php

/**
 * i-doit
 *
 * DAO: specific category list for network listener
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_net_listener extends isys_component_dao_category_table_list
{
    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $language = isys_application::instance()->container->get('language');

        return [
            'isys_catg_net_listener_list__opened_by'   => 'LC__CMDB__CATG__NET_LISTENER__OPENED_BY_APPLICATION',
            'isys_cats_net_ip_addresses_list__title'   => 'LC__CMDB__CATG__NET_LISTENER__IP_ADDRESS',
            'port_range'                               => $language->get_in_text('Port / LC__UNIVERSAL__PORT_RANGE'),
            'isys_net_protocol__title'                 => 'LC__CMDB__CATG__NET_LISTENER__PROTOCOL',
            'isys_net_protocol_layer_5__title'         => 'LC__CMDB__CATG__NET_LISTENER__LAYER_5_PROTOCOL',
            'isys_catg_net_listener_list__gateway'     => 'LC__CATG__NET_CONNECTIONS__GATEWAY',
            'isys_catg_net_listener_list__description' => 'LC__CMDB__CATG__DESCRIPTION'
        ];
    }

    public function make_row_link($getParams = [])
    {
        $getParams['cateID'] = '[{isys_catg_net_listener_list__id}]';

        return '?' . isys_glob_http_build_query($getParams);
    }

    /**
     * Modifies single rows for displaying links or getting translations
     *
     * @param array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row['port_range'] = $p_row['isys_catg_net_listener_list__port_from'];
        $p_row['isys_catg_net_listener_list__opened_by'] = isys_tenantsettings::get('gui.empty_value', '-');
        $p_row['isys_catg_net_listener_list__gateway'] = isys_tenantsettings::get('gui.empty_value', '-');

        if ($p_row['isys_catg_net_listener_list__port_from'] != $p_row['isys_catg_net_listener_list__port_to']) {
            $p_row['port_range'] .= '-' . $p_row['isys_catg_net_listener_list__port_to'];
        }

        if ($p_row['isys_catg_net_listener_list__opened_by']) {
            $p_row['isys_catg_net_listener_list__opened_by'] = isys_ajax_handler_quick_info::instance()
                ->getQuickInfoReplacement($p_row['isys_catg_net_listener_list__opened_by'], $p_row['opened_by_title']);
        }

        if ($p_row['isys_catg_net_listener_list__gateway']) {
            $p_row['isys_catg_net_listener_list__gateway'] = isys_ajax_handler_quick_info::instance()
                ->getQuickInfoReplacement($p_row['isys_catg_net_listener_list__gateway'], $p_row['gateway_title']);
        }
    }
}
