<?php

namespace idoit\AddOn\Manager\ManageTenant;

use Exception;
use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\Exception\TenantException;
use idoit\AddOn\Manager\Logger;
use idoit\AddOn\Manager\ManageActionInterface;
use isys_application;
use isys_component_dao;
use isys_component_database;
use isys_component_signalcollection;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class Uninstall implements ManageActionInterface
{
    /**
     * @var string
     */
    private string $identifier = '';

    /**
     * @var string
     */
    private string $addonRootPath = '';

    /**
     * @var array
     */
    private array $packageJsonData = [];

    /**
     * @var isys_component_database
     */
    private isys_component_database $db;

    /**
     * @var null|Logger
     */
    private ?\idoit\Component\Logger $logger = null;

    /**
     * @param string                  $identifier
     * @param string                  $addonRootPath
     * @param array                   $packageJsonData
     * @param isys_component_database $db
     */
    public function __construct(string $identifier, string $addonRootPath, array $packageJsonData, isys_component_database $db)
    {
        $this->identifier = $identifier;
        $this->addonRootPath = $addonRootPath;
        $this->packageJsonData = $packageJsonData;
        $this->db = $db;
    }

    /**
     * @param string $moduleClassName
     *
     * @return void
     */
    private function callAddonUninstall(string $moduleClassName): void
    {
        // Call custom uninstall method of module
        if (class_exists($moduleClassName)) {
            $this->logger->info("Executing uninstall script.");
            try {
                if (is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                    $moduleClassName::uninstall($this->db);
                    $this->logger->debug(' > Done!');
                } elseif (is_callable([$moduleClassName, 'uninstall'])) {
                    call_user_func([$moduleClassName, 'uninstall']);
                    $this->logger->debug(' > Done!');
                } else {
                    $this->logger->info(' > No uninstall script found. Continuing process.');
                }
            } catch (Throwable $e) {
                $this->logger->error("An error occurred while executing the uninstall script with message: {$e->getMessage()}");
            }
        }
    }

    /**
     * @param isys_component_dao $dao
     *
     * @return void
     * @throws \isys_exception_dao
     */
    private function dropSqlTables(isys_component_dao $dao): void
    {
        // Drop sql tables.
        if (!isset($this->packageJsonData['sql-tables']) || !is_array($this->packageJsonData['sql-tables']) || empty($this->packageJsonData['sql-tables'])) {
            $this->logger->notice("No sql-tables array found in package.json. Skipping standardized table drop.");
            return;
        }

        $this->logger->notice(sprintf("Dropping %d tables in database.", count($this->packageJsonData['sql-tables'])));

        foreach ($this->packageJsonData['sql-tables'] as $table) {
            if (count($dao->retrieve('SHOW TABLES LIKE ' . $dao->convert_sql_text($table) . ';')) === 0) {
                continue;
            }

            $this->logger->notice("Attempting to truncate and drop {$table}.");

            try {
                $dao->update("TRUNCATE TABLE {$table};") && $dao->apply_update();
                $this->logger->notice("> {$table} truncated.");
            } catch (Throwable $e) {
                $this->logger->warning("> {$table} could not be truncated.");
                $this->logger->warning($e->getMessage());
            }

            try {
                $dao->update("DROP TABLE {$table};") && $dao->apply_update();
                $this->logger->notice("> {$table} dropped.");
            } catch (Throwable $e) {
                $this->logger->warning("> {$table} could not be dropped.");
                $this->logger->warning($e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function afterChange()
    {
        $tenantId = isys_application::instance()->tenant->id;
        $systemDatabase = isys_application::instance()->container->get('database_system');

        try {
            $result = $systemDatabase->query("SELECT * FROM isys_settings WHERE isys_settings__key = 'cmdb.refresh-table-configurations';");
            // @see  ID-6382  Flag all tenants to refresh their table configurations.
            $systemDatabase->begin();
            if ($systemDatabase->num_rows($result) > 0) {
                $systemDatabase->query("UPDATE isys_settings SET isys_settings__value = 1 WHERE isys_settings__key = 'cmdb.refresh-table-configurations'");
            } else {
                $systemDatabase->update("INSERT INTO isys_settings SET isys_settings__key = 'cmdb.refresh-table-configurations', isys_settings__value = 1;");
            }

            // @see ID-11119 Remove installdate in system settings.
            $sql = "DELETE FROM isys_settings
                WHERE isys_settings__key = 'admin.module.{$this->identifier}.installed'
                AND isys_settings__isys_mandator__id = '{$tenantId}';";

            $systemDatabase->query($sql);
            $systemDatabase->commit();
        } catch (Throwable $e) {
            throw new Exception('Could not set settings with key "cmdb.refresh-table-configurations"!');
        }

        (new Filesystem())->remove(BASE_DIR . 'temp/');

        isys_component_signalcollection::get_instance()->emit('system.afterChange');
    }

    /**
     * @return bool
     * @throws TenantException
     */
    public function process(): bool
    {
        // @see ID-8699
        require_once __DIR__ . '/../../../../classes/modules/report/init.php';
        $this->logger = Logger::getLogger('uninstall', $this->identifier);

        try {
            $tenantDbName = $this->db->get_db_name();

            if (empty($this->packageJsonData)) {
                // @todo Add Log package data is not available
                throw PackageException::noPackageFound();
            }

            if ($this->packageJsonData['type'] !== 'addon') {
                $exception = TenantException::uninstallCoreModule($this->identifier);
                $this->logger->error($exception->getMessage());
                throw $exception;
            }

            $moduleManager = \isys_application::instance()->container->get('moduleManager');
            $addonEntry = $moduleManager
                ->get_modules(null, null, null, " AND isys_module__identifier = '" . $this->db->escape_string($this->identifier) . "'")
                ->get_row();

            if ($addonEntry && $addonEntry['isys_module__id'] >= 1010) {
                $this->callAddonUninstall("isys_module_{$this->identifier}");

                $dao = isys_component_dao::instance($this->db);
                $dao->begin_update();
                $dao->update('SET FOREIGN_KEY_CHECKS = 0;');

                $this->dropSqlTables($dao);

                // Delete module entry.
                if ($moduleManager->delete($this->identifier)) {
                    $this->logger->notice("Successfully uninstall add-on for mandator db {$tenantDbName}.");
                }

                $dao->apply_update();
            } else {
                $this->logger->notice("Module {$this->identifier} not found in mandator db {$tenantDbName}. Skipped uninstall for mandator db {$tenantDbName}.");
            }

            // Call system has changed post notification.
            $this->afterChange();
            isys_application::instance()->renewCache();
            return true;
        } catch (Throwable $e) {
            // Cancel transaction
            if (isset($dao) && is_object($dao)) {
                $dao->cancel_update();
            }

            $exception = TenantException::uninstallFailed($this->identifier, $e->getMessage());
            $this->logger->error($exception->getMessage());
            throw $exception;
        }
    }
}
