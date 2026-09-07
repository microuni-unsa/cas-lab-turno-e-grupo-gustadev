<?php

/**
 * i-doit
 *
 * Dialog DAO.
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_dialog extends isys_cmdb_dao
{
    /**
     * The static cache variable.
     */
    private array $m_cache = [];

    /**
     * Cache which contains parent tables
     */
    private array $m_cache_parent_tables = [];

    /**
     * The dialogs table-name.
     *
     * @var string
     */
    protected $m_table = '';

    protected array $additionFieldsToCondition = [];

    /**
     * @param array $additionFieldsToCondition
     *
     * @return isys_cmdb_dao_dialog
     */
    public function setAdditionFieldsToCondition(array $additionFieldsToCondition)
    {
        $this->additionFieldsToCondition = $additionFieldsToCondition;
        return $this;
    }

    /**
     * Checks a dialog entry for its existence and creates a new one or returns the identifier of the existing one.
     *
     * @param string $table
     * @param string $title
     * @param null   $checkName
     * @param null   $parentID
     * @param array  $customData
     * @param string $const
     * @return false|mixed|null
     * @throws isys_exception_database
     */
    public function check_dialog($table, $title, $checkName = null, $parentID = null, array $customData = [], string $const = '')
    {
        $this->m_table = $table;
        $this->setAdditionFieldsToCondition($customData);
        $this->load();

        if ($title !== '') {
            $title = (string)$title;

            // We use the dialog factory for cached data, saves thousands of queries during im-/export.
            if ($checkName === null && $parentID === null) {
                $l_data = $this->get_data(null, $title);

                if ($l_data !== false) {
                    return $l_data[$table . '__id'];
                }
            } else {
                $l_data = $this->get_data_by_parent($title, $parentID);

                if ($l_data !== false) {
                    return $l_data[$table . '__id'];
                }
            }

            $l_id = isys_cmdb_dao_dialog_admin::instance($this->get_database_component())
                ->create($table, $title, 0, $const, C__RECORD_STATUS__NORMAL, $parentID, null, '', $customData);

            // reload dialog data
            $this->reset();

            return $l_id;
        } else {
            return null;
        }
    }

    /**
     * Retrieves data with the specified title and parent id
     *
     * @param string $p_title
     * @param int    $p_parent_id
     * @param array  $excludeIds
     * @return boolean|array
     * @throws isys_exception_database
     */
    public function get_data_by_parent($p_title, $p_parent_id, array $excludeIds = [])
    {
        if (!$this->m_cache_parent_tables[$this->m_table]) {
            $this->m_cache_parent_tables[$this->m_table] = isys_cmdb_dao_dialog_admin::instance($this->m_db)
                ->get_parent_table($this->m_table);
        }

        if (!isset($this->m_cache[$this->m_table])) {
            $this->load();
        }

        $l_title_lower = trim(strtolower($p_title));
        if (isset($this->m_cache[$this->m_table])) {
            foreach ($this->m_cache[$this->m_table] as $l_data) {
                $l_data_lower_title = $l_data['title_lower'] ?? strtolower(($l_data['title'] ?? $l_data[$this->m_table . '__title']));

                if ($l_data[$this->m_table . '__' . $this->m_cache_parent_tables[$this->m_table] . '__id'] == $p_parent_id && $l_data_lower_title === $l_title_lower && !in_array($l_data[$this->m_table . '__id'], $excludeIds)) {
                    return $l_data;
                }
            }
        }

        return false;
    }

    /**
     * Method for retrieving data from a dialog-table.
     *
     * @param int|string|null $p_id
     * @param string|null $p_title
     * @return false|mixed
     * @throws isys_exception_database
     */
    public function get_data($p_id = null, $p_title = null)
    {
        // This should never fail, but we want to go sure.
        if (!$this->m_cache[$this->m_table]) {
            $this->load();
        }

        if ($p_id !== null) {
            if (is_numeric($p_id)) {
                if (isset($this->m_cache[$this->m_table]) && isset($this->m_cache[$this->m_table][$p_id])) {
                    return $this->m_cache[$this->m_table][$p_id];
                }
            } else {
                // Find the entry by constant.
                foreach ($this->m_cache[$this->m_table] as $l_data) {
                    if (is_string($p_id) && is_string($l_data[$this->m_table . '__const']) && strtolower($l_data[$this->m_table . '__const']) == strtolower($p_id)) {
                        return $l_data;
                    }
                }
            }

            return false;
        }

        if ($p_title !== null) {
            $p_title = strtolower($p_title);

            if (is_array($this->m_cache[$this->m_table])) {
                foreach ($this->m_cache[$this->m_table] as $l_data) {
                    // @see  ID-4842  We use type safe comparison, because "1" !== "01"
                    if (strtolower($l_data[$this->m_table . '__title']) === $p_title) {
                        return $l_data;
                    }
                }
            }

            // If we can't find the given title, we return false.
            return false;
        }

        return $this->m_cache[$this->m_table];
    }

    /**
     * Method for retrieving the raw data from a dialog-table.
     *
     * @param int|null $p_id
     * @param string|null $p_title
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data_raw($p_id = null, $p_title = null)
    {
        // We cache the data, as soon as this class is instanced.
        return $this->get_dialog($this->m_table, $p_id, $p_title);
    }

    /**
     * Method for (re-)loading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @throws isys_exception_database
     */
    public function load()
    {
        if (!isset($this->m_cache[$this->m_table]) || !$this->m_cache[$this->m_table]) {
            // We cache the data, as soon as this class is instanced.
            $l_res = $this->get_dialog($this->m_table, null, null, 'DESC', null, $this->additionFieldsToCondition);
            $language = isys_application::instance()->container->get('language');

            while ($l_row = $l_res->get_row()) {
                $title = $language->get(trim($l_row[$this->m_table . '__title'] ?? ''));

                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']] = $l_row;
                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title'] = $title;
                $this->m_cache[$this->m_table][$l_row[$this->m_table . '__id']]['title_lower'] = strtolower($title);
            }
        }

        return $this;
    }

    /**
     * Method for resetting and reloading the dialog-data.
     *
     * @return  isys_cmdb_dao_dialog
     * @throws isys_exception_database
     */
    public function reset()
    {
        if ($this->m_table) {
            unset($this->m_cache[$this->m_table], $this->m_cache_parent_tables[$this->m_table]);

            $this->load();
        }

        return $this;
    }

    /**
     * Setter Method which sets the current table
     *
     * @param string $p_table
     * @return $this
     */
    public function set_table($p_table)
    {
        $this->m_table = $p_table;

        return $this;
    }

    /**
     * Getter method which retrieves the current table
     *
     * @return string
     */
    public function get_table()
    {
        return $this->m_table;
    }

    /**
     * Constructor.
     *
     * @param isys_component_database $p_db
     * @param string|null             $p_table
     * @throws isys_exception_database
     */
    public function __construct(isys_component_database $p_db, $p_table = null)
    {
        parent::__construct($p_db);

        if (is_string($p_table) && trim($p_table) !== '') {
            $this->m_table = $p_table;

            // Immediately load the dialog-data.
            $this->load();
        }
    }

    /**
     *
     * @param  string $value
     * @param  string $identifier
     * @param  array  $exclude
     *
     * @return boolean
     * @throws isys_exception_database
     */
    public function entryExists($value, $identifier = null, array $exclude = [])
    {
        $sql = 'SELECT COUNT(*) AS count
            FROM ' . $this->m_table . '
            WHERE ' . $this->m_table . '__title = ' . $this->convert_sql_text($value);

        if ($identifier !== null) {
            $sql .= ' AND ' . $this->m_table . '__identifier = ' . $this->convert_sql_text($identifier);
        }

        if (count($exclude)) {
            $sql .= ' AND ' . $this->m_table . '__id ' . $this->prepare_in_condition($exclude, true);
        }

        return (bool) $this->retrieve($sql . ';')->get_row_value('count');
    }

    /**
     * Appends data to the cache
     *
     * @param int         $id
     * @param array       $data
     * @param string|null $table
     * @return isys_cmdb_dao_dialog
     */
    public function appendToCache($id, array $data, $table = null)
    {
        $table = $table ?: $this->m_table;
        $this->m_cache[$table][$id] = $data;
        return $this;
    }
}
