<?php

/**
 * i-doit
 *
 * DAO: Category list for certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_certificate extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * @return int|mixed|null
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CERTIFICATE');
    }

    /**
     * @return int
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param $data
     *
     * @return void
     * @throws Exception
     */
    public function modify_row(&$data)
    {
        $locales = isys_application::instance()->container->get('locales');
        $data['created'] = $data['expire'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (!empty($data['isys_catg_certificate_list__created'])) {
            // @see ID-9382 I convert the value because 'fmt_date' will otherwise keep the time, I didn't change this because it might lead to unwanted results in other places.
            $data['created'] = $locales->fmt_date(strtotime($data['isys_catg_certificate_list__created']));
        }

        if (!empty($data['isys_catg_certificate_list__expire'])) {
            // @see ID-9382 I convert the value because 'fmt_date' will otherwise keep the time, I didn't change this because it might lead to unwanted results in other places.
            $data['expire'] = $locales->fmt_date(strtotime($data['isys_catg_certificate_list__expire']));
        }
    }

    /**
     * @return array
     */
    public function get_fields()
    {
        return [
            'isys_catg_certificate_list__common_name' => 'LC__CMDB__CATG__CERTIFICATE__COMMON_NAME',
            'isys_certificate_type__title'            => 'LC__CMDB__CATG__TYPE',
            'created'                                 => 'LC__CMDB__CATG__CERTIFICATE__CREATE_DATE',
            'expire'                                  => 'LC__CMDB__CATG__CERTIFICATE__EXPIRE_DATE',
            'isys_catg_certificate_list__created'     => false,
            'isys_catg_certificate_list__expire'      => false,
            'isys_catg_certificate_list__description' => 'LC__CMDB__CAT__COMMENTARY'
        ];
    }

    /**
     * @param $column
     * @param $direction
     *
     * @return string
     */
    public function get_order_condition($column, $direction)
    {
        if ($column === 'created') {
            $column = 'isys_catg_certificate_list__created';
        }

        if ($column === 'expire') {
            $column = 'isys_catg_certificate_list__expire';
        }

        return "{$column} {$direction}";
    }
}
