<?php

/**
 * i-doit
 *
 * DAO for table template
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Niclas Potthast <npotthast@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_table extends isys_component_dao
{
    private $m_strFilter;

    /**
     * @param string $p_m_strFilter
     *
     * @return isys_component_dao_result
     */
    public function set_filter($p_strFilter)
    {
        $this->m_strFilter = $p_strFilter;
    }

    /**
     * Cleans the database of old temporary tables. Every temporary table
     * has to be deleted except the ones which are in use by logged in users.
     *
     * @return void
     * @throws isys_exception_dao
     */
    public function clean_temp_tables(): void
    {
        global $g_comp_database;

        $removableTableNames = array_diff(
            $g_comp_database->get_table_names("tempObjList_%"),
            $this->get_temp_table_names()
        );

        if (count($removableTableNames) === 0) {
            return;
        }

        $this->begin_update();

        foreach ($removableTableNames as $tableName) {
            $tableName = $this->convert_sql_text($tableName);

            if (!$this->update("DROP TABLE IF EXISTS {$tableName};")) {
                throw new isys_exception_dao("Could not delete temp table '{$tableName}'");
            }
        }

        if (!$this->apply_update()) {
            throw new isys_exception_dao("Could not commit transaction for deletion of temp tables!");
        }
    }

    /**
     * Delete temporary entries
     *
     * @param $p_SesID
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function clean_temp_tables_at_logout($p_SesID)
    {
        $l_strTempTable = isys_glob_get_obj_list_table_name(null, $p_SesID);

        $this->begin_update();

        // Delete own session entry in db
        $sessionId = $this->convert_sql_text($p_SesID);
        $l_strSQL = "DELETE FROM isys_user_session WHERE isys_user_session__php_sid = {$sessionId};";

        $l_bRet = $this->update($l_strSQL);

        if ($l_bRet) {
            $l_strSQL = "DROP TABLE IF EXISTS {$l_strTempTable};";

            $l_bRet = $this->update($l_strSQL);
        }

        if ($l_bRet) {
            $l_bRet = $this->apply_update();
        }

        return $l_bRet;
    }

    /**
     * @param string  $p_strTable table name
     * @param string  $p_strPKey  primary key name
     * @param array   $p_arFields table fields
     * @param integer $p_nStart   where to start the limitation ;)
     * @param integer $p_nLimit   limit result
     *
     * @return isys_component_dao_result
     */
    public function get_result($p_strTable, $p_strPKey, $p_arFields, $p_nStart = null, $p_nLimit = null)
    {
        $l_strSQL = "SELECT " . $p_strPKey . ", " . implode(", ", $p_arFields) . " FROM $p_strTable";

        if (strlen($this->m_strFilter) > 0) {
            $l_strSQL .= " WHERE ";
            foreach ($p_arFields as $l_key => $l_value) {
                $l_strSQL .= $l_key . " LIKE '%$l_value%'";
                $l_strSQL .= " OR ";
            }
            //remove last " OR " (4 chars)
            $l_strSQL = substr($l_strSQL, 0, strlen($l_strSQL) - 4);
        }

        $l_strSQL = isys_glob_sql_append_order($l_strSQL);

        if ($p_nStart >= 0 and $p_nLimit) {
            //limit query
            $l_strSQL .= " LIMIT " . $p_nStart . ", " . $p_nLimit;
        }

        $l_strSQL .= ";";

        return $this->retrieve($l_strSQL);
    }

    /**
     * Returns current session IDs.
     *
     * @return array
     * @throws isys_exception_database
     */
    private function get_temp_table_names(): array
    {
        $l_arCurrentSESIDs = [];

        $result = $this->retrieve('SELECT isys_user_session__php_sid AS sid FROM isys_user_session;');

        while ($row = $result->get_row()) {
            $l_arCurrentSESIDs[] = "tempObjList_" . $row['sid'];
        }

        return $l_arCurrentSESIDs;
    }
}
