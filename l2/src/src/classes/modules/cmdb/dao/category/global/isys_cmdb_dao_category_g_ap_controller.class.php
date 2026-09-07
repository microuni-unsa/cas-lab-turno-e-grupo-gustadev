<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;

/**
/**
 * i-doit
 *
 * DAO: global category for Access Point Controller
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Paul Kolbovich <pkolbovich@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_ap_controller extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db)
    {
        $this->m_category = 'ap_controller';

        parent::__construct($db);

        $this->categoryTitle               = 'LC__CMDB__CATG__AP_CONTROLLER';
        $this->m_is_purgable               = true;
        $this->m_has_relation              = true;
        $this->m_connected_object_id_field = 'isys_connection__isys_obj__id';
        $this->m_object_id_field           = 'isys_catg_ap_controller_list__isys_obj__id';
    }

    /**
     * Dynamic property handling for connected object
     *
     * @param $row
     *
     * @return string
     * @throws isys_exception_general
     */
    public function dynamic_property_callback_connected_object($row)
    {
        /**
         * @var isys_cmdb_dao_category_g_ap_controller
         */
        $dao = isys_cmdb_dao_category_g_ap_controller::instance(
            isys_application::instance()->container->get('database')
        );

        if (!isset($row['isys_connection__isys_obj__id'])) {
            $row['isys_connection__isys_obj__id'] = $dao->get_data(null, $row['isys_obj__id'])
                ->get_row_value('isys_connection__isys_obj__id');
        }
        $title = $dao->get_obj_name_by_id_as_string($row['isys_connection__isys_obj__id']);
        if ($title === '') {
            return '';
        }

        return (new isys_ajax_handler_quick_info())
            ->get_quick_info(
                $row['isys_connection__isys_obj__id'],
                $title,
                C__LINK__OBJECT
            );
    }

    /**
     * @inheritDoc
     */
    public function save_data($entryId, $data)
    {
        $catData = $this->get_data_by_object($data['isys_obj__id'])->get_row();

        if (empty($catData['isys_catg_ap_controller_list__id'])) {
            $id = $this->create(
                $data['isys_obj__id'],
                $data['status'],
                $data['connected_object'],
                $data['description']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
            return (bool) $id;
        }

        if ($this->save(
            $catData['isys_catg_ap_controller_list__id'],
            $data['status'],
            $data['connected_object'],
            $data['description']
        )) {
            $this->m_strLogbookSQL = $this->get_last_query();
            return true;
        }

        return false;
    }

    /**
     * @param int    $objectId
     * @param int    $status
     * @param null   $assignedObject
     * @param string $description
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    public function create($objectId, $status = C__RECORD_STATUS__NORMAL, $assignedObject = null, $description = '')
    {
        $connection = isys_cmdb_dao_connection::instance($this->m_db);
        $lastId = false;

        $sql = "INSERT INTO isys_catg_ap_controller_list (
            isys_catg_ap_controller_list__isys_obj__id,
            isys_catg_ap_controller_list__status,
            isys_catg_ap_controller_list__isys_connection__id,
            isys_catg_ap_controller_list__description)
            VALUES(
            " . $this->convert_sql_id($objectId) . ",
            " . $this->convert_sql_int($status) . ",
            " . $this->convert_sql_id($connection->add_connection($assignedObject)) . ",
            " . $this->convert_sql_text($description) . ");";

        if ($this->update($sql) && $this->apply_update()) {
            $lastId = $this->get_last_insert_id();
            $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $relationDao->handle_relation(
                $lastId,
                'isys_catg_ap_controller_list',
                defined_or_default('C__RELATION_TYPE__AP_CONTROLLER'),
                null,
                $objectId,
                $assignedObject
            );
        }

        return $lastId;
    }

    /**
     * @param int       $catLevel
     * @param array|int $status
     * @param null      $assignedObject
     * @param string    $description
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_general
     */
    public function save($catLevel, $status = C__RECORD_STATUS__NORMAL, $assignedObject = null, $description = '')
    {
        $oldData = $this->get_data($catLevel)
            ->get_row();

        $connection = new isys_cmdb_dao_connection($this->get_database_component());
        $connectionId = $connection->update_connection($oldData["isys_catg_ap_controller_list__isys_connection__id"], $assignedObject);

        $sql = "UPDATE isys_catg_ap_controller_list SET
            isys_catg_ap_controller_list__status = " . $this->convert_sql_int($status) . ",
            isys_catg_ap_controller_list__description = " . $this->convert_sql_text($description) . ",
            isys_catg_ap_controller_list__isys_connection__id = " . $this->convert_sql_id($connectionId) . "
            WHERE isys_catg_ap_controller_list__id = " . $this->convert_sql_id($catLevel);

        if ($this->update($sql) && $this->apply_update()) {
            $relationDao = isys_cmdb_dao_category_g_relation::instance($this->m_db);
            $data = $this->get_data($catLevel)->__to_array();

            $relationDao->handle_relation(
                $catLevel,
                'isys_catg_ap_controller_list',
                defined_or_default('C__RELATION_TYPE__AP_CONTROLLER'),
                $data['isys_catg_ap_controller_list__isys_catg_relation_list__id'],
                $data['isys_catg_ap_controller_list__isys_obj__id'],
                $assignedObject
            );

            return true;
        }

        return false;
    }

    /**
     * @return  array
     */
    protected function properties()
    {
        return [
            'connected_object' => (
                new ObjectBrowserConnectionProperty(
                    'C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT',
                    'LC__CMDB__CATG__AP_CONTROLLER',
                    'isys_catg_ap_controller_list__isys_connection__id',
                    'isys_catg_ap_controller_list'
                )
            )->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__AP_CONTROLLER')
            )->setPropertyDataRelationHandler(
                new isys_callback(
                    [
                        'isys_cmdb_dao_category_g_ap_controller',
                        'callback_property_relation_handler',
                    ],
                    ['isys_cmdb_dao_category_g_ap_controller']
                )
            )->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__MULTISELECTION => false,
                isys_popup_browser_object_ng::C__CAT_FILTER     => 'C__CATG__AP_DEVICES',
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__VALIDATION => false,
                Property::C__PROPERTY__PROVIDES__EXPORT     => true,
                Property::C__PROPERTY__PROVIDES__IMPORT     => true,
                Property::C__PROPERTY__PROVIDES__SEARCH     => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT  => true,
            ]),
            'description'      => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__AP_CONTROLLER', 'C__CATG__AP_CONTROLLER'),
                'isys_catg_ap_controller_list__description',
                'isys_catg_ap_controller_list'
            ),
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $categoryData
     * @param   integer $objectId
     * @param   integer $status
     *
     * @return  mixed
     */
    public function sync($categoryData, $objectId, $status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {

        if (is_array($categoryData) && isset($categoryData['properties'])) {
            // Create category data identifier if needed:
            if ($status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create(
                    $objectId,
                    C__RECORD_STATUS__NORMAL,
                    $categoryData['properties']['connected_object'][C__DATA__VALUE],
                    $categoryData['properties']['description'][C__DATA__VALUE]
                );
            } elseif ($status == isys_import_handler_cmdb::C__UPDATE) {
                if ($categoryData['data_id'] === null) {
                    $sql = 'SELECT isys_catg_ap_controller_list__id FROM isys_catg_ap_controller_list
                    WHERE isys_catg_ap_controller_list__isys_obj__id = ' . $this->convert_sql_id($objectId);
                    $res = $this->retrieve($sql . ';');
                    if ($res->count() > 0) {
                        $categoryData['data_id'] = $res->get_row_value('isys_catg_ap_controller_list__id');
                    } else {
                        return $this->create(
                            $objectId,
                            C__RECORD_STATUS__NORMAL,
                            $categoryData['properties']['connected_object'][C__DATA__VALUE],
                            $categoryData['properties']['description'][C__DATA__VALUE]
                        );
                    }
                }

                $this->save(
                    $categoryData['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $categoryData['properties']['connected_object'][C__DATA__VALUE],
                    $categoryData['properties']['description'][C__DATA__VALUE]
                );

                return $categoryData['data_id'];
            }
        }

        return false;
    }
}
