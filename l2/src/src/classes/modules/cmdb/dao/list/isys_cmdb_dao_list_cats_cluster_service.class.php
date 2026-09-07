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
class isys_cmdb_dao_list_cats_cluster_service extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__CLUSTER_SERVICE');
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
            ->get_data(null, null, " AND isys_connection__isys_obj__id = " . $p_objID, null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    /**
     * @param array $p_row
     *
     * @throws isys_exception_database
     */
    public function modify_row(&$p_row)
    {
        isys_cmdb_dao_list_catg_cluster_service::instance($this->m_db)
            ->modify_row($p_row);

        $p_row['application'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $p_row['isys_obj__id'],
            $p_row['isys_obj__title']
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
            'application'              => 'LC__CMDB__CATG__CLUSTER',
            'isys_cluster_type__title' => 'LC__CMDB__CATG__CLUSTER_SERVICE__CLUSTER_TYPE',
            'runs_on'                  => 'LC__CMDB__CATG__CLUSTER_SERVICE__RUNS_ON',
            'default_server'           => 'LC__CMDB__CATG__CLUSTER_SERVICE__DEFAULT_SERVER',
            'hostaddresses'            => 'LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES',
            'isys_catg_cluster_service_list__description' => 'LC__CMDB__CAT__COMMENTARY',
        ];
    }
}
