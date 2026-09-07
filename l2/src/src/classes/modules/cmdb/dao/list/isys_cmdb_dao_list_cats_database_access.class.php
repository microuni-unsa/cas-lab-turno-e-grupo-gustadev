<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_database_access extends isys_component_dao_category_table_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__DATABASE_ACCESS');
    }

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        $row['assignment_title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row['isys_connection__isys_obj__id'],
            $row['assignment_title']
        );
    }

    /**
     * Gets flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "assignment_title" => isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__OBJTYPE__APPLICATION") . " / " . isys_application::instance()->container->get('language')
                    ->get("LC__CMDB__OBJTYPE__SERVICE")
        ];
    }

    /**
     * @param array $getParams
     *
     * @return  string
     */
    public function make_row_link($getParams = [])
    {
        return "#";
    }
}
