<?php

namespace idoit\AddOn;

use Composer\Semver\Comparator;
use idoit\Exception\JsonException;
use isys_format_json as JSON;

/**
 * i-doit
 *
 * Add-on Check class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class AddonVerify
{
    /**
     * @var array|null
     */
    private ?array $compatibilityMap;

    /**
     * Check constructor.
     *
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        try {
            $compatibilityFile = $path ?? BASE_DIR . 'updates/addon-compatibilities.json';

            // @see ID-11149 Set proper default value, before processing further.
            $this->compatibilityMap = null;

            if (!file_exists($compatibilityFile)) {
                return;
            }

            $compatibilityFileContent = file_get_contents($compatibilityFile);

            if (!JSON::is_json_array($compatibilityFileContent)) {
                return;
            }

            $this->compatibilityMap = JSON::decode($compatibilityFileContent);
        } catch (JsonException $e) {
            $this->compatibilityMap = null;
        }
    }

    /**
     * @param string $identifier
     * @param string $version
     *
     * @return bool
     */
    public function canInstall(string $identifier, string $version): bool
    {
        $compatibleVersion = $this->getCompatibleVersion($identifier);

        if ($compatibleVersion === null) {
            return true;
        }

        return Comparator::greaterThanOrEqualTo($version, $compatibleVersion);
    }

    /**
     * @param string $identifier
     *
     * @return string|null
     */
    public function getCompatibleVersion(string $identifier): ?string
    {
        if ($this->compatibilityMap === null || !isset($this->compatibilityMap[$identifier]['incompatibleBelow'])) {
            return null;
        }

        return $this->compatibilityMap[$identifier]['incompatibleBelow'];
    }

    /**
     * @return array
     */
    public function getIncompatibleAddons(): array
    {
        $incompatibleAddons = [];

        if ($this->compatibilityMap === null) {
            return $incompatibleAddons;
        }

        foreach ($this->compatibilityMap as $identifier => $constraint) {
            $packageJsonPath = BASE_DIR . "src/classes/modules/{$identifier}/package.json";

            if (!file_exists($packageJsonPath)) {
                continue;
            }

            $packageJsonContent = file_get_contents($packageJsonPath);

            try {
                if (!JSON::is_json_array($packageJsonContent)) {
                    continue;
                }

                $packageJson = JSON::decode($packageJsonContent);

                if (!isset($packageJson['version'])) {
                    continue;
                }

                if (Comparator::lessThan($packageJson['version'], $constraint['incompatibleBelow'])) {
                    $incompatibleAddons[] = [
                        'title'             => $packageJson['title'] ?: ucfirst($identifier),
                        'compatibleVersion' => $constraint['incompatibleBelow'],
                        'currentVersion'    => $packageJson['version']
                    ];
                }
            } catch (JsonException $e) {
                // Nothing to do.
            }
        }

        return $incompatibleAddons;
    }
}
