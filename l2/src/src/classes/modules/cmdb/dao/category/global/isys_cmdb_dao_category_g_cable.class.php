<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\IntProperty;

/**
 * i-doit
 *
 * DAO: global category for cables.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cable extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11486 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'cable';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CATG__CABLE';
        $this->m_is_purgable = true;
    }

    /**
     * Dynamic property handling for getting the connected objects.
     *
     * @param   array $p_row
     *
     * @return  string
     */
    public function dynamic_property_callback_connection(array $p_row)
    {
        $l_conn_data = [];
        $l_dao = isys_cmdb_dao_cable_connection::instance(isys_application::instance()->container->get('database'));
        $objectId = $p_row['isys_catg_cable_list__isys_obj__id'] ?? $p_row['isys_obj__id'];
        $l_cable_connection = $l_dao->get_cable_connection_id_by_cable_id($objectId);
        $l_connection = $l_dao->get_connection_info($l_cable_connection);

        while ($l_row = $l_connection->get_row()) {
            $l_conn_data[] = $l_row['isys_obj__title'] . ' (' . $l_row['isys_catg_connector_list__title'] . ')';
        }

        return html_entity_decode(implode(' &lsaquo;&mdash;&rsaquo; ', $l_conn_data));
    }

    /**
     * Retrieve the calculated cable length with unit
     *
     * @param array $data
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_length(array $data)
    {
        $dao = isys_cmdb_dao_category_g_cable::instance(isys_application::instance()->container->get('database'));
        $property = $dao->get_property_by_key('length');
        $objectId = $data['isys_catg_cable_list__isys_obj__id'] ?? $data['isys_obj__id'];
        /**
         * @var $selectQuery \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect
         */
        $selectQuery = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $condition[] = $selectQuery->getSelectFieldObjectID() . ' = ' . $dao->convert_sql_id($objectId);
        $selectQuery->setSelectCondition(\idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory($condition));

        $result = $dao->retrieve($selectQuery);
        if (is_countable($result) && count($result) > 0) {
            return current($result->get_row());
        }
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @inheritDoc
     */
    public function create_data($data)
    {
        if (!isset($data['length_unit']) || !$data['length_unit']) {
            $data['length_unit'] = defined_or_default('C__DEPTH_UNIT__CM', 2);
        }

        $data['length'] = isys_convert::measure($data['length'], $data['length_unit'], C__CONVERT_DIRECTION__FORMWARD);

        return parent::create_data($data);
    }

    /**
     * @inheritDoc
     */
    public function save_data($entryId, $data)
    {
        if (!isset($data['length_unit']) || !$data['length_unit']) {
            $data['length_unit'] = defined_or_default('C__DEPTH_UNIT__CM', 2);
        }

        $data['length'] = isys_convert::measure($data['length'], $data['length_unit'], C__CONVERT_DIRECTION__FORMWARD);

        return parent::save_data($entryId, $data);
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_connection' => new DynamicProperty(
                'LC__CATS__CABLE__CONNECTION',
                'isys_catg_cable_list__isys_obj__id',
                'isys_catg_cable_list',
                [
                    $this,
                    'dynamic_property_callback_connection'
                ]
            ),
            '_length' => new DynamicProperty(
                'LC__CMDB__CATS__CABLE__LENGTH',
                'isys_catg_cable_list__isys_obj__id',
                'isys_catg_cable_list',
                [
                    $this,
                    'dynamic_property_callback_length'
                ]
            )
        ];
    }


    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'cable_type' => (new DialogPlusProperty(
                'C__CATG__CABLE_TYPE',
                'LC__CMDB__CATS__CABLE__TYPE',
                'isys_catg_cable_list__isys_cable_type__id',
                'isys_catg_cable_list',
                'isys_cable_type'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'cable_colour' => (new DialogPlusProperty(
                'C__CATG__CABLE_COLOUR',
                'LC__CMDB__CATS__CABLE__COLOUR',
                'isys_catg_cable_list__isys_cable_colour__id',
                'isys_catg_cable_list',
                'isys_cable_colour'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'cable_occupancy' => (new DialogPlusProperty(
                'C__CATG__CABLE_OCCUPANCY',
                'LC__CMDB__CATS__CABLE__OCCUPANCY',
                'isys_catg_cable_list__isys_cable_occupancy__id',
                'isys_catg_cable_list',
                'isys_cable_occupancy'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false
            ]),
            'length'                     => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO   => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__CABLE__LENGTH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Length in CM'
                ],
                C__PROPERTY__DATA   => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cable_list__length',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(' . $this->m_db->format_number('isys_catg_cable_list__length / isys_depth_unit__factor') . ', \' \', isys_depth_unit__title)
                            FROM isys_catg_cable_list
                            INNER JOIN isys_depth_unit ON isys_depth_unit__id = isys_catg_cable_list__isys_depth_unit__id',
                        'isys_catg_cable_list',
                        'isys_catg_cable_list__id',
                        'isys_catg_cable_list__isys_obj__id'
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_cable_list', 'LEFT', 'isys_catg_cable_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_depth_unit', 'LEFT', 'isys_catg_cable_list__isys_depth_unit__id', 'isys_depth_unit__id')
                    ],
                ],
                C__PROPERTY__FORMAT => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['measure']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'length_unit'
                ],
                C__PROPERTY__UI     => [
                    C__PROPERTY__UI__ID     => 'C__CATG__CABLE_LENGTH',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ]
            ]),
            'length_unit' => (new DialogProperty(
                'C__CATG__CABLE_LENGTH_UNIT',
                'LC__CMDB__CATS__CABLE__LENGTH_UNIT',
                'isys_catg_cable_list__isys_depth_unit__id',
                'isys_catg_cable_list',
                'isys_depth_unit'
            ))->mergePropertyUiParams([
                'p_strClass'   => 'input-mini',
                'p_bDbFieldNN' => 1
            ])->setPropertyUiDefault(
                C__DEPTH_UNIT__CM
            )->setPropertyProvidesOffset(Property::C__PROPERTY__PROVIDES__IMPORT, true),
            'max_amount_of_fibers_leads' => new IntProperty(
                'C__CATG__CABLE_MAX_AMOUNT_OF_FIBERS_LEADS',
                'LC__CMDB__CATS__CABLE__MAX_AMOUNT_OF_FIBERS_LEADS',
                'isys_catg_cable_list__max_amount_of_fibers_leads',
                'isys_catg_cable_list'
            ),
            'connection'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::virtual(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATS__CABLE__CONNECTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Kabelverbindung'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cable_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj__id FROM isys_obj',
                        'isys_obj',
                        'isys_obj__id',
                        'isys_obj__id'
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CABLE', 'C__CATG__CABLE'),
                'isys_catg_cable_list__description',
                'isys_catg_cable_list'
            )
        ];
    }

    /**
     * Hack for ID-7135 to prevent the warning
     *
     * @return array
     * @throws isys_exception_ui
     */
    public function sanitize_post_data()
    {
        $_POST["C__CATG__CABLE_LENGTH"] = isys_helper::filter_number($_POST["C__CATG__CABLE_LENGTH"]);

        return $_POST = parent::sanitize_post_data();
    }
}
