<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for AP Controller (Devices)
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Paul Kolbovich <pkolbovich@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_ap_devices extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    /**
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db)
    {
        $this->m_category    = 'ap_devices';
        $this->m_multivalued = true;

        parent::__construct($db);

        $this->categoryTitle               = 'LC__CMDB__CATG__AP_DEVICES';
        $this->m_object_browser_category   = true;
        $this->m_object_browser_property   = 'connected_object';
        $this->m_table                     = 'isys_catg_ap_controller_list';
        $this->m_connected_object_id_field = 'isys_catg_ap_controller_list__isys_obj__id';
        $this->m_object_id_field           = 'isys_connection__isys_obj__id';
    }

    /**
     * Wrapper to retrieve all assigned objects
     *
     * @param $objId
     *
     * @return isys_component_dao_result
     */
    public function get_assigned_objects($objId)
    {
        return $this->get_data(null, $objId);
    }

    /**
     * Save global category AP Controller element.
     *
     * @param int   $objectId
     * @param array $objects
     *
     * @return  null
     */
    public function attachObjects($objectId, array $objects)
    {
        $conArr = $objects;

        $assignedObjects = [];
        $assignedObjectsRes = $this->get_assigned_objects($objectId);

        if ($assignedObjectsRes->count() > 0) {
            while ($row = $assignedObjectsRes->get_row()) {
                $assignedObjects[$row['isys_obj__id']] = $row['isys_catg_ap_controller_list__id'];
            }
        }

        if (is_array($conArr)) {
            foreach ($conArr as $val) {
                if (is_numeric($val) && $val != "on") {
                    if (!isset($assignedObjects[$val])) {
                        $this->create($objectId, C__RECORD_STATUS__NORMAL, $val);
                    } else {
                        unset($assignedObjects[$val]);
                    }
                }
            }
        }

        if (count($assignedObjects) > 0) {
            foreach ($assignedObjects as $objId => $entryId) {
                $this->delete_entry($entryId, 'isys_catg_ap_controller_list');
            }
        }

        return null;
    }

    /**
     * Create method.
     *
     * @param   integer $objID
     * @param   integer $newRecStatus
     * @param   integer $connectedObjID
     * @param   string  $description
     *
     * @return  mixed
     */
    public function create($objID, $newRecStatus, $connectedObjID, $description = null)
    {
        /**
         * @var isys_cmdb_dao_category_g_ap_controller
         */
        $dao = isys_cmdb_dao_category_g_ap_controller::instance($this->m_db);

        return $dao->create($connectedObjID, $newRecStatus, $objID, $description);
    }

    /**
     * Count method
     *
     * @param int|null $objId
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_count($objId = null)
    {
        $sql = 'SELECT COUNT(isys_catg_ap_controller_list__id) AS count FROM isys_catg_ap_controller_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_ap_controller_list__isys_connection__id
            WHERE isys_connection__isys_obj__id = ' . $this->convert_sql_id($objId);

        return (int) $this->retrieve($sql)->get_row_value('count');
    }

    /**
     * Retrieves data which devices are connected to the AP controller
     *
     * @param null   $catgListId
     * @param null   $objId
     * @param string $condition
     * @param null   $filter
     * @param null   $status
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_data($catgListId = null, $objId = null, $condition = "", $filter = null, $status = null)
    {
        $sql = 'SELECT
                isys_catg_ap_controller_list__id,
                isys_obj__id,
                isys_obj__title,
                isys_obj__isys_obj_type__id,
                isys_obj__sysid,
                isys_catg_ap_controller_list__isys_obj__id,
                isys_connection__isys_obj__id
            FROM isys_catg_ap_controller_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_ap_controller_list__isys_connection__id
            INNER JOIN isys_obj ON isys_catg_ap_controller_list__isys_obj__id = isys_obj__id
            WHERE TRUE ' . $this->prepare_filter($filter);

        if ($objId !== null) {
            $sql .= ' ' . $this->get_object_condition($objId);
        }

        if ($catgListId !== null) {
            $sql .= " AND isys_catg_ap_controller_list__id = " . $this->convert_sql_id($catgListId);
        }

        if ($status !== null) {
            $sql .= " AND isys_catg_ap_controller_list__status = " . $this->convert_sql_int($status);
        }

        return $this->retrieve($sql);
    }

    /**
     * Helper method to build the object condition
     *
     * @param int|null $objId
     *
     * @return string
     */
    public function get_object_condition($objId = null, $alias = 'isys_obj')
    {
        if (!empty($objId)) {
            if (is_array($objId)) {
                return ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($objId) . ') ';
            } else {
                return ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($objId) . ') ';
            }
        }

        return '';
    }

    /**
     * @return  array
     */
    protected function properties()
    {
        return [
            'connected_object' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT_BACKWARD',
                'LC__CMDB__CATG__AP_CONTROLLER__ASSIGNED_OBJECT',
                'isys_catg_ap_controller_list__isys_obj__id',
                'isys_catg_ap_controller_list',
                '',
                [],
                'C__CATG__AP_CONTROLLER',
                null,
                'isys_cmdb_dao_category_g_ap_controller::connected_object'
            ))->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                Property::C__PROPERTY__PROVIDES__REPORT     => true,
                Property::C__PROPERTY__PROVIDES__EXPORT     => true,
                Property::C__PROPERTY__PROVIDES__IMPORT     => true,
                Property::C__PROPERTY__PROVIDES__SEARCH     => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT  => true,
            ])->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => new isys_callback([
                    'isys_cmdb_dao_category_g_ap_devices',
                    'getEntriesByRequestObject'
                ]),
            ]),
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $categoryData
     * @param   integer $objectId
     * @param   integer $status (1 is isys_import_handler_cmdb::C__CREATE)
     *
     * @return  mixed
     */
    public function sync($categoryData, $objectId, $status = 1)
    {
        if (is_array($categoryData) && isset($categoryData['properties'])) {
            $connectedObject = $this->convert_sql_id($categoryData['properties']['connected_object'][C__DATA__VALUE]);

            // Create category data identifier if needed:
            if ($status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create($objectId, C__RECORD_STATUS__NORMAL, $connectedObject, $categoryData['properties']['description'][C__DATA__VALUE]);
            } elseif ($status == isys_import_handler_cmdb::C__UPDATE) {
                if ($categoryData['data_id'] === null && $connectedObject) {
                    $sql = 'SELECT isys_catg_ap_controller_list__id FROM isys_catg_ap_controller_list
                        WHERE isys_catg_ap_controller_list__isys_obj__id = ' . $this->convert_sql_id($connectedObject);
                    $res = $this->retrieve($sql . ';');
                    if ($res->count()) {
                        $categoryData['data_id'] = $res->get_row_value('isys_catg_ap_controller_list__id');
                    } else {
                        return $this->create($objectId, C__RECORD_STATUS__NORMAL, $connectedObject, $categoryData['properties']['description'][C__DATA__VALUE]);
                    }
                }

                return $categoryData['data_id'];
            }
        }

        return false;
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
            ->getConnectedObjectsReversed('isys_catg_ap_controller_list', $id, $asId);
    }
}
