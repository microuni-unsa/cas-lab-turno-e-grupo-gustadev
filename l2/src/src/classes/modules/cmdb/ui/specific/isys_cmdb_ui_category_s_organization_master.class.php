<?php

/**
 * i-doit
 *
 * UI: specific category for organizations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_organization_master extends isys_cmdb_ui_category_specific
{
    /**
     * @param isys_cmdb_dao_category $dao
     *
     * @return array|void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $dao)
    {
        $this->fill_formfields($dao, $l_rules, $dao->get_general_data());

        $oldConstant = 'C__CMDB__CAT__COMMENTARY_' . defined_or_default('C__CMDB__CATEGORY__TYPE_SPECIFIC', '') . defined_or_default('C__CATS__ORGANIZATION', '');
        $newConstant = 'C__CMDB__CAT__COMMENTARY_' . defined_or_default('C__CMDB__CATEGORY__TYPE_SPECIFIC', '') . defined_or_default('C__CATS__ORGANIZATION_MASTER_DATA', '');

        if (isset($l_rules[$oldConstant]) && $_GET[C__CMDB__GET__CATS] == C__CATS__ORGANIZATION_MASTER_DATA) {
            $l_rules[$newConstant] = $l_rules[$oldConstant];
        }

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
