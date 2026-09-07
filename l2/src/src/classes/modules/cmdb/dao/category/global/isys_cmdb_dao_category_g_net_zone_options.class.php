<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\VirtualProperty;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * CMDB global category for net zone options.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_dao_category_g_net_zone_options extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_zone_options';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CATG__NET_ZONE_OPTIONS';

    /**
     * Method for retrieving all category properties.
     *
     * @return array
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function properties()
    {
        $colorHtmlParts = [
            '\'<div class="cmdb-marker" style="background-color:\'',
            'isys_catg_net_zone_options_list__color',
            '\';"></div>\''
        ];

        return [
            'colorblob'       => (new VirtualProperty(
                'C__CATG__NET_ZONE_OPTIONS__COLOR_BLOB',
                'LC__CATG__NET_ZONE_OPTIONS__COLOR',
                'isys_catg_net_zone_options_list__color',
                'isys_catg_net_zone_options_list'
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    'SELECT CONCAT(' . implode(',', $colorHtmlParts) . ') FROM isys_catg_net_zone_options_list',
                    'isys_catg_net_zone_options_list',
                    'isys_catg_net_zone_options_list__id',
                    'isys_catg_net_zone_options_list__isys_obj__id'
                )
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__LIST => true
            ]),
            'color'       => (new TextProperty(
                'C__CATG__NET_ZONE_OPTIONS__COLOR',
                'LC__CATG__NET_ZONE_OPTIONS__COLOR_HEX',
                'isys_catg_net_zone_options_list__color',
                'isys_catg_net_zone_options_list'
            ))->mergePropertyUiParams([
                'size'        => 'mini',
                'placeholder' => '#ffffff',
                'default'     => '#ffffff'
            ]),
            'domain'      => (new TextProperty(
                'C__CATG__NET_ZONE_OPTIONS__DOMAIN',
                'LC__CATG__NET_ZONE_OPTIONS__DOMAIN',
                'isys_catg_net_zone_options_list__domain',
                'isys_catg_net_zone_options_list'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'description' => (new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . C__CATG__NET_ZONE_OPTIONS,
                'isys_catg_net_zone_options_list__description',
                'isys_catg_net_zone_options_list'
            ))
        ];
    }
}
