<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Module\Cmdb\Interfaces\Legacy\ComparableCategory;

/**
 * i-doit
 *
 * DAO: global category for graphic cards.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_graphic extends isys_cmdb_dao_category_global implements ComparableCategory
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11486 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'graphic';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__GRAPHIC';
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function dynamic_property_callback_memory($p_row)
    {
        return isys_convert::retrieveFormattedMemoryByDao($p_row, $this, '__memory');
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function dynamic_properties()
    {
        return [
            '_memory' => new DynamicProperty(
                'LC__CMDB__CATG__MEMORY',
                'isys_catg_graphic_list__id',
                'isys_catg_graphic_list',
                [
                    $this,
                    'dynamic_property_callback_memory'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return array
     */
    protected function properties()
    {
        return [
            'title'        => new TextProperty(
                'C__CATG__GRAPHIC_TITLE',
                'LC__CMDB__CATG__TITLE',
                'isys_catg_graphic_list__title',
                'isys_catg_graphic_list'
            ),
            'manufacturer' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog_plus(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MANUFACTURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Manufacturer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD        => 'isys_catg_graphic_list__isys_graphic_manufacturer__id',
                    C__PROPERTY__DATA__SOURCE_TABLE => 'isys_graphic_manufacturer',
                    C__PROPERTY__DATA__REFERENCES   => [
                        'isys_graphic_manufacturer',
                        'isys_graphic_manufacturer__id'
                    ],
                    C__PROPERTY__DATA__SELECT       => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_graphic_manufacturer__title
                            FROM isys_catg_graphic_list
                            INNER JOIN isys_graphic_manufacturer ON isys_graphic_manufacturer__id = isys_catg_graphic_list__isys_graphic_manufacturer__id',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN         => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_graphic_list', 'LEFT', 'isys_catg_graphic_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_graphic_manufacturer',
                            'LEFT',
                            'isys_catg_graphic_list__isys_graphic_manufacturer__id',
                            'isys_graphic_manufacturer__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID      => 'C__CATG__GRAPHIC_MANUFACTURER',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strTable' => 'isys_graphic_manufacturer'
                    ],
                    C__PROPERTY__UI__DEFAULT => '-1'
                ]
            ]),
            'memory'       => array_replace_recursive(isys_cmdb_dao_category_pattern::float(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__MEMORY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Memory'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_graphic_list__memory',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(ROUND(isys_catg_graphic_list__memory / isys_memory_unit__factor), \' \', isys_memory_unit__title)
                            FROM isys_catg_graphic_list
                            INNER JOIN isys_memory_unit ON isys_memory_unit__id = isys_catg_graphic_list__isys_memory_unit__id',
                        'isys_catg_graphic_list',
                        'isys_catg_graphic_list__id',
                        'isys_catg_graphic_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_graphic_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_graphic_list', 'LEFT', 'isys_catg_graphic_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_memory_unit',
                            'LEFT',
                            'isys_catg_graphic_list__isys_memory_unit__id',
                            'isys_memory_unit__id'
                        )
                    ],
                    C__PROPERTY__DATA__CONDITION => function (isys_cmdb_dao_list_objects $dao, $name, $value) {
                        return self::speedCondition($dao, $name, $value);
                    }
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__GRAPHIC_MEMORY',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-medium'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'convert',
                        ['memory']
                    ],
                    C__PROPERTY__FORMAT__UNIT     => 'unit'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'unit'         => (new DialogProperty(
                'C__CATG__GRAPHIC_MEMORY_UNIT',
                'LC__CATG__MEMORY_UNIT',
                'isys_catg_graphic_list__isys_memory_unit__id',
                'isys_catg_graphic_list',
                'isys_memory_unit'
            ))->mergePropertyUiParams([
                'p_strTable'   => 'isys_memory_unit',
                'p_strClass'   => 'input-mini',
                'p_bDbFieldNN' => 1
            ])->setPropertyUiDefault(defined_or_default('C__MEMORY_UNIT__B', 1000)),
            'firmware'     => new TextProperty(
                'C__CATG__GRAPHIC_FIRMWARE',
                'LC__CMDB__CATG__GRAPHIC_FIRMWARE',
                'isys_catg_graphic_list__firmware',
                'isys_catg_graphic_list'
            ),
            'description'  => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__GRAPHIC', 'C__CATG__GRAPHIC'),
                'isys_catg_graphic_list__description',
                'isys_catg_graphic_list'
            )
        ];
    }

    /**
     * Import-Handler for this category
     *
     * @param array $p_data
     * @param int $p_objid
     * @return array
     */
    public function import($p_data, $p_objid)
    {
        if (is_countable($p_data) && count($p_data) > 0) {
            foreach ($p_data as $l_graphic) {
                $l_ids[] = $this->create_data([
                    'isys_obj__id' => (int)$p_objid, // @see ID-12180 Set the object ID.
                    'title'        => $l_graphic["name"],
                    'manufacturer' => isys_import::check_dialog("isys_graphic_manufacturer", $l_graphic["manufacturer"]),
                    'memory'       => max(0, $l_graphic["memory"] ?? 0),
                    'unit'         => isys_import::check_dialog("isys_memory_unit", $l_graphic["memory_unit"] ?: 'MB'),
                    'firmware'     => $l_graphic["firmware"],
                    'status'       => C__RECORD_STATUS__NORMAL
                ]);
            }
        }

        return $l_ids;
    }

    /**
     * Builds an array with minimal requirement for the sync function
     *
     * @param $p_data
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function parse_import_array($p_data)
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_graphic_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title']
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'memory'       => [
                    'value' => $p_data['memory']
                ],
                'unit'         => [
                    'value' => $p_data['unit']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ],
                'firmware'     => [
                    'value' => $p_data['firmware']
                ]
            ]
        ];
    }

    /**
     * Compares category data for import.
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
        $l_title = strtolower($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['title']['value']);
        if (isset($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value_converted'])) {
            $l_memory = round($p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value_converted'], 0);
        } else {
            $l_unit = $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['unit']['const'];
            $l_memory = strval(round(isys_convert::memory(
                $p_category_data_values[isys_import_handler_cmdb::C__PROPERTIES]['memory']['value'],
                $l_unit,
                C__CONVERT_DIRECTION__FORMWARD
            ), 0));
        }

        // Iterate through local data sets:
        foreach ($p_object_category_dataset as $l_dataset_key => $l_dataset) {
            $p_dataset_id_changed = false;
            $p_dataset_id = $l_dataset[$p_table . '__id'];

            if (isset($p_already_used_data_ids[$p_dataset_id])) {
                // Skip it because ID has already been used for another entry
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
                $p_logger->debug('  Dateset ID "' . $p_dataset_id . '" has already been handled. Skipping to next entry.');
                continue;
            }

            //$p_logger->debug(sprintf('Handle dataset %s.', $p_dataset_id));

            // Test the category data identifier:
            if ($p_category_data_values['data_id'] !== null) {
                if ($p_mode === isys_import_handler_cmdb::C__USE_IDS && $p_category_data_values['data_id'] !== $p_dataset_id) {
                    //$p_logger->debug('Category data identifier is different.');
                    $p_badness[$p_dataset_id]++;
                    $p_dataset_id_changed = true;
                    if ($p_mode === isys_import_handler_cmdb::C__USE_IDS) {
                        continue;
                    }
                }
            }

            $l_dataset_title = strtolower($l_dataset['isys_catg_graphic_list__title']);
            if ($l_dataset_title === $l_title && $l_dataset['isys_catg_graphic_list__memory'] == $l_memory) {
                // Check properties
                // We found our dataset
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__SAME][$l_dataset_key] = $p_dataset_id;

                return;
            } elseif ($l_dataset_title === $l_title && $l_dataset['isys_catg_graphic_list__memory'] !== $l_memory) {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__PARTLY][$l_dataset_key] = $p_dataset_id;
            } else {
                $p_comparison[isys_import_handler_cmdb::C__COMPARISON__DIFFERENT][$l_dataset_key] = $p_dataset_id;
            }
        }
    }

    /**
     * @param $data
     *
     * @return bool|mixed
     */
    public function create_data($data)
    {
        $data['memory'] = isys_convert::memory($data['memory'], $data['unit']);

        return parent::create_data($data);
    }

    /**
     * @param $entryId
     * @param $data
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($entryId, $data)
    {
        $data['memory'] = isys_convert::memory($data['memory'], $data['unit']);

        return parent::save_data($entryId, $data);
    }
}
