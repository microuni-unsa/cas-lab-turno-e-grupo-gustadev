<?php

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Module\Cmdb\Model\Entry\Entry;
use idoit\Module\Cmdb\Model\Entry\EntryCollection;

/**
 * i-doit
 *
 * DAO: specific category for layer2-net assigned logical ports
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_layer2_net_assigned_logical_ports extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'layer2_net_assigned_logical_ports';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'isys_obj__id';

    /**
     * Determines if Category is multivalued or not
     *
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * Flag which defines if the category is only a list with an object browser
     *
     * @var bool
     */
    protected $m_object_browser_category = true;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = 'isys_catg_log_port_list__id';

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table = 'isys_catg_log_port_list_2_isys_obj';

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_obj__id';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_catg_log_port_list__isys_obj__id';

    /**
     * Create method.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_port_id
     * @param   integer $p_status
     *
     * @return  mixed  Integer with last inserted ID on success, boolean false on failure.
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function create($p_obj_id, $p_port_id, $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_sql = 'INSERT INTO ' . $this->m_table . ' SET ' . $this->m_table . '__status = ' . $this->convert_sql_int($p_status) . ', ' . 'isys_obj__id = ' .
            $this->convert_sql_int($p_obj_id) . ', ' . 'isys_catg_log_port_list__id = ' . $this->convert_sql_int($p_port_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            $this->m_strLogbookSQL .= $l_sql;

            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Create element method, gets called from the object browser after confirming the selection.
     *
     * @param   integer $p_cat_level
     * @param   integer & $p_new_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $this->delete_entries_by_obj_id((int) $p_object_id);

        if (count($p_objects) > 0) {
            foreach ($p_objects as $l_entry) {
                $this->create($p_object_id, $l_entry);
            }
        }
    }

    /**
     * Delete entries by object id for this category
     *
     * @param int $objectId
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function delete_entries_by_obj_id(int $objectId): bool
    {
        if ($objectId > 0) {
            $table = $this->get_table();

            $query = "DELETE FROM {$table} WHERE isys_obj__id = {$objectId};";

            return $this->update($query) && $this->apply_update();
        }

        return false;
    }

    /**
     * @param null $p_obj_id
     *
     * @return int
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        if ($this->m_table && $l_obj_id > 0) {
            $l_sql = "SELECT COUNT(isys_obj__id) AS count
				FROM " . $this->m_table . "
				WHERE (" . $this->m_table . "__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . " OR " . $this->m_table . "__status = " .
                $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ")
				AND isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ";";

            $l_amount = $this->retrieve($l_sql)
                ->get_row();

            return (int)$l_amount["count"];
        }

        return false;
    }

    /**
     * Get-data method.
     *
     * @param   integer $p_catg_port_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_data($p_catg_port_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_log_port_list_2_isys_obj
			INNER JOIN isys_obj AS main ON main.isys_obj__id = ' . $this->m_table . '.isys_obj__id
			INNER JOIN isys_catg_log_port_list ON isys_catg_log_port_list.isys_catg_log_port_list__id = ' . $this->m_table . '.isys_catg_log_port_list__id
			WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter) . ' ';

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_port_id !== null) {
            $l_sql .= 'AND isys_catg_log_port_list_2_isys_obj__id = ' . $this->convert_sql_id($p_catg_port_id) . ' ';
        }

        if ($p_status !== null) {
            $l_sql .= 'AND isys_obj__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (' . $this->m_table . '.isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (' . $this->m_table . '.isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'isys_obj__id'                => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__UNIVERSAL__OBJECT_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Object title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_obj__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'main',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(obj.isys_obj__title, \' {\', obj.isys_obj__id, \'}\')
                                FROM isys_catg_log_port_list_2_isys_obj AS main
                                INNER JOIN isys_catg_log_port_list AS lp ON lp.isys_catg_log_port_list__id = main.isys_catg_log_port_list__id
                                INNER JOIN isys_obj obj ON obj.isys_obj__id = lp.isys_catg_log_port_list__isys_obj__id',
                        'isys_catg_log_port_list_2_isys_obj',
                        'main.isys_catg_log_port_list_2_isys_obj__id',
                        'main.isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_log_port_list_2_isys_obj',
                            'LEFT',
                            'isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_log_port_list',
                            'LEFT',
                            'isys_catg_log_port_list__id',
                            'isys_catg_log_port_list__id',
                            'main',
                            'lp',
                            'lp'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_log_port_list__isys_obj__id',
                            'isys_obj__id',
                            'lp',
                            'obj',
                            'obj'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_OBJ__ID'
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT => false,
                    C__PROPERTY__PROVIDES__IMPORT => false,
                    C__PROPERTY__PROVIDES__EXPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ]),
            'isys_catg_log_port_list__id' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__LAYER2_NET_ASSIGNED_LOGICAL_PORTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned objects',
                    C__PROPERTY__INFO__BACKWARD_PROPERTY => 'isys_cmdb_dao_category_g_network_ifacel::net'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_log_port_list__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_log_port_list',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT lp.isys_catg_log_port_list__title
                                FROM isys_catg_log_port_list_2_isys_obj AS main
                                INNER JOIN isys_catg_log_port_list AS lp ON lp.isys_catg_log_port_list__id = main.isys_catg_log_port_list__id',
                        'isys_catg_log_port_list_2_isys_obj',
                        'main.isys_catg_log_port_list_2_isys_obj__id',
                        'main.isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN        => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_log_port_list_2_isys_obj',
                            'LEFT',
                            'isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_log_port_list',
                            'LEFT',
                            'isys_catg_log_port_list__id',
                            'isys_catg_log_port_list__id',
                            'main',
                            'lp',
                            'lp'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_log_port_list__isys_obj__id',
                            'isys_obj__id',
                            'lp'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__LAYER2_NET_ASSIGNED_PORTS__ISYS_CATG_PORT_LIST__ID',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__MULTISELECTION   => true,
                        isys_popup_browser_object_ng::C__FORM_SUBMIT      => true,
                        isys_popup_browser_object_ng::C__CAT_FILTER       => 'C__CATG__NETWORK',
                        isys_popup_browser_object_ng::C__RETURN_ELEMENT   => C__POST__POPUP_RECEIVER,
                        isys_popup_browser_object_ng::C__SECOND_SELECTION => true,
                        isys_popup_browser_object_ng::C__SECOND_LIST      => [
                            'isys_cmdb_dao_category_s_layer2_net_assigned_logical_ports::object_browser',
                            [C__CMDB__GET__OBJECT => (isset($_GET[C__CMDB__GET__OBJECT]) ? $_GET[C__CMDB__GET__OBJECT] : 0)]
                        ],
                        isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT => 'isys_cmdb_dao_category_s_layer2_net_assigned_logical_ports::format_selection'
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'logical_port'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => false
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $value = $p_category_data['properties']['isys_catg_log_port_list__id']['ref_id'];

                if (is_array($value)) {
                    $unassignedEntries = $this->filterUnassignedEntries($p_object_id, $value);

                    if (empty($unassignedEntries)) {
                        return true;
                    }

                    $lastId = true;
                    foreach ($unassignedEntries as $id) {
                        $lastId = $this->create($p_object_id, $id);
                    }
                    return $lastId;
                }

                $l_id = $this->create($p_object_id, $value);
                if ($l_id > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A method, which bundles the handle_ajax_request and handle_preselection.
     *
     * @param  integer $p_context
     * @param  array   $p_parameters
     *
     * @return string|array
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function object_browser($p_context, array $p_parameters)
    {
        global $g_comp_database;

        $language = isys_application::instance()->container->get('language');

        switch ($p_context) {
            case isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST:
                $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                $l_dao_port = new isys_cmdb_dao_category_g_network_ifacel($g_comp_database);
                $l_res_port = $l_dao_port->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);

                if ($l_res_port->num_rows() > 0) {
                    while ($l_row_port = $l_res_port->get_row()) {
                        $l_return[] = [
                            '__checkbox__'                              => $l_row_port["isys_catg_log_port_list__id"],
                            $language->get('LC__CATD__PORT')            => $l_row_port["isys_catg_log_port_list__title"],
                            $language->get('LC__CMDB__CATG__PORT__MAC') => $l_row_port["isys_catg_log_port_list__mac"]
                        ];
                    }
                }

                return isys_format_json::encode($l_return);

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION:
                $l_return = [
                    'category' => [],
                    'first'    => [],
                    'second'   => []
                ];

                if (isset($p_parameters['preselection'])) {
                    $preselection = isys_format_json::is_json_array($p_parameters['preselection'])? isys_format_json::decode($p_parameters['preselection']): $p_parameters['preselection'];

                    $dao = isys_cmdb_dao_category_g_network_ifacel::instance(isys_application::instance()->container->get('database'));
                    $l_res = $dao->get_data(null, null, 'AND isys_catg_log_port_list.isys_catg_log_port_list__id IN (' . (is_array($preselection) ? implode(',', $preselection): $preselection) . ')');
                } else {
                    $l_obj = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $p_parameters[C__CMDB__GET__OBJECT];

                    // Create this class, because we can't just use "this" or we'll get an exception "Database component not loaded!".
                    $l_dao = new isys_cmdb_dao_category_s_layer2_net_assigned_logical_ports($g_comp_database);
                    $l_res = $l_dao->get_data(null, $l_obj, '', null, C__RECORD_STATUS__NORMAL);
                }

                while ($l_row = $l_res->get_row()) {
                    // @see  ID-6220  Also return a 'first' selection.
                    $l_return['first'][] = (int)$l_row['isys_obj__id'];

                    $l_return['second'][] = [
                        $l_row['isys_catg_log_port_list__id'],
                        $l_row['isys_catg_log_port_list__title'],
                        $l_row['isys_catg_log_port_list__mac'],
                    ];
                }

                return $l_return;

            case isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION:
                // @see  ID-5688  New callback case.
                $preselection = [];

                if (is_array($p_parameters['dataIds']) && count($p_parameters['dataIds'])) {
                    foreach ($p_parameters['dataIds'] as $dataId) {
                        $categoryRow = isys_cmdb_dao_category_g_network_ifacel::instance($this->m_db)->get_data($dataId)->get_row();

                        $preselection[] = [
                            $categoryRow['isys_catg_log_port_list__id'],
                            $categoryRow['isys_obj__title'],
                            $language->get($categoryRow['isys_obj_type__title']),
                            $categoryRow['isys_catg_log_port_list__title'],
                            $categoryRow['isys_catg_log_port_list__mac']
                        ];
                    }
                }

                return [
                    'header' => [
                        '__checkbox__',
                        $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                        $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                        $language->get('LC__CATD__PORT'),
                        $language->get('LC__CMDB__CATG__PORT__MAC')
                    ],
                    'data' => $preselection
                ];
        }
    }

    /**
     * Format selection for the object browser.
     *
     * @param   int $portId
     *
     * @return  string
     */
    public function format_selection($portId = null, $unused = null)
    {
        $dao = new isys_cmdb_dao_category_g_network_ifacel($this->m_db);
        $objPlugin = new isys_smarty_plugin_f_text();

        if (!$portId) {
            return null;
        }

        $data = $dao->get_data($portId)->get_row();
        return (!empty($data)) ? $data['isys_obj__title'] . " >> " . $data['isys_catg_log_port_list__title']: null;
    }

    /**
     * @param int|int[] $entryIds
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws isys_exception_database
     */
    public function getAttachedEntries($entryIds, $tag = '', $asId = false): CollectionInterface
    {
        $return = new EntryCollection();

        if (is_numeric($entryIds)) {
            $entryIds = [$entryIds];
        }

        if (empty($entryIds)) {
            return $return;
        }

        $query = 'SELECT logport.isys_catg_log_port_list__id as id, obj.isys_obj__id as objId, isys_catg_log_port_list__title as title, logport.*
            FROM isys_catg_log_port_list_2_isys_obj as main
			INNER JOIN isys_catg_log_port_list as logport ON logport.isys_catg_log_port_list__id = main.isys_catg_log_port_list__id
            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = logport.isys_catg_log_port_list__isys_obj__id
			WHERE main.isys_obj__id IN (%s)';

        if ($tag === 'isys_catg_log_port_list__id') {
            $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
            $query = "SELECT
                isys_catg_log_port_list__id as id,
                CONCAT(isys_obj__title, {$this->convert_sql_text($separator)}, isys_catg_log_port_list__title) as title,
                isys_catg_log_port_list__isys_obj__id as objId,
                isys_catg_log_port_list.*
              FROM isys_catg_log_port_list
              INNER JOIN isys_obj ON isys_obj__id = isys_catg_log_port_list__isys_obj__id
              WHERE isys_catg_log_port_list__id IN (%s)";
        }

        $result = $this->retrieve(sprintf($query, implode(',', array_map(function ($entryId) {
            return $this->convert_sql_id($entryId);
        }, $entryIds))));

        while ($row = $result->get_row()) {
            $return->addEntry(Entry::factory($row['id'], $row['objId'], $row['title'], $row));
        }

        return $return;
    }
}
