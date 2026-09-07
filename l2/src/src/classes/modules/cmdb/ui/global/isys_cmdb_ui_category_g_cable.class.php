<?php

/**
 * i-doit
 *
 * CMDB Active Directory: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_cable extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param isys_cmdb_dao_category $dao
     *
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $dao)
    {
        $entryData = $dao->get_general_data();
        $rules = [];

        $this->fill_formfields($dao, $rules, $entryData);

        // Make rules.
        $rules['C__CATG__CABLE_LENGTH']['p_strValue'] = $this->formatNumberViewMode(isys_convert::measure(
            $entryData['isys_catg_cable_list__length'],
            $entryData['isys_catg_cable_list__isys_depth_unit__id'],
            C__CONVERT_DIRECTION__BACKWARD
        ));

        $rules['C__CMDB__CAT__COMMENTARY_' . $dao->get_category_type() . $dao->get_category_id()]['p_strValue'] = $entryData['isys_catg_cable_list__description'];

        // Apply rules.
        $this->get_template_component()->smarty_tom_add_rules("tom.content.bottom.content", $rules);
    }
}
