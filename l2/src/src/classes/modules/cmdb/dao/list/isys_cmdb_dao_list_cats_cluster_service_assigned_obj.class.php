<?php

/**
 * i-doit
 *
 * DAO: list for cluster members
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_cluster_service_assigned_obj extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__CLUSTER_SERVICE_ASSIGNED_OBJ');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID = null, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_cluster_service::instance($this->m_db)
            ->get_assigned_objects_and_relations(null, $p_objID, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        $quickinfo = isys_ajax_handler_quick_info::instance();

        $row['main_obj_title'] = $quickinfo->getQuickInfoReplacement(
            $row['main_obj_id'],
            $row['main_obj_title']
        );
        $row['rel_obj_title'] = $quickinfo->getQuickInfoReplacement(
            $row['rel_obj_id'],
            $row['rel_obj_title']
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
            "rel_obj_title"  => "LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE",
            "main_obj_title" => "LC__UNIVERSAL__INSTALLED_ON",
        ];
    }

    /**
     *
     * @param array $getParams
     *
     * @return  string
     */
    public function make_row_link($getParams = [])
    {
        return "#";
    }
}
