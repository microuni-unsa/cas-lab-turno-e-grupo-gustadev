<?php

namespace idoit\AddOn\Manager\ManageTenant;

use idoit\AddOn\Manager\Logger;
use idoit\AddOn\Manager\ManageActionInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeleteFiles implements ManageActionInterface
{
    /** @var array */
    private array $packageJsonData;

    /** @var string */
    private string $addonRootPath;

    /**
     * @param array  $packageJsonData
     * @param string $addonRootPath
     */
    public function __construct(array $packageJsonData, string $addonRootPath)
    {
        $this->packageJsonData = $packageJsonData;
        $this->addonRootPath = $addonRootPath;
    }

    /**
     * @return bool
     */
    public function process(): bool
    {
        global $g_db_system;

        $logger = Logger::getLogger('delete-files', $this->packageJsonData['identifier']);

        try {
            // First we need to go sure that the add-on is not installed in any other tenant.
            $logger->notice('Check if the add-on is used in any other tenant..');

            $daoMandator = new \isys_component_dao_mandator(\isys_application::instance()->container->get('database_system'));
            $recordStatusNormal = $daoMandator->convert_sql_int(C__RECORD_STATUS__NORMAL);
            $identifier = $daoMandator->convert_sql_text($this->packageJsonData['identifier']);

            $mandatorResult = $daoMandator->get_mandator();

            while ($row = $mandatorResult->get_row()) {
                $tenantCmdbDao = new \isys_cmdb_dao(\isys_component_database::get_database(
                    $g_db_system["type"],
                    $row["isys_mandator__db_host"],
                    $row["isys_mandator__db_port"],
                    $row["isys_mandator__db_user"],
                    $daoMandator::getPassword($row),
                    $row["isys_mandator__db_name"]
                ));

                $query = "SELECT COUNT(1) AS cnt
                    FROM isys_module
                    WHERE isys_module__identifier = {$identifier}
                    AND isys_module__status = {$recordStatusNormal};";

                // As soon as we find out that the add-on is active in one of our tenants, we skip this step.
                if ($tenantCmdbDao->retrieve($query)->get_row_value('cnt')) {
                    $logger->warning(sprintf('Add-on is used in tenant "%s" (ID %d).. Skip file deletion', $row['isys_mandator__title'], $row['isys_mandator__id']));
                    return true;
                }
            }
        } catch (\Throwable $exception) {
            $logger->error('An error occurred, while checking other tenants: ' . $exception->getMessage());
            return false;
        }

        $filesystem = new Filesystem();
        $addonPath = "{$this->addonRootPath}/{$this->packageJsonData['identifier']}";

        // @see ID-11744 Simply delete add-on directory.
        // @see ID-10736 After the first batch of files was removed, remove any leftovers.
        try {
            $filesystem->remove($addonPath);
            $logger->notice("Deleted add-on directory {$addonPath}");
        } catch (\Throwable $exception) {
            $logger->error("Could not delete {$addonPath}: {$exception->getMessage()}");
        }

        return true;
    }
}
