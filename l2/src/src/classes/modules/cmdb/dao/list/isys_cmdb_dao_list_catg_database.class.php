<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_database extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__DATABASE');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $row["assigned_dbms"] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row["isys_connection__isys_obj__id"],
            isys_application::instance()->container->get('cmdb_dao')->get_obj_name_by_id_as_string($row["isys_connection__isys_obj__id"])
        );
    }

    /**
     * Method for retrieving the fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'assigned_dbms' => 'LC__CATG__DATABASE__ASSIGNED_DBMS',
            'isys_catg_database_list__instance_name' => 'LC__CATG__DATABASE__INSTANCE_NAME',
            'isys_database_instance_type__title' => 'LC__CATG__DATABASE__INSTANCE_TYPE',
            'isys_application_manufacturer__title' => 'LC__CATG__DATABASE__MANUFACTURER',
            'isys_catg_version_list__title' => 'LC__CATG__DATABASE__VERSION',
            'isys_catg_database_list__path' => 'LC__CATG__DATABASE__PATH',
            'isys_catg_database_list__port' => 'LC__CATG__DATABASE__PORT',
            'isys_catg_database_list__port_name' => 'LC__CATG__DATABASE__PORT_NAME',
            'isys_catg_database_list__description' => "LC__CMDB__CAT__COMMENTARY",
        ];
    }
}
