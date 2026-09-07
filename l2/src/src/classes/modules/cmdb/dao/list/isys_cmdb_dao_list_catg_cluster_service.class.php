<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_cluster_service extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category
     *
     * @return integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CLUSTER_SERVICE');
    }

    /**
     * Return constant of category type
     *
     * @return integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param array $row
     *
     * @throws isys_exception_database
     */
    public function modify_row(&$row)
    {
        $l_dao = isys_cmdb_dao_category_g_cluster_service::instance($this->m_db);
        $quickinfo = isys_ajax_handler_quick_info::instance();

        $l_members = $l_dao->get_cluster_members($row['isys_catg_cluster_service_list__id'], null, C__RECORD_STATUS__NORMAL);
        $l_addresses = $l_dao->get_cluster_addresses($row['isys_catg_cluster_service_list__id']);
        $drives = $l_dao->get_cluster_drives($row['isys_catg_cluster_service_list__id']);
        $shares = $l_dao->get_cluster_shares($row['isys_catg_cluster_service_list__id']);

        $row['hostaddresses'] = $row['runs_on'] = $row['default_server'] = isys_tenantsettings::get('gui.empty_value', '-');

        $row['serviceStatus'] = isys_cmdb_dao_category_g_cluster_service::getServiceStatus($row['isys_catg_cluster_service_list__service_status']);

        if ($row['isys_catg_cluster_service_list__cluster_members_list__id'] > 0) {
            $row['default_server'] = $l_dao->get_obj_name_by_id_as_string(isys_cmdb_dao_connection::instance($this->m_db)
                ->get_object_id_by_connection($row['isys_catg_cluster_members_list__isys_connection__id']));
        }

        if (is_countable($l_members) && count($l_members)) {
            $row['runs_on'] = [];

            while ($memberRow = $l_members->get_row()) {
                $row['runs_on'][] = $quickinfo->getQuickInfoReplacement(
                    $memberRow['isys_obj__id'],
                    isys_application::instance()->container->get('language')->get($l_dao->get_objtype_name_by_id_as_string($memberRow['isys_obj__isys_obj_type__id'])) . " >> " . $memberRow['isys_obj__title']
                );
            }
        }

        if (is_countable($l_addresses) && count($l_addresses)) {
            $row['hostaddresses'] = [];

            while ($hostaddressRow = $l_addresses->get_row()) {
                $row['hostaddresses'][] = $hostaddressRow['isys_cats_net_ip_addresses_list__title'];
            }
        }

        if (is_countable($drives) && count($drives)) {
            $filesystemDao = isys_cmdb_dao_dialog::factory($this->m_db)->set_table('isys_filesystem_type');
            $memoryUnitDao = isys_cmdb_dao_dialog::factory($this->m_db)->set_table('isys_memory_unit');

            $row['drives'] = [];

            while ($driveRow = $drives->get_row()) {
                $filesystemType = '';
                $memory = '';

                if ($driveRow['isys_catg_drive_list__isys_filesystem_type__id']) {
                    $filesystemTypeData = $filesystemDao->get_data($driveRow['isys_catg_drive_list__isys_filesystem_type__id']);
                    $filesystemType = $filesystemTypeData['title'] ?? '';
                }

                if ($driveRow['isys_catg_drive_list__capacity'] && $driveRow['isys_catg_drive_list__isys_memory_unit__id']) {
                    $memoryUnit = $memoryUnitDao->get_data($driveRow['isys_catg_drive_list__isys_memory_unit__id']);
                    $memory = isys_convert::memory(
                        $driveRow['isys_catg_drive_list__capacity'],
                        $driveRow['isys_catg_drive_list__isys_memory_unit__id'],
                        C__CONVERT_DIRECTION__BACKWARD
                    ) . ' ' . ($memoryUnit['title'] ?? '');
                }

                $row['drives'][] = implode(', ', array_filter([
                    $driveRow['isys_catg_drive_list__title'] . ' (' . $driveRow['isys_catg_drive_list__driveletter'] . ')',
                    $filesystemType,
                    $memory
                ]));
            }
        }

        if (is_countable($shares) && count($shares)) {
            $row['shares'] = [];

            while ($shareRow = $shares->get_row()) {
                $row['shares'][] = $shareRow['isys_catg_shares_list__title'] . ' (UNC: ' . $shareRow['isys_catg_shares_list__unc_path'] . ')';
            }
        }

        // @see ID-9225 Use the correct DAO.
        $assignedDatabaseSchema = $l_dao->callback_property_assigned_database_schema(isys_request::factory()->set_row($row));

        if ($assignedDatabaseSchema) {
            $databaseSchemaData = $this->m_cat_dao->get_object($assignedDatabaseSchema)->get_row();

            if (is_array($databaseSchemaData)) {
                $row['assigned_database_schema'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                    $databaseSchemaData['isys_obj__id'],
                    $databaseSchemaData['isys_obj__title']
                );
            }
        }

        $row['application'] = $quickinfo->getQuickInfoReplacement(
            $row['isys_connection__isys_obj__id'],
            $l_dao->get_obj_name_by_id_as_string($row['isys_connection__isys_obj__id'])
        );
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'application'                                 => 'LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE',
            'isys_cluster_type__title'                    => 'LC__CMDB__CATG__CLUSTER_SERVICE__CLUSTER_TYPE',
            'runs_on'                                     => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
            'default_server'                              => 'LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER',
            'hostaddresses'                               => 'LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES',
            'drives'                                      => 'LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES',
            'shares'                                      => 'LC__CMDB__CATG__CLUSTER_SERVICE__SHARES',
            'serviceStatus'                               => 'LC__CMDB__CATG__CLUSTER_SERVICE__SERVICE_STATUS',
            'assigned_database_schema'                    => 'LC__CMDB__CATS__DATABASE_SCHEMA',
            'isys_catg_cluster_service_list__description' => 'LC__CMDB__CAT__COMMENTARY'
        ];
    }
}
