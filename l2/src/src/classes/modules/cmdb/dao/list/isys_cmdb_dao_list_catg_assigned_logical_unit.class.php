<?php

/**
 * i-doit
 *
 * DAO: assigned logical units-list
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_Lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_assigned_logical_unit extends isys_component_dao_category_table_list
{
    /**
     * Returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__ASSIGNED_LOGICAL_UNIT');
    }

    /**
     * Returns the category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Method for retrieving the result.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID = null, $p_unused = null)
    {
        return isys_cmdb_dao_category_g_assigned_logical_unit::instance($this->get_database_component())
            ->get_selected_objects($p_objID);
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $language = isys_application::instance()->container->get('language');

        $row['isys_obj__title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row['isys_obj__id'],
            $row['isys_obj__title']
        );
        $row['isys_obj_type__title'] = $language->get($dao->get_obj_type_name_by_obj_id($row['isys_obj__id']));
    }

    /**
     * Flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Build header for the list.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_obj__id'         => 'ID',
            'isys_obj__title'      => 'LC__UNIVERSAL__OBJECT_TITLE',
            'isys_obj_type__title' => 'LC__UNIVERSAL__OBJECT_TYPE'
        ];
    }
}
