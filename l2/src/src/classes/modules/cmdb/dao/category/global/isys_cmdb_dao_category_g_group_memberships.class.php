<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for group memberships.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_group_memberships extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'group_memberships';
        $this->m_multivalued = true;
        $this->m_table = 'isys_cats_group_list';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__GROUP_MEMBERSHIPS';
        $this->m_connected_object_id_field = 'isys_cats_group_list__isys_obj__id';
        $this->m_entry_identifier = 'connected_object';
        $this->m_has_relation = true;
        $this->m_object_browser_category = true;
        $this->m_object_browser_property = 'connected_object'; // @see ID-11600
        $this->m_object_id_field = 'isys_connection__isys_obj__id';
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_backup__id $p_fk_id
     *
     * @param int    $p_objID
     * @param int    $p_newRecStatus
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return int the newly created ID or false
     */
    public function create($p_objID, $p_newRecStatus = C__RECORD_STATUS__NORMAL, $p_connectedObjID = null, $p_description = null)
    {
        // Preparation
        $selection = $p_connectedObjID;

        // Check whether data was delivered as json
        if (isys_format_json::is_json_array($selection)) {
            $selection = isys_format_json::decode($selection);
        }

        // Cast selection to array
        $selection = (array)$selection;

        // 1. Get all groups that already assigned to object
        $resource = $this->getAssignedGroups($p_objID);
        $assignedGroups = [];

        if ($resource->num_rows() > 0) {
            // Get assignment
            while ($assignment = $resource->get_row()) {
                // Store group id
                $assignedGroups[] = $assignment['groupId'];
            }
        }

        // 2. Calculate unassigned groups
        $unassignedGroups = array_diff($selection, $assignedGroups);

        // 3. Create assignments if there are any left

        // Check whether there are unassigned groups left
        if (!empty($unassignedGroups)) {
            $connectionDao = new isys_cmdb_dao_connection($this->get_database_component());
            $relationDao = new isys_cmdb_dao_category_g_relation($this->get_database_component());

            foreach ($unassignedGroups as $groupId) {
                // Prepare insert statement
                $sql = 'INSERT INTO isys_cats_group_list SET
                    isys_cats_group_list__isys_connection__id = ' . $this->convert_sql_id($connectionDao->add_connection($p_objID)) . ',
                    isys_cats_group_list__description = ' . $this->convert_sql_text($p_description) . ',
                    isys_cats_group_list__status = ' . $this->convert_sql_id($p_newRecStatus) . ',
                    isys_cats_group_list__isys_obj__id = ' . $this->convert_sql_id($groupId) . ';';

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    // Create relation object
                    $relationDao->handle_relation(
                        $this->get_last_insert_id(),
                        "isys_cats_group_list",
                        defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                        null,
                        $groupId, // @see ID-9956 Group as master.
                        $p_objID // @see ID-9956 Assigned object as slave.
                    );
                }
            }
        }

        return true;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level
     *
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param int    $p_connectedObjID
     * @param String $p_description
     *
     * @return boolean true, if transaction executed successfully, else false
     */
    public function save($p_cat_level, $p_newRecStatus, $p_connectedObjID = null, $p_description = null)
    {
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());

        $l_strSql = 'UPDATE isys_cats_group_list SET
            isys_cats_group_list__isys_obj__id = ' . $this->convert_sql_id($p_connectedObjID) . ',
            isys_cats_group_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_group_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
            WHERE isys_cats_group_list__id = ' . $this->convert_sql_id($p_cat_level) . ';';

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_data = $this->get_data($p_cat_level)->get_row();

            $l_dao_relation->handle_relation(
                $p_cat_level,
                "isys_cats_group_list",
                defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                $l_data["isys_cats_group_list__isys_catg_relation_list__id"],
                $p_connectedObjID, // @see ID-9956 Group as master.
                $l_data["isys_connection__isys_obj__id"] // @see ID-9956 Assigned object as slave.
            );

            return true;
        }

        return false;
    }

    /**
     * Get assigned groups
     *
     * @param $objectId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getAssignedGroups($objectId)
    {
        // Create sql for retrieving assigned groups
        $sql = 'SELECT gl.isys_cats_group_list__isys_obj__id AS groupId FROM isys_cats_group_list gl
            INNER JOIN isys_connection con ON con.isys_connection__id = gl.isys_cats_group_list__isys_connection__id
            WHERE con.isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

        // Create resource by querying assignments
        return $this->retrieve($sql);
    }

    /**
     * Set Status for category entry.
     *
     * @param int $entryId
     * @param int $status
     *
     * @return bool
     */
    public function set_status($entryId, $status)
    {
        $sql = 'UPDATE isys_cats_group_list
            SET isys_cats_group_list__status = ' . $this->convert_sql_id($status) . '
            WHERE isys_cats_group_list__id = ' . $this->convert_sql_id($entryId) . ';';

        return $this->update($sql) && $this->apply_update();
    }

    /**
     * Deletes connection between group and object
     *
     * @param int $p_cat_level
     * @param int $p_objtype_id
     *
     * @return bool
     */
    public function delete($p_cat_level, $p_objtype_id = null)
    {
        if ($p_objtype_id === null && defined('C__OBJECT_TYPE__GROUP')) {
            $p_objtype_id = C__OBJECT_TYPE__GROUP;
        }
        $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_catdata = $this->get_data($p_cat_level)
            ->__to_array();

        if ($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"] > 0) {
            $l_dao_relation->delete_relation($l_catdata["isys_cats_group_list__isys_catg_relation_list__id"]);
        } else {
            $l_sql = "DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = " . $this->convert_sql_id($p_cat_level);
            $this->update($l_sql);
        }

        if ($this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not delete id '{$p_cat_level}' in table isys_catg_application_list.");
        }
    }

    public function get_count($objectId = null)
    {
        if (empty($objectId)) {
            $objectId = $this->m_object_id;
        }

        $l_sql = "SELECT COUNT(isys_cats_group_list__id) AS cnt FROM isys_cats_group_list
            LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id
            LEFT JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id
            WHERE TRUE ";

        if (!empty($objectId)) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        $l_sql .= ' AND isys_cats_group_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        return (int) $this->retrieve($l_sql)->get_row_value('cnt');
    }

    /**
     * Return Category Data
     *
     * @param int|null       $entryId
     * @param int|array|null $objectId
     * @param string         $condition
     * @param mixed          $filter
     * @param int|null       $status
     *
     * @return isys_component_dao_result
     */
    public function get_data($entryId = null, $objectId = null, $condition = '', $filter = null, $status = null)
    {
        $condition .= $this->prepare_filter($filter);

        $sql = "SELECT * FROM isys_cats_group_list
            INNER JOIN isys_obj ON  isys_obj__id = isys_cats_group_list__isys_obj__id
            LEFT JOIN isys_connection ON isys_cats_group_list__isys_connection__id = isys_connection__id
            WHERE TRUE {$condition} ";

        if ($objectId !== null) {
            $sql .= $this->get_object_condition($objectId);
        }

        if ($entryId !== null) {
            $sql .= ' AND isys_cats_group_list__id = ' . $this->convert_sql_id($entryId);
        }

        if ($status !== null) {
            $sql .= ' AND isys_cats_group_list__status = ' . $this->convert_sql_id($status);
        }

        return $this->retrieve($sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param $p_obj_id
     * @param $p_alias
     *
     * @return string
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ' ';
            }

            return ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ' ';
        }

        return '';
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'connected_object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATG__GROUP__OBJECT',
                'LC__CMDB__CATG__GLOBAL_GROUP',
                'isys_cats_group_list__isys_obj__id',
                'isys_cats_group_list',
                '',
                []
            ))->mergePropertyInfo([
                Property::C__PROPERTY__INFO__BACKWARD_PROPERTY => 'isys_cmdb_dao_category_s_group::object'
            ])->setPropertyDataOffset(
                \idoit\Component\Property\Property::C__PROPERTY__DATA__INDEX,
                true
            )->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATS__GROUP;C__CATS__GROUP_TYPE',
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__DATARETRIEVAL => new isys_callback([
                    'isys_cmdb_dao_category_g_group_memberships',
                    'getEntriesByRequestObject'
                ])
            ])->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_group_memberships',
                    'callback_property_relation_handler'
                ], ['isys_cmdb_dao_category_g_group_memberships'])
            ),
            // deprecated has to be removed in the next version
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_group_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__GROUP_MEMBERSHIPS', 'C__CATG__GROUP_MEMBERSHIPS')
                ],
                // @See ID-5991 in comment from 16.10.2018 14:10
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH       => false,
                    C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
                    C__PROPERTY__PROVIDES__IMPORT       => false,
                    C__PROPERTY__PROVIDES__EXPORT       => false,
                    C__PROPERTY__PROVIDES__REPORT       => false,
                    C__PROPERTY__PROVIDES__LIST         => false,
                    C__PROPERTY__PROVIDES__VALIDATION   => false,
                    C__PROPERTY__PROVIDES__VIRTUAL      => false
                ],
            ])
        ];
    }

    /**
     * @param $p_category_data
     * @param $p_object_id
     * @param $p_status
     *
     * @return bool|int
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $groups = $p_category_data['properties']['connected_object'][C__DATA__VALUE];
            if (!is_array($groups)) {
                $groups = [$groups];
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE && $p_object_id > 0) {
                $lastId = true;
                foreach ($groups as $group) {
                    $lastId = $this->create(
                        $p_object_id,
                        C__RECORD_STATUS__NORMAL,
                        $group,
                        $p_category_data['properties']['description'][C__DATA__VALUE]
                    );
                }
                return $lastId;
            }

            if ($p_status === isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
                $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    current($groups),
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );

                return (int)$p_category_data['data_id'];
            }
        }

        return false;
    }

    /**
     * Attach $objects to $object_id
     *
     * @param int   $object_id
     * @param array $objects
     *
     * @return int last inserted id
     */
    public function attachObjects($object_id, array $objects)
    {
        // 1. Get all groups that already assigned to object
        $resource = $this->getAssignedGroups($object_id);
        $assignedGroups = [];

        if ($resource->num_rows() > 0) {
            // Get assignment
            while ($assignment = $resource->get_row()) {
                // Store group id
                $assignedGroups[] = $assignment['groupId'];
            }
        }

        // 2. Calculate unassigned groups
        $missingGroups = array_diff($objects, $assignedGroups);

        $connectionDao = new isys_cmdb_dao_connection($this->get_database_component());
        $relationDao = new isys_cmdb_dao_category_g_relation($this->get_database_component());
        $l_logbook_dao = new isys_component_dao_logbook($this->get_database_component());

        // Check whether there are unassigned groups left
        if (!empty($missingGroups)) {
            foreach ($missingGroups as $groupId) {
                // Prepare insert statement
                $sql = "INSERT INTO isys_cats_group_list SET
                    isys_cats_group_list__isys_connection__id = " . $this->convert_sql_id($connectionDao->add_connection($object_id)) . ",
                    isys_cats_group_list__status = " . $this->convert_sql_id(C__RECORD_STATUS__NORMAL) . ",
                    isys_cats_group_list__isys_obj__id = " . $this->convert_sql_id($groupId);

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    $entryId = (int)$this
                        ->retrieve('SELECT MAX(isys_cats_group_list__id) AS id FROM isys_cats_group_list;')
                        ->get_row_value('id');

                    // @see ID-9599 Remove the explicit logic to write logbook entries.

                    // Create relation object
                    $relationDao->handle_relation(
                        $entryId,
                        "isys_cats_group_list",
                        defined_or_default('C__RELATION_TYPE__GROUP_MEMBERSHIPS'),
                        null,
                        $groupId, // @see ID-9956 Group as master.
                        $object_id // @see ID-9956 Assigned object as slave.
                    );
                }
            }
        }

        $obsoleteGroups = array_diff($assignedGroups, $objects);

        if (!empty($obsoleteGroups)) {
            foreach ($obsoleteGroups as $groupId) {
                $entryData = $this->get_data(null, $object_id, ' AND isys_cats_group_list__isys_obj__id = ' . $this->convert_sql_id($groupId))->get_row();

                if (empty($entryData['isys_cats_group_list__id'])) {
                    continue;
                }

                $sql = 'DELETE FROM isys_cats_group_list WHERE isys_cats_group_list__id = ' . $this->convert_sql_id($entryData['isys_cats_group_list__id']);

                // Create category entry
                if ($this->update($sql) && $this->apply_update()) {
                    // @see ID-9599 Remove the explicit logic to write logbook entries.

                    $relationDao->delete_relation($entryData['isys_cats_group_list__isys_catg_relation_list__id']);
                }
            }

            return true;
        }
    }

    /**
     * @param int|int[] $id
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws isys_exception_database
     */
    public function getAttachedEntries($id, $tag = '', $asId = false): CollectionInterface
    {
        return isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'))
            ->getConnectedObjects(
                'isys_cats_group_list',
                $id,
                'isys_cats_group_list__isys_connection__id',
                'isys_cats_group_list__isys_obj__id',
                'isys_connection__isys_obj__id'
            );
    }
}
