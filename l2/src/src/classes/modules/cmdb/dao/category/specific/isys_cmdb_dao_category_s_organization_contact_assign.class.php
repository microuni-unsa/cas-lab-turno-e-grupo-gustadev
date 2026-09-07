<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;

/**
 * i-doit
 *
 * DAO: specific category for organizations with assigned contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_organization_contact_assign extends isys_cmdb_dao_category_specific implements ObjectBrowserAssignedEntries
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'organization_contact_assign';
        $this->m_multivalued = true;
        $this->m_category_const = 'C__CATS__ORGANIZATION_CONTACT_ASSIGNMENT';
        $this->m_table = 'isys_catg_contact_list';
        $this->m_tpl = 'cats__contact_assign.tpl';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CONTACT_ASSIGNMENT__ORGANIZATION';
        $this->m_object_id_field = 'isys_connection__isys_obj__id';
        $this->m_connected_object_id_field = 'isys_catg_contact_list__isys_obj__id';
    }

    /**
     * Get count for graying the category title.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        $l_obj_id = $p_obj_id ?: $this->m_object_id;

        $l_sql = 'SELECT COUNT(isys_catg_contact_list__id) AS count FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
			WHERE isys_catg_contact_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        if ($l_obj_id > 0) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($l_obj_id);
        }

        return (int)$this->retrieve($l_sql . ';')
            ->get_row_value('count');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        // Changed "LEFT JOIN isys_obj AS o1" from "INNER JOIN" because it caused an error: ID-1164.
        $l_sql = 'SELECT isys_catg_contact_list.*, o1.*, isys_connection.*
            FROM isys_catg_contact_list
            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list.isys_catg_contact_list__isys_connection__id
            LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list.isys_catg_contact_list__isys_contact_tag__id
            LEFT JOIN isys_obj AS o1 ON isys_catg_contact_list.isys_catg_contact_list__isys_obj__id = o1.isys_obj__id
            INNER JOIN isys_obj AS o2 ON o2.isys_obj__id = isys_connection__isys_obj__id
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= ' AND isys_catg_contact_list.isys_catg_contact_list__id = ' . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_contact_list.isys_catg_contact_list__status = ' . $this->convert_sql_int($p_status);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Creates the condition to the object table
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_connection__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
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
            'object' => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__ASSIGNED_OBJECTS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Title',
                    C__PROPERTY__INFO__BACKWARD_PROPERTY => 'isys_cmdb_dao_category_g_contact::contact_object'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_contact_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contact_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_contact_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_contact_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__ORGANISATION_TARGET_OBJECT',
                    C__PROPERTY__UI__PARAMS => []
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'role'   => (new DialogPlusProperty(
                'C__CONTACT__ORGANISATION_ROLE',
                'LC__CMDB__CONTACT_ROLE',
                'isys_catg_contact_list__isys_contact_tag__id',
                'isys_catg_contact_list',
                'isys_contact_tag'
            ))->mergePropertyData(
                [
                    Property::C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_contact_tag__title
                            FROM isys_catg_contact_list
                            INNER JOIN isys_connection ON isys_connection__id = isys_catg_contact_list__isys_connection__id
                            INNER JOIN isys_contact_tag ON isys_contact_tag__id = isys_catg_contact_list__isys_contact_tag__id',
                        'isys_connection',
                        'isys_connection__id',
                        'isys_connection__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_connection__isys_obj__id'])
                    ),
                    Property::C__PROPERTY__DATA__JOIN => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_connection', 'LEFT', 'isys_connection__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_contact_list',
                            'LEFT',
                            'isys_connection__id',
                            'isys_catg_contact_list__isys_connection__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_contact_tag',
                            'LEFT',
                            'isys_catg_contact_list__isys_contact_tag__id',
                            'isys_contact_tag__id'
                        )
                    ]
                ]
            )->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__SEARCH => false,
                Property::C__PROPERTY__PROVIDES__REPORT => true
            ])
        ];
    }

    /**
     * @param array  $entryIds
     * @param int    $direction
     * @param string $table
     * @param mixed  $checkMethod
     * @param bool   $purge
     *
     * @return true
     */
    public function rank_records($entryIds, $direction = C__CMDB__RANK__DIRECTION_DELETE, $table = "isys_obj", $checkMethod = null, $purge = false)
    {
        $daoContact = isys_cmdb_dao_category_g_contact::instance($this->get_database_component());
        $daoRelation = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $event = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__ARCHIVE:
                $targetStatus = C__RECORD_STATUS__ARCHIVED;
                $event = 'C__LOGBOOK_EVENT__CATEGORY_ARCHIVED';
                break;

            case C__NAVMODE__DELETE:
                $targetStatus = C__RECORD_STATUS__DELETED;
                $event = 'C__LOGBOOK_EVENT__CATEGORY_DELETED';
                break;

            case C__NAVMODE__RECYCLE:
                if (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__ARCHIVED) {
                    $targetStatus = C__RECORD_STATUS__NORMAL;
                } elseif (intval(isys_glob_get_param("cRecStatus")) == C__RECORD_STATUS__DELETED) {
                    $targetStatus = C__RECORD_STATUS__ARCHIVED;
                }
                $event = 'C__LOGBOOK_EVENT__CATEGORY_RECYCLED';
                break;

            case C__NAVMODE__QUICK_PURGE:
            case C__NAVMODE__PURGE:
                if (!empty($entryIds)) {
                    foreach ($entryIds as $entryId) {
                        $objectTitle = $daoContact->get_data($entryId)->get_row_value('isys_obj__title');

                        // This is necessary, because the method gets called three times.
                        if ($objectTitle !== null) {
                            // @see ID-11245 Write logbook message.
                            $this->logbook_rank(
                                $entryId,
                                'C__LOGBOOK_EVENT__CATEGORY_PURGED',
                                '',
                                'LC__CMDB__CONTACT_ASSIGNMENT__ORGANIZATION',
                                $objectTitle
                            );
                        }

                        $daoContact->delete($entryId);
                    }
                    unset($entryIds);
                }

                return true;
        }

        foreach ($entryIds as $entryId) {
            $l_data = $daoContact->get_data($entryId)->get_row();

            if ($daoContact->save(
                $entryId,
                $_GET[C__CMDB__GET__OBJECT],
                $l_data["isys_catg_contact_list__isys_contact_tag__id"],
                $l_data["isys_catg_contact_list__description"],
                $targetStatus
            )) {
                // Re-fetch after data was saved.
                $l_data = $daoContact
                    ->get_data($entryId)
                    ->get_row();

                $relationObjectId = $daoRelation
                    ->get_data($l_data["isys_catg_contact_list__isys_catg_relation_list__id"])
                    ->get_row_value('isys_catg_relation_list__isys_obj__id');

                $daoRelation->set_object_status($relationObjectId, $targetStatus);

                // @see ID-11245 Write logbook message.
                $this->logbook_rank($entryId, $event, '', 'LC__CMDB__CONTACT_ASSIGNMENT__ORGANIZATION', $l_data['isys_obj__title']);
            }
        }

        return true;
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database).
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            /* @var  $l_dao_connection  isys_cmdb_dao_connection */
            $l_dao_connection = isys_cmdb_dao_connection::factory($this->get_database_component());

            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                /**
                * Wrong handling of references fixed:
                *
                * Organization and assigned object changed
                */
                $p_category_data['data_id'] = $this->create_connector('isys_catg_contact_list', $p_category_data['properties']['object'][C__DATA__VALUE]);
                $l_connection_id = $l_dao_connection->add_connection($p_object_id);
            } else {
                $l_connection_id = $l_dao_connection->retrieve_connection(
                    'isys_catg_contact_list',
                    $p_category_data['data_id'],
                    'isys_catg_contact_list__isys_connection__id'
                );

                if (!$l_connection_id) {
                    $l_connection_id = $l_dao_connection->attach_connection(
                        'isys_catg_contact_list',
                        $p_category_data['data_id'],
                        null,
                        'isys_catg_contact_list__isys_connection__id'
                    );
                }

                $l_dao_connection->update_connection($l_connection_id, $p_category_data['properties']['object'][C__DATA__VALUE]);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    C__RECORD_STATUS__NORMAL,
                    $l_connection_id,
                    $p_category_data['properties']['role'][C__DATA__VALUE],
                    $p_category_data['properties']['object'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_contact_list__status"];

        $l_list_id = $l_catdata["isys_catg_contact_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_catg_contact_list", $_POST["C__CONTACT__ORGANISATION_TARGET_OBJECT__HIDDEN"]);
            $l_connection_id = isys_cmdb_dao_connection::instance($this->m_db)
                ->add_connection($_GET[C__CMDB__GET__OBJECT]);
        } else {
            $l_connection_id = $l_catdata["isys_catg_contact_list__isys_connection__id"];
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                C__RECORD_STATUS__NORMAL,
                $l_connection_id,
                $_POST["C__CONTACT__ORGANISATION_ROLE"],
                $_POST["C__CONTACT__ORGANISATION_TARGET_OBJECT__HIDDEN"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * @param int    $p_catlevel
     * @param int    $p_status
     * @param int    $p_connection
     * @param int    $p_role
     * @param int    $p_objID
     * @param string $p_description
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save($p_catlevel, $p_status, $p_connection, $p_role, $p_objID, $p_description)
    {
        return isys_cmdb_dao_category_s_person_group_contact_assign::instance(isys_application::instance()->container->get('database'))
            ->save($p_catlevel, $p_status ?: C__RECORD_STATUS__NORMAL, $p_connection, $p_role, $p_objID, $p_description);
    }

    /**
     * Executes the query to create the category entry.
     *
     * @return  null
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create()
    {
        return null;
    }

    /**
     * @param int|int[]    $id
     * @param string $tag
     * @param bool   $asId
     *
     * @return CollectionInterface
     * @throws Exception
     */
    public function getAttachedEntries($id, $tag = '', $asId = true): CollectionInterface
    {
        return isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'))
            ->getConnectedObjectsReversed('isys_catg_contact_list', $id, $asId);
    }
}
