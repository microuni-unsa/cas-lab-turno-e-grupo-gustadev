<?php

/**
 * i-doit
 *
 * Export helper for global category logical ports
 *
 * @package     i-doit
 * @subpackage  Export
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_network_ifacel_export_helper extends isys_export_helper
{
    /**
     * @param $logPortDataId
     *
     * @return isys_export_data
     */
    public function log_port_assigned_ips($logPortDataId)
    {
        $return = [];
        $logPortDao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

        $logPortCategoryRow = $logPortDao->get_data($logPortDataId)
            ->get_row();

        $ipResult = $logPortDao->get_ips_by_obj_id($logPortCategoryRow['isys_catg_log_port_list__isys_obj__id'], false, $logPortDataId);

        while ($ipRow = $ipResult->get_row()) {
            $return[] = [
                'id'       => $ipRow['isys_catg_ip_list__id'],
                'title'    => $ipRow['isys_cats_net_ip_addresses_list__title'],
                'hostname' => $ipRow['isys_catg_ip_list__hostname'],
                'obj_id'   => $ipRow['isys_catg_log_port_list__isys_obj__id'],
                'type'     => 'C__CATG__IP'
            ];
        }

        return new isys_export_data($return);
    }

    /**
     * Import method for the logical ports assigned IP's.
     *
     * @param   array $value
     *
     * @return  mixed
     */
    public function log_port_assigned_ips_import($value)
    {
        if (is_array($value[C__DATA__VALUE])) {
            $return = [];

            foreach ($value[C__DATA__VALUE] as $data) {
                // @todo  Remove in i-doit 1.12
                $id = is_array($data) && isset($data['id']) ? $data['id'] : (is_numeric($data) ? $data : null);

                if ($id === null) {
                    continue;
                }

                if (defined('C__CMDB__SUBCAT__NETWORK_INTERFACE_L') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_L')][$id])) {
                    $return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CMDB__SUBCAT__NETWORK_INTERFACE_L')][$id];
                }

                if (defined('C__CATG__IP') && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$id])) {
                    $return[] = $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][constant('C__CATG__IP')][$id];
                }
            }

            if (count($return) > 0) {
                return $return;
            }
        }

        return null;
    }

    /**
     * @param $p_value
     *
     * @return isys_export_data|null
     */
    public function log_port($p_value)
    {
        $l_arr = [];
        $l_dao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

        $l_res = $l_dao->get_attached_layer_2_net($p_value, null, false, true);
        if ($l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_object = $l_dao->get_object_by_id($l_row['isys_obj__id'])
                    ->get_row();
                $l_objtype = $l_dao->get_objtype($l_dao->get_objTypeID($l_row['isys_obj__id']))
                    ->get_row();

                $l_arr[] = [
                    'id'    => $l_object['isys_obj__id'],
                    'title' => $l_object['isys_obj__title'],
                    'sysid' => $l_object['isys_obj__sysid'],
                    'type'  => $l_objtype['isys_obj_type__const'],
                ];
            }

            return new isys_export_data($l_arr);
        } else {
            return null;
        }
    }

    /**
     * @param $p_value
     *
     * @return array|null
     */
    public function log_port_import($p_value)
    {
        $l_data = $p_value[C__DATA__VALUE];

        if (is_array($l_data)) {
            $l_arr = [];
            if (count($l_data) > 0) {
                if (isset($l_data['id'])) {
                    // One entry
                    if (isset($this->m_object_ids[$l_data['id']])) {
                        $l_arr[] = $this->m_object_ids[$l_data['id']];
                    }
                } else {
                    foreach ($l_data as $l_obj_layer) {
                        if (isset($l_obj_layer['id'])) {
                            if (isset($this->m_object_ids[$l_obj_layer['id']])) {
                                $l_arr[] = $this->m_object_ids[$l_obj_layer['id']];
                            }
                        } elseif (!is_array($l_obj_layer) && is_numeric($l_obj_layer)) {
                            if (isset($this->m_object_ids[$l_obj_layer])) {
                                $l_arr[] = $this->m_object_ids[$l_obj_layer];
                            }
                        }
                    }
                }

                return $l_arr;
            }
        }

        return null;
    }

    /**
     * @param integer $logPortDataId
     *
     * @return array|isys_export_data
     */
    public function logiface_ports($logPortDataId)
    {
        if (is_numeric($logPortDataId) && $logPortDataId > 0) {
            $logicalPorts = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database)
                ->get_ports_for_ifacel($logPortDataId);

            if (is_array($logicalPorts)) {
                $return = [];

                foreach ($logicalPorts as $portId => $portTitle) {
                    $return[] = [
                        'id'    => $portId,
                        'title' => $portTitle,
                        'type'  => 'C__CATG__NETWORK_PORT'
                    ];
                }

                return new isys_export_data($return);
            }
        }

        return [];
    }

    /**
     * Imports logical interface ports.
     *
     * @param   array $logPorts
     *
     * @return  array
     */
    public function logiface_ports_import($logPorts = null)
    {
        $logPorts = $logPorts[C__DATA__VALUE] ?: $this->m_property_data['ports'][C__DATA__VALUE];

        if (!is_array($logPorts) || !count($logPorts)) {
            return null;
        }

        $l_result = [];

        $subcatCategory = defined_or_default('C__CMDB__SUBCAT__NETWORK_INTERFACE_L', 'C__CMDB__SUBCAT__NETWORK_INTERFACE_L');
        $category = defined_or_default('C__CATG__NETWORK_LOG_PORT', 'C__CATG__NETWORK_LOG_PORT');
        foreach ($logPorts as $port) {
            $portCategory = null;
            // @todo  Remove in i-doit 1.12
            if (isset($port['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$subcatCategory][$port['id']]] = $port;
            }

            if (isset($port['id']) && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$port['id']]] = $port;
            }

            if (isset($port['type'])) {
                $portCategory = defined_or_default($port['type']);
            }

            if ($portCategory !== null && isset($this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$portCategory][$port['id']])) {
                $l_result[$this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$portCategory][$port['id']]] = $port;
            }
        }

        return $l_result;
    }

    /**
     * @param integer $logPortDataId
     *
     * @return array
     */
    public function logicalPortAssignedConnection($logPortDataId)
    {
        if (is_numeric($logPortDataId) && $logPortDataId > 0) {
            $logPortDao = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_database);

            $LogPortCategoryRow = $logPortDao->get_data($logPortDataId)
                ->get_row();

            return [
                // Use the same keys as the "object" method (for compability).
                'id'           => $LogPortCategoryRow['isys_obj__id'],
                'title'        => $LogPortCategoryRow['isys_obj__title'],
                'sysid'        => $LogPortCategoryRow['isys_obj__sysid'],
                'type'         => $LogPortCategoryRow['isys_obj_type__const'],
                'type_title'   => $this->translate($LogPortCategoryRow['isys_obj_type__title']),
                // Use these keys for the "*_import" method.
                'logPortID'    => $LogPortCategoryRow['isys_catg_log_port_list__id'],
                'logPortTitle' => $LogPortCategoryRow['isys_catg_log_port_list__title'],
                'ref_id' => $LogPortCategoryRow['isys_catg_log_port_list__id'],
                'ref_title' => $LogPortCategoryRow['isys_catg_log_port_list__title'],
            ];
        }

        return [];
    }

    /**
     * @param array $value
     *
     * @return mixed
     */
    public function logicalPortAssignedConnection_import($value)
    {
        if (isset($value[C__DATA__VALUE]['logPortID']) || isset($value[C__DATA__VALUE]['ref_id'])) {
            return $value[C__DATA__VALUE]['logPortID'] ?? $value[C__DATA__VALUE]['ref_id'];
        }

        return null;
    }
}
