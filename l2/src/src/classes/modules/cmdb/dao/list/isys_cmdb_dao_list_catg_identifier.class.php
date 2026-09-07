<?php

/**
 * i-doit
 *
 * DAO: specific category list for custom identifier
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_identifier extends isys_component_dao_category_table_list
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
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_identifier_list__id'          => 'ID',
            'isys_catg_identifier_list__key'         => 'LC__CMDB__CATG__IDENTIFIER__KEY',
            'isys_catg_identifier_list__value'       => 'LC__CMDB__CATG__IDENTIFIER__VALUE',
            'isys_catg_identifier_list__group'       => 'LC__CMDB__CATG__IDENTIFIER__GROUP',
            'isys_catg_identifier_list__datetime'    => 'LC__CMDB__CATG__IDENTIFIER__LAST_EDITED',
            'isys_catg_identifier_type__title'       => 'LC__CMDB__CATG__IDENTIFIER__TYPE',
            'isys_catg_identifier_list__last_scan'   => 'LC__CMDB__CATG__IDENTIFIER__LAST_SCAN',
            'isys_catg_identifier_list__description' => 'LC__CMDB__CATG__DESCRIPTION'
        ];
    }
}
