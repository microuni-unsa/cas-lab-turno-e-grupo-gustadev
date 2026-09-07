<?php

use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * DAO: person assigned workstation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_person_assigned_workstation extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'person_assigned_workstation';
        $this->m_table = 'isys_catg_logical_unit_list';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION';
        $this->m_object_browser_property = 'assigned_workstations';
        $this->m_object_browser_category = true;
        $this->m_object_id_field = 'isys_catg_logical_unit_list__isys_obj__id';
    }

    /**
     * This method is used by the object-browser to retrieve the selected object.
     *
     * @param   integer $p_obj_id
     *
     * @return  isys_component_dao_result
     */
    public function get_selected_objects($p_obj_id)
    {
        return $this->get_data(null, $p_obj_id);
    }

    /**
     * Remove entry by parent object id and assigned object
     *
     * @param int $parentObjectId
     * @param int $assignedObjectId
     */
    public function deleteEntryByAssignedId($parentObjectId, $assignedObjectId)
    {
        $dao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);

        $data = $dao
            ->get_data(null, $assignedObjectId, ' AND isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($parentObjectId))
            ->get_row();

        if (!empty($data)) {
            isys_cmdb_dao_category_g_relation::instance($this->m_db)
                ->delete_relation($data['isys_catg_logical_unit_list__isys_catg_relation_list__id']);
            $objectTypeID = $this->get_objTypeID($data['isys_catg_logical_unit_list__isys_obj__id']);

            // See ID-4425
            // @see ID-9331 Remove outdated setting 'cmdb.logical-location.handle-location-inheritage'.

            $this->delete_entry($data['isys_catg_logical_unit_list__id'], 'isys_catg_logical_unit_list');
        }
    }

    /**
     * Checks if assignment already exists
     *
     * @param int $p_parent
     * @param int $p_assigned_workstation
     *
     * @return bool
     */
    public function assignment_exists($p_parent, $p_assigned_workstation)
    {
        $l_sql = 'SELECT isys_catg_logical_unit_list__id
            FROM isys_catg_logical_unit_list
            WHERE isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($p_parent) . '
            AND isys_catg_logical_unit_list__isys_obj__id = ' . $this->convert_sql_id($p_assigned_workstation) . '
            LIMIT 1;';

        return count($this->retrieve($l_sql)) > 0;
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param int $objectId
     *
     * @return int
     * @throws isys_exception_database
     */
    public function get_count($objectId = null)
    {
        if (empty($objectId)) {
            $objectId = $this->m_object_id;
        }

        $query = 'SELECT count(isys_catg_logical_unit_list__id) AS cnt
            FROM isys_catg_logical_unit_list
            WHERE TRUE
            AND isys_catg_logical_unit_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($objectId !== null) {
            $query .= ' AND isys_catg_logical_unit_list__isys_obj__id__parent = ' . $this->convert_sql_id($objectId);
        }

        return (int) $this->retrieve($query)->get_row_value('cnt');
    }

    /**
     * Get data method, uses logical unit DAO.
     *
     * @param int    $p_catg_list_id
     * @param int    $p_obj_id
     * @param string $p_condition
     * @param mixed  $p_filter
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return isys_cmdb_dao_category_g_logical_unit::instance($this->m_db)->get_data_by_parent($p_obj_id);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_workstations' => (new ObjectBrowserProperty(
                'C__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION',
                'LC__CMDB__CATG__PERSON_ASSIGNED_WORKSTATION',
                'isys_catg_logical_unit_list__isys_obj__id',
                'isys_catg_logical_unit_list',
                ['isys_export_helper', 'object'],
                'C__CATG__LOGICAL_UNIT',
                null,
                'isys_cmdb_dao_category_g_logical_unit::parent',
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT           => SelectSubSelect::factory(
                    'SELECT CONCAT(child.isys_obj__title, \' {\', child.isys_obj__id, \'}\')
                        FROM isys_catg_logical_unit_list
                        INNER JOIN isys_obj AS parent ON parent.isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent
                        INNER JOIN isys_obj AS child ON child.isys_obj__id = isys_catg_logical_unit_list__isys_obj__id',
                    'isys_catg_logical_unit_list',
                    'isys_catg_logical_unit_list__id',
                    'isys_catg_logical_unit_list__isys_obj__id__parent',
                    '',
                    '',
                    SelectCondition::factory([' AND parent.isys_obj__isys_obj_type__id IN
                        (SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                        INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                        WHERE isysgui_catg__const IN (\'C__CATG__ASSIGNED_LOGICAL_UNIT\', \'C__CATG__ASSIGNED_WORKSTATION\'))
                        AND child.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL)
                    ]),
                    SelectGroupBy::factory(['isys_catg_logical_unit_list__isys_obj__id__parent'])
                ),
                C__PROPERTY__DATA__JOIN             => [
                    SelectJoin::factory(
                        'isys_catg_logical_unit_list',
                        'LEFT',
                        'isys_catg_logical_unit_list__isys_obj__id__parent',
                        'isys_obj__id',
                        'main',
                        '',
                        'main'
                    ),
                    SelectJoin::factory(
                        'isys_obj',
                        'LEFT',
                        'isys_catg_logical_unit_list__isys_obj__id',
                        'isys_obj__id',
                        'main',
                        'child',
                        'child'
                    )
                ]
            ])->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__DATARETRIEVAL => new isys_callback([
                    'isys_cmdb_dao_category_g_person_assigned_workstation',
                    'getEntriesByRequestObject'
                ])
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__MULTIEDIT => false
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
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @throws  isys_exception_database
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $indicator = false;
        if (is_array($p_category_data) && isset($p_category_data[isys_import_handler_cmdb::C__PROPERTIES])) {
            $logicalUnitDao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);
            $workstations = $p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['assigned_workstations'][C__DATA__VALUE];
            $workstations = array_flip(is_array($workstations) ? $workstations : (is_numeric($workstations) ? [$workstations] : []));

            if ($p_status == isys_import_handler_cmdb::C__CREATE || $p_status == isys_import_handler_cmdb::C__UPDATE) {
                $result = $this->get_data(null, $p_object_id);
                $indicator = true;
                if ($result->num_rows() > 0) {
                    $relationDao = new isys_cmdb_dao_category_g_relation($this->m_db);
                    while ($row = $result->get_row()) {
                        if (isset($workstations[$row['isys_catg_logical_unit_list__isys_obj__id']])) {
                            unset($workstations[$row['isys_catg_logical_unit_list__isys_obj__id']]);
                            continue;
                        }
                        $relationDao->delete_relation($row['isys_catg_logical_unit_list__isys_catg_relation_list__id']);
                        $indicator = $this->delete_entry($row['isys_catg_logical_unit_list__id'], 'isys_catg_logical_unit_list');
                    }
                }

                if (!empty($workstations)) {
                    foreach ($workstations as $workplaceId => $unused) {
                        $relationId = null;
                        $data = $logicalUnitDao->get_data_by_object($workplaceId)->get_row();
                        if (isset($data['isys_catg_logical_unit_list__id'])) {
                            $relationId = $data['isys_catg_logical_unit_list__isys_catg_relation_list__id'];
                            $p_category_data['data_id'] = $data['isys_catg_logical_unit_list__id'];
                        } else {
                            $p_category_data['data_id'] = $this->create_connector('isys_catg_logical_unit_list', $workplaceId);
                        }

                        $indicator = $logicalUnitDao->save(
                            $p_category_data['data_id'],
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $workplaceId,
                            $relationId,
                            $p_category_data[isys_import_handler_cmdb::C__PROPERTIES]['description'][C__DATA__VALUE],
                        );
                    }
                }
            }
        }

        return $indicator === true ? $p_category_data['data_id'] : false;
    }

    /**
     * @param isys_request $request
     *
     * @return isys_component_dao_result
     */
    public function callbackGetAttachedWorkstations(isys_request $request)
    {
        return $this->get_data(null, $request->get_object_id());
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
        return isys_cmdb_dao_category_g_assigned_logical_unit::instance(isys_application::instance()->container->get('database'))
            ->getAttachedEntries($id, $tag, $asId);
    }

    /**
     * @param       $objectId
     * @param array $attachedObjects
     *
     * @return int|mixed|null
     * @see ID-11129 Change category to be 'assignment' category.
     */
    public function attachObjects($objectId, array $attachedObjects)
    {
        $currentObjects = [];

        $result = $this->get_data_by_object($objectId);

        // First record all 'currently attached' entries and remove those which are not part of the 'attached objects'.
        while ($row = $result->get_row()) {
            if (!in_array($row["isys_obj__id"], $attachedObjects)) {
                $this->deleteEntryByAssignedId($objectId, $row["isys_obj__id"]);
            } else {
                $currentObjects[] = $row["isys_obj__id"];
            }
        }

        $lastEntryId = null;
        $logicalUnitDao = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db);

        // Now create entries for 'to be attached' objects.
        foreach ($attachedObjects as $attachedObjectId) {
            if (is_numeric($attachedObjectId) && !in_array($attachedObjectId, $currentObjects)) {
                $relationId = null;
                $data = $logicalUnitDao->get_data_by_object($attachedObjectId)->get_row();

                if (isset($data['isys_catg_logical_unit_list__id'])) {
                    $relationId = $data['isys_catg_logical_unit_list__isys_catg_relation_list__id'];
                    $lastEntryId = $data['isys_catg_logical_unit_list__id'];
                } else {
                    $lastEntryId = $this->create_connector('isys_catg_logical_unit_list', $attachedObjectId);
                }

                $indicator = $logicalUnitDao->save(
                    $lastEntryId,
                    $objectId,
                    C__RECORD_STATUS__NORMAL,
                    $attachedObjectId,
                    $relationId,
                    $data['isys_catg_logical_unit_list__description']
                );
            }
        }

        return $lastEntryId;
    }
}
