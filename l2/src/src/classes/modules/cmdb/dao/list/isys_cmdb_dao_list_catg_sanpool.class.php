<?php

/**
 * i-doit
 *
 * DAO: Global category sanpool
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken - 09-2009
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_sanpool extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__LDEV_SERVER');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id = null, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_sanpool::instance($this->m_db)
            ->get_data(null, $p_object_id, "", null, (empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus));
    }

    /**
     * Row modification.
     *
     * @param  array &$row
     */
    public function modify_row(&$row)
    {
        $l_client_list = '';
        $l_res = isys_cmdb_dao_category_g_ldevclient::instance($this->m_db)
            ->get_clients($row["isys_catg_sanpool_list__id"]);

        if (is_countable($l_res) && count($l_res)) {
            $l_client_list = [];

            while ($l_row = $l_res->get_row()) {
                $l_client_list[] = $l_row["isys_obj__title"] . ' >> ' . $l_row["isys_catg_ldevclient_list__title"];
            }
        }

        $row["clients"] = $l_client_list;

        $row["isys_catg_sanpool_list__capacity"] = isys_convert::memory(
            $row["isys_catg_sanpool_list__capacity"],
            $row["isys_catg_sanpool_list__isys_memory_unit__id"],
            C__CONVERT_DIRECTION__BACKWARD
        );

        $row["isys_catg_sanpool_list__capacity"] = isys_convert::formatNumber($row["isys_catg_sanpool_list__capacity"]) . " " . $row["isys_memory_unit__title"];

        $row['paths'] = [];
        $result = $this->m_cat_dao->get_paths($row['isys_catg_sanpool_list__id']);

        if (!isset($row['isys_catg_sanpool_list__primary_path']) || empty($row['isys_catg_sanpool_list__primary_path'])) {
            $row['isys_catg_sanpool_list__primary_path'] = isys_tenantsettings::get('gui.empty_value', '-');
        }

        while ($pathRow = $result->get_row()) {
            $row['paths'][] = $pathRow['isys_catg_fc_port_list__title'];

            if ($row['isys_catg_sanpool_list__primary_path'] == $pathRow['isys_catg_fc_port_list__id']) {
                $row['isys_catg_sanpool_list__primary_path'] = $pathRow['isys_catg_fc_port_list__title'];
            }
        }

        $list = isys_component_list::instance($this->m_db);
        $list->set_listdao($this);
        $tableConfig = $list->get_table_config();

        $groupAsList = $tableConfig->getGroupingType() === isys_cmdb_dao_category_property_ng::C__GROUPING__LIST;

        if ($groupAsList) {
            $row['paths'] = '<ul><li>' . implode('</li><li>', $row['paths']) . '</li></ul>';
        } else {
            $row['paths'] = implode(', ', $row['paths']);
        }
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_sanpool_list__title'        => 'LC__CATD__DRIVE_TITLE',
            'isys_catg_sanpool_list__lun'          => 'LC__CATD__SANPOOL_LUN',
            'isys_catg_sanpool_list__segment_size' => 'LC__CATD__SANPOOL_SEGMENT_SIZE',
            'paths'                                => 'LC__UNIVERSAL__PATHS',
            'isys_catg_sanpool_list__primary_path' => 'LC__CMDB__CATG__LDEVCLIENT__PRIMARY_PATH',
            'isys_ldev_multipath__title'           => 'LC__CMDB__CATG__LDEV_MULTI_PATH',
            'isys_tierclass__title'                => 'LC__CATG__LDEV__TIERCLASS',
            'isys_catg_sanpool_list__capacity'     => 'LC__CATD__DRIVE_CAPACITY',
            'clients'                              => 'LC__CMDB__CATG__LDEV_CLIENT',
            'isys_catg_sanpool_list__description'  => 'LC__CMDB__CAT__COMMENTARY',
        ];
    }
}
