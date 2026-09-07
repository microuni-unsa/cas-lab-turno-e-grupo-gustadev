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
class isys_cmdb_ui_category_g_ap_controller extends isys_cmdb_ui_category_global
{
    /**
     * Process method for displaying the template.
     *
     * @param   isys_cmdb_dao_category_g_rm_controller & $cat
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $cat)
    {
        // Initializing some variables.
        $rules = [];
        $catData = $cat->get_general_data();

        // We let the system fill our form-fields.
        $this->fill_formfields($cat, $rules, $catData);

        $rules["C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT"]["p_strSelectedID"] = $catData['isys_connection__isys_obj__id'];

        $ajaxParam = array_merge($_GET, [
            C__GET__AJAX           => 1,
            C__GET__AJAX_CALL      => 'category',
            C__CMDB__GET__CATLEVEL => $catData['isys_catg_ap_controller_list__id']
        ]);

        $link = http_build_query($ajaxParam, '', '&');

        $this->get_template_component()
            ->assign('ap_controller_ajax_url', '?' . $link);
        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }
}
