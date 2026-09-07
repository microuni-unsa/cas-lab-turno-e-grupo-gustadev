<?php

/**
 * i-doit
 *
 * DAO: specific category list for contract assignment.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_contract_assignment extends isys_component_dao_category_table_list
{

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    }

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    }

    /**
     * @param array $row
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        $locales = isys_application::instance()->container->get('locales');

        $row["object_title"] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
            $row["isys_connection__isys_obj__id"],
            isys_application::instance()->container->get('cmdb_dao')->get_obj_name_by_id_as_string($row["isys_connection__isys_obj__id"])
        );

        if ((empty($row['isys_catg_contract_assignment_list__contract_start']) || empty($row['isys_catg_contract_assignment_list__contract_end'])) && !empty($row["isys_connection__isys_obj__id"])) {
            $contractData = isys_cmdb_dao_category_s_contract::instance($this->get_database_component())
                ->get_data(null, $row["isys_connection__isys_obj__id"])
                ->get_row();

            if (empty($row['isys_catg_contract_assignment_list__contract_start'])) {
                $row['isys_catg_contract_assignment_list__contract_start'] = $locales->fmt_date($contractData['isys_cats_contract_list__start_date']);
            } else {
                $row['isys_catg_contract_assignment_list__contract_start'] = $locales->fmt_date(str_replace("00:00:00", "", $row['isys_catg_contract_assignment_list__contract_start']));
            }

            if (empty($row['isys_catg_contract_assignment_list__contract_end'])) {
                $row['isys_catg_contract_assignment_list__contract_end'] = $locales->fmt_date($contractData['isys_cats_contract_list__end_date']);
            } else {
                $row["isys_catg_contract_assignment_list__contract_end"] = $locales->fmt_date(str_replace("00:00:00", "", $row['isys_catg_contract_assignment_list__contract_end']));
            }
        } else {
            $row['isys_catg_contract_assignment_list__contract_start'] = $locales->fmt_date(str_replace("00:00:00", "", $row['isys_catg_contract_assignment_list__contract_start']));
            $row["isys_catg_contract_assignment_list__contract_end"] = $locales->fmt_date(str_replace("00:00:00", "", $row['isys_catg_contract_assignment_list__contract_end']));
        }

        $row['reaction_rate'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($row['isys_catg_contract_assignment_list__reaction_rate__id'])) {
            $reactionRateData = isys_cmdb_dao_dialog::instance($this->m_db)
                ->set_table('isys_contract_reaction_rate')
                ->get_data($row['isys_catg_contract_assignment_list__reaction_rate__id']);

            $row['reaction_rate'] = $reactionRateData['title'];
        } elseif (!empty($row['isys_cats_contract_list__isys_contract_reaction_rate__id'])) {
            // @see ID-10852 Use specific category content as fallback, if reaction rate was not overwritten.
            $reactionRateData = isys_cmdb_dao_dialog::instance($this->m_db)
                ->set_table('isys_contract_reaction_rate')
                ->get_data($row['isys_cats_contract_list__isys_contract_reaction_rate__id']);

            $row['reaction_rate'] = $reactionRateData['title'];
        }
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'object_title'                                       => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACT',
            'isys_catg_contract_assignment_list__contract_start' => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START',
            'isys_catg_contract_assignment_list__contract_end'   => 'LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END',
            'reaction_rate'                                      => 'LC__CMDB__CATS__CONTRACT__REACTION_RATE',
            'isys_catg_contract_assignment_list__description'    => 'LC__CMDB__LOGBOOK__DESCRIPTION',
        ];
    }
}
