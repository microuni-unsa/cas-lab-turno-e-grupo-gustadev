<?php

/**
 * i-doit
 *
 * DAO: Specific Layer2 assigned ports list.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_layer2_net_assigned_logical_ports extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Get result method for retrieving data to display in the table.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID = null, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;
        $l_sql = 'SELECT lo.isys_catg_log_port_list__id, isys_catg_log_port_list__title AS port_title,
            sec.isys_obj__id AS obj_id,
            sec.isys_obj__title AS obj_title,
            isys_obj_type__title
            FROM isys_catg_log_port_list_2_isys_obj AS lo
            LEFT JOIN isys_obj AS main ON main.isys_obj__id = lo.isys_obj__id
            LEFT JOIN isys_catg_log_port_list AS log_port ON log_port.isys_catg_log_port_list__id = lo.isys_catg_log_port_list__id
            LEFT JOIN isys_obj AS sec ON sec.isys_obj__id = log_port.isys_catg_log_port_list__isys_obj__id
            LEFT JOIN isys_obj_type ON isys_obj_type__id = sec.isys_obj__isys_obj_type__id
            WHERE lo.isys_obj__id = ' . $this->convert_sql_id($p_objID) . ' ' . 'AND lo.isys_catg_log_port_list_2_isys_obj__status = ' . $l_cRecStatus;

        return $this->retrieve($l_sql);
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $l_link = isys_helper_link::create_url([
            C__CMDB__GET__OBJECT     => $row['obj_id'],
            C__CMDB__GET__CATG       => defined_or_default('C__CATG__NETWORK_LOG_PORT'),
        ]);

        $row['obj_title'] = isys_ajax_handler_quick_info::instance()->get_quick_info(
            $row['obj_id'],
            $row['obj_title'] . ' (' . isys_application::instance()->container->get('language')->get($row['isys_obj_type__title']) . ')',
            $l_link
        );
    }

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Method for retrieving the fields to display in the list-view.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'obj_title'  => 'LC_UNIVERSAL__OBJECT',
            'port_title' => 'LC__CMDB__CATG__NETWORK_TREE_CONFIG_PORT_L'
        ];
    }

    /**
     * @param array $getParams
     *
     * @return string
     */
    public function make_row_link($getParams = [])
    {
        $objectField = '[{obj_id}]';

        return isys_helper_link::create_url([
            C__CMDB__GET__OBJECT   => $objectField,
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG     => defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__TREEMODE => $getParams[C__CMDB__GET__TREEMODE]
        ]);
    }
}
