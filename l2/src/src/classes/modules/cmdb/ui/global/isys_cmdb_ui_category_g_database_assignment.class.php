<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_database_assignment extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category monitor.
     *
     * @param isys_cmdb_dao_category_g_database_assignment $p_cat
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        $l_rules = [
            'C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT' => [
                'p_strValue' => $l_catdata['assigned_obj_id'],
            ]
        ];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        $this
            ->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
