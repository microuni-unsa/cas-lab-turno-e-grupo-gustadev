<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_login extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = parent::process($p_cat);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $l_rules['C__CONTACT__CHANGE_PASSWORD_POPUP'] = [
            'target-field' => 'C__CONTACT__PERSON_PASSWORD',
            'data-field'   => 'C__CONTACT__PERSON_PASSWORD__action',
            'p_strClass'   => 'input',
        ];

        $logLastLogin = (bool)isys_tenantsettings::get('logging.user.last-login', false);

        if (isset($l_rules['C__CONTACT__PERSON_PASSWORD']['p_strValue']) && !empty($l_rules['C__CONTACT__PERSON_PASSWORD']['p_strValue'])) {
            $l_rules['C__CONTACT__PERSON_PASSWORD']['p_strValue'] = '*********';
        } else {
            $l_rules['C__CONTACT__CHANGE_PASSWORD_POPUP']['hide-set-empty-button'] = true;
        }

        if (!$logLastLogin) {
            $l_rules['C__CONTACT__PERSON_LAST_LOGIN']['p_strValue'] = null;
        }

        $this->get_template_component()
            ->assign('logLastLogin', $logLastLogin)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
