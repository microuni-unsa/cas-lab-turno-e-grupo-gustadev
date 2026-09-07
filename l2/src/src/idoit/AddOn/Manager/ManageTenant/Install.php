<?php

namespace idoit\AddOn\Manager\ManageTenant;

use idoit\AddOn\Manager\Exception\TenantException;
use idoit\AddOn\Manager\Logger;
use idoit\AddOn\Manager\ManageActionInterface;
use isys_application;
use isys_component_database;
use isys_update_files;
use isys_update_xml;
use Throwable;

class Install implements ManageActionInterface
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
     * @return bool
     * @throws TenantException
     * @throws \isys_exception_dao
     * @throws \isys_exception_filesystem
     */
    public function process(): bool
    {
        $installFile = "{$this->addonRootPath}{$this->identifier}/install/isys_module_{$this->identifier}_install.class.php";
        $deleteFile = "{$this->addonRootPath}{$this->identifier}/install/update_files.xml";

        // Include module installscript if available.
        if (file_exists($installFile)) {
            include_once($installFile);
        }

        if (file_exists($deleteFile)) {
            (new isys_update_files())->delete("{$this->addonRootPath}{$this->identifier}/install");
        }
        $moduleManager = isys_application::instance()->container->get('moduleManager');

        $type = $moduleManager->is_installed($this->identifier) ? 'update' : 'install';
        $this->logger = Logger::getLogger($type, $this->identifier);

        $moduleId = $moduleManager->installAddOn($this->packageJsonData);

        $this->processDbUpdates();
        $this->processInstallScript($moduleId, $type);
        isys_application::instance()->renewCache();

        return true;
    }

    /**
     * @return void
     * @throws TenantException
     */
    private function processDbUpdates(): void
    {
        try {
            $update = new isys_update_xml();

            if (file_exists("{$this->addonRootPath}{$this->identifier}/install/update_data.xml")) {
                $this->logger->info("Updating tenant database.");
                $update->update_database("{$this->addonRootPath}{$this->identifier}/install/update_data.xml", $this->db);
            }

            if (file_exists("{$this->addonRootPath}{$this->identifier}/install/update_sys.xml")) {
                $this->logger->info("Updating system database.");
                $update->update_database(
                    "{$this->addonRootPath}{$this->identifier}/install/update_sys.xml",
                    isys_application::instance()->container->get('database_system')
                );
            }
        } catch (Throwable $e) {
            $this->logger->error("Database update failed with message: {$e->getMessage()}");
            throw TenantException::dbUpdateException($e->getMessage());
        }
    }

    /**
     * @param int    $moduleId
     * @param string $type
     *
     * @return void
     * @throws TenantException
     * @throws \isys_exception_dao
     */
    private function processInstallScript(int $moduleId, string $type): void
    {
        $tenantId = isys_application::instance()->tenant->id;
        $systemDb = isys_application::instance()->container->get('database_system');

        $moduleClassName = "isys_module_{$this->identifier}";

        try {
            if (class_exists($moduleClassName) && is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                $this->logger->info("Executing install script from add-on.");
                $moduleClassName::install($this->db, $systemDb, $moduleId, $type, $tenantId);
            } else {
                // Call module installscript if available.
                $l_installclass = "isys_module_{$this->identifier}_install";
                if (class_exists($l_installclass)) {
                    $this->logger->info("Executing install script from add-on.");
                    call_user_func([$l_installclass, 'init'], $this->db, $systemDb, $moduleId, $type, $tenantId);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error("Execution of installscript failed with: {$e->getMessage()}");
            throw TenantException::installScriptException($e->getMessage());
        }

        // @see ID-11119 Always set installdate in system settings
        $sql = "REPLACE INTO isys_settings SET
                isys_settings__key = 'admin.module.{$this->identifier}.installed',
                isys_settings__value = '" . time() . "',
                isys_settings__isys_mandator__id = '{$tenantId}';";

        $systemDb->query($sql);
        $systemDb->commit();
    }
}
