<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_file extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param    isys_cmdb_dao_category_g_file &$dao
     */
    public function process(isys_cmdb_dao_category $dao)
    {
        $categoryData = $dao->get_general_data();

        $rules = [];
        $this->fill_formfields($dao, $rules, $categoryData);

        $rules['C__CATG__FILE_OBJ_FILE']['value'] = 0;

        if ($categoryData["isys_connection__isys_obj__id"] > 0) {
            $fileObjectId = $categoryData["isys_connection__isys_obj__id"];

            $activeFile = isys_cmdb_dao_category_s_file::instance($this->get_database_component())
                ->get_file_by_obj_id($fileObjectId)
                ->get_row();

            $rules['C__CATG__FILE_OBJ_FILE']['value'] = $fileObjectId;
            $rules['C__CATG__FILE_NAME']['p_strValue'] = $activeFile["isys_file_physical__filename_original"];

            if (is_array($activeFile) && count($activeFile)) {
                $allowedToView = true;

                if (isys_tenantsettings::get('auth.use-in-file-browser', false)) {
                    $allowedToView = isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileObjectId);
                }

                $downloadUrl = isys_application::instance()->container->get('route_generator')
                    ->generate('system.file-download', ['type' => 'cmdb.global-file-category', 'identifier' => $activeFile["isys_file_physical__id"]]);

                $this->get_template_component()
                    ->assign("file_uploaded", true)
                    ->assign('allowedToView', $allowedToView)
                    ->assign('downloadUrl', $downloadUrl);
            } else {
                $rules["C__CATG__FILE_NAME"]["placeholder"] = "No file version uploaded.";
            }
        } else {
            $rules["C__CATG__FILE_NAME"]["placeholder"] = "No file selected.";
        }

        $this->get_template_component()->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $dao
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
     */
    public function process_list(
        isys_cmdb_dao_category &$dao,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {

        isys_component_template_navbar::getInstance()
            ->set_js_onclick("document.isys_form.sort.value=''; document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; document.isys_form.submit();", C__NAVBAR_BUTTON__EDIT);

        return parent::process_list($dao, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

}
