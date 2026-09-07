<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for vehicle information.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.4.0
 */
class isys_cmdb_dao_category_g_vehicle extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11126 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'vehicle';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CATG__VEHICLE';
        $this->m_is_purgable = true;
    }

    /**
     * Method for returning the properties.
     *
     * @return array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'licence_plate' => new TextProperty(
                'C__CATG__VEHICLE__LICENCE_PLATE',
                'LC__CATG__VEHICLE__LICENCE_PLATE',
                'isys_catg_vehicle_list__licence_plate',
                'isys_catg_vehicle_list'
            ),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL', 0) . defined_or_default('C__CATG__VEHICLE', 'C__CATG__VEHICLE'),
                'isys_catg_vehicle_list__description',
                'isys_catg_vehicle_list'
            )
        ];
    }
}
