<?php

namespace idoit\AddOn\Manager;

use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManageTenant\Activate;
use idoit\AddOn\Manager\ManageTenant\Deactivate;
use idoit\AddOn\Manager\ManageTenant\DeleteFiles;
use idoit\AddOn\Manager\ManageTenant\Install;
use idoit\AddOn\Manager\ManageTenant\Uninstall;
use isys_component_database;

class ManageTenant
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
     * @var isys_component_database
     */
    private isys_component_database $db;

    /**
     * @param isys_component_database $db
     */
    public function __construct(isys_component_database $db, string $addonRootPath)
    {
        $this->db = $db;
        $this->addonRootPath = $addonRootPath;
    }

    /**
     * @param string $addonRootPath
     *
     * @return $this
     */
    public function setAddonRootPath(string $addonRootPath): ManageTenant
    {
        $this->addonRootPath = $addonRootPath;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier(string $identifier): ManageTenant
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return array
     * @throws PackageException
     */
    private function getPackageJsonData(): array
    {
        $packageJsonFile = "{$this->addonRootPath}{$this->identifier}/package.json";

        if (!file_exists($packageJsonFile)) {
            throw PackageException::noPackageFound();
        }
        return json_decode(file_get_contents($packageJsonFile), true);
    }

    /**
     * @return bool
     * @throws PackageException
     * @throws \isys_exception_dao
     * @throws \isys_exception_filesystem
     */
    public function install(): bool
    {
        return (new Install($this->identifier, $this->addonRootPath, $this->getPackageJsonData(), $this->db))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function uninstall(): bool
    {
        return (new Uninstall($this->identifier, $this->addonRootPath, $this->getPackageJsonData(), $this->db))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function activate()
    {
        return (new Activate($this->identifier))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function deactivate()
    {
        return (new Deactivate($this->identifier))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function deleteAddonFiles()
    {
        return (new DeleteFiles($this->getPackageJsonData(), $this->addonRootPath))->process();
    }
}
