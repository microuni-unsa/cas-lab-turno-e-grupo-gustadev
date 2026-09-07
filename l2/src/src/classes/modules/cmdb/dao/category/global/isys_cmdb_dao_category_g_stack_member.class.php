<?php

use idoit\Module\Cmdb\Interfaces\Legacy\ComparableCategory;

/**
 * i-doit
 *
 * DAO: specific category for stack members.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_stack_member extends isys_cmdb_dao_category_global implements ComparableCategory
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stack_member';

    /**
     * Flag which defines if the category is a multivalued category.
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_stack_member_list__stack_member';

    /**
     * Callback method for property mode.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_mode(isys_request $p_request)
    {
        return [
            '0' => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__PASSIVE'),
            '1' => isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__ACTIVE')
        ];
    }

    /**
     * Gets the connected meta stack object(s) (if the given object is part of stacking).
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_stacking_meta($p_obj_id)
    {
        $l_sql = 'SELECT * FROM isys_catg_stack_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_stack_member_list__stack_member = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_stack_member_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Gets all assigned objects of the given stack object.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_connected_objects($p_obj_id)
    {
        $l_sql = 'SELECT *
			FROM isys_catg_stack_member_list
			INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__stack_member
			INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_catg_stack_member_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . '
			AND isys_catg_stack_member_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Create a new entity.
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function create_data($p_data)
    {
        $l_result = parent::create_data($p_data);

        if ($l_result > 0) {
            // This is necessary to create the relations and stuff.
            $this->save_data($l_result, $p_data);
        }

        return $l_result;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STACK_MEMBER__STACK_MEMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Member',
                    C__PROPERTY__INFO__BACKWARD_PROPERTY => 'isys_cmdb_dao_category_g_stack_membership::assigned_object'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD      => 'isys_catg_stack_member_list__stack_member',
                    C__PROPERTY__DATA__REFERENCES => [
                        'isys_obj',
                        'isys_obj__id'
                    ],
                    C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_stack_member_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__stack_member',
                        'isys_catg_stack_member_list',
                        'isys_catg_stack_member_list__id',
                        'isys_catg_stack_member_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stack_member_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN       => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_stack_member_list',
                            'LEFT',
                            'isys_catg_stack_member_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_stack_member_list__stack_member', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__INDEX      => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STACK_MEMBER__STACK_MEMBER',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ]
                ]
            ]),
            'mode'            => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__STACK_MEMBER__MODE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Mode'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stack_member_list__mode',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_stack_member_list__mode = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__ACTIVE') . '
                        	    WHEN isys_catg_stack_member_list__mode = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__PASSIVE') . ' END)
                                FROM isys_catg_stack_member_list',
                        'isys_catg_stack_member_list',
                        'isys_catg_stack_member_list__id',
                        'isys_catg_stack_member_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stack_member_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_stack_member_list',
                            'LEFT',
                            'isys_catg_stack_member_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID     => 'C__CATG__STACK_MEMBER__MODE',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'   => new isys_callback([
                            'isys_cmdb_dao_category_g_stack_member',
                            'callback_property_mode'
                        ]),
                        'p_strClass' => 'input-mini'
                    ]
                ]
            ]),
            'description'     => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_stack_member_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_stack_member_list__description FROM isys_catg_stack_member_list',
                        'isys_catg_stack_member_list',
                        'isys_catg_stack_member_list__id',
                        'isys_catg_stack_member_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_stack_member_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__STACK_MEMBER', 'C__CATG__STACK_MEMBER')
                ]
            ])
        ];
    }

    /**
     * Updates existing entity.
     *
     * @param   integer $p_id   Entity's identifier
     * @param   array   $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_data($p_id, $p_data)
    {
        $l_result = parent::save_data($p_id, $p_data);

        if ($l_result) {
            $l_target_object = null;
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
            $l_relation_default = $l_relation_dao->get_relation_type(defined_or_default('C__RELATION_TYPE__STACKING'))
                ->get_row_value('isys_relation_type__default');

            $l_relation_id = $this->retrieve('SELECT isys_catg_stack_member_list__isys_catg_relation_list__id FROM isys_catg_stack_member_list WHERE isys_catg_stack_member_list__id = ' .
                $this->convert_sql_id($p_id) . ';')
                ->get_row_value('isys_catg_stack_member_list__isys_catg_relation_list__id');

            if ($l_relation_default == C__RELATION_DIRECTION__I_DEPEND_ON) {
                $l_master = $p_data['assigned_object'];
                $l_slave = $p_data['isys_obj__id'];
            } else {
                $l_master = $p_data['isys_obj__id'];
                $l_slave = $p_data['assigned_object'];
            }

            // Handle the implicit relation.
            $l_relation_dao->handle_relation($p_id, "isys_catg_stack_member_list", defined_or_default('C__RELATION_TYPE__STACKING'), $l_relation_id, $l_master, $l_slave);
        }

        return $l_result;
    }

    /**
     * Compares category data for import.
     *
     * If your unique properties needs them, implement it!
     *
     * @param  array    $p_category_data_values
     * @param  array    $p_object_category_dataset
     * @param  array    $p_used_properties
     * @param  array    $p_comparison
     * @param  integer  $p_badness
     * @param  integer  $p_mode
     * @param  integer  $p_category_id
     * @param  string   $p_unit_key
     * @param  array    $p_category_data_ids
     * @param  mixed    $p_local_export
     * @param  boolean  $p_dataset_id_changed
     * @param  integer  $p_dataset_id
     * @param  isys_log $p_logger
     * @param  string   $p_category_name
     * @param  string   $p_table
     * @param  mixed    $p_cat_multi
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    ) {
        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];
            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it ID has already been used
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            // Test the category data identifier:
            if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                //$p_logger->debug('Category data identifier is different.');
                $p_badness[$p_dataset_id]++;
                $p_dataset_id_changed = true;

                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                    continue;
                }
            }

            if ($l_dataset['isys_obj__id'] == $p_category_data_values['properties']['assigned_object']['id']) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }
}
