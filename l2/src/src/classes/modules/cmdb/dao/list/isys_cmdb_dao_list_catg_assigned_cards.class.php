<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for assigned cards.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_assigned_cards extends isys_component_dao_category_table_list
{
    /**
     * This method returns the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__ASSIGNED_CARDS');
    }

    /**
     * This method returns the category type.
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
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_recStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_table = null, $p_object_id = null, $p_recStatus = null)
    {
        $l_cRecStatus = empty($p_recStatus) ? $this->get_rec_status() : $p_recStatus;

        if (empty($l_cRecStatus)) {
            $l_cRecStatus = C__RECORD_STATUS__NORMAL;
        }

        return isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component())
            ->get_data(null, $p_object_id, "", null, $l_cRecStatus);
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

        $row['obj_title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row['isys_catg_assigned_cards_list__isys_obj__id__card'],
            $dao->get_obj_name_by_id_as_string($row['isys_catg_assigned_cards_list__isys_obj__id__card'])
        );
        $row['obj_type'] = $language->get($dao->get_objtype_name_by_id_as_string($dao->get_objTypeID($row['isys_catg_assigned_cards_list__isys_obj__id__card'])));
    }

    /**
     * This method returns the fields and translations.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "obj_title" => "LC__CMDB__CATG__TITLE",
            "obj_type"  => "LC__CMDB__CATG__TYPE"
        ];
    }

    /**
     * Method for retrieving the row-link.
     *
     * @param array $getParams
     *
     * @return  string
     */
    public function make_row_link($getParams = [])
    {
        $objectField = '[{'. $this->get_dao_category()->get_connected_object_id_field() .'}]';

        return isys_helper_link::create_url([
            C__CMDB__GET__OBJECT     => $objectField,
            C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG       => defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__TREEMODE   => $getParams[C__CMDB__GET__TREEMODE]
        ]);
    }
}
