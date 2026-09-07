<?php

use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\EntryInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * i-doit
 *
 * DAO: global category for assigned QinQ CE-VLANs
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_qinq_ce extends isys_cmdb_dao_category_global implements ObjectBrowserReceiver, ObjectBrowserAssignedEntries
{
    public function __construct(isys_component_database $database)
    {
        $this->m_category = 'qinq_ce';
        $this->m_multivalued = true;
        $this->m_table = 'isys_catg_qinq_list';

        parent::__construct($database);

        $this->m_list = 'isys_cmdb_dao_list_catg_qinq';
        $this->m_object_browser_category = true;
        $this->m_object_browser_property = 'spvlan';
        $this->m_reverse_category_of = 'isys_cmdb_dao_category_g_qinq_ce';
        $this->m_object_id_field = 'isys_connection__isys_obj__id';
        $this->m_connected_object_id_field = 'isys_catg_qinq_list__isys_obj__id';
    }

    /**
     * Method for getting the object-browsers preselection.
     *
     * @param int $objectId
     *
     * @return isys_component_dao_result
     */
    public function get_selected_objects($objectId)
    {
        return isys_cmdb_dao_category_g_qinq_sp::instance($this->m_db)->get_data_by_parent($objectId);
    }

    /**
     * @param int   $p_object_id
     * @param array $p_objects
     *
     * @return void
     * @throws isys_exception_database
     */
    public function attachObjects($p_object_id, array $p_objects)
    {
        $l_dao = isys_cmdb_dao_category_g_qinq_sp::instance($this->m_db);
        $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

        // First get assigned devices
        $l_dao_res = $l_dao->get_data_by_parent($p_object_id);
        $l_assigned_units = [];

        // Get allready assigned units
        if ($l_dao_res->num_rows() > 0) {
            while ($l_dao_row = $l_dao_res->get_row()) {
                $l_assigned_units[$l_dao_row['isys_obj__id']] = [
                    'category_id' => $l_dao_row['isys_catg_qinq_list__id'],
                    'relation_id' => $l_dao_row['isys_catg_qinq_list__isys_catg_relation_list__id'],
                ];
            }
        }

        // Now we create the new entries.
        if (is_array($p_objects)) {
            foreach ($p_objects as $l_id) {
                $this->set_ce($l_id, $p_object_id);

                // Remove from
                unset($l_assigned_units[$l_id]);
            }
        }

        // Now we delete the entries
        if (count($l_assigned_units) > 0) {
            foreach ($l_assigned_units as $l_obj_id => $l_data) {
                $this->delete_entry($l_data['category_id'], 'isys_catg_qinq_list');
                $l_relation_dao->delete_relation($l_data['relation_id']);
            }
        }
    }

    public function set_ce($p_ceID, $p_parent_id)
    {
        // Get data
        $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);
        $l_res = $l_dao->get_data(null, $p_ceID);

        // Check for category entry
        if (!$l_res->count()) {
            $l_category_id = $l_dao->create_connector('isys_catg_qinq_list', $p_ceID);
        } else {
            $l_data = $l_res->get_row();
            $l_category_id = $l_data['isys_catg_qinq_list__id'];
        }

        return $l_dao->save($l_category_id, C__RECORD_STATUS__NORMAL, $p_parent_id, $p_ceID);
    }

    /**
     * Do nothing
     *
     * @param      $p_cat_level
     * @param      $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        return null;
    }

    /**
     * @param int $objectId
     *
     * @return int
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_count($objectId = null)
    {
        if (empty($objectId)) {
            $objectId = $this->m_object_id;
        }

        $query = 'SELECT COUNT(isys_catg_qinq_list__id) AS cnt
            FROM isys_catg_qinq_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_qinq_list__isys_connection__id
            WHERE TRUE
            AND isys_catg_qinq_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if (!empty($objectId)) {
            $query .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($objectId);
        }

        return (int) $this->retrieve($query)->get_row_value('cnt');
    }

    /**
     * Get data method, uses logical unit DAO.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        // Consider connection field in condition
        $p_condition .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);

        return isys_cmdb_dao_category_g_qinq_sp::instance($this->m_db)->get_data(null, null, $p_condition, $p_filter, $p_status);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'spvlan' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATG__QINQ_SP__SPVLAN',
                'LC__CATG__QINQ_SP__SPVLAN',
                'isys_catg_qinq_list__isys_connection__id',
                'isys_catg_qinq_list',
                '',
                ['isys_export_helper', 'connection'],
                'C__CATG__QINQ_SP',
                null,
                'isys_cmdb_dao_category_g_qinq_sp::spvlan'
            ))->mergePropertyUiParams([
                'multiselection' => true,
                isys_popup_browser_object_ng::C__DATARETRIEVAL => new isys_callback([
                    'isys_cmdb_dao_category_g_qinq_ce',
                    'getEntriesByRequestObject'
                ])
            ])
        ];
    }

    /**
     * Purge entries.
     *
     * @param   array $p_cat_ids
     *
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     * @return  boolean
     */
    public function rank_records($p_cat_ids, $p_direction = C__CMDB__RANK__DIRECTION_DELETE, $p_table = "isys_obj", $p_checkMethod = null, $p_purge = false)
    {
        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                $l_dao = new isys_cmdb_dao_category_g_qinq_sp($this->m_db);
                $l_relation_dao = new isys_cmdb_dao_category_g_relation($this->m_db);

                if (is_array($p_cat_ids)) {
                    foreach ($p_cat_ids as $l_cat_id) {
                        $l_catdata = $l_dao->get_data($l_cat_id)
                            ->get_row();

                        // First delete relation.
                        if ($l_relation_dao->delete_relation($l_catdata['isys_catg_qinq_list__isys_catg_relation_list__id'])) {
                            // Then delete entry.
                            $l_dao->delete_entry($l_cat_id, 'isys_catg_qinq_list');
                        }
                    }
                }

                return true;
                break;
            default:
                return true;
        }
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done,
     *                 otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $layer2Nets = $p_category_data['properties']['spvlan'][C__DATA__VALUE];
            if (!is_array($layer2Nets)) {
                $layer2Nets = [$layer2Nets];
            }
            $layer2Nets = array_unique($layer2Nets);

            if ($p_status === isys_import_handler_cmdb::C__CREATE || isys_import_handler_cmdb::C__UPDATE) {
                $lastId = true;

                /** @var EntryInterface[] $assignedObjects */
                $assignedObjects = $this->getAttachedEntries($p_object_id)
                    ->getEntries();

                foreach ($layer2Nets as $layer2Net) {
                    if (isset($assignedObjects[$layer2Net])) {
                        unset($assignedObjects[$layer2Net]);
                        continue;
                    }

                    $lastId = $this->set_ce($layer2Net, $p_object_id);
                }

                if (!empty($assignedObjects)) {
                    $daoSp = isys_cmdb_dao_category_g_qinq_sp::instance($this->m_db);
                    $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

                    foreach ($assignedObjects as $data) {
                        if (!$data instanceof EntryInterface) {
                            continue;
                        }
                        $categoryData = $daoSp->get_data($data->getData()->offsetGet('id'))->get_row();

                        // First delete relation.
                        if ($relationDao->delete_relation($categoryData['isys_catg_qinq_list__isys_catg_relation_list__id'])) {
                            // Then delete entry.
                            $daoSp->delete_entry($categoryData['isys_catg_qinq_list__id'], 'isys_catg_qinq_list');
                        }
                    }
                }

                return $lastId;
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
            ->getConnectedObjectsReversed('isys_catg_qinq_list', $id, $asId);
    }
}
