<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_database_assignment extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'database_assignment';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__TREE__DATABASE_ASSIGNMENT';
        $this->m_entry_identifier = 'database_assignment';
        $this->m_table = 'isys_cats_database_access_list';
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_id
     * @param   array   $p_targetSchemaID
     * @param   integer $p_connectedObjID
     * @param   integer $p_status
     * @param   string  $p_commentary
     *
     * @return  boolean
     */
    public function save($p_id, $p_targetSchemaID, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL, $p_commentary = '')
    {
        $l_dao_access = new isys_cmdb_dao_category_s_database_access($this->m_db);

        if ($l_dao_access->save($p_id, $p_connectedObjID, $p_status)) {
            if ($p_targetSchemaID) {
                $l_sql = "UPDATE isys_cats_database_access_list
				SET isys_cats_database_access_list__isys_obj__id = " . $this->convert_sql_id($p_targetSchemaID) . ",
				isys_cats_database_access_list__description = " . $this->convert_sql_text($p_commentary) . "
				WHERE isys_cats_database_access_list__id = " . $this->convert_sql_id($p_id) . ";";

                return $l_dao_access->update($l_sql) && $this->apply_update();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param int    $p_object_id
     * @param int    $p_connectedObjID
     * @param int    $p_status
     * @param string $p_commentary
     * @return  mixed  Integer of the newly created ID or boolean false on failure.
     */
    public function create($p_object_id, $p_connectedObjID, $p_status = C__RECORD_STATUS__NORMAL, $p_commentary = '')
    {
        if ($p_object_id > 0) {
            $l_dao_access = new isys_cmdb_dao_category_s_database_access($this->m_db);

            if ($l_last_id = $l_dao_access->create($p_object_id, $p_connectedObjID, $p_status, $p_commentary)) {
                return $l_last_id;
            }
        }

        return false;
    }

    /**
     * Save category.
     *
     * @param int $p_cat_level
     * @param int &$p_intOldRecStatus
     * @return int
     * @throws Exception
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_cats_database_access_list__status"];

        $l_list_id = $l_catdata["isys_cats_database_access_list__id"];

        if (empty($l_list_id)) {
            if ($_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"] > 0) {
                $l_list_id = $this->create(
                    $_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"],
                    $_POST["C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT__HIDDEN"],
                    C__RECORD_STATUS__NORMAL,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT')]
                );
            }
        } else {
            $this->save(
                $l_list_id,
                $_POST["C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA__HIDDEN"],
                $_POST["C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT__HIDDEN"],
                C__RECORD_STATUS__NORMAL,
                $_POST["C__CMDB__CAT__COMMENTARY_" . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__DATABASE_ASSIGNMENT', 'C__CATG__DATABASE_ASSIGNMENT')]
            );
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_list_id;
    }

    /**
     * Checks if a connection to a database schema object exists.
     *
     * @param int $p_object
     * @param int $p_schema_object
     * @return  bool
     */
    public function connection_exists($p_object, $p_schema_object)
    {
        return !!count($this->get_data(null, $p_object, ' AND isys_connection__isys_obj__id = ' . (int)$p_schema_object));
    }

    /**
     * Method for counting the category rows.
     *
     * @param  integer $objectId
     *
     * @return integer
     * @throws isys_exception_database
     */
    public function get_count($objectId = null)
    {
        if (empty($objectId)) {
            $objectId = $this->m_object_id;
        }

        $sql = 'SELECT COUNT(*) AS cnt
			FROM isys_cats_database_access_list
			INNER JOIN isys_obj AS self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
			INNER JOIN isys_connection ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
			LEFT OUTER JOIN isys_obj AS assign ON assign.isys_obj__id = isys_connection__isys_obj__id
			LEFT OUTER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = assign.isys_obj__id
			WHERE TRUE
			' . $this->get_object_condition($objectId) . '
			AND isys_cats_database_access_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			LIMIT 1';

        return (int)$this->retrieve($sql)->get_row_value('cnt');
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT isys_cats_database_access_list.*, self.*, assign.isys_obj__title AS assigned_title, assign.isys_obj__id AS assigned_obj_id, isys_catg_relation_list.*
			FROM isys_cats_database_access_list
			INNER JOIN isys_obj AS self ON self.isys_obj__id = isys_cats_database_access_list__isys_obj__id
			INNER JOIN isys_connection ON isys_cats_database_access_list__isys_connection__id = isys_connection__id
			LEFT OUTER JOIN isys_obj AS assign ON assign.isys_obj__id = isys_connection__isys_obj__id
			LEFT OUTER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = assign.isys_obj__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter) . " ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_cats_database_access_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_database_access_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   mixed $p_obj_id
     *
     * @return  string
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_subquery = 'SELECT isys_catg_relation_list__isys_obj__id
					FROM isys_catg_relation_list
					WHERE (isys_catg_relation_list__isys_obj__id__slave ' . $this->prepare_in_condition($p_obj_id) . ' OR isys_catg_relation_list__isys_obj__id__master ' .
                    $this->prepare_in_condition($p_obj_id) . ')
					AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__SOFTWARE'));
            } else {
                $l_subquery = 'SELECT isys_catg_relation_list__isys_obj__id
					FROM isys_catg_relation_list
					WHERE (isys_catg_relation_list__isys_obj__id__slave = ' . $this->convert_sql_id($p_obj_id) . ' OR isys_catg_relation_list__isys_obj__id__master = ' .
                    $this->convert_sql_id($p_obj_id) . ')
					AND isys_catg_relation_list__isys_relation_type__id = ' . $this->convert_sql_id(defined_or_default('C__RELATION_TYPE__SOFTWARE'));
            }

            $l_sql = ' AND isys_connection__isys_obj__id IN (' . $l_subquery . ') ';
        }

        return $l_sql;
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [
            '_database_assignment' => new DynamicProperty(
                'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                'isys_cats_database_access_list__id',
                'isys_cats_database_access_list',
                [
                    $this,
                    'dynamic_property_callback_database_assignment'
                ]
            ),
            '_runs_on' => new DynamicProperty(
                'LC__CMDB__CATG__DATABASE_ASSIGNMENT__SOFTWARE_RUNS_ON',
                'isys_cats_database_access_list__id',
                'isys_cats_database_access_list',
                [
                    $this,
                    'dynamic_property_callback_runs_on'
                ]
            )
        ];
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_database_assignment($p_row)
    {
        $title = null;

        if (isset($p_row['isys_cats_database_access_list__id'])) {
            $sql = 'SELECT dba.isys_obj__title as title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_obj AS dba ON dba.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                WHERE isys_cats_database_access_list__id = ' . $this->convert_sql_id($p_row['isys_cats_database_access_list__id']) . ';';

            $title = $this
                ->retrieve($sql)
                ->get_row_value('title');
        }

        return $title ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @param $p_row
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_runs_on($p_row)
    {
        $title = null;

        if (isset($p_row['isys_cats_database_access_list__id'])) {
            $sql = 'SELECT device.isys_obj__title as title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS device ON isys_catg_relation_list__isys_obj__id__master = device.isys_obj__id
                WHERE isys_cats_database_access_list__id = ' . $this->convert_sql_id($p_row['isys_cats_database_access_list__id']) . ';';

            $title = $this
                ->retrieve($sql)
                ->get_row_value('title');
        }

        return $title ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'database_assignment' => (new ObjectBrowserProperty(
                'C__CATG__DATABASE_ASSIGNMENT__TARGET_SCHEMA',
                'LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA',
                'isys_cats_database_access_list__isys_obj__id',
                'isys_cats_database_access',
                [],
                'C__CATS__DATABASE_SCHEMA',
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    'SELECT dba.isys_obj__title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_obj AS dba ON dba.isys_obj__id = isys_cats_database_access_list__isys_obj__id
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id',
                    'isys_obj',
                    '',
                    'app.isys_obj__id',
                    '',
                    '',
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_relation_list__isys_relation_type__id = (SELECT isys_relation_type__id FROM isys_relation_type WHERE isys_relation_type__const = \'C__RELATION_TYPE__SOFTWARE\')' .
                        ' AND dba.isys_obj__status = ' . C__RECORD_STATUS__NORMAL .
                        ' AND isys_cats_database_access_list__status = ' . C__RECORD_STATUS__NORMAL,
                    ]),
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['app.isys_obj__id']),
                ),
            ])->mergePropertyCheck([
                C__PROPERTY__CHECK__MANDATORY => true,
            ]),
            'runs_on'             => (new ObjectBrowserProperty(
                'C__CATG__DATABASE_ASSIGNMENT__RELATION_OBJECT',
                'LC__CMDB__CATG__DATABASE_ASSIGNMENT__SOFTWARE_RUNS_ON',
                'isys_catg_relation_list__isys_obj__id__slave',
                'isys_catg_relation_list',
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    'SELECT ci.isys_obj__title
                              FROM isys_cats_database_access_list
                                INNER JOIN isys_connection ON isys_connection__id = isys_cats_database_access_list__isys_connection__id
                                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__isys_obj__id = isys_connection__isys_obj__id
                                INNER JOIN isys_obj AS app ON isys_catg_relation_list__isys_obj__id__slave = app.isys_obj__id
                                INNER JOIN isys_obj AS ci ON isys_catg_relation_list__isys_obj__id__master = ci.isys_obj__id',
                    'isys_obj',
                    '',
                    'app.isys_obj__id',
                    '',
                    '',
                    idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([
                        'isys_catg_relation_list__isys_relation_type__id =
                                (SELECT isys_relation_type__id FROM isys_relation_type WHERE isys_relation_type__const = \'C__RELATION_TYPE__SOFTWARE\')' .
                        ' AND isys_cats_database_access_list__status = ' . C__RECORD_STATUS__NORMAL,
                    ]),
                    idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['app.isys_obj__id']),
                ),
            ])->mergePropertyCheck([
                C__PROPERTY__CHECK__MANDATORY => true,
            ])->mergePropertyUiParams([
                isys_popup_browser_object_ng::C__CUSTOM_CONDITIONS            => [idoit\Module\Cmdb\Component\Browser\Condition\LocalSoftwareRelationCondition::class],
                isys_popup_browser_object_ng::C__DISABLE_PRIMARY_CONDITIONS   => true,
                isys_popup_browser_object_ng::C__DISABLE_SECONDARY_CONDITIONS => true,
                'p_bDisableDetach'                                            => true,
                'p_bReadonly'                                                 => true,
            ]),
            'description'         => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . $this->m_cat_type . $this->m_category_id,
                'isys_cats_database_access_list__description',
                'isys_cats_database_access_list',
            ),
        ];
    }

    /**
     * Sync method.
     *
     * @param array   $p_category_data
     * @param integer $p_object_id
     * @param integer $p_status
     * @return mixed
     * @throws isys_exception_cmdb
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Do some assertions for QS.
            if ($p_status == isys_import_handler_cmdb::C__CREATE && $p_object_id > 0) {
                $p_category_data['data_id'] = $this->create(
                    $p_object_id,
                    $p_category_data['properties']['database_assignment'][C__DATA__VALUE],
                );
                $p_status = isys_import_handler_cmdb::C__UPDATE;
            }

            if ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
                $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['runs_on'][C__DATA__VALUE],
                    $p_category_data['properties']['database_assignment'][C__DATA__VALUE],
                );

                return $p_category_data['data_id'];
            }
        }

        return false;
    }

    /**
     * Validates property data.
     *
     * @param array $p_data
     * @param mixed $p_prepend_table_field
     * @return mixed
     * @throws Exception
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_empty_fields = [];

        if (count($p_data) > 1 && empty($p_data['database_assignment'])) {
            $l_empty_fields['database_assignment'] = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION');
        }

        if (count($p_data) > 1 && empty($p_data['runs_on'])) {
            $l_empty_fields['runs_on'] = isys_application::instance()->container->get('language')
                ->get('LC__CMDB__OBJECT_BROWSER__PLEASE_MAKE_A_SELECTION');
        }

        if (count($l_empty_fields)) {
            return $l_empty_fields;
        }

        return parent::validate($p_data);
    }
}
