<?php

namespace idoit\AddOn\Manager;

use idoit\AddOn\Manager\ManagePackage\Requirements;
use isys_component_database;

class ManageCandidates
{
    /**
     * @var string
     */
    private string $identifier = '';

    /**
     * @var string
     */
    private string $addonRootPath;

    /**
     * @var array
     */
    private array $packageData = [];

    /**
     * @var isys_component_database
     */
    private isys_component_database $db;

    /**
     * @param isys_component_database $db
     * @param string                  $addonRootPath
     */
    public function __construct(isys_component_database $db, string $addonRootPath)
    {
        $this->db = $db;
        $this->addonRootPath = $addonRootPath;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier(string $identifier): ManageCandidates
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @param array $packageData
     *
     * @return $this
     */
    public function setPackageData(array $packageData): ManageCandidates
    {
        $this->packageData = $packageData;

        return $this;
    }

    /**
     * @param string $addonRootPath
     *
     * @return $this
     */
    public function setAddonRootPath(string $addonRootPath): ManageCandidates
    {
        $this->addonRootPath = $addonRootPath;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInstalled(): bool
    {
        $query = "SELECT isys_module__id FROM isys_module WHERE isys_module__identifier = '{$this->db->escape_string($this->identifier)}' limit 1;";
        $result = $this->db->query($query);

        return $this->db->num_rows($result) > 0;
    }

    /**
     * @return array|null
     */
    public function getPackageJson(): ?array
    {
        $localPackageJson = "{$this->addonRootPath}{$this->identifier}/package.json";
        if ($this->isInstalled() && file_exists($localPackageJson)) {
            $localPackageJsonData = json_decode(file_get_contents($localPackageJson), true);
            return $localPackageJsonData;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isUpdateAvailable(): bool
    {
        $localPackageJsonData = $this->getPackageJson();
        if (!empty($this->packageData)) {
            return version_compare($localPackageJsonData['version'], $this->packageData['version'], '<');
        }

        return false;
    }

    /**
     * @return bool
     * @throws Exception\PackageException
     */
    public function checkRequirements()
    {
        return (new Requirements($this->packageData, $this->addonRootPath))->process();
    }
}
