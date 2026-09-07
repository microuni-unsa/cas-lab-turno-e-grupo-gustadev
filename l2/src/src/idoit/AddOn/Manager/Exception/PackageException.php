<?php

namespace idoit\AddOn\Manager\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class PackageException extends Exception
{
    /**
     * @return self
     */
    public static function zipExtensionMissing(): PackageException
    {
        return new self(
            "Add-on installation requires php’s zip extension to work correctly. Please install it and try again.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $fileName
     *
     * @return self
     */
    public static function invalidPackageJson(string $fileName): PackageException
    {
        return new self(
            "Invalid data in package.json from: {$fileName}",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $fileName
     *
     * @return self
     */
    public static function unableToReadPackageJson(string $fileName): PackageException
    {
        return new self(
            "Could not read package.json from: {$fileName}",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $fileName
     *
     * @return self
     */
    public static function addonPackageDoesNotExists(string $fileName): PackageException
    {
        return new self(
            "Add-on package '{$fileName}' does not exist.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @return self
     */
    public static function noZipLibraryForExtractionFound(): PackageException
    {
        return new self(
            'Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.',
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @return self
     */
    public static function noPackageFound(): PackageException
    {
        return new self(
            'No package found. Please load and unpack an add-on package and try again.',
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param array $requiredKeys
     *
     * @return self
     */
    public static function invalidAddonPackage(array $requiredKeys): PackageException
    {
        return new self(
            'Invalid package.json found. Please check the required fields: ' . implode(', ', $requiredKeys),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $identifier
     *
     * @return PackageException
     */
    public static function invalidAddonLicense(string $identifier): PackageException
    {
        return new self(
            "Invalid add-on license for '{$identifier}'.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $version
     *
     * @return self
     */
    public static function requiredCoreVersionNotMet(string $version): PackageException
    {
        return new self(
            "Add-on requires i-doit version {$version}. Please update your instance to allow installation of this add-on.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $addonTitle
     * @param string $compatibleVersion
     * @param string $givenVersion
     *
     * @return self
     */
    public static function incompatibleAddonVersion(string $addonTitle, string $compatibleVersion, string $givenVersion): PackageException
    {
        return new self(
            "{$addonTitle} can not be processed, please try at least version {$compatibleVersion} (you provided {$givenVersion})",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $addonTitle
     *
     * @return self
     */
    public static function addonVersionLowerThanCurrent(string $addonTitle): PackageException
    {
        return new self(
            "Lower version of the add-on {$addonTitle} found, you need to provide a higher or equal version.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @return self
     */
    public static function loadedPackageMissing(): PackageException
    {
        return new self(
            "No add-on package has been loaded, please load an add-on package.",
            Response::HTTP_BAD_REQUEST
        );
    }
}
