<?php
/**
 * i-doit - Updates.
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Logger;

define("C__SQL__FIELD", 1);
define("C__SQL__FOREIGN_KEY", 2);
define("C__SQL__REFERENCED_TABLE", 3);
define("C__SQL__REFERENCED_FIELD", 4);
define("C__SQL__CONSTRAINT", 5);

class isys_update_migration extends isys_update
{
    /**
     * @var  null
     */
    protected $m_referential_constraints = null;

    /**
     * Checks if a migration identified by $p_migration_identifier is already done.
     *
     * @param   string  $p_migration_identifier
     * @param   boolean $checkVersion
     *
     * @return  boolean
     */
    public function is_migration_done($p_migration_identifier, $checkVersion = false)
    {
        global $g_comp_database;

        $l_sql = "SELECT isys_migration__done
			FROM isys_migration
			WHERE isys_migration__title = '" . $g_comp_database->escape_string($p_migration_identifier) . "'
			AND isys_migration__done = 1";

        // This is if the migration has to be called in every version or only one time
        if ($checkVersion) {
            $l_sql .= " AND isys_migration__version = '" . $g_comp_database->escape_string($_SESSION["update_directory"]) . "';";
        }

        return $g_comp_database->num_rows($g_comp_database->query($l_sql)) > 0;
    }

    /**
     * Sets migration status to done for $p_migration_identifier.
     *
     * @param   string $p_migration_identifier
     *
     * @return  mixed
     */
    public function migration_done($p_migration_identifier)
    {
        global $g_comp_database;

        $g_comp_database->set_autocommit(true);

        $l_sql = "INSERT INTO isys_migration SET
			isys_migration__done = 1,
            isys_migration__executed_on = NOW(),
			isys_migration__version = '" . $g_comp_database->escape_string($_SESSION["update_directory"]) . "',
			isys_migration__title = '" . $g_comp_database->escape_string($p_migration_identifier) . "';";

        return $g_comp_database->query($l_sql);
    }

    public function migrate($p_path)
    {
        global $g_comp_database, $g_comp_database_system, $g_migration_log, $g_absdir, $g_product_info;

        $tenantDatabaseName = $g_comp_database_system->escape_string($g_comp_database->get_db_name());
        $query = $g_comp_database_system->query("SELECT isys_mandator__id FROM isys_mandator WHERE isys_mandator__db_name = '{$tenantDatabaseName}' LIMIT 1;");

        $tenantId = $g_comp_database->fetch_array($query)['isys_mandator__id'];

        $l_mig_executed = [];

        try {
            $logger = Logger::factory(
                'migration',
                $g_absdir . '/log/idoit_migration_' . $g_product_info['version'] . '.log',
                Logger::DEBUG // @todo Change this after i-doit 35 release to 'Level::Debug->value'.
            );

            $g_comp_database->query("SET FOREIGN_KEY_CHECKS = 0;");
            $g_comp_database_system->query("SET FOREIGN_KEY_CHECKS = 0;");

            $g_comp_database->set_autocommit(false);
            $g_comp_database_system->set_autocommit(false);

            if (is_dir($p_path)) {
                // Using glob() since it also sorts the files alphabetically.
                $l_files = glob(rtrim($p_path, "/\\") . '/*.php');
                foreach ($l_files as $l_file) {
                    $l_file = str_replace($p_path, '', $l_file);
                    if (strpos($l_file, ".") !== 0 && strpos($l_file, '.php') > 0) {
                        if (file_exists($p_path . $l_file) && !is_dir($p_path . $l_file)) {
                            $logger->debug("Starting migration: " . $p_path . $l_file);

                            $g_migration_log = [];

                            try {
                                include($p_path . $l_file);

                                $g_migration_log[] = "-";
                                $l_mig_executed[] = $g_migration_log;
                            } catch (Exception $e) {
                                $g_migration_log[] = "<span class=\"bold red indent\">" . $e->getMessage() . "</span>";
                                $l_mig_executed[] = $g_migration_log;
                            }
                        }
                    }
                }

                if (!class_exists('isys_module_licence')) {
                    $l_mig_executed[] = $this->removeAddOns($g_comp_database);
                }
            }

            if (is_array($g_migration_log)) {
                foreach ($g_migration_log as $message) {
                    $logger->debug(strip_tags($message));
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $l_mig_executed;
    }

    /**
     * @param isys_component_database $tenantDb
     *
     * @return void
     */
    private function removeAddOns(isys_component_database $tenantDb): array
    {
        global $g_absdir;

        $logEntries = [];

        // @see ID-10422 Remove non-open module entries from the database.
        $keepModuleEntries = [
            "'C__MODULE__AUTH'",
            "'C__MODULE__CMDB'",
            "'C__MODULE__DASHBOARD'",
            "'C__MODULE__DIALOG_ADMIN'",
            "'C__MODULE__EXPORT'",
            "'C__MODULE__IMPORT'",
            "'C__MODULE__LOGBOOK'",
            "'C__MODULE__MANAGER'",
            "'C__MODULE__MONITORING'",
            "'C__MODULE__MULTIEDIT'",
            "'C__MODULE__NOTIFICATIONS'",
            "'C__MODULE__REPORT'",
            "'C__MODULE__SEARCH'",
            "'C__MODULE__SYSTEM'",
            "'C__MODULE__SYSTEM_SETTINGS'",
            "'C__MODULE__TEMPLATES'",
            "'C__MODULE__TTS'",
            "'C__MODULE__USER_SETTINGS'",
            "'C__MODULE__CUSTOM_FIELDS'", // @see ID-10422 Remove?
            "'C__MODULE__QRCODE'", // @see ID-10422 Remove?
            "'C__MODULE__LDAP'", // @see ID-10422 Remove?
            "'C__MODULE__JDISC'", // @see ID-10422 Remove?
        ];

        $logEntries[] = '<span class="bold">Remove entries from database</span>';
        $moduleEntryList = implode(',', $keepModuleEntries);
        $tenantDb->query("DELETE FROM isys_module WHERE isys_module__const NOT IN ({$moduleEntryList});");
        $logEntries[] = '<span class="indent">Removed module entries</span>';
        // @see ID-10379
        $tenantDb->query("DELETE FROM isys_tts_type WHERE isys_tts_type__const IN ('C__TTS__ZAMMAD');");
        $logEntries[] = '<span class="indent">Removed TTS entry</span>';

        // @see ID-10422 Remove non-open module code.
        $removeFilesFromOpen = [
            // @see ID-10379
            '/src/classes/modules/cmdb/dao/category/global/isys_cmdb_dao_category_g_ldap_dn.class.php',
            '/src/classes/modules/cmdb/ui/global/isys_cmdb_ui_category_g_ldap_dn.class.php',
            '/src/classes/modules/ldap/isys_module_ldap_autoload.class.php',
            '/src/idoit/Console/Command/Ldap/SyncCommand.php',
            '/src/idoit/Console/Command/Ldap/SyncDistinguishedNamesCommand.php',
            '/src/idoit/Component/Helper/LdapUrlGenerator.php',
            '/src/classes/libraries/isys_library_ldap.class.php',
            '/src/classes/libraries/ldapi/ldapi.class.php',
            '/src/classes/libraries/ldapi/ldapi_acc.class.php',
            '/src/themes/default/smarty/templates/content/bottom/content/catg__ldap_dn.tpl',
            '/src/themes/default/smarty/templates/content/bottom/content/module__ldap.tpl',
            '/src/classes/libraries/ldapi',
            '/src/idoit/Console/Command/Ldap',
            '/src/classes/modules/ldap',
            // @see ID-10380
            '/src/classes/modules/jdisc',
            // @see ID-10381
            '/src/classes/connector/ticketing/isys_connector_ticketing_zammad.class.php',
            // @see ID-10422
            '/admin/templates/pages/modules.tpl',
            '/src/classes/modules/console/src/Console/Command/Addon',
            '/src/classes/modules/console/src/Steps/Addon',
        ];

        $filesystem = new Symfony\Component\Filesystem\Filesystem();
        $rootPath = rtrim(str_replace('\\', '/', $g_absdir), '/');

        $logEntries[] = '<span class="bold">Remove module files</span>';

        foreach ($removeFilesFromOpen as $filePathname) {
            if (! file_exists($rootPath . $filePathname)) {
                continue;
            }

            $logEntries[] = '<span class="indent">Remove "' . $filePathname . '"</span>';
            $filesystem->remove($rootPath . $filePathname);
        }

        return $logEntries;
    }

    /**
     * Returns the corresponding foreign key name for field $p_field of table $p_table
     *
     * @param string $p_table
     * @param string $p_field
     *
     * @return string
     */
    public function get_foreign_key($p_table, $p_field = null, $p_checkTableExists = true)
    {
        global $g_comp_database;

        $l_exec = false;

        if ($p_checkTableExists) {
            $l_check = $g_comp_database->query("SHOW TABLES LIKE '" . $p_table . "';");
            if ($g_comp_database->num_rows($l_check) > 0) {
                $l_exec = true;
            }
        } else {
            $l_exec = true;
        }

        if ($l_exec) {
            $l_query = $g_comp_database->query("SHOW CREATE TABLE " . $p_table);
            $l_create = $g_comp_database->fetch_array($l_query);

            $l_sql = $l_create["Create Table"];
            $l_parsed = $this->parse_sql($l_sql);

            if ($p_field) {
                if (isset($l_parsed[$p_field])) {
                    return $l_parsed[$p_field][C__SQL__FOREIGN_KEY];
                }
            } else {
                return $l_parsed;
            }
        }

        return false;
    }

    public function delete_foreign_key($p_table, $p_fk)
    {
        global $g_comp_database;

        $l_update = "ALTER TABLE " . $p_table . " DROP FOREIGN KEY " . $p_fk;

        return $g_comp_database->query($l_update);
    }

    public function add_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate)
    {
        global $g_comp_database;

        $l_update = "ALTER TABLE " . $p_table . " " . "ADD FOREIGN KEY ( `" . $p_field . "` ) " . "REFERENCES `" . $p_refTable . "` (`" . $p_refField . "`) ON DELETE " .
            $p_onDelete . " ON UPDATE " . $p_onUpdate;
        $g_comp_database->query($l_update);
    }

    public function reinit_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate)
    {
        global $g_comp_database;

        $l_check = $g_comp_database->query("SHOW TABLES LIKE '" . $p_table . "'");
        if ($g_comp_database->num_rows($l_check) < 1) {
            return false;
        }

        while ($l_fk = $this->get_foreign_key($p_table, $p_field, false)) {
            $this->delete_foreign_key($p_table, $l_fk);
        }

        return $this->add_foreign_key($p_table, $p_field, $p_refTable, $p_refField, $p_onDelete, $p_onUpdate);
    }

    /**
     * Parses an sql string for CONSTRAINTS
     *
     * @param string $p_sql
     *
     * @return array
     */
    public function parse_sql($p_sql)
    {
        $l_fields = [];
        $l_query = explode("\n", $p_sql);
        $l_identifier = "[a-z0-9-_]+";

        $l_match = "/^.*CONSTRAINT[\s]*`([\s]*{$l_identifier}[\s]*)`[\s]*" . "FOREIGN KEY[\s]*\(`({$l_identifier})`\)[\s]*" .
            "REFERENCES[\s]*`({$l_identifier})`[\s]*\(`({$l_identifier})`\)(.*?)$/i";

        foreach ($l_query as $l_qline) {
            $l_register = [];

            if (preg_match($l_match, $l_qline, $l_register)) {
                if (preg_match(
                    "/.*?ON (DELETE|UPDATE)[\s]*(CASCADE|SET NULL|NO ACTION)[\s]*(?:ON (DELETE|UPDATE)[\s]*(CASCADE|SET NULL|NO ACTION))?/i",
                    $l_qline,
                    $l_constraint
                )) {
                    $l_constraint = [
                        $l_constraint[1] => $l_constraint[2],
                        $l_constraint[3] => $l_constraint[4],
                    ];
                } else {
                    $l_constraint = false;
                }

                $l_fields[$l_register[2]] = [
                    C__SQL__FOREIGN_KEY      => $l_register[1],
                    C__SQL__FIELD            => $l_register[2],
                    C__SQL__REFERENCED_TABLE => $l_register[3],
                    C__SQL__REFERENCED_FIELD => $l_register[4],
                    C__SQL__CONSTRAINT       => $l_constraint
                ];
            }
        }

        return $l_fields;
    }
}
