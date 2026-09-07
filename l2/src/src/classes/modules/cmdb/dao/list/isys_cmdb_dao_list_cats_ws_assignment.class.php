<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_ws_assignment extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__WS_ASSIGNMENT');
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
     *
     * @param   null    $p_strTable
     * @param   integer $p_objID
     * @param   null    $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_strTable = null, $p_objID = null, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_s_ws_assignment::instance($this->get_database_component())
            ->get_data(null, $p_objID, "", null, empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus);
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        $row['isys_obj__title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row['isys_obj__id'],
            $row['isys_obj__title']
        );
    }

    /**
     * Gets flag for the rec status dialog
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title" => "LC__CMDB__CATG__ASSIGNED_OBJECTS"
        ];
    }
}
