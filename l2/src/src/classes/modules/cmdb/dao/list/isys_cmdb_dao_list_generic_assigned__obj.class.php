<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for CPU
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_generic_assigned__obj extends isys_component_dao_category_table_list
{
    /**
     * @var  string
     */
    protected $m_strTableName;

    /**
     * Set the name of the sourcetable as string without "__list".
     *
     * @param   string $p_strTableName
     */
    public function set_source_table($p_strTableName)
    {
        $this->m_strTableName = $p_strTableName;
    }

    /**
     * @return  string
     */
    public function get_source_table()
    {
        return $this->m_strTableName;
    }

    /**
     *
     * @param   null    $p_str
     * @param   integer $p_fk_Id
     * @param   null    $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_fk_Id = null, $p_cRecStatus = null)
    {
        $l_tb = $this->get_source_table();

        $l_strSQL = "SELECT isys_obj__id, isys_obj_type__id, isys_obj_type__title, isys_obj_type__isys_obj_type_group__id, isys_obj__title
			FROM " . $l_tb . "_list
			INNER JOIN isys_connection ON " . $l_tb . "_list__isys_connection__id = isys_connection__id
			INNER JOIN isys_obj ON " . $l_tb . "_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE isys_connection__isys_obj__id= " . $this->convert_sql_id($p_fk_Id) . " ";

        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        if (!empty($l_cRecStatus)) {
            $l_strSQL .= " AND " . $l_tb . "_list__status = " . $this->convert_sql_id($l_cRecStatus) . " AND isys_obj__status = " . $this->convert_sql_id($l_cRecStatus);
        }

        return $this->retrieve($l_strSQL);
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        global $g_dirs;

        $l_strImage = '<img src="' . $g_dirs["images"] . 'icons/silk/link.png" class="vam" />';

        if ($row["isys_obj__id"] != null) {
            // Link to connected objType (list of objects).
            $l_arrConnectedObjType = [
                C__CMDB__GET__VIEWMODE    => C__CMDB__VIEW__LIST_OBJECT,
                C__CMDB__GET__TREEMODE    => C__CMDB__VIEW__TREE_OBJECTTYPE,
                C__CMDB__GET__OBJECTTYPE  => $row["isys_obj_type__id"],
                C__CMDB__GET__OBJECTGROUP => $row["isys_obj_type__isys_obj_type_group__id"]
            ];

            $quickinfo = isys_ajax_handler_quick_info::instance();

            $row['isys_obj__title'] = $quickinfo->getQuickInfoReplacement(
                $row["isys_obj__id"],
                $l_strImage . ' ' . $row['isys_obj__title']
            );
            $row['isys_obj_type__title'] = $quickinfo->get_quick_info(
                $row["isys_obj__id"],
                $l_strImage . ' ' . isys_application::instance()->container->get('language')->get($row['isys_obj_type__title']),
                $l_arrConnectedObjType
            );
        }
    }

    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__id"         => "ID",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE",
            "isys_obj__title"      => "LC__CMDB__CATG__GLOBAL_TITLE"
        ];
    }

    /**
     * Make row link.
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
