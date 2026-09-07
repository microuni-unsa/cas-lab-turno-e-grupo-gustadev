<?php

use idoit\Component\Helper\Ip;

/**
 * i-doit
 *
 * DAO: Port category list 'IP'
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_ip extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__IP');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'isys_cats_net_ip_addresses_list__title') {
            $p_column = 'isys_cats_net_ip_addresses_list__ip_address_long';
        }

        return $p_column . " " . $p_direction;
    }

    /**
     * Modifies content of each line.
     *
     * @param   array &$p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        $wwwDir = isys_application::instance()->www_path;
        $language = isys_application::instance()->container->get('language');

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');

        // Assigned net.
        if ($p_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'] > 0) {
            /** @var isys_cmdb_dao_category_s_net_ip_addresses $l_cats_net_ip_addresses_dao */
            $l_cats_net_ip_addresses_dao = isys_cmdb_dao_category_s_net_ip_addresses::instance($this->get_database_component());

            $l_row = $l_cats_net_ip_addresses_dao->get_data($p_row['isys_catg_ip_list__isys_cats_net_ip_addresses_list__id'])
                ->get_row();

            if ($l_row['isys_cats_net_ip_addresses_list__isys_obj__id'] > 0) {
                $l_row2 = $l_cats_net_ip_addresses_dao->get_data_by_object($l_row['isys_cats_net_ip_addresses_list__isys_obj__id'])
                    ->get_row();

                // @see  ID-4941  Adding the object title before the ID.
                $p_row['isys_catg_ip_list__assigned_net'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                    $l_row['isys_cats_net_ip_addresses_list__isys_obj__id'],
                    $l_row2['isys_obj__title']
                );
            }
        }

        if ($p_row['isys_catg_ip_list__hostname']) {
            if ($p_row['isys_catg_ip_list__domain']) {
                $p_row['isys_catg_ip_list__hostname'] .= '.' . $p_row['isys_catg_ip_list__domain'];
            }
        } else {
            $p_row['isys_catg_ip_list__hostname'] = $l_empty_value;
        }

        // "Yes" / "No" for the primary field.
        $p_row['isys_catg_ip_list__primary'] = '<div class="display-flex align-items-center">' . ($p_row['isys_catg_ip_list__primary'] == 0
            ? '<img src="' . $wwwDir . 'images/axialis/basic/symbol-cancel.svg" alt="" class="mr5" /><span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>'
            : '<img src="' . $wwwDir . 'images/axialis/basic/symbol-ok.svg" alt="" class="mr5" /><span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>') .
            '</div>';

        $p_row['isys_catg_ip_list__active'] = '<div class="display-flex align-items-center">' . ($p_row['isys_catg_ip_list__active'] == 0
            ? '<img src="' . $wwwDir . 'images/axialis/basic/symbol-cancel.svg" alt="" class="mr5" /><span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>'
            : '<img src="' . $wwwDir . 'images/axialis/basic/symbol-ok.svg" alt="" class="mr5" /><span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>') .
            '</div>';

        $standardGatewaySql = 'SELECT isys_cats_net_list__isys_catg_ip_list__id AS standardGateway
            FROM isys_catg_ip_list
            LEFT JOIN isys_cats_net_list ON isys_cats_net_list__isys_catg_ip_list__id = isys_catg_ip_list__id
            WHERE isys_catg_ip_list__id = ' . $this->convert_sql_id($p_row['isys_catg_ip_list__id']) . '
            LIMIT 1;';

        $standardGateway = (int)$this->retrieve($standardGatewaySql)->get_row_value('standardGateway');

        $p_row['standard_gateway'] = '<div class="display-flex align-items-center">' . ($standardGateway === 0
                ? '<img src="' . $wwwDir . 'images/axialis/basic/symbol-cancel.svg" alt="" class="mr5" /><span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>'
                : '<img src="' . $wwwDir . 'images/axialis/basic/symbol-ok.svg" alt="" class="mr5" /><span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>') .
            '</div>';

        // If we display an IPv6 address, we shorten the output.
        if ($p_row['isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            $p_row['ip_assignment'] = $language->get($p_row['isys_ipv6_assignment__title']);
            $p_row['isys_cats_net_ip_addresses_list__title'] = Ip::validate_ipv6($p_row['isys_cats_net_ip_addresses_list__title'], true);
            if (empty($p_row['isys_cats_net_ip_addresses_list__title'])) {
                $p_row['isys_cats_net_ip_addresses_list__title'] = $l_empty_value;
            }
        } else {
            $p_row['ip_assignment'] = $language->get($p_row['isys_ip_assignment__title']);
            if (empty($p_row['isys_cats_net_ip_addresses_list__title'])) {
                $p_row['isys_cats_net_ip_addresses_list__title'] = $l_empty_value;
            }
        }

        /** @var  isys_cmdb_dao_category_g_ip $l_dao_ip */
        $l_dao_ip = isys_cmdb_dao_category_g_ip::instance($this->get_database_component());

        // Retrieve domains.
        $l_res_domains = $l_dao_ip->get_assigned_dns_domain(null, $p_row['isys_catg_ip_list__id']);

        $p_row['dns_domains'] = $l_empty_value;
        $p_row['zone_assignment'] = $l_empty_value;

        if (is_countable($l_res_domains) && count($l_res_domains)) {
            $l_domain_titles = [];

            while ($l_row_domain = $l_res_domains->get_row()) {
                // Add title
                $l_domain_titles[] = $l_row_domain['isys_net_dns_domain__title'];
            }

            // Build list of dns domains
            $p_row['dns_domains'] = $l_domain_titles;
        }

        if ($p_row['isys_catg_ip_list__isys_obj__id__zone'] > 0) {
            $dao = isys_cmdb_dao_category_g_net_zone_options::instance($this->m_db);

            $l_zone = $dao
                ->get_data(null, $p_row['isys_catg_ip_list__isys_obj__id__zone'])
                ->get_row();

            $l_color = isys_helper_color::unifyHexColor((string)$l_zone['isys_catg_net_zone_options_list__color']);

            $p_row['zone_assignment'] = '<div class="display-flex align-items-center">' .
                '<div class="bullet-marker mr5" style="background-color: ' . $l_color . '"></div>' .
                isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                    $l_row['isys_catg_ip_list__isys_obj__id__zone'],
                    ($l_zone['isys_obj__title'] ?: $dao->obj_get_title_by_id_as_string($p_row['isys_catg_ip_list__isys_obj__id__zone']))
                ) .
                '</div>';
        }
    }

    /**
     * Sets header of the list.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_ip_list__id'                            => 'ID',
            'isys_catg_ip_list__active'                        => 'LC__CATP__IP__ACTIVE',
            'isys_catg_ip_list__primary'                       => 'LC__CMDB__CATG__NETWORK__PRIM_IP_BOOL',
            'standard_gateway'                                 => 'LC__CATG__IP__DEFAULT_GATEWAY_FOR_THE_NET',
            'isys_net_type__title'                             => 'LC__CMDB__CATG__NETWORK__TYPE',
            'isys_cats_net_ip_addresses_list__title'           => 'LC__CMDB__CATG__NETWORK__ADDRESS',
            'ip_assignment'                                    => 'LC__CATP__IP__ASSIGN',
            'zone_assignment'                                  => 'LC__CMDB__CATS__NET__ZONE',
            // This is no real field inside the database.
            'isys_catg_ip_list__hostname'                      => 'LC__CATP__IP__HOSTNAME_FQDN',
            'isys_catg_ip_list__assigned_net'                  => 'LC__CATG__IP__ASSIGNED_NET',
            // This is no real field inside the database.
            'isys_cats_net_ip_addresses_list__ip_address_long' => false,
            'dns_domains'                                      => 'LC__CMDB__CATS__NET__SEARCH_DOMAIN',
            'isys_catg_ip_list__description'                   => 'LC__CMDB__CAT__COMMENTARY',
            // This is no real field inside the database.
        ];
    }
}
