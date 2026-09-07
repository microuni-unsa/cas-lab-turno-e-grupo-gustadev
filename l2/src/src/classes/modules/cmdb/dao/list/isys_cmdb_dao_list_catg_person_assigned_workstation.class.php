<?php

/**
 * i-doit
 *
 * DAO: Category list for contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-11129 Change category to be 'assignment' category
 */
class isys_cmdb_dao_list_catg_person_assigned_workstation extends isys_component_dao_category_table_list
{
    public function get_category()
    {
        return defined_or_default('C__CATG__PERSON_ASSIGNED_WORKSTATION');
    }

    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    public function rec_status_list_active()
    {
        return false;
    }

    public function modify_row(&$row)
    {
        $id = $row['isys_obj__id'];
        $title = $row['isys_obj__title'];

        $row['isys_obj__title'] = "{$title} {{$id}}";
    }

    public function get_fields()
    {
        return [
            'isys_obj__id'         => 'ID',
            'isys_obj__title'      => 'Title',
            'isys_obj_type__title' => 'Type',
        ];
    }
}
