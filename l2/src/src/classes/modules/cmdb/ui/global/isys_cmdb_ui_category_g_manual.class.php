<?php

/**
 * i-doit
 *
 * CMDB UI: Global category manual
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_manual extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     * @return void
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_daoConnection = new isys_cmdb_dao_connection($this->m_database_component);

        $l_object_id = $l_daoConnection->get_object_id_by_connection($l_catdata["isys_catg_manual_list__isys_connection__id"]);

        $l_rules["C__CATG__MANUAL_TITLE"]["p_strValue"] = $l_catdata["isys_catg_manual_list__title"];
        $l_rules["C__CATG__MANUAL_OBJ_FILE"]["p_strSelectedID"] = $l_object_id;
        $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $l_catdata["isys_catg_manual_list__description"];

        if ($l_object_id) {
            $dao_cat_s_file = new isys_cmdb_dao_category_s_file($this->m_database_component);

            $l_file_dao = $dao_cat_s_file->get_file_by_obj_id($l_object_id);

            if (is_countable($l_file_dao) && count($l_file_dao) > 0) {
                $l_active_file = $l_file_dao->get_row();

                // store upload path in a hidden field -> and activate the download link.
                $l_rules["C__CATG__FILE_NAME"]["p_strValue"] = $l_active_file["isys_file_physical__filename_original"];

                $downloadUrl = isys_application::instance()->container->get('route_generator')
                    ->generate('system.file-download', ['type' => 'cmdb.global-file-category', 'identifier' => $l_active_file["isys_file_physical__id"]]);

                $this->get_template_component()
                    ->assign("file_uploaded", true)
                    ->assign('download_link', $downloadUrl);
            }
        }

        if (!$p_cat->get_validation()) {
            // Display the posted value in fields so fill posted values to $l_rules dont forget the hidden one...
            $l_rules["C__CATG__MANUAL_TITLE"]["p_strValue"] = $_POST["C__CATG__MANUAL_TITLE"];
            $l_rules["C__CATG__MANUAL_OBJ_FILE"]["p_strSelectedID"] = $_POST["C__CATG__MANUAL_OBJ_FILE"];
            $l_rules["C__CMDB__CAT__COMMENTARY_" . $p_cat->get_category_type() . $p_cat->get_category_id()]["p_strValue"] = $_POST["C__CMDB__CAT__COMMENTARY_" .
            $p_cat->get_category_type() . $p_cat->get_category_id()];

            // Merge exiting rules with errors.
            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        // Apply rules
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
