<?php

/**
 * i-doit
 *
 * Import
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Set time limit to 12 hours.
set_time_limit(60 * 60 * 24);

// Head.
define('C__HEAD', 'head');

/**
 * Class isys_import
 */
abstract class isys_import
{
    const c__insert  = 1;
    const c__update  = 2;
    const c__replace = 3;

    private static $m_changed = false;

    /**
     * Start time of the import
     *
     * @var mixed|null
     */
    protected $m_import_start_time = null;

    /**
     * Contains arrays with 'dao' and 'messages' information.
     * @var array
     */
    protected array $validationErrors;

    /**
     * set $m_changed to false
     */
    public static function change_reset()
    {
        self::$m_changed = false;
    }

    /**
     * @return bool
     */
    public static function changed()
    {
        return self::$m_changed;
    }

    /**
     * Checks a dialog entry for its existence and creates a new one or returns
     * the identifier of the existing one.
     *
     * @param string $table
     * @param string $title
     * @param null   $checkName
     * @param null   $parentID
     * @param array  $customData
     * @param string $const
     *
     * @deprecated Use isys_cmdb_dao_dialog::check_dialog
     * @todo       remove this function!
     *
     * @return  integer  Returns null if no data could be found.
     */
    public static function check_dialog($table, $title, $checkName = null, $parentID = null, array $customData = [], string $const = '')
    {
        return isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'))
            ->set_table($table)
            ->load()
            ->check_dialog($table, $title, $checkName, $parentID, $customData, $const);
    }

    /**
     * Synchronizes a single field.
     *
     * @param string $p_old_value
     * @param string $p_new_value
     * @param string $p_fieldname    Used for logging
     * @param string $p_type         Filed type. Can be 'value' or 'dialog'. Defaults to 'value'.
     * @param string $p_dialog_table The name of the (optional) dialog-table.
     *
     * @return mixed  Old or new value otherwise null
     * @deprecated This method seems unused?
     */
    public static function field_sync($p_old_value, $p_new_value, $p_fieldname, $p_type = 'value', $p_dialog_table = null)
    {
        if ($p_old_value == $p_new_value || is_null($p_new_value)) {
            return $p_old_value;
        } else {
            isys_import_handler_cmdb::set_change(true);
            isys_import::synclog($p_old_value, $p_new_value, $p_fieldname);

            switch ($p_type) {
                case 'dialog':
                case 'dialog_plus':
                    if ($p_new_value) {
                        return isys_import::check_dialog($p_dialog_table, (string)$p_new_value);
                    } else {
                        return null;
                    }

                default:
                case 'value':
                    return $p_new_value;
            }
        }
    }

    /**
     * Logs a syncronization on data change.
     *
     * @param   string $p_oldval
     * @param   string $p_newval
     * @param   string $l_tag
     *
     * @return  boolean
     */
    public static function synclog($p_oldval, $p_newval, $l_tag)
    {
        if ($p_oldval != $p_newval) {
            if ($p_oldval == '') {
                isys_import_log::add('|- ADD: ' . $l_tag . ' | NV: ' . $p_newval);
            } else {
                isys_import_log::add('|- MERGE: ' . $l_tag . ' | CV: ' . $p_oldval . ' | NV: ' . $p_newval);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Method for parsing the import data
     *
     * @param   string $p_data
     *
     * @return  boolean
     */
    public function parse($p_data = null)
    {
        return true;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $messages
     *
     * @return void
     */
    protected function recordValidationError(isys_cmdb_dao_category $dao, array $messages): void
    {
        $this->validationErrors[] = ['dao' => $dao, 'messages' => $messages];
    }

    /**
     * @return array
     */
    public function getRecordedValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->validationErrors = [];
        $this->m_import_start_time = microtime(true);
        // Nothing to do here.
    }
}
