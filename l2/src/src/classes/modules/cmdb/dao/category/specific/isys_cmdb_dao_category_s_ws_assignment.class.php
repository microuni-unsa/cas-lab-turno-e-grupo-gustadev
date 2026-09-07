<?php

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;

/**
 * i-doit
 *
 * DAO: specific category for ws assignments.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_ws_assignment extends isys_cmdb_dao_category_specific implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'ws_assignment';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATS__MAINTENANCE_LINKED_OBJECT_LIST';

    /**
     * Category's constant.
     *
     * @var    string
     * @fixme  No standard behavior!
     */
    protected $m_category_const = 'C__CATS__WS_ASSIGNMENT';

    /**
     * Category's identifier.
     *
     * @var    integer
     * @fixme  No standard behavior!
     * This is removed, because it is done automatically in constructor of dao_category
     */
    //     protected $m_category_id = C__CATS__WS_ASSIGNMENT;

    /**
     * @var string
     */
    protected $m_entry_identifier = 'connected_object';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
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
    protected $m_object_browser_property = 'connected_object';

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_cats_ws_net_type_list__isys_obj__id';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_obj__id';

    /**
     * Category's table.
     *
     * @var string
     */
    protected $m_table = 'isys_cats_ws_net_type_list_2_isys_obj';

    public function get_assigned_objects($p_obj_id, $p_as_string = false)
    {
        $l_catdata = $this->get_data(null, $p_obj_id, "", null, C__RECORD_STATUS__NORMAL)
            ->__to_array();

        $l_query = "SELECT * FROM " . "isys_cats_ws_net_type_list_2_isys_obj " .
            "INNER JOIN isys_obj ON isys_cats_ws_net_type_list_2_isys_obj.isys_obj__id = isys_obj.isys_obj__id " .
            "INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id " . "WHERE isys_cats_ws_net_type_list__id = " .
            $this->convert_sql_id($l_catdata["isys_cats_ws_net_type_list__id"]);

        return $this->retrieve($l_query);
    }

    /**
     * @param         $p_cat_layer
     * @param integer &$p_status
     *
     * @author  Dennis Stücken 2006-04-25 <dstuecken@synetics.de>
     * @version Niclas Potthast <npotthast@i-doit.org> - 2007-08-22
     */
    public function save_element($p_cat_level, &$p_status)
    {
        return null;
    }

    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID)
    {
        return null;
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objets
     *
     * @return bool|null
     * @throws isys_exception_dao
     */
    public function attachObjects($p_object_id, array $p_objets)
    {
        $l_members_dao = $this->get_assigned_objects($p_object_id);
        $l_id = null;
        $l_members = [];

        while ($l_row = $l_members_dao->get_row()) {
            $l_members[$l_row["isys_obj__id"]] = $l_row['isys_cats_ws_net_type_list_2_isys_obj__id'];
        }

        foreach ($p_objets as $l_object_id) {
            if (is_numeric($l_object_id)) {
                if (!isset($l_members[$l_object_id])) {
                    $l_id = $this->create($p_object_id, $l_object_id);
                } else {
                    unset($l_members[$l_object_id]);
                }
            }
        }

        if (count($l_members)) {
            // Delete members which are not assigned anymore
            $l_delete = 'DELETE FROM isys_cats_ws_net_type_list_2_isys_obj
				WHERE isys_cats_ws_net_type_list_2_isys_obj__id IN (' . implode(',', $l_members) . ');';

            return $this->update($l_delete) && $this->apply_update();
        }

        return $l_id;
    }

    public function create($p_obj_id, $p_assigned_obj_id)
    {
        $l_dao = new isys_cmdb_dao_category_s_ws_net_type($this->get_database_component());
        $l_catdata = $l_dao->get_data(null, $p_obj_id)
            ->__to_array();

        if (empty($l_catdata["isys_cats_ws_net_type_list__id"])) {
            $l_catdata["isys_cats_ws_net_type_list__id"] = $l_dao->create($p_obj_id, 0, '');
        }

        $l_insert = "INSERT INTO isys_cats_ws_net_type_list_2_isys_obj " . "SET isys_cats_ws_net_type_list__id = " .
            $this->convert_sql_id($l_catdata["isys_cats_ws_net_type_list__id"]) . ", " . "isys_obj__id = " . $this->convert_sql_id($p_assigned_obj_id) . ';';

        $this->m_strLogbookSQL .= $l_insert;

        return $this->update($l_insert) && $this->apply_update();
    }

    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null && $p_obj_id > 0) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_ws_net_type_list_2_isys_obj__id) AS count FROM isys_cats_ws_net_type_list_2_isys_obj
				INNER JOIN isys_cats_ws_net_type_list ON isys_cats_ws_net_type_list_2_isys_obj.isys_cats_ws_net_type_list__id = isys_cats_ws_net_type_list.isys_cats_ws_net_type_list__id
				WHERE (isys_cats_ws_net_type_list__status = '" . C__RECORD_STATUS__NORMAL . "')
				AND isys_cats_ws_net_type_list__isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ";";

        $l_amount = $this->retrieve($l_sql)
            ->get_row();

        return (int)$l_amount["count"];
    }

    /**
     * Return Category Data
     *
     * @param   integer $p_cats_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_ws_net_type_list_2_isys_obj tb1 " .
            "INNER JOIN isys_cats_ws_net_type_list tb2 ON tb2.isys_cats_ws_net_type_list__id = tb1.isys_cats_ws_net_type_list__id " .
            "INNER JOIN isys_obj tb3 ON tb3.isys_obj__id = tb1.isys_obj__id " . "WHERE TRUE " . $p_condition;

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (tb2.isys_cats_ws_net_type_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (tb3.isys_obj__status = '{$p_status}')";
        }

        return $this->retrieve($l_sql . ";");
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
                $l_sql = ' AND (tb2.isys_cats_ws_net_type_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (tb2.isys_cats_ws_net_type_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $value = $p_category_data['properties']['connected_object'][C__DATA__VALUE];
                if (!is_array($value)) {
                    $value = [$value];
                }

                $unassignedObjects = $this->filterUnassignedEntries($p_object_id, $value);

                if (empty($unassignedObjects)) {
                    return true;
                }

                $lastId = true;
                foreach ($unassignedObjects as $object) {
                    $lastId = $this->create($p_object_id, $object);
                }
                return $lastId;
            }
        }

        return false;
    }

    /**
     * Method for returning the properties.
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function properties()
    {
        return [
            'connected_object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ASSIGNED_OBJECTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(obj.isys_obj__title, \' {\', obj.isys_obj__id, \'}\')
                            FROM isys_cats_ws_net_type_list AS main
                            INNER JOIN isys_cats_ws_net_type_list_2_isys_obj AS ws2obj ON ws2obj.isys_cats_ws_net_type_list__id = main.isys_cats_ws_net_type_list__id
                            INNER JOIN isys_obj AS obj ON obj.isys_obj__id = ws2obj.isys_obj__id',
                        'isys_cats_ws_net_type_list',
                        'main.isys_cats_ws_net_type_list__id',
                        'main.isys_cats_ws_net_type_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['main.isys_cats_ws_net_type_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_ws_net_type_list',
                            'LEFT',
                            'isys_cats_ws_net_type_list__isys_obj__id',
                            'isys_obj__id',
                            'main',
                            '',
                            'main'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_ws_net_type_list_2_isys_obj',
                            'LEFT',
                            'isys_cats_ws_net_type_list__id',
                            'isys_cats_ws_net_type_list_2_isys_obj__id',
                            'main',
                            'ws2obj',
                            'ws2obj'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_obj__id', 'isys_obj__id', 'ws2obj', 'obj', 'obj')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATG__ITSERVICE__CONNECTED_OBJECT',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter' => "C__CATG__CABLE;C__CATG__CABLE_CONNECTION",
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        isys_popup_browser_object_ng::C__DATARETRIEVAL => new isys_callback([
                            'isys_cmdb_dao_category_s_ws_assignment',
                            'getEntriesByRequestObject'
                        ])
                    ]
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => true
                ]
            ])
        ];
    }

    /**
     * @param int|int[] $ids
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws isys_exception_database
     */
    public function getAttachedEntries($ids, $tag = '', $asId = false): CollectionInterface
    {
        $collection = new ObjectCollection();

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return $collection;
        }

        $query = 'SELECT
            isys_cats_ws_net_type_list_2_isys_obj__id as id,
            obj.isys_obj__id as objId,
            obj.isys_obj__title as title,
            obj.isys_obj__sysid as sysid,
            isys_obj_type__title as objType
            FROM isys_cats_ws_net_type_list_2_isys_obj as main
                INNER JOIN isys_cats_ws_net_type_list as con ON main.isys_cats_ws_net_type_list__id = con.isys_cats_ws_net_type_list__id
                INNER JOIN isys_obj obj ON obj.isys_obj__id = main.isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = obj.isys_obj__isys_obj_type__id
            WHERE con.isys_cats_ws_net_type_list__isys_obj__id IN (' . implode(',', array_map(function ($id) {
            return $this->convert_sql_id($id);
        }, $ids)). ')';

        $result = $this->retrieve($query);

        while ($row = $result->get_row()) {
            $collection->addEntry(ObjectEntry::factory(
                $row['objId'],
                $row['title'],
                $row['sysid'],
                isys_application::instance()->container->get('language')->get($row['objType']),
                $row
            ));
        }

        return $collection;
    }
}
