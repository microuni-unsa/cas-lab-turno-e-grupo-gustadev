<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_database_assignment extends isys_component_dao_category_table_list
{
    /**
     * Method for retrieving the category ID.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__DATABASE_ASSIGNMENT');
    }

    /**
     * Method for retrieving the category-type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param array $p_row
     *
     * @throws Exception
     */
    public function modify_row(&$p_row)
    {
        $addition = '';
        $quickinfo = isys_ajax_handler_quick_info::instance();

        if (isset($p_row['isys_catg_relation_list__status']) && $p_row['isys_catg_relation_list__status'] != C__RECORD_STATUS__NORMAL) {
            $translation = isys_application::instance()->container->get('language')->get(
                'LC__CMDB__CATG__DATABASE_ASSIGNMENT__APPLICATION_HAS_STATUS',
                $this->m_cat_dao->get_record_status_as_string($p_row['isys_catg_relation_list__status'])
            );

            $addition = ' <em class="text-neutral-400">(' . $translation . ')</em>';
        }

        $p_row["assignment_title"] = $quickinfo->getQuickInfoReplacement($p_row["isys_obj__id"], $p_row["isys_obj__title"]);

        $p_row["runs_on"] = $quickinfo->getQuickInfoReplacement(
            $p_row["isys_catg_relation_list__isys_obj__id__master"],
            isys_application::instance()->container->get('cmdb_dao')->get_obj_name_by_id_as_string($p_row["isys_catg_relation_list__isys_obj__id__master"]) . $addition
        );
    }

    /**
     * Method for retrieving the fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'assignment_title' => 'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
            'runs_on'          => 'LC__CMDB__CATG__DATABASE_ASSIGNMENT__SOFTWARE_RUNS_ON',
            'isys_cats_database_access_list__description' => 'LC__CMDB__CAT__COMMENTARY',
        ];
    }
}
