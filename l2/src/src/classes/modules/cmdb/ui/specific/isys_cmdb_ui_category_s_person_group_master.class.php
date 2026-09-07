<?php

use idoit\Component\FeatureManager\FeatureManager;

/**
 * i-doit
 *
 * CMDB Person: Specific category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_group_master extends isys_cmdb_ui_category_specific
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_ldap = false;
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // @see ID-10569 Simply check if 'isys_module_ldap' exists (broke by ID-10379).
        if (class_exists(isys_module_ldap::class) && FeatureManager::isFeatureActive('ldap-login')) {
            $l_ldap = true;
            $l_rules["C__CONTACT__GROUP_LDAP"]["p_strValue"] = $l_catdata["isys_cats_person_group_list__ldap_group"];
        }

        $personCommentary = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP', 'C__CATS__PERSON_GROUP');
        $personMasterCommentary = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_GROUP_MASTER', 'C__CATS__PERSON_GROUP_MASTER');

        $l_rules[$personCommentary]['p_strValue'] = $l_rules[$personMasterCommentary]['p_strValue'];

        $this->m_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->assign("ldap", $l_ldap);
    }
}
