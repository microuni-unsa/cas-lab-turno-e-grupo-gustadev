<?php

namespace idoit\AddOn\Manager;

use Exception;
use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManagePackage\AddonValidation;
use idoit\AddOn\Manager\ManagePackage\LicenseValidation;
use idoit\AddOn\Manager\ManagePackage\MoveAddon;
use idoit\AddOn\Manager\ManagePackage\Requirements;
use idoit\AddOn\Manager\ManagePackage\Unpack;

class ManagePackage
{
    /**
     * @var string
     */
    private string $fileName = '';

    /**
     * @var array
     */
    private array $packageJsonData = [];

    /**
     * @var string
     */
    private string $temporaryPath = '';

    /**
     * @var string
     */
    private string $addonRootPath = '';

    /**
     * @param string $addonRootPath
     */
    public function __construct(string $addonRootPath)
    {
        $this->addonRootPath = $addonRootPath;
    }

    /**
     * @param string $addonRootPath
     *
     * @return $this
     */
    public function setAddonRootPath(string $addonRootPath): ManagePackage
    {
        $this->addonRootPath = $addonRootPath;

        return $this;
    }

    /**
     * @param string $addonZip
     *
     * @return $this
     * @throws PackageException
     */
    public function load(string $addonZip): ManagePackage
    {
        if (!file_exists($addonZip)) {
            throw PackageException::addonPackageDoesNotExists($addonZip);
        }

        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            throw PackageException::zipExtensionMissing();
        }

        $packageJsonRaw = file_get_contents("zip://{$addonZip}#package.json", false);

        if (!$packageJsonRaw) {
            throw PackageException::unableToReadPackageJson($addonZip);
        }

        $packageJsonData = json_decode($packageJsonRaw, true);

        if (!$packageJsonData) {
            throw PackageException::invalidPackageJson($addonZip);
        }

        $this->fileName = $addonZip;
        $this->packageJsonData = $packageJsonData;

        return $this;
    }

    /**
     * @return bool
     * @throws PackageException|Exception
     */
    public function unpack(): bool
    {
        $this->checkRequirements();

        if (!file_exists($this->fileName)) {
            throw PackageException::addonPackageDoesNotExists($this->fileName);
        }

        if (!$this->isAddonValid()) {
            throw PackageException::invalidAddonPackage(AddonValidation::getKeysToCheck());
        }

        if (!$this->isLicenseValid()) {
            throw PackageException::invalidAddonLicense($this->packageJsonData['identifier']);
        }

        $tempDirectory = BASE_DIR . 'temp/addon-' . substr(md5(microtime()), 0, 8) . '/';

        if ((new Unpack($this->fileName, $tempDirectory))->process()) {
            $this->temporaryPath = $tempDirectory;

            return true;
        }

        return false;
    }

    /**
     * @return true
     * @throws PackageException
     */
    public function moveToaddonRootPath()
    {
        $this->checkRequirements();

        copy("{$this->temporaryPath}/package.json", "{$this->temporaryPath}src/classes/modules/{$this->packageJsonData['identifier']}/package.json");
        return (new MoveAddon(
            "{$this->temporaryPath}src/classes/modules/{$this->packageJsonData['identifier']}",
            "{$this->addonRootPath}{$this->packageJsonData['identifier']}"
        ))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function isAddonValid(): bool
    {
        return (new AddonValidation($this->packageJsonData))->process();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isLicenseValid()
    {
        return (new LicenseValidation($this->packageJsonData))->process();
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function checkRequirements(): bool
    {
        global $g_product_info;
        if (empty($this->packageJsonData)) {
            throw PackageException::loadedPackageMissing();
        }

        return (new Requirements(
            $this->packageJsonData,
            $this->addonRootPath
        )
        )->process();
    }
}
