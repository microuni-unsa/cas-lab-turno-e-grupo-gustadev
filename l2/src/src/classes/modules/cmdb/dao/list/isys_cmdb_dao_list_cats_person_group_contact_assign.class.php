<?php

/**
 * i-doit
 *
 * DAO: Class for overview category
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_person_group_contact_assign extends isys_component_dao_category_table_list
{
    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__PERSON_GROUP_CONTACT_ASSIGNMENT');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_result($p_str = null, $p_objID = null, $p_cRecStatus = null)
    {
        $objectId = $this->convert_sql_id($p_objID);

        $sql = "SELECT isys_catg_contact_list.*, o1.*, ot.*, isys_contact_tag.* FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list.isys_catg_contact_list__isys_connection__id
			INNER JOIN isys_obj AS o1 ON isys_catg_contact_list.isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
			INNER JOIN isys_obj_type AS ot ON o1.isys_obj__isys_obj_type__id = ot.isys_obj_type__id
			INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list.isys_catg_contact_list__isys_contact_tag__id
			WHERE isys_connection__isys_obj__id = {$objectId} ";

        $l_cRecStatus = (int)(($p_cRecStatus === null) ? $this->get_rec_status() : $p_cRecStatus);

        if ($l_cRecStatus > 0) {
            $recordStatus = $this->convert_sql_int($l_cRecStatus);
            $recordStatusNormal = $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

            // @see  ID-8287 Only show entries as ranked, when they actually are.
            if (isys_tenantsettings::get('cmdb.only-show-ranked-entries-as-such', 0)) {
                $sql .= "AND isys_catg_contact_list.isys_catg_contact_list__status = {$recordStatus}";
            } else {
                $sql .= "AND o1.isys_obj__status = {$recordStatusNormal}
				    AND isys_catg_contact_list.isys_catg_contact_list__status = {$recordStatus}";
            }
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        if ($row['isys_obj__id'] != null) {
            $row['isys_obj__title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                $row['isys_obj__id'],
                $row['isys_obj__title']
            );
        }
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_obj__id'                        => 'LC__UNIVERSAL__ID',
            'isys_obj__title'                     => 'LC__CMDB__LOGBOOK__TITLE',
            'isys_obj_type__title'                => 'LC__CMDB__OBJTYPE',
            'isys_contact_tag__title'             => 'LC__CMDB__CONTACT_ROLE',
            'isys_catg_contact_list__description' => 'LC__CMDB__CAT__COMMENTARY',
        ];
    }

    /**
     * Make row link method.
     *
     * @param   array $urlOverwrite
     *
     * @return  string
     */
    public function make_row_link($urlOverwrite = null)
    {
        $l_sql = 'SELECT isys_obj__id, isys_obj_type__isys_obj_type_group__id, isys_obj__isys_obj_type__id
			FROM isys_obj
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ';';

        $l_catdata = $this->retrieve($l_sql)->get_row();
        $l_gets = isys_module_request::get_instance()->get_gets();

        $urlParameters = [
            C__CMDB__GET__VIEWMODE           => C__CMDB__VIEW__CATEGORY_GLOBAL,
            C__CMDB__GET__TREEMODE           => $l_gets[C__CMDB__GET__TREEMODE],
            C__CMDB__GET__OBJECT             => $l_catdata["isys_obj__id"],
            C__CMDB__GET__CATS               => defined_or_default('C__CATS__PERSON_GROUP_CONTACT_ASSIGNMENT'),
            C__CMDB__GET__OBJECTGROUP        => $l_catdata["isys_obj_type__isys_obj_type_group__id"],
            C__GET__MAIN_MENU__NAVIGATION_ID => $l_gets["mNavID"],
            C__CMDB__GET__OBJECTTYPE         => $l_catdata["isys_obj__isys_obj_type__id"],
            C__CMDB__GET__CATLEVEL           => "[{isys_catg_contact_list__id}]"
        ];

        if (is_array($urlOverwrite) && count($urlOverwrite)) {
            // @todo  Can this be done via array_merge?
            foreach ($urlOverwrite as $key => $value) {
                $urlParameters[$key] = $urlOverwrite[$key];
            }
        }

        return isys_helper_link::create_url($urlParameters);
    }
}
