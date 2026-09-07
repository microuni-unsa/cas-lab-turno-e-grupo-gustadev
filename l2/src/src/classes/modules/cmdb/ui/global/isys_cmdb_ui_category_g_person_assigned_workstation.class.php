<?php

/**
 * i-doit
 *
 * CMDB person assigned workstation: global category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_person_assigned_workstation extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return null
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * @param isys_cmdb_dao_category $p_cat
     * @param                        $p_get_param_override
     * @param                        $p_strVarName
     * @param                        $p_strTemplateName
     * @param                        $p_bCheckbox
     * @param                        $p_bOrderLink
     * @param                        $p_db_field_name
     *
     * @return null
     * @throws isys_exception_cmdb
     * @throws isys_exception_general
     * @see ID-11129 Change category to be 'assignment' category
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $l_edit_right = isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $p_cat->get_category_const());

        $this->object_browser_as_new([
            'name'                                               => 'C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION',
            isys_popup_browser_object_ng::C__MULTISELECTION      => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT         => true,
            isys_popup_browser_object_ng::C__CAT_FILTER          => 'C__CATG__LOGICAL_UNIT',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT      => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL       => [
                ['isys_cmdb_dao_category_g_person_assigned_workstation', 'get_selected_objects'],
                $_GET[C__CMDB__GET__OBJECT]
            ]
        ], 'LC__UNIVERSAL__BUTTON_ADD', 'LC__UNIVERSAL__OBJECT_ADD_DESCRIPTION');

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW);

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }
}
