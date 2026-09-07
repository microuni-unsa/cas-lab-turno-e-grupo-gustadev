<?php

/**
 * i-doit
 *
 * UI: Global CMDB Category Licence
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_lic_overview extends isys_cmdb_ui_category_specific
{
    /**
     * Defines if this category is multivalued or not.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @return  boolean
     */
    public function is_multivalued()
    {
        return false;
    }

    /**
     * Process method.
     *
     * @global  array                                 $index_includes
     *
     * @param   isys_cmdb_dao_category_s_lic_overview &$p_cat
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        isys_component_template_navbar::getInstance()
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);
        $l_rules = [];

        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_dao_licence = new isys_cmdb_dao_licences($this->m_database_component, $_GET[C__CMDB__GET__OBJECT]);

            $l_sum_licences = $l_dao_licence->calculate_sum(null, null, [
                isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__SINGLE_LICENCE,
                isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__VOLUME_LICENCE
            ]);

            $l_lic_in_use = $l_dao_licence->get_licences_in_use();

            $sumCpuCoreLicences = $l_dao_licence->calculate_sum(null, null, [isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE]);
            $cpuCoreInUse = $l_dao_licence->getCpuCoreLicencesInUse();

            // Calc free licences.
            $l_licences_free = ($l_sum_licences - $l_lic_in_use);
            $freeCpuCoreLicences = ($sumCpuCoreLicences - $cpuCoreInUse);

            if ($l_licences_free < 0) {
                $l_licences_free = 0;
            }

            // Get all licence keys and find out, if some of them are exhausted.
            $l_exhausted = [];
            $l_res = $l_dao_licence->get_licences();

            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_used = $l_dao_licence->get_licences_in_use(C__RECORD_STATUS__NORMAL, $l_row['isys_cats_lic_list__id'], $l_row['isys_cats_lic_list__type']);

                    if ($l_row['isys_cats_lic_list__amount'] > 0 && $l_used > $l_row['isys_cats_lic_list__amount']) {
                        $l_lic_item_link = isys_helper_link::create_url([
                            C__CMDB__GET__OBJECT   => $_GET[C__CMDB__GET__OBJECT],
                            C__CMDB__GET__CATS     => defined_or_default('C__CATS__LICENCE_LIST'),
                            C__CMDB__GET__CATLEVEL => $l_row['isys_cats_lic_list__id']
                        ]);

                        $l_exhausted[] = '<a href="' . $l_lic_item_link . '">' . isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATS__LICENCE_KEY') . ' "' . $l_row['isys_cats_lic_list__key'] . '"</a> <span class="text-neutral-400">(' .
                            ($l_row['isys_cats_lic_list__amount'] - $l_used) . ')</span>';
                    }
                }
            } else {
                $l_exhausted[] = '-';
            }

            $l_rules["C__CATS__LICENCE_SUM"]["p_strValue"] = $l_sum_licences;
            $l_rules["C__CATS__LICENCE_COST"]["p_strValue"] = $l_dao_licence->calculate_cost();
            $l_rules["C__CATS__LICENCE_IN_USE"]["p_strValue"] = $l_lic_in_use;
            $l_rules["C__CATS__LICENCE_FREE"]["p_strValue"] = $l_licences_free;
            $l_rules["C__CATS__CPU_CORE_LICENCE_IN_USE"]["p_strValue"] = $cpuCoreInUse;
            $l_rules["C__CATS__CPU_CORE_LICENCE_FREE"]["p_strValue"] = $freeCpuCoreLicences < 0 ? 0 : $freeCpuCoreLicences;
            $l_rules["C__CATS__EXHAUSTED_LICENCE_KEYS"]["p_strValue"] = (count($l_exhausted) > 0) ? implode(
                ', ',
                $l_exhausted
            ) : isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__NONE');
        } else {
            $l_rules["C__CATS__LICENCE_SUM"]["p_strValue"] = 0;
            $l_rules["C__CATS__LICENCE_COST"]["p_strValue"] = 0;
            $l_rules["C__CATS__LICENCE_IN_USE"]["p_strValue"] = 0;
            $l_rules["C__CATS__LICENCE_FREE"]["p_strValue"] = 0;
            $l_rules["C__CATS__EXHAUSTED_LICENCE_KEYS"]["p_strValue"] = isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__NONE');
        }

        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->deactivate_commentary()
            ->get_template();
    }
}
