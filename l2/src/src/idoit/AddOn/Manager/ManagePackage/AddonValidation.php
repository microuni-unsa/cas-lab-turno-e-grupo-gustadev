<?php

namespace idoit\AddOn\Manager\ManagePackage;

use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManageActionInterface;

class AddonValidation implements ManageActionInterface
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
     * @return string[]
     */
    public static function getKeysToCheck(): array
    {
        return [
            'identifier',
            'version',
            'type',
            'requirements'
        ];
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function process(): bool
    {
        if (empty($this->packageJsonData)) {
            throw PackageException::loadedPackageMissing();
        }

        $missingKeys = array_filter(self::getKeysToCheck(), fn ($key) => !isset($this->packageJsonData[$key]));
        return empty($missingKeys);
    }
}
