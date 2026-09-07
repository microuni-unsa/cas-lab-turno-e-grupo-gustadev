<?php

class isys_cmdb_dao_list_catg_rm_controller extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__RM_CONTROLLER');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
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
        $query = "SELECT * FROM isys_catg_rm_controller_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_rm_controller_list__isys_connection__id
            INNER JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            LEFT JOIN isys_catg_access_list ON isys_catg_access_list__isys_obj__id = isys_obj__id AND isys_catg_access_list__primary = 1
            WHERE isys_catg_rm_controller_list__isys_obj__id = {$this->convert_sql_id($p_objID)} ";

        if ($p_cRecStatus !== null) {
            $query .= "AND isys_catg_rm_controller_list__status = {$this->convert_sql_int($p_cRecStatus)}";
        }

        return $this->retrieve($query);
    }

    /**
     * @return  boolean
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * @param array $row
     *
     * @see ID-11185
     */
    public function modify_row(&$row)
    {
        if ($row['isys_catg_access_list__url'] !== null) {
            $row['isys_catg_access_list__url'] = isys_helper_link::prependProtocol(isys_helper_link::handle_url_variables($row['isys_catg_access_list__url'], $row['isys_obj__id']));
        }
    }

    /**
     *
     * @return array
     */
    public function get_fields()
    {
        return [
            'isys_obj__title'            => 'LC_UNIVERSAL__OBJECT',
            'isys_obj_type__title'       => 'LC__CMDB__OBJTYPE',
            'isys_catg_access_list__url' => 'LC__CMDB__CATG__RM_CONTROLLER__PRIMARY_URL_READONLY',
        ];
    }
}
