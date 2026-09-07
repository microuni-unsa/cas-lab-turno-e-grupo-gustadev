<?php

use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: specific category for applications with assigned objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_database_installation extends isys_cmdb_dao_category_s_application_assigned_obj
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'database_installation';

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_dao = new isys_cmdb_dao_category_g_application($this->m_db);

        if ($p_obj_id > 0) {
            $l_condition = ' AND isys_connection__isys_obj__id = ' . $l_dao->convert_sql_id($p_obj_id);
        } else {
            $l_condition = '';
        }

        return $l_dao->get_data($p_cats_list_id, null, $l_condition, $p_filter, $p_status);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function properties()
    {
        /** @var Property[] $properties */
        $properties = parent::properties();

        $properties['description']->getUi()
            ->setId('C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATS__DATABASE_INSTALLATION', 'C__CATS__DATABASE_INSTALLATION'));

        return $properties;
    }
}
