<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for telephone/fax
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_telephone_fax extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'telephone_fax';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [
            'type' => new DialogPlusProperty(
                'C__CATG__TELEPHONE_FAX__TYPE',
                'LC__CMDB__CATG__TELEPHONE_FAX__TYPE',
                'isys_catg_telephone_fax_list__isys_telephone_fax_type__id',
                'isys_catg_telephone_fax_list',
                'isys_telephone_fax_type'
            ),
            'telephone_number' => new TextProperty(
                'C__CATG__TELEPHONE_FAX__TELEPHONE_NUMBER',
                'LC__CMDB__CATG__TELEPHONE_FAX__TELEPHONE_NUMBER',
                'isys_catg_telephone_fax_list__telephone_number',
                'isys_catg_telephone_fax_list'
            ),
            'fax_number' => new TextProperty(
                'C__CATG__TELEPHONE_FAX__FAX_NUMBER',
                'LC__CMDB__CATG__TELEPHONE_FAX__FAX_NUMBER',
                'isys_catg_telephone_fax_list__fax_number',
                'isys_catg_telephone_fax_list'
            ),
            'extension' => new TextProperty(
                'C__CATG__TELEPHONE_FAX__EXTENSION',
                'LC__CMDB__CATG__TELEPHONE_FAX__EXTENSION',
                'isys_catg_telephone_fax_list__extension',
                'isys_catg_telephone_fax_list'
            ),
            'pincode' => new TextProperty(
                'C__CATG__TELEPHONE_FAX__PINCODE',
                'LC__CMDB__CATG__TELEPHONE_FAX__PINCODE',
                'isys_catg_telephone_fax_list__pincode',
                'isys_catg_telephone_fax_list'
            ),
            'imei' => new TextProperty(
                'C__CATG__TELEPHONE_FAX__IMEI',
                'LC__CMDB__CATG__TELEPHONE_FAX__IMEI',
                'isys_catg_telephone_fax_list__imei',
                'isys_catg_telephone_fax_list'
            ),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__TELEPHONE_FAX', 'C__CATG__TELEPHONE_FAX'),
                'isys_catg_telephone_fax_list__description',
                'isys_catg_telephone_fax_list'
            )
        ];
    }
}
