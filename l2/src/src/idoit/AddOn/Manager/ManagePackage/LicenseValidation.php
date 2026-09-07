<?php

namespace idoit\AddOn\Manager\ManagePackage;

use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManageActionInterface;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;

class LicenseValidation implements ManageActionInterface
{
    /**
     * @var array
     */
    private array $packageJsonData = [];

    /**
     * @param array $packageJsonData
     */
    public function __construct(array $packageJsonData)
    {
        $this->packageJsonData = $packageJsonData;
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function process(): bool
    {
        global $g_license_token;

        if (empty($this->packageJsonData)) {
            throw PackageException::loadedPackageMissing();
        }

        if (!isset($this->packageJsonData['license'])) {
            return true;
        }

        $licenseIdentifier = $this->packageJsonData['license'];

        $licenseService = LicenseServiceFactory::createDefaultLicenseService(
            isys_application::instance()->container->get('database_system'),
            $g_license_token
        );

        $licensedAddons = $licenseService->getLicensedAddOns();

        if (isset($licensedAddons[$licenseIdentifier]) && $licensedAddons[$licenseIdentifier]) {
            return true;
        }

        return false;
    }
}
