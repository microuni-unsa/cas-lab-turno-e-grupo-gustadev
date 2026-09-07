<?php

namespace idoit\AddOn\Manager\ManagePackage;

use Composer\Semver\Semver;
use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManageActionInterface;
use isys_application;

class Requirements implements ManageActionInterface
{
    /**
     * @var array
     */
    private array $packageJsonData = [];

    /**
     * @var string
     */
    private string $addonRootPath = '';

    /**
     * @param array $packageJsonData
     */
    public function __construct(array $packageJsonData, string $addonRootPath)
    {
        $this->packageJsonData = $packageJsonData;
        $this->addonRootPath = $addonRootPath;
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function process(): bool
    {
        global $g_product_info;

        $identifier = $this->packageJsonData['identifier'];
        $addonVersion = $this->packageJsonData['version'];
        $addonTitle = $this->packageJsonData['title'] ?: ucfirst($identifier);

        if (isset($this->packageJsonData['requirements']['core']) && !Semver::satisfies($g_product_info['version'], $this->packageJsonData['requirements']['core'])) {
            [, $version] = explode(' ', $this->packageJsonData['requirements']['core']);

            throw PackageException::requiredCoreVersionNotMet($this->packageJsonData['requirements']['core']);
        }

        $addonChecker = isys_application::instance()->container->get('addon.checker');

        if (!$addonChecker->canInstall($identifier, $addonVersion)) {
            $compatibleVersion = $addonChecker->getCompatibleVersion($identifier);
            $givenVersion = $addonVersion;

            throw PackageException::incompatibleAddonVersion($addonTitle, $compatibleVersion, $givenVersion);
        }

        // Prevent an add-on from being downgraded.
        $addonRootPath = $this->addonRootPath . $identifier . '/package.json';

        if (file_exists($addonRootPath)) {
            $currentAddonInfo = json_decode(file_get_contents($addonRootPath), true);

            // Check if the installed add-on version is bigger than the provided one
            if (Semver::satisfies($currentAddonInfo['version'], '> ' . $addonVersion)) {
                throw PackageException::addonVersionLowerThanCurrent($addonTitle);
            }
        }

        return true;
    }
}
