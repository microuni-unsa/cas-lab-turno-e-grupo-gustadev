<?php

/**
 * i-doit
 *
 * DAO: UI class for layer2-net assigned logical ports.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_assigned_sim_cards extends isys_cmdb_ui_category_global
{
    /**
     * Empty process-list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $rules = [
            'C__CMDB__CATG__ASSIGNED_SIM_CARDS__ID' => [
                isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT      => false,
                isys_popup_browser_object_ng::C__CAT_FILTER       => "C__CATG__CARDS",
                isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                isys_popup_browser_object_ng::C__SECOND_LIST      => [
                    'isys_cmdb_dao_category_g_assigned_sim_cards::object_browser',
                    [C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT]]
                ],
                isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_g_assigned_sim_cards::format_selection',
            ]
        ];

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules);

        $this->process_list($p_cat);
    }

    /**
     * Process list method.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return boolean
     * @throws isys_exception_cmdb
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
            'name'                                            => 'C__CMDB__CATG__ASSIGNED_SIM_CARDS__ID',
            isys_popup_browser_object_ng::C__MULTISELECTION   => true,
            isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
            isys_popup_browser_object_ng::C__CAT_FILTER       => "C__CATG__CARDS",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
            isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
            isys_popup_browser_object_ng::C__SECOND_LIST      => [
                'isys_cmdb_dao_category_g_assigned_sim_cards::object_browser',
                [C__CMDB__GET__OBJECT => $_GET[C__CMDB__GET__OBJECT]]
            ],
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        // We deactivate the edit, archive and purge functions.
        isys_component_template_navbar::getInstance()
            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible($l_edit_right, C__NAVBAR_BUTTON__NEW)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__ARCHIVE)
            ->set_visible(false, C__NAVBAR_BUTTON__PURGE);

        // We create our list DAO.
        $l_dao_list = isys_cmdb_dao_list_catg_assigned_sim_cards::build($p_cat->get_database_component(), $p_cat);

        // We cast the object-id to INT so nobody can do bad bad things to our code.
        $l_obj_id = (int)$_GET[C__CMDB__GET__OBJECT];

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_template $template
     */
    public function __construct(isys_component_template &$template)
    {
        $this->set_template("catg__assigned_sim_cards.tpl");
        parent::__construct($template);
    }
}
