<?php

/**
 * i-doit
 *
 * UI: global category for AP Controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_ap_devices extends isys_cmdb_ui_category_global
{
    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category $cat
     * @param   null                   $getParamOverride
     * @param   null                   $strVarName
     * @param   null                   $strTemplateName
     * @param   boolean                $bCheckbox
     * @param   boolean                $bOrderLink
     * @param   null                   $dbFieldName
     *
     * @return  null
     * @throws  isys_exception_general
     */
    public function process_list(
        isys_cmdb_dao_category &$cat,
        $getParamOverride = null,
        $strVarName = null,
        $strTemplateName = null,
        $bCheckbox = false,
        $bOrderLink = true,
        $dbFieldName = null
    ) {
        $this->object_browser_as_new([
            'name'                                          => 'C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT_BACKWARD',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__AP_CONTROLLER',
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                ['isys_cmdb_dao_category_g_ap_devices', 'get_assigned_objects'],
                $_GET[C__CMDB__GET__OBJECT],
                [
                    "isys_obj__id",
                    "isys_obj__title",
                    "isys_obj__isys_obj_type__id",
                    "isys_obj__sysid"
                ]
            ],
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        isys_component_template_navbar::getInstance()
            ->hide_all_buttons([C__NAVBAR_BUTTON__NEW])
            ->set_active(isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $_GET[C__CMDB__GET__OBJECT], $cat->get_category_const()), C__NAVBAR_BUTTON__NEW);

        return parent::process_list($cat, $getParamOverride, $strVarName, $strTemplateName, $bCheckbox, $bOrderLink, $dbFieldName);
    }

    /**
     * UI constructor.
     *
     * @param  isys_component_template $p_template
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__ap_controller.tpl");
    }
}
