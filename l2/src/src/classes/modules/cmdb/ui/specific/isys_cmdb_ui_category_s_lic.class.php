<?php

/**
 * i-doit
 *
 * CMDB specific category for licences.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_lic extends isys_cmdb_ui_category_specific
{
    /**
     * Define if this sub category is multivalued or not.
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function is_multivalued()
    {
        return true;
    }

    /**
     * Process this single value sub category.
     *
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs;

        $l_objects = $l_rules = $l_usage = [];
        $l_catdata = $p_cat->get_general_data();

        $l_licence_dao = new isys_cmdb_dao_licences($this->m_database_component, $_GET[C__CMDB__GET__OBJECT]);

        $l_cats_lic_id = 0;

        if (isset($l_catdata['isys_cats_lic_list__id'])) {
            // This ID will be explicitly set to 0 if we want to create a new entity:
            $l_cats_lic_id = (int)$l_catdata['isys_cats_lic_list__id'];
        }

        $l_licence_res = $l_licence_dao->getLicencesInUseDetailed(C__RECORD_STATUS__NORMAL, $l_cats_lic_id, $l_catdata['isys_cats_lic_list__type'], true);
        $language = isys_application::instance()->container->get('language');

        while ($l_row = $l_licence_res->get_row()) {
            $l_usage[$l_row['client_obj_id']]++;

            $l_objects[$l_row['client_obj_id']] = [
                'type'          => $language->get($p_cat->get_objtype_name_by_id_as_string($l_row['client_obj_type'])),
                'title'         => $l_row['client_obj_title'] . ' {' . $l_row['client_obj_id'] . '}',
                'used_licences' => $l_row['licenced_cores'] ?? $l_usage[$l_row['client_obj_id']]
            ];
        }

        // This needs to happen before the "smarty_tom_add_rules" method, because $l_rules gets filled by reference.
        $templatePath = $this
            ->fill_formfields($p_cat, $l_rules, $l_catdata)
            ->activate_commentary($p_cat)
            ->get_template();

        $l_rules['C__CATS__LICENCE_TYPE']['p_arData'] = $p_cat->callback_property_type(isys_request::factory());
        $l_rules['C__CATS__LICENCE_TYPE']['p_strSelectedId'] = $l_catdata['isys_cats_lic_list__type'] ?? isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__SINGLE_LICENCE;

        /**
         * @see ID-8424  Use the table component in order to display the objects that use the current licence. This takes care of sorting and filtering.
         */
        $renderedObjectTable = $language->get('LC__CMDB__CATS__LICENCE_NO_ASSIGNED_OBJECTS');
        $objectTable = new isys_component_list(array_values($l_objects));
        $objectTable->config([
            'type' => 'LC_UNIVERSAL__OBJECT_TYPE',
            'title' => 'LC__UNIVERSAL__TITLE',
            'used_licences' => 'LC__UNIVERSAL__IN_USE'
        ]);
        $objectTable->disableCheckboxes();
        $objectTable->setScoped(true);

        if ($objectTable->createTempTable()) {
            $renderedObjectTable = $objectTable->render();
        }

        $this->get_template_component()
            ->assign('objectTable', $renderedObjectTable)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', $templatePath);

        return true;
    }

    /**
     * Process list method.
     *
     * @param   isys_cmdb_dao_category_s_lic &$p_cat
     *
     * @return  null
     * @author  Dennis Stücken <dstuecken@synetics.de>
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
        return parent::process_list($p_cat, [C__CMDB__GET__CATS => defined_or_default('C__CATS__LICENCE_LIST')], null, null, true, true, "isys_cats_lic_list__id");
    }
}
