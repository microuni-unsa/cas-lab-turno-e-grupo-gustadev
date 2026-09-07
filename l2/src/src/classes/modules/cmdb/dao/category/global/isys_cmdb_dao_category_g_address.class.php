<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\IntProperty;
use idoit\Component\Property\Type\TextAreaProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for address.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_address extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11126 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'address';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CATG__ADDRESS';
        $this->m_is_purgable = true;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'address'  => new TextAreaProperty(
                'C__CATG__ADDRESS__ADDRESS',
                'LC__CATG__ADDITIONAL_ADDRESS_INFORMATION',
                'isys_catg_address_list__address',
                'isys_catg_address_list'
            ),
            'street' => new TextProperty(
                'C__CATG__ADDRESS__STREET',
                'LC__CONTACT__ORGANISATION_STREET',
                'isys_catg_address_list__street',
                'isys_catg_address_list'
            ),
            'house_no' => new TextProperty(
                'C__CATG__ADDRESS__HOUSE_NO',
                'LC__CATG__ADDRESS__HOUSE_NO',
                'isys_catg_address_list__house_no',
                'isys_catg_address_list'
            ),
            'stories' => new IntProperty(
                'C__CATG__ADDRESS__STORIES',
                'LC__CATG__ADDRESS__FLOORS',
                'isys_catg_address_list__stories',
                'isys_catg_address_list'
            ),
            'postcode' => new TextProperty(
                'C__CATG__ADDRESS__PCODE',
                'LC__CONTACT__ORGANISATION_POSTAL_CODE',
                'isys_catg_address_list__postalcode',
                'isys_catg_address_list'
            ),
            'city' => new TextProperty(
                'C__CATG__ADDRESS__CITY',
                'LC__CONTACT__ORGANISATION_CITY',
                'isys_catg_address_list__city',
                'isys_catg_address_list'
            ),
            'region' => new TextProperty(
                'C__CATG__ADDRESS__REGION',
                'LC__CATG__ADDRESS__REGION',
                'isys_catg_address_list__region',
                'isys_catg_address_list'
            ),
            'country' => new TextProperty(
                'C__CATG__ADDRESS__COUNTRY',
                'LC__CATG__ADDRESS__COUNTRY',
                'isys_catg_address_list__country',
                'isys_catg_address_list'
            ),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL', 0) . defined_or_default('C__CATG__ADDRESS', 'C__CATG__ADDRESS'),
                'isys_catg_address_list__description',
                'isys_catg_address_list'
            )
        ];
    }
}
