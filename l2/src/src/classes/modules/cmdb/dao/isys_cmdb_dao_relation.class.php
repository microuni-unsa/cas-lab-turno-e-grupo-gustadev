<?php

/**
 * @package   i-doit
 * @version   1.0
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_relation extends isys_cmdb_dao_category_g_relation
{
    const C__DEAD_RELATION_OBJECTS          = 'objects';
    const C__DEAD_RELATION_CATEGORY_ENTRIES = 'cat_entries';

    private array $m_regenerated_tables = [];

    private array $relationTypes = [];

    /**
     * Method which deletes all dead relation objects and entries
     *
     * @return array
     * @throws Exception
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function delete_dead_relations()
    {
        $l_dead_rel_objects = $l_delete_relation = $l_delete_dead_relations = [];

        $l_check_sql = 'SELECT
                isys_catg_relation_list__id,
                isys_catg_relation_list__isys_obj__id,
                slave.isys_obj__id AS slaveID,
                master.isys_obj__id AS masterID
			FROM isys_catg_relation_list
        	INNER JOIN isys_relation_type ON isys_relation_type__id = isys_catg_relation_list__isys_relation_type__id
        	LEFT JOIN isys_obj AS master ON master.isys_obj__id = isys_catg_relation_list__isys_obj__id__master
        	LEFT JOIN isys_obj AS slave ON slave.isys_obj__id = isys_catg_relation_list__isys_obj__id__slave
          	WHERE isys_relation_type__type != ' . C__RELATION__EXPLICIT .
            (defined('C__RELATION_TYPE__DATABASE_INSTANCE')
                ? (' AND isys_relation_type__id != ' . constant('C__RELATION_TYPE__DATABASE_INSTANCE'))
                : '') . ';';

        $l_res = $this->retrieve($l_check_sql);
        // Collect all relations from table "isys_catg_relation_list" which has no slave or master
        while ($l_row = $l_res->get_row()) {
            if (empty($l_row['masterID']) || empty($l_row['slaveID'])) {
                $l_delete_dead_relations[$l_row['isys_catg_relation_list__id']] = $l_row['isys_catg_relation_list__isys_obj__id'];
            }
        }

        $l_check_sql = 'SELECT isys_obj__id
			FROM isys_obj
			LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_obj__id
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__RELATION')) . '
			AND isys_catg_relation_list__id IS NULL';

        $l_res = $this->retrieve($l_check_sql);
        // Collect all relation objects which have no entry in table "isys_catg_relation_list"
        while ($l_row = $l_res->get_row()) {
            $l_dead_rel_objects[] = $l_row['isys_obj__id'];
        }

        $l_amount_dead_relations = count($l_delete_dead_relations);
        // Delete relation object. If it fails add the relation id to the array which deletes only the relation entry
        if ($l_amount_dead_relations) {
            foreach ($l_delete_dead_relations as $l_rel_id => $l_rel_obj_id) {
                $this->delete_object_and_relations($l_rel_obj_id);

                if ($this->affected_after_update() == 0) {
                    // If the object does not exist (for whatever reason) delete the relation entry instead.
                    if (is_numeric($l_rel_id)) {
                        $l_delete_relation[] = $l_rel_id;
                        $l_amount_dead_relations--;
                    }
                }
            }
        }

        // Delete relation objects which have no entry in isys_catg_relation_list
        if (count($l_dead_rel_objects)) {
            foreach ($l_dead_rel_objects as $l_dead_object) {
                $this->delete_object_and_relations($l_dead_object);
            }
            $l_amount_dead_relations += count($l_dead_rel_objects);
        }

        // Delete relation entries which have no relation object
        $l_relations_with_no_object = count($l_delete_relation);
        if ($l_relations_with_no_object) {
            $l_delete = 'DELETE FROM isys_catg_relation_list WHERE isys_catg_relation_list__id IN (' . implode(',', $l_delete_relation) . ')';
            $this->update($l_delete);
        }

        $this->apply_update();

        return [
            self::C__DEAD_RELATION_OBJECTS          => $l_amount_dead_relations,
            self::C__DEAD_RELATION_CATEGORY_ENTRIES => $l_relations_with_no_object,
        ];
    }

    /**
     * Regenerate relations
     *
     * @param array $selectedCategories
     *
     * @throws Exception
     * @throws isys_exception_database
     */
    public function regenerate_relations(array $selectedCategories = [])
    {
        $l_affected_categories = [];
        $useSelectedCategories = count($selectedCategories) > 0;

        $l_all_catg = $this->get_all_catg();
        while ($l_row = $l_all_catg->get_row()) {
            if ($useSelectedCategories) {
                if (isset($selectedCategories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']])) {
                    $l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']] = $l_row;
                }
            } else {
                $l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_row['isysgui_catg__id']] = $l_row;
            }
        }

        $l_all_cats = $this->get_all_cats();
        while ($l_row = $l_all_cats->get_row()) {
            if ($useSelectedCategories) {
                if (isset($selectedCategories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']])) {
                    $l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']] = $l_row;
                }
            } else {
                $l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_row['isysgui_cats__id']] = $l_row;
            }
        }

        $this->mapRelationTypes();

        try {
            // Global
            if (isset($l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL])) {
                $sql = 'SELECT
                        isysgui_catg__id,
                        isysgui_catg__class_name AS class_name,
                        CASE WHEN LOCATE(\'_list\', isysgui_catg__source_table) = 0 OR LOCATE(\'_listener\', isysgui_catg__source_table) > 0
                            THEN CONCAT(isysgui_catg__source_table, \'_list\')
                            ELSE isysgui_catg__source_table
                        END AS source_table
					FROM isysgui_catg
					WHERE isysgui_catg__id IN (' . implode(',', array_keys($l_affected_categories[C__CMDB__CATEGORY__TYPE_GLOBAL])) . ');';

                $globalResult = $this->retrieve($sql);
                while ($globalCatRow = $globalResult->get_row()) {
                    if (is_string($globalCatRow['source_table']) && $this->has_relation_field($globalCatRow['source_table'])) {
                        $this->regenerate_category_relation($globalCatRow['class_name'], $globalCatRow['source_table']);
                    }
                }
            }

            // Specific
            if (isset($l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])) {
                $sql = 'SELECT
                    isysgui_cats__id,
                    isysgui_cats__class_name AS class_name,
                    isysgui_cats__source_table AS source_table
                    FROM isysgui_cats
                    WHERE isysgui_cats__id IN (' . implode(',', array_keys($l_affected_categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])) . ');';

                $specificCatResult = $this->retrieve($sql);
                while ($specificCatRow = $specificCatResult->get_row()) {
                    if (is_string($specificCatRow['source_table']) && $this->has_relation_field($specificCatRow['source_table'])) {
                        $this->regenerate_category_relation($specificCatRow['class_name'], $specificCatRow['source_table']);
                    }
                }
            }
        } catch (Exception $e) {
            isys_notify::error('An error occurred while regenerating relations with error message: ' . $e->getMessage());
            throw new isys_exception_general($e->getMessage());
        }
    }

    /**
     * @var null|isys_array
     */
    private $m_object_id_relation_master_map = null;

    /**
     * Creates object master relation map
     *
     * @throws isys_exception_database
     */
    private function create_object_id_relation_master_map()
    {
        $l_sql = "SELECT isys_obj__id
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj_type__relation_master > 0
            AND isys_obj_type__const NOT IN ('C__OBJTYPE__RELATION', 'C__OBJTYPE__PARALLEL_RELATION');";

        $l_res = $this->retrieve($l_sql);

        $this->m_object_id_relation_master_map = [];

        while ($l_row = $l_res->get_row()) {
            $this->m_object_id_relation_master_map[$l_row['isys_obj__id']] = true;
        }
    }

    /**
     * Helper method which rebuilds the empty relations
     *
     * @param string $daoClassName
     * @param string $sourceTable
     * @return void
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function regenerate_category_relation(string $daoClassName, string $sourceTable): void
    {
        $blockedTables = ['isys_catg_virtual_list', 'isys_catg_virtual'];

        if (in_array($sourceTable, $blockedTables, true)) {
            return;
        }

        if (in_array($sourceTable, $this->m_regenerated_tables, true)) {
            return;
        }

        if (!class_exists($daoClassName)) {
            return;
        }

        if (!is_a($daoClassName, isys_cmdb_dao_category::class, true)) {
            return;
        }

        $l_dao = $daoClassName::instance(isys_application::instance()->container->get('database'));

        if (!$l_dao->has_relation()) {
            return;
        }

        $l_relation_field = $sourceTable . '__isys_catg_relation_list__id';
        $l_relation_handler = $l_relation_type = null;
        $l_dao->unset_properties();
        $l_properties = $l_dao->get_properties();

        $l_object_field = $l_dao->get_object_id_field();
        $l_connected_object_field = $l_dao->get_connected_object_id_field();
        $l_data_field = '';

        foreach ($l_properties as $l_property_info) {
            if (isset($l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE])) {
                $l_relation_type = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE];
                $l_relation_handler = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER];
                $l_data_field = $l_property_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                break;
            }
        }

        if ($l_connected_object_field === null || $l_relation_type === null || $l_relation_handler === null) {
            return;
        }

        if ($l_relation_type instanceof isys_callback) {
            // Callback method will be executed callback_property_relation_type_handler.
            $l_relation_type_id = $l_relation_type->execute();
        } else {
            $l_relation_type_id = $l_relation_type;
        }

        if (str_contains($l_object_field, 'isys_connection')) {
            $l_connected_object_field = $l_object_field;
        }

        $l_sql = 'SELECT ' . $sourceTable . '__id, isys_catg_relation_list__id
            FROM ' . $sourceTable . '
            LEFT JOIN isys_catg_relation_list ON isys_catg_relation_list__id = ' . $l_relation_field . ' ';

        if (str_contains($l_connected_object_field, 'isys_connection')) {
            $l_connection_field = $sourceTable . '__isys_connection__id';

            if (!str_contains($l_data_field, 'isys_connection')) {
                $l_connection_field = $l_data_field;
            }

            $l_sql .= 'INNER JOIN isys_connection ON isys_connection__id = ' . $l_connection_field . ' AND isys_connection__isys_obj__id IS NOT NULL ';
        } elseif (str_contains($l_connected_object_field, '_list') && !str_contains($l_connected_object_field, $sourceTable)) {
            $l_join_table = substr($l_connected_object_field, 0, strpos($l_connected_object_field, '__'));
            $l_join_field = $l_join_table . '__id';
            $l_connected_join_field = $sourceTable . '__' . $l_join_field;

            $l_sql .= 'LEFT JOIN ' . $l_join_table . ' ON ' . $l_join_field . ' = ' . $l_connected_join_field . ' ';
        }

        $l_sql .= 'WHERE ' . $l_connected_object_field . ' IS NOT NULL;';
        $l_res = $l_dao->retrieve($l_sql);

        if ($this->m_object_id_relation_master_map === null) {
            $this->create_object_id_relation_master_map();
        }

        while ($l_data = $l_res->get_row()) {
            $l_data_id = (int)$l_data[$sourceTable . '__id'];

            if (!is_object($l_relation_handler)) {
                continue;
            }

            $l_request = isys_request::factory()
                ->set_category_data_id($l_data_id);
            if (isset($l_data[$sourceTable . '__isys_obj__id'])) {
                $l_request->set_object_id($l_data[$sourceTable . '__isys_obj__id']);
            }

            // Callback method will be executed callback_property_relation_handler.
            $l_relation_data = $l_relation_handler->execute($l_request);

            if (!$this->check_related_objects($l_relation_data)) {
                continue;
            }

            // Determine if object master has to be switched or not
            if ($this->checkRelationMaster(
                $l_relation_data[C__RELATION_OBJECT__MASTER],
                $l_relation_data[C__RELATION_OBJECT__SLAVE],
                $l_relation_type_id
            )) {
                $l_cache_obj_id = $l_relation_data[C__RELATION_OBJECT__SLAVE];
                $l_relation_data[C__RELATION_OBJECT__SLAVE] = $l_relation_data[C__RELATION_OBJECT__MASTER];
                $l_relation_data[C__RELATION_OBJECT__MASTER] = $l_cache_obj_id;
            }

            $this->handle_relation(
                $l_data_id,
                $sourceTable,
                $l_relation_type_id,
                $l_data['isys_catg_relation_list__id'],
                $l_relation_data[C__RELATION_OBJECT__MASTER],
                $l_relation_data[C__RELATION_OBJECT__SLAVE]
            );

            if ($daoClassName === 'isys_cmdb_dao_category_g_it_service_components') {
                if ($l_data['isys_catg_relation_list__id'] > 0) {
                    $l_rel_id = (int)$l_data['isys_catg_relation_list__id'];
                } else {
                    $sql = "SELECT {$sourceTable}__isys_catg_relation_list__id
                        FROM {$sourceTable}
                        WHERE {$sourceTable}__id = {$l_data_id}
                        LIMIT 1;";

                    $l_rel_id = (int)$this
                        ->retrieve($sql)
                        ->get_row_value("{$sourceTable}__isys_catg_relation_list__id");
                }

                $this->set_it_service($l_rel_id, $l_relation_data[C__RELATION_OBJECT__SLAVE]);
            }
        }
        $this->m_regenerated_tables[$sourceTable] = true;
    }

    /**
     * @param int $objectId
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function isObjectRelationMaster(int $objectId): bool
    {
        $query = "SELECT isys_obj_type__relation_master AS master
            FROM isys_obj_type
            INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__id = {$objectId}
            LIMIT 1;";

        return (bool) $this->retrieve($query)->get_row_value('master');
    }

    /**
     * @param int $objectTypeId
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function isObjectTypeRelationMaster(int $objectTypeId): bool
    {
        $query = "SELECT isys_obj_type__relation_master AS master
            FROM isys_obj_type
            WHERE isys_obj_type__id = {$objectTypeId}
            LIMIT 1;";

        return (bool) $this
            ->retrieve($query)
            ->get_row_value('master');
    }

    /**
     * Check if slave object is defined as master object and the master object is not as master defined
     *
     * @param $masterObject
     * @param $slaveObject
     * @param $relationTypeId
     *
     * @return bool
     */
    private function checkRelationMaster($masterObject, $slaveObject, $relationTypeId)
    {
        return (!isset($this->m_object_id_relation_master_map[$masterObject]) && $this->m_object_id_relation_master_map[$slaveObject] &&
            $this->relationTypes[$relationTypeId]['isys_relation_type__const'] === null &&
            $this->relationTypes[$relationTypeId]['isys_relation_type__type'] !== C__RELATION__EXPLICIT);
    }

    /**
     * Helper method which checks the existence of the master and slave objects.
     *
     * @param array $objects
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function check_related_objects(array $objects): bool
    {
        $slaveObject = $this->convert_sql_id($objects[C__RELATION_OBJECT__SLAVE]);
        $masterObject = $this->convert_sql_id($objects[C__RELATION_OBJECT__MASTER]);

        $sql = "SELECT (SELECT 1 FROM isys_obj WHERE isys_obj__id = {$slaveObject}) +
            (SELECT 1 FROM isys_obj WHERE isys_obj__id = {$masterObject}) AS existing;";

        return (bool)$this->retrieve($sql)->get_row_value('existing');
    }

    /**
     * Get all default relation types which have a constant as array.
     *
     * @throws isys_exception_database
     */
    private function mapRelationTypes(): void
    {
        $this->relationTypes = [];
        $result = $this->retrieve('SELECT * FROM isys_relation_type;');

        while ($row = $result->get_row()) {
            $this->relationTypes[$row['isys_relation_type__id']] = $row;
        }
    }
}
