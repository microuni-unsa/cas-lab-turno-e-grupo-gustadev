<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\TextAreaProperty;

/**
 * i-doit
 *
 * DAO: global category for Certificate
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_certificate extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'certificate';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CERTIFICATE';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @return array
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'type'        => new DialogPlusProperty(
                'C__CATG__CERTIFICATE__TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_catg_certificate_list__isys_certificate_type__id',
                'isys_catg_certificate_list',
                'isys_certificate_type'
            ),
            'create_date' => new DateProperty(
                'C__CATG__CERTIFICATE__CREATE_DATE',
                'LC__CMDB__CATG__CERTIFICATE__CREATE_DATE',
                'isys_catg_certificate_list__created',
                'isys_catg_certificate_list'
            ),
            'expire_date' => new DateProperty(
                'C__CATG__CERTIFICATE__EXPIRE_DATE',
                'LC__CMDB__CATG__CERTIFICATE__EXPIRE_DATE',
                'isys_catg_certificate_list__expire',
                'isys_catg_certificate_list'
            ),
            'common_name' => new TextAreaProperty(
                'C__CATG__CERTIFICATE__COMMON_NAME',
                'LC__CMDB__CATG__CERTIFICATE__COMMON_NAME',
                'isys_catg_certificate_list__common_name',
                'isys_catg_certificate_list'
            ),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CERTIFICATE', 'C__CATG__CERTIFICATE'),
                'isys_catg_certificate_list__description',
                'isys_catg_certificate_list'
            )
        ];
    }
}
