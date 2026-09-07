<?php

use idoit\AddOn\AuthableInterface;
use idoit\AddOn\RoutingAwareInterface;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Context\Context;
use idoit\Module\Manager\Addon\BundleInstaller;
use Symfony\Component\Filesystem\Filesystem;

/**
 * i-doit
 *
 * Module manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_manager extends isys_module implements isys_module_interface
{
    const ADDON_INSTALL_INSTRUCTIONS_FILE = 'addon_install.txt';

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    // Define, if this module shall be displayed in the system-settings.
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the extras menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    private static $m_modules_loaded = false;

    /**
     * Array with modules which are on trial
     *
     * @var array
     */
    private static $m_trials = [];

    /**
     * Array with module register entries.
     *
     * @var  isys_module_register[]
     */
    protected $m_installed = [];

    /**
     * ID of active module.
     *
     * @var  integer
     */
    private $m_activemod;

    /**
     * Array of initialized modules (init.php)
     *
     * @var array
     */
    private $m_initialized = [];

    /**
     * Array with module register entries.
     *
     * @var  isys_module_register[]
     */
    private $m_modules;

    /**
     * Cache of isys_module database table
     *
     * @var array
     */
    private $m_modules_cache = [];

    /**
     * Module request.
     *
     * @var  isys_module_request
     */
    private $m_userrequest;

    /**
     * Module auth instances
     *
     * @var array
     */
    private $m_module_auth = [];

    /**
     * @var isys_component_database
     */
    protected $db = null;

    private isys_cmdb_dao $dao;

    /**
     * @return isys_module_request
     */
    public function get_request()
    {
        return $this->m_userrequest;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return self::$m_modules_loaded;
    }

    /**
     * Initializes the module manager
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_manager
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req)) {
            isys_component_signalcollection::get_instance()
                ->connect('system.afterChange', [
                    'isys_core',
                    'post_system_has_changed'
                ]);

            $this->m_userrequest = $p_req;
        }

        if ($this->db == null) {
            $this->db = isys_application::instance()->container->get('database');
        }

        /**
         * Call module loader
         */
        $this->module_loader();

        return $this;
    }

    /**
     * Return initialized modules
     */
    public function get_initialized_modules()
    {
        return $this->m_initialized;
    }

    /**
     * @param $p_module_identifier
     *
     * @return bool
     */
    public static function is_trial($p_module_identifier)
    {
        if (isset(self::$m_trials[$p_module_identifier])) {
            return self::$m_trials[$p_module_identifier];
        }

        return false;
    }

    /**
     * Uninstall add-on by identifier.
     *
     * @param       $p_identifier
     * @param array $p_mandatorDBs
     * @param array $errorMessages
     *
     * @return bool
     * @throws Exception
     */
    public function uninstallAddOn($p_identifier, array $p_mandatorDBs, array &$errorMessages)
    {
        // @see ID-8699
        require_once __DIR__ . '/../report/init.php';

        /* Get dao instance */
        $l_log = isys_log::get_instance($p_identifier . '-uninstall');
        $filesystem = new Filesystem();

        try {
            $l_log->notice('Uninstalling ' . $p_identifier);
            $l_log->set_auto_flush(true);

            $l_module_dir = dirname(__DIR__) . '/';
            $l_path = $l_module_dir . $p_identifier . '/';
            $l_moduleTitle = null;

            if (file_exists($l_path) && file_exists($l_path . 'package.json')) {
                /**
                 * Parse package.json
                 */
                $l_package = json_decode(file_get_contents($l_path . 'package.json'), true);

                if ($l_package) {
                    if (isset($l_package['type'])) {
                        if ($l_package['type'] == 'addon') {
                            global $g_comp_database;
                            $l_log->notice(sprintf('package.json initialized'));

                            /**
                             * Uninstall in current mandator only if we received an empty array
                             */
                            if (count($p_mandatorDBs) === 0) {
                                $p_mandatorDBs[] = $this->db;
                            }

                            // Keep the current Database component
                            $currentDB = ($g_comp_database ?: isys_application::instance()->container->get('database'));

                            /**
                             * Call uninstall method and drop tables in all mandators
                             */
                            foreach ($p_mandatorDBs as $l_mandatorDB) {
                                // overwrite database component in container
                                isys_application::instance()->container->get('database')->setDatabase($l_mandatorDB);
                                // @todo: this must be removed soon. Because we already set database above for isys_application
                                $g_comp_database = $l_mandatorDB;
                                $mandatorModuleManager = new self($l_mandatorDB);
                                $l_module = $mandatorModuleManager
                                    ->get_modules(null, null, null, " AND isys_module__identifier = '" . $l_mandatorDB->escape_string($p_identifier) . "'")
                                    ->get_row();

                                if ($l_module && $l_module['isys_module__id'] >= 1010) {
                                    $moduleClassName = 'isys_module_' . $p_identifier;

                                    // Call custom uninstall method of module
                                    if (class_exists($moduleClassName)) {
                                        $l_log->notice(sprintf(
                                            'Calling uninstall method for mandator %s in %s',
                                            $l_mandatorDB->get_db_name(),
                                            'isys_module_' . $p_identifier
                                        ));

                                        if (is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                                            $moduleClassName::uninstall($l_mandatorDB);
                                            $l_log->notice(' > Done!');
                                        } elseif (is_callable([$moduleClassName, 'uninstall'])) {
                                            // @todo: this must be removed soon. we should send db object here
                                            call_user_func([$moduleClassName, 'uninstall']);
                                            $l_log->debug(' > Done!');
                                        } else {
                                            $l_log->debug(' > Uninstall method does not exist.');
                                        }
                                    }

                                    $l_dao = isys_component_dao::instance($l_mandatorDB);
                                    $l_dao->begin_update();
                                    $l_dao->update('SET FOREIGN_KEY_CHECKS = 0;');

                                    /* Drop sql tables */
                                    if (isset($l_package['sql-tables']) && is_array($l_package['sql-tables'])) {
                                        $l_log->notice(sprintf('Dropping %d tables in mandator database %s..', count($l_package['sql-tables']), $l_mandatorDB->get_db_name()));

                                        foreach ($l_package['sql-tables'] as $l_table) {
                                            if (count($l_dao->retrieve('SHOW TABLES LIKE ' . $l_dao->convert_sql_text($l_table) . ';')) === 0) {
                                                continue;
                                            }

                                            $l_log->notice(sprintf('Attempting to truncate and drop %s.', $l_table));

                                            try {
                                                $l_dao->update("TRUNCATE TABLE {$l_table};") && $l_dao->apply_update();
                                                $l_log->notice("> {$l_table} truncated.");
                                            } catch (Throwable $e) {
                                                $l_log->warning("> {$l_table} could not be truncated.");
                                                $l_log->warning($e->getMessage());
                                            }

                                            try {
                                                $l_dao->update("DROP TABLE {$l_table};") && $l_dao->apply_update();
                                                $l_log->notice("> {$l_table} dropped.");
                                            } catch (Throwable $e) {
                                                $l_log->warning("> {$l_table} could not be dropped.");
                                                $l_log->warning($e->getMessage());
                                            }
                                        }
                                    } else {
                                        $l_log->notice('No sql-tables array found in package.json. Skipping standardized table drop.');
                                    }

                                    // Delete module entry.
                                    if ($mandatorModuleManager->delete($p_identifier)) {
                                        $l_log->notice('Uninstall was successfully for mandator db ' . $l_mandatorDB->get_db_name() . '.');
                                        if ($l_moduleTitle === null) {
                                            $l_moduleTitle = isys_application::instance()->container->get('language')
                                                ->get($l_module['isys_module__title']);
                                        }
                                    } else {
                                        throw new Exception("Could not delete module with identifier: " . $p_identifier . "<br />" .
                                            $l_mandatorDB->get_last_error_as_string());
                                    }

                                    $l_dao->apply_update();
                                } else {
                                    $errorMessage = 'Module ' . $p_identifier . ' not found in mandator db ' . $l_mandatorDB->get_db_name() .
                                        '. Skipped uninstall for mandator db ' . $l_mandatorDB->get_db_name() . '.';
                                    $l_log->error($errorMessage);
                                    $errorMessages[] = $errorMessage;
                                }
                            }

                            // Turn back global database
                            if ($currentDB instanceof isys_component_database_proxy && $currentDB->getDatabase() !== null) {
                                // @see ID-9614 Do not set the proxy itself, this will end in recursion!
                                isys_application::instance()->container->get('database')->setDatabase($currentDB->getDatabase());
                            } else {
                                isys_application::instance()->container->get('database')->setDatabase($currentDB);
                            }

                            // @todo this must be removed if we don´t need the global variable anymore
                            $g_comp_database = $currentDB;

                            // @see ID-11744 Simply delete add-on directory.
                            try {
                                $filesystem->remove(rtrim($l_path, '/'));
                                $l_log->notice("Deleted add-on directory {$l_path}");
                            } catch (\Throwable $exception) {
                                $l_log->error("Could not delete {$l_path}: {$exception->getMessage()}");
                            }

                            // Call system has changed post notification.
                            $this->afterChange();

                            return ($l_moduleTitle !== null) ? $l_moduleTitle : $p_identifier;
                        } else {
                            $errorMessage = 'Could not delete module. Only addon modules can be uninstalled.';
                            $l_log->warning($errorMessage);
                            $errorMessages[] = $errorMessage;
                        }
                    } else {
                        throw new Exception('Could not delete module: package.json structure invalid.');
                    }
                }
            } else {
                $errorMessage = 'package.json for module ' . $p_identifier . ' not found. Module was not successfully uninstalled.';
                $l_log->warning($errorMessage);
                $errorMessages[] = $errorMessage;
            }

            return false;
        } catch (Exception $e) {
            /* Cancel transaction */
            if (isset($l_dao) && is_object($l_dao)) {
                $l_dao->cancel_update();
            }

            $errorMessage = 'Error while uninstalling module ' . $p_identifier . ':' . $e->getMessage();
            $l_log->error($errorMessage);
            $errorMessages[] = $errorMessage;

            throw $e;
        }
    }

    /**
     * @param string $p_identifier
     *
     * @return mysqli_result
     */
    public function delete($p_identifier)
    {
        $l_sql = "DELETE FROM isys_module WHERE isys_module__identifier = " . $this->dao->convert_sql_text($p_identifier) . ";";

        return $this->db->query($l_sql);
    }

    /**
     * Install module to database ($p_package = package.json content).
     *
     * @param array $p_package
     * @param bool  $clearTempDir
     *
     * @return false|int
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function installAddOn(array $p_package, bool $clearTempDir = true)
    {
        if (is_array($p_package) && isset($p_package['identifier'])) {
            $l_dao = isys_component_dao::instance($this->db);

            if (strstr(' ', $p_package['identifier'])) {
                throw new isys_exception_general('Wrong module identifier in package.json. Spaces not allowed.');
            }

            if (isset($p_package['icon']) && is_scalar($p_package['icon'])) {
                $l_icon = $p_package['icon'];
            } else {
                if (isset($p_package['icons']['16'])) {
                    $l_icon = $p_package['icons']['16'];
                } else {
                    $l_icon = '';
                }
            }
            $l_id = $this->is_installed($p_package['identifier']);

            if (!$l_id) {
                $l_sql = 'INSERT INTO isys_module SET
                    isys_module__title = ' . $l_dao->convert_sql_text(isset($p_package['name']) ? $p_package['name'] : $p_package['title']) . ',
                    isys_module__identifier = ' . $l_dao->convert_sql_text($p_package['identifier']) . ',
                    isys_module__icon = ' . $l_dao->convert_sql_text($l_icon) . ',
                    isys_module__const = ' . $l_dao->convert_sql_text('C__MODULE__' . strtoupper($p_package['identifier'])) . ',
                    isys_module__persistent = ' . $l_dao->convert_sql_boolean(isset($p_package['persistent']) ? $p_package['persistent'] : '1') . ',
                    isys_module__class = ' . $l_dao->convert_sql_text('isys_module_' . $p_package['identifier']) . ',
                    isys_module__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ',
                    isys_module__date_install = NOW()';

                if ($l_dao->update($l_sql)) {
                    $l_id = $l_dao->get_last_insert_id();
                    $l_parent_module_id = null;

                    if (isset($p_package['parent']) && !empty($p_package['parent'])) {
                        $l_parent_module_res = $this->get_modules(null, $p_package['parent']);
                        if ($l_parent_module_res->num_rows() > 0) {
                            $l_parent_module = $l_parent_module_res->get_row();
                            $l_parent_module_id = $l_parent_module['isys_module__id'];
                        }
                    }
                    $this->set_parent_module($l_id, $l_parent_module_id);
                }
            } else {
                $l_sql = 'UPDATE isys_module SET
                    isys_module__title = ' . $l_dao->convert_sql_text(isset($p_package['name']) ? $p_package['name'] : $p_package['title']) . ',
                    isys_module__identifier = ' . $l_dao->convert_sql_text($p_package['identifier']) . ',
                    isys_module__const = ' . $l_dao->convert_sql_text('C__MODULE__' . strtoupper($p_package['identifier'])) . ',
                    isys_module__icon = ' . $l_dao->convert_sql_text($l_icon) . ',
                    isys_module__date_install = NOW(),
                    isys_module__persistent = ' . $l_dao->convert_sql_boolean(isset($p_package['persistent']) ? $p_package['persistent'] : '1') . ',
                    isys_module__class = ' . $l_dao->convert_sql_text('isys_module_' . $p_package['identifier']) . ',
                    isys_module__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
                    WHERE isys_module__id = ' . $l_dao->convert_sql_id($l_id) . ';';

                if ($l_dao->update($l_sql)) {
                    $l_parent_module_id = null;

                    if (isset($p_package['parent']) && !empty($p_package['parent'])) {
                        $l_parent_module_res = $this->get_modules(null, $p_package['parent']);
                        if ($l_parent_module_res->num_rows() > 0) {
                            $l_parent_module = $l_parent_module_res->get_row();
                            $l_parent_module_id = $l_parent_module['isys_module__id'];
                        }
                    }
                    $this->set_parent_module($l_id, $l_parent_module_id);
                }
            }

            // Call system has changed post notification.
            $this->afterChange($clearTempDir);

            return $l_id;
        }

        return false;
    }

    /**
     * Sets the parent module for the current module.
     *
     * @param   $p_child_module_id
     * @param   $p_parent_module_id
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_parent_module($p_child_module_id, $p_parent_module_id)
    {
        $l_sql = 'UPDATE isys_module
            SET isys_module__parent = ' . $this->dao->convert_sql_id($p_parent_module_id) . '
            WHERE isys_module__id = ' . $this->dao->convert_sql_id($p_child_module_id) . ';';

        return (bool) $this->db->query($l_sql);
    }

    /**
     * Activate a add-on.
     *
     * @param string $identifier
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function activateAddOn($identifier)
    {
        $l_sql = "UPDATE isys_module
			SET isys_module__status = " . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE isys_module__identifier = " . $this->dao->convert_sql_text($identifier) . ";";

        if ($this->dao->update($l_sql) && $this->dao->apply_update()) {
            $moduleClassName = 'isys_module_' . $identifier;
            if (class_exists($moduleClassName)) {
                if (is_a($moduleClassName, 'idoit\AddOn\ActivatableInterface', true)) {
                    $moduleClassName::activate($this->db);
                } elseif (method_exists($moduleClassName, 'activate')) {
                    // @see ID-9263 Check if the method exists, before trying to call it.
                    global $g_comp_database;

                    $g_comp_database = $this->db;
                    call_user_func("{$moduleClassName}::activate", $identifier);
                    $g_comp_database = isys_application::instance()->container->get('database');
                }
            }

            // Call system has changed post notification.
            $this->afterChange();

            return true;
        }

        return false;
    }

    /**
     * Deactivate a add-on.
     *
     * @param string $identifier
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function deactivateAddOn($identifier): bool
    {
        $l_sql = "UPDATE isys_module
			SET isys_module__status = " . $this->dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . "
			WHERE isys_module__identifier = " . $this->dao->convert_sql_text($identifier) . ";";

        if ($this->dao->update($l_sql) && $this->dao->apply_update()) {
            $moduleClassName = "isys_module_{$identifier}";

            if (class_exists($moduleClassName)) {
                if (is_a($moduleClassName, 'idoit\AddOn\ActivatableInterface', true)) {
                    $moduleClassName::deactivate($this->db);
                } elseif (method_exists($moduleClassName, 'deactivate')) {
                    // @see ID-9263 Check if the method exists, before trying to call it.
                    global $g_comp_database;

                    $g_comp_database = $this->db;

                    call_user_func("{$moduleClassName}::deactivate", $identifier);
                    $g_comp_database = isys_application::instance()->container->get('database');
                }
            }

            // Call system has changed post notification.
            $this->afterChange();

            return true;
        }

        return false;
    }

    /**
     * Checks wheather a module is installed or not, should return the module id.
     *
     * @param string $p_identifier
     * @param bool   $p_and_active
     *
     * @return int|bool
     */
    public function is_installed($p_identifier = null, $p_and_active = false)
    {
        if ($p_identifier) {
            if (!is_countable($this->m_installed) || !count($this->m_installed)) {
                $this->get_installed_modules();
            }

            if ($this->m_installed) {
                if (isset($this->m_installed[$p_identifier])) {
                    if ($p_and_active) {
                        if (!$this->m_installed[$p_identifier]['active']) {
                            return false;
                        }
                    }

                    return $this->m_installed[$p_identifier]['id'];
                }
            } else {
                // fallback
                // @todo my remove this in future (1.8)?
                if (!is_object($this->db)) {
                    return false;
                }

                $l_sql = 'SELECT isys_module__id AS id
                    FROM isys_module
                    WHERE isys_module__identifier = ' . $this->dao->convert_sql_text($p_identifier);

                if ($p_and_active) {
                    $l_sql .= ' AND isys_module__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
                }

                $l_id = $this->dao->retrieve($l_sql . ';')->get_row_value('id');

                return $l_id ? $l_id : false;
            }
        }

        return false;
    }

    /**
     * @param   string $p_identifier
     *
     * @return  bool
     */
    public function is_active($p_identifier)
    {
        return (bool)$this->is_installed($p_identifier, true);
    }

    /**
     * @desc Starts module process
     */
    public function start()
    {
        ;
    }

    /**
     * Get single isys_modules row
     *
     * @param $p_id
     *
     * @return mixed
     */
    public function get_module_by_id($p_id)
    {
        return $this->m_modules_cache[$p_id];
    }

    /**
     * Get single isys_modules row
     *
     * @param $p_identifier
     *
     * @return mixed
     */
    public function get_module_by_identifier($p_identifier)
    {
        if (isset($this->m_installed[$p_identifier]['id'])) {
            return $this->m_modules_cache[$this->m_installed[$p_identifier]['id']];
        }

        return null;
    }

    /**
     * Method for retrieving rows from "isys_module".
     *
     * @param   integer $id
     * @param   string  $constant
     * @param   bool $active
     * @param   string  $condition
     *
     * @throws  isys_exception_database
     * @return  isys_component_dao_result
     */
    public function get_modules($id = null, $constant = null, $active = null, $condition = "")
    {
        if (!is_object($this->db)) {
            throw new isys_exception_database("Error. Database component not loaded.", [], 0, true);
        }

        $sql = 'SELECT isys_module__id AS id, t_mod.*
            FROM isys_module AS t_mod
            WHERE TRUE';

        if ($id != null) {
            $sql .= ' AND isys_module__id =  ' . $this->dao->convert_sql_id($id);
        }

        if ($constant != null) {
            $sql .= ' AND isys_module__const = ' . $this->dao->convert_sql_text($constant);
        }

        if ($active) {
            $sql .= ' AND isys_module__status = ' . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
        }

        return $this->dao->retrieve($sql . ' ' . $condition . ' ORDER BY isys_module__title ASC;');
    }

    /**
     * Enumerates all available modules by querying the module table.
     *
     * @param   boolean $p_include_inactive
     *
     * @throws  isys_exception_general
     * @return  integer
     */
    public function enum($p_include_inactive = false)
    {
        $l_enumerated = 0;
        $l_res = $this->get_modules();

        if ($l_res && $l_res->num_rows() > 0) {
            while ($l_row = $l_res->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY)) {
                $l_mod_id = $l_row["id"];

                if ($p_include_inactive || $l_row['isys_module__status'] == C__RECORD_STATUS__NORMAL) {
                    if (!$this->register($l_mod_id, $l_row)) {
                        throw new isys_exception_general("Could not register module $l_mod_id: " . var_export($l_row, true));
                    }

                    $l_enumerated++;
                }
            }
        }

        return $l_enumerated;
    }

    /**
     * Function to initialize modules in specified directory
     *
     * @param string $p_directory
     */
    private function load_modules_in_directory($p_directory = 'src/classes/modules/')
    {
        if ($l_dirhandle = opendir($p_directory)) {
            while (($l_file = readdir($l_dirhandle)) !== false) {
                if (is_dir($p_directory . $l_file) && strpos($l_file, '.') !== 0) {
                    try {
                        if (file_exists($p_directory . $l_file . '/init.php')) {
                            include_once($p_directory . $l_file . '/init.php');

                            $this->m_initialized[str_replace($p_directory, '', $l_file)] = $p_directory . $l_file . '/init.php';
                        }


                    } catch (isys_exception_database $e) {
                        ;
                    } catch (Exception $e) {
                        $GLOBALS['g_error'] .= $e->getMessage() . "\n";
                        isys_application::instance()->container->get('logger')->critical($e->getMessage());
                    }
                }
            }

            closedir($l_dirhandle);

            if (!self::$m_modules_loaded) {
                isys_component_signalcollection::get_instance()->emit('cmdb.modules-loaded');
            }
        }
    }

    /**
     * Calls a slot registration method for every persistent module
     *
     * @param bool $forceReload
     *
     * @return $this
     */
    public function module_loader(bool $forceReload = false)
    {
        try {
            // In some specific cases (for example "API") the licence info was not yet set inside the session, so we might need to re-run this code to.
            if (self::$m_modules_loaded && !$forceReload) {
                return $this;
            }

            // @see ID-8917 Move this logic to a separate method so we can run it multiple times without causing other side-effects.
            $this->processAddonLicenses();
            $this->get_installed_modules();

            // Initialize modules.
            $this->load_modules_in_directory(isys_application::instance()->app_path . '/src/classes/modules/');

            // Also check for to-be-initialized composer modules
            if (file_exists(isys_application::instance()->app_path . '/vendor/synetics/')) {
                $this->load_modules_in_directory(isys_application::instance()->app_path . '/vendor/synetics/');
            }

            // Register routing of all modules after including all init.php files
            $this->registerRouting();

            $l_modules = $this->get_modules(null, null, null, " AND isys_module__persistent = 1 AND isys_module__status = " . (int)C__RECORD_STATUS__NORMAL);

            while ($l_row = $l_modules->get_row()) {
                $className = $l_row["isys_module__class"];

                if (class_exists($className)) {
                    // Register module.
                    $this->register($l_row['isys_module__id'], $l_row);

                    // Call initslots method.
                    if (method_exists($className, 'initslots')) {
                        call_user_func([new $className(), 'initslots']);
                    }
                }
            }
        } catch (isys_exception_database $e) {
            ;
        } catch (Exception $e) {
            isys_notify::debug($e->getMessage());
        }

        self::$m_modules_loaded = true;

        return $this;
    }

    /**
     * @return void
     */
    public function registerRouting(): void
    {
        // Initialize Routes
        foreach ($this->installedModules() as $module) {
            if (class_exists($module['class']) && is_subclass_of($module['class'], RoutingAwareInterface::class)) {
                $module['class']::registerRouting();
            }
        }
    }

    /**
     * @return void
     *             @see ID-8917
     */
    public function processAddonLicenses()
    {
        if (!isset($_SESSION['licensed_addons']) || !is_array($_SESSION['licensed_addons']) || !count($_SESSION['licensed_addons'])) {
            return;
        }
        $setAddonAsLicensed = Context::instance()->getOrigin() === Context::ORIGIN_CONSOLE && FeatureManager::isCloud();

        $setAddonAsLicensed = Context::instance()->getOrigin() === Context::ORIGIN_CONSOLE && FeatureManager::isCloud();

        // Set licence info for licenced modules.
        foreach ($_SESSION['licensed_addons'] as $identifier => $addon) {
            $moduleClassName = strtolower('isys_module_' . $identifier);
            if (class_exists($moduleClassName)) {
                if (is_a($moduleClassName, 'idoit\AddOn\LicensableInterface', true)) {
                    /** @var $moduleClassName idoit\AddOn\LicensableInterface */
                    $moduleClassName::setLicensed($addon['licensed'] || $setAddonAsLicensed);
                } else {
                    /** @var $moduleClassName isys_module */
                    $moduleClassName::set_licenced($addon['licensed'] || $setAddonAsLicensed);
                }
            }
        }
    }

    /**
     * Registers a module
     *
     * @param integer $p_id
     * @param array   $p_data
     *
     * @return boolean
     */
    private function register($p_id, $p_data)
    {
        // Create a module register entry.
        if (!is_value_in_constants($p_id, ['C__MODULE__MANAGER'])) {
            $l_regobj = new isys_module_register($p_id, $p_data, $this);
        } else {
            // If the module to be registered is the module manager, add $this to the register.
            $l_regobj = new isys_module_register($p_id, $p_data, $this, true, $this);
        }

        // Append to register list.
        $this->m_modules[$p_id] = &$l_regobj;

        // Query register entry in order to create the object and pre-initialize the module.
        try {
            if ($this->m_userrequest instanceof isys_module_request) {
                if ($this->m_modules[$p_id]->make_object($this->m_userrequest) == null) {
                    return false;
                }
            } else {
                return false;
            }
        } catch (isys_exception_general $e) {
            if (intval($_GET[C__GET__MODULE_ID]) != defined_or_default('C__MODULE__MANAGER')) {
                isys_application::instance()->container->get('notify')->error($e->getMessage());
            }
            return false;
        }

        return true;
    }

    /**
     * Unregisters a module
     *
     * @param $p_id
     *
     * @return bool
     */
    public function unregister($p_id)
    {
        if (array_key_exists($p_id, $this->m_modules)) {
            $l_regobj = $this->m_modules[$p_id];

            unset($l_regobj);
            unset($this->m_modules[$p_id]);

            return true;
        }

        return false;
    }

    /**
     * Get all modules
     *
     * @return array|isys_module_register
     */
    public function modules()
    {
        return $this->m_modules;
    }

    /**
     * @return array
     */
    private function installedModules()
    {
        if (empty($this->m_modules_cache)) {
            // load modules
            $this->get_installed_modules();
        }
        return $this->m_modules_cache;
    }

    /**
     * Get module by id
     *
     * @param $p_id
     *
     * @return mixed
     * @throws isys_exception_general
     */
    public function get_by_id($p_id)
    {
        if (!isset($this->m_modules[$p_id])) {
            $l_res = $this->get_modules($p_id);

            if ($l_res && $l_res->num_rows() > 0) {
                $l_row = $l_res->get_row(IDOIT_C__DAO_RESULT_TYPE_ARRAY);
                $l_mod_id = $l_row["id"];

                if ($l_row['isys_module__status'] == C__RECORD_STATUS__NORMAL) {
                    if (!$this->register($l_mod_id, $l_row)) {
                        throw new isys_exception_general("Could not register module " . isys_application::instance()->container->get('language')
                                ->get($l_row['isys_module__title']) . ": " . var_export($l_row, true));
                    }
                } else {
                    throw new isys_exception_general('Module ' . isys_application::instance()->container->get('language')
                            ->get($l_row['isys_module__title']) . ' is deactivated.');
                }
            }
        }

        return @$this->m_modules[$p_id];
    }

    /**
     * @return  integer
     */
    public function count()
    {
        return count($this->m_modules);
    }

    /**
     * Module loader.
     *
     * @param   integer       $p_id
     * @param   isys_register $p_request
     *
     * @return  isys_module
     *
     * @throws  isys_exception_general
     * @throws  Exception|isys_exception_cmdb
     */
    public function load($p_id, $p_request = null)
    {
        if (!is_numeric($p_id)) {
            throw new isys_exception_general("Could not load module $p_id : Invalid arguments for module loader!");
        }

        /**
         * @var isys_module_register $l_modentry
         */
        $l_modentry = $this->get_by_id($p_id);

        if (!$l_modentry) {
            throw new isys_exception_general("Could not load module $p_id : Couldn't get module entry!");
        }

        $this->m_activemod = $p_id;
        if (!method_exists($l_modentry, 'get_object')) {
            return $this;
        }

        /**
         * @var isys_module $l_modobj
         */
        $l_modobj = $l_modentry->get_object();

        if (!is_object($l_modobj)) {
            throw new isys_exception_general("Could not load module " . $l_modentry->get_identifier() . ": Module does not exist!");
        }

        // Check wheather module is licenced as a trial version or not.
        if ((is_a($l_modobj, 'idoit\AddOn\LicensableInterface') && !$l_modobj::isLicensed()) || !$l_modobj->is_licenced()) {
            if ($this->is_trial($l_modentry->get_data('isys_module__identifier'))) {
                $l_modobj->start_trial($l_modentry, self::$m_trials[$l_modentry->get_data('isys_module__identifier')]);
            }
        }

        // Emitting module load event.
        isys_component_signalcollection::get_instance()
            ->emit("mod.manager.onBeforeLoad", $l_modobj);

        $l_modobj->start($p_request);

        // Emitting module loaded event.
        isys_component_signalcollection::get_instance()
            ->emit("mod.manager.onAfterLoad", $l_modobj);

        return $l_modobj;
    }

    /**
     * Return active module ID.
     *
     * @return  int|null
     */
    public function get_active_module()
    {
        if (is_numeric($this->m_activemod)) {
            return $this->m_activemod;
        }

        return null;
    }

    /**
     * Retrieves the singleton instance
     *
     * @return  isys_module_manager
     */
    public static function instance()
    {
        return isys_application::instance()->container->get('moduleManager');
    }

    /**
     * Retrives module sorting.
     *
     * @return array|bool
     */
    public function get_module_sorting()
    {
        if (is_object($this->db)) {
            $l_dao = isys_component_dao::instance($this->db);
            $l_sort_array = [];

            $l_sql = 'SELECT * FROM isys_module_sorting
                WHERE TRUE ORDER BY isys_module_sorting__sort ASC;';
            $l_res = $l_dao->retrieve($l_sql);

            while ($l_row = $l_res->get_row()) {
                $l_sort_array[$l_row['isys_module_sorting__title']] = $l_row['isys_module_sorting__sort'];
            }

            return $l_sort_array;
        }

        return false;
    }

    /**
     * Method for retrieving all PHP / apache package dependencies.
     *
     * @param   string $for "php" or "apache".
     *
     * @return  array
     * @throws  \idoit\Exception\JsonException
     * @throws  isys_exception_filesystem
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function getPackageDependencies($for)
    {
        global $g_dirs;

        $language = isys_application::instance()->container->get('language');
        $packageFiles = glob($g_dirs['class'] . '/modules/*/package.json');
        $dependencies = [];

        // @see ID-11468 Check for required PHP extensions.
        if ($for === 'php') {
            foreach (isys_application::REQUIRED_PHP_EXTENSIONS as $extension) {
                $dependencies[$extension] = ['Core'];
            }
        }

        if (is_array($packageFiles) && count($packageFiles)) {
            foreach ($packageFiles as $packageFile) {
                if (file_exists($packageFile)) {
                    $jsonContent = isys_format_json::decode(file_get_contents($packageFile));

                    if (isset($jsonContent['dependencies'][$for]) && is_array($jsonContent['dependencies'][$for])) {
                        foreach ($jsonContent['dependencies'][$for] as $dependency) {
                            $moduleName = $language->get($jsonContent['name']);

                            if (strpos($moduleName, 'LC_') === 0) {
                                $moduleName = $jsonContent['title'];
                            }

                            $dependencies[$dependency][] = $moduleName;
                        }
                    }
                }
            }
        }

        return $dependencies;
    }

    /**
     * Function which gets active or inactive modules.
     *
     * @param   boolean  $getActive
     *
     * @return  array
     * @throws  isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_active_inactive_modules($getActive = true)
    {
        $return = [];
        $sql = 'SELECT isys_module__id
            FROM isys_module
            WHERE isys_module__status ' . ($getActive ? '= ' : '!= ') . $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';';

        $result = $this->dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row['isys_module__id'];
        }

        return $return;
    }

    /**
     * Get auth class of module by module id.
     *
     * @param int|string $p_module_id
     *
     * @return isys_auth|bool
     */
    public function get_module_auth($p_module_id)
    {
        if (!is_numeric($p_module_id)) {
            if (defined($p_module_id)) {
                $p_module_id = constant($p_module_id);
            } else {
                throw new isys_exception_general('Unable to handle given $p_module_id');
            }
        }

        if (!isset($this->m_module_auth[$p_module_id])) {
            // Retrieve module information: Only active modules
            $l_module_res = $this->get_modules($p_module_id, null, true);

            if ($l_module_res->count()) {
                $l_module_data = $l_module_res->get_row();

                if (is_a($l_module_data['isys_module__class'], AuthableInterface::class, true)) {
                    // Check for Authable.
                    $this->m_module_auth[$p_module_id] = call_user_func([$l_module_data['isys_module__class'], 'getAuth']);
                } elseif (is_a($l_module_data['isys_module__class'], isys_module_authable::class, true)) {
                    // Check for isys_module_authable.
                    $this->m_module_auth[$p_module_id] = call_user_func([$l_module_data['isys_module__class'], 'get_auth']);
                }
            }
        }

        if (isset($this->m_module_auth[$p_module_id])) {
            return $this->m_module_auth[$p_module_id];
        }

        // fallback
        return isys_module_system::getAuth();
    }

    /**
     * @return array
     * @throws isys_exception_database
     */
    private function get_installed_modules()
    {
        if (!$this->db) {
            return $this->m_installed;
        }

        if (count($this->m_installed)) {
            return $this->m_installed;
        }

        $l_dao = new isys_component_dao($this->db);
        $l_sql = 'SELECT
            isys_module__id AS id,
            isys_module__status as status,
            isys_module__title as title,
            isys_module__const as const,
            isys_module__class as class,
            isys_module__icon as icon,
            isys_module__identifier as identifier
            FROM isys_module;';

        $l_modules = $l_dao->retrieve($l_sql);

        while ($l_row = $l_modules->get_row()) {
            $this->m_installed[$l_row['identifier']] = [
                'id'     => $l_row['id'],
                'active' => $l_row['status'] == C__RECORD_STATUS__NORMAL
            ];

            $this->m_modules_cache[$l_row['id']] = $l_row;
        }

        return $this->m_installed;
    }

    /**
     * Constructor.
     */
    public function __construct($database)
    {
        parent::__construct();

        $this->db = $database;
        $this->dao = isys_application::instance()->container->get('cmdb_dao');

        $this->m_modules = [];
    }

    /**
     * @param       array $package
     *
     * @deprecated  Do not use this method for installing a add-on!
     * @return      bool|int
     * @throws      isys_exception_dao
     * @throws      isys_exception_database
     * @throws      isys_exception_general
     */
    public function install(array $package)
    {
        return $this->installAddOn($package);
    }

    /**
     * @param       string $identifier
     * @param       array  $mandatorDatabases
     * @param       array  &$errorMessages
     *
     * @deprecated  Do not use this method for uninstalling a add-on!
     * @return      boolean
     * @throws      Exception
     */
    public function uninstall($identifier, array $mandatorDatabases, array &$errorMessages)
    {
        return $this->uninstallAddOn($identifier, $mandatorDatabases, $errorMessages);
    }

    /**
     * @param       string $identifier
     *
     * @deprecated  Do not use this method for activating a add-on!
     * @return      bool|void
     * @throws      isys_exception_dao
     */
    public function activate($identifier)
    {
        return $this->activateAddOn($identifier);
    }

    /**
     * @param       string $identifier
     *
     * @deprecated  Do not use this method for deactivating a add-on!
     * @return      boolean
     * @throws      isys_exception_dao
     */
    public function deactivate($identifier)
    {
        return $this->deactivateAddOn($identifier);
    }

    /**
     * @throws Exception
     */
    private function afterChange(bool $clearTempDir = true)
    {
        $systemDatabase = isys_application::instance()->container->get('database_system');
        $systemDao = new isys_component_dao($systemDatabase);

        try {
            $result = $systemDao->retrieve("SELECT * FROM isys_settings WHERE isys_settings__key = 'cmdb.refresh-table-configurations';");
            // @see  ID-6382  Flag all tenants to refresh their table configurations.
            if (count($result)) {
                $systemDao->update("UPDATE isys_settings SET isys_settings__value = 1 WHERE isys_settings__key = 'cmdb.refresh-table-configurations'");
                $systemDao->apply_update();
            } else {
                $systemDao->update("INSERT INTO isys_settings SET isys_settings__key = 'cmdb.refresh-table-configurations', isys_settings__value = 1;");
                $systemDao->apply_update();
            }
        } catch (Exception $e) {
            throw new Exception('Could not set settings with key "cmdb.refresh-table-configurations"!');
        }

        if ($clearTempDir) {
            isys_glob_delete_recursive(isys_glob_get_temp_dir(), $l_deleted, $l_undeleted);
        }

        isys_component_signalcollection::get_instance()->emit('system.afterChange');
    }

    /**
     * @return bool
     */
    public static function hasBundle()
    {
        return class_exists(BundleInstaller::class) && BundleInstaller::hasBundle();
    }

    /**
     * @return bool
     */
    public static function hasAddonInstallInstructions()
    {
        if (!file_exists(BASE_DIR . self::ADDON_INSTALL_INSTRUCTIONS_FILE)) {
            return false;
        }
        return true;
    }

    /**
     * @param isys_component_database $systemDb
     * @param array                   $bundles
     * @param int                     $tenantId
     *
     * @return bool
     * @throws Exception
     */
    public static function bundleInstall(isys_component_database $systemDb, array $bundles = [], int $tenantId = 1)
    {
        return class_exists(BundleInstaller::class) && (new BundleInstaller())
            ->prepareEnvironment($systemDb, $bundles, $tenantId)
            ->installBundle();
    }
}
