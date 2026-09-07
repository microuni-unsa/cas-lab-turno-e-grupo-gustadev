<?php

/**
 * i-doit
 *
 * DAO: specific category list for audits
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_audit extends isys_component_dao_category_table_list
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
     * Modifies single rows for displaying links or getting translations
     *
     * @param   array & $p_row
     */
    public function modify_row(&$p_row)
    {
        $l_table = $this->m_cat_dao->get_table();
        $relationDao = isys_cmdb_dao_category_g_contact::instance($this->m_db);
        $l_type_table = 'isys_catg_audit_type';

        if ($p_row[$l_table . '__type'] > 0) {
            $l_sql = 'SELECT ' . $l_type_table . '__title FROM ' . $l_type_table . ' WHERE ' . $l_type_table . '__id = ' . $p_row[$l_table . '__type'] . ' LIMIT 1;';

            $l_query = $this->retrieve($l_sql);

            if ($l_row = $l_query->get_row()) {
                $p_row[$l_table . '__type'] = $l_row[$l_type_table . '__title'];
            }
        }

        if ($p_row[$l_table . '__apply']) {
            $p_row[$l_table . '__apply'] = isys_application::instance()->container->get('locales')->fmt_date($p_row[$l_table . '__apply']);
        } else {
            $p_row[$l_table . '__apply'] = isys_tenantsettings::get('gui.empty_value', '-');
        }

        $contactAssignments = [
            $l_table . '__commission',
            $l_table . '__responsible',
            $l_table . '__involved'
        ];

        $l_list_dao = isys_component_list::instance($this->m_db);
        $l_list_dao->set_listdao($this);
        $tableConfig = $l_list_dao->get_table_config();

        $groupAsList = $tableConfig->getGroupingType() === isys_cmdb_dao_category_property_ng::C__GROUPING__LIST;

        foreach ($contactAssignments as $assignment) {
            if (isset($p_row[$assignment]) && !empty($p_row[$assignment])) {
                $contacts = [];

                $personRes = $relationDao->get_assigned_contacts_by_relation_id($p_row[$assignment]);

                while ($row = $personRes->get_row()) {
                    $contacts[] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement($row['isys_obj__id'], $row['isys_obj__title']);
                }

                if ($groupAsList) {
                    $p_row[$assignment] = '<ul><li>' . implode('</li><li>', $contacts) . '</li></ul>';
                } else {
                    $p_row[$assignment] = implode(', ', $contacts);
                }
            }
        }
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        $l_table = $this->m_cat_dao->get_table();
        $l_properties = $this->m_cat_dao->get_properties();

        return [
            $l_table . '__id'                  => 'ID',
            $l_table . '__title'               => $l_properties['title'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__type'                => $l_properties['type'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__commission'          => $l_properties['commission'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__responsible'         => $l_properties['responsible'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__involved'            => $l_properties['involved'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__period_manufacturer' => $l_properties['period_manufacturer'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__period_operator'     => $l_properties['period_operator'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__apply'               => $l_properties['apply'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__result'              => $l_properties['result'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__fault'               => $l_properties['fault'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__incident'            => $l_properties['incident'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $l_table . '__description'         => $l_properties['description'][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
        ];
    }
}
