<?php

/**
 * i-doit
 *
 * DAO: global category list for e-mail addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_mail_addresses extends isys_component_dao_category_table_list
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
     * Modifies output of every row
     *
     * @param array $row
     */
    public function modify_row(&$row)
    {
        $wwwDir = isys_application::instance()->www_path;
        $language = isys_application::instance()->container->get('language');

        $row['isys_catg_mail_addresses_list__primary'] = '<div class="display-flex align-items-center">' .
            ($row['isys_catg_mail_addresses_list__primary']
                ? '<img src="' . $wwwDir . 'images/axialis/basic/symbol-ok.svg" alt="" class="mr5" /><span class="text-green">' . $language->get('LC__UNIVERSAL__YES') . '</span>'
                : '<img src="' . $wwwDir . 'images/axialis/basic/symbol-cancel.svg" alt="" class="mr5" /><span class="text-red">' . $language->get('LC__UNIVERSAL__NO') . '</span>') .
            '</div>';
    }

    /**
     * Gets fields to display in the list view.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_mail_addresses_list__title'       => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
            'isys_catg_mail_addresses_list__primary'     => 'LC__CATG__CONTACT_LIST__PRIMARY',
            'isys_catg_mail_addresses_list__description' => 'LC__CMDB__CATG__DESCRIPTION'
        ];
    }
}
