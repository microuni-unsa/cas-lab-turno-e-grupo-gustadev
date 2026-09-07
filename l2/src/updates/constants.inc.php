<?php

// ------------- Configuration -------------
define("C__XML__SYSTEM", "update_sys.xml");
define("C__XML__DATA", "update_data.xml");
define("C__CHANGELOG", "CHANGELOG");
define("C__DIR__FILES", "files/");
define("C__DIR__MIGRATION", "migration/");
define("C__DIR__MODULES", "modules/");

/* Defining minimum php version for this update */
$versionConstants = [
    // PHP version requirements
    'UPDATE_PHP_VERSION_MINIMUM' => '8.2',
    'UPDATE_PHP_VERSION_DEPRECATED_BELOW' => '8.3',
    'UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED' => '8.4',
    'UPDATE_PHP_VERSION_MAXIMUM' => '8.4.99',

    // MariaDB version requirements
    'UPDATE_MARIADB_VERSION_MINIMUM' => '10.6',
    'UPDATE_MARIADB_VERSION_DEPRECATED_BELOW'=> '10.6',
    'UPDATE_MARIADB_VERSION_MAXIMUM' => '11.8.99',
    'UPDATE_MARIADB_VERSION_MINIMUM_RECOMMENDED' => '10.11',

    // MySQL version requirements
    'UPDATE_MYSQL_VERSION_MINIMUM' => '5.7.0',
    'UPDATE_MYSQL_VERSION_DEPRECATED_BELOW' => '5.8.0',
    'UPDATE_MYSQL_VERSION_MAXIMUM' => '8.4.99',
    'UPDATE_MYSQL_VERSION_MINIMUM_RECOMMENDED' => '8.4'
];


// Create undefined version constants
foreach ($versionConstants as $versionConstant => $versionValue) {
    // Check whether constant is already defined
    if (!defined($versionConstant)) {
        // Define it!
        define($versionConstant, $versionValue);
    }
}

/**
 * Define version check related functions
 */

if (!function_exists('checkVersion')) {
    /**
     * Check whether version meets requirements
     * defined by minimum and maximum information
     *
     * Please provide comparable version values
     * to guarantee valid handling. Therefore
     * you can use getVersion().
     *
     * @param string $version
     * @param string $minVersion
     * @param string $maxVersion
     *
     * @return bool
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    function checkVersion($version, $minVersion, $maxVersion)
    {
        return (version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<='));
    }
}

if (!function_exists('checkVersionIsAbove')) {
    /**
     * Check whether version is above max version
     *
     * Please provide comparable version values
     * to guarantee valid handling. Therefore
     * you can use getVersion().
     *
     * @param string $version
     * @param string $maxVersion
     *
     * @return mixed
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    function checkVersionIsAbove($version, $maxVersion)
    {
        return version_compare($version, $maxVersion, '>');
    }
}

if (!function_exists('getVersion')) {
    /**
     * Get cleaned version string
     *
     * Some operating systems add specific stuff
     * to phpversion() and mysql which disrupts version
     * comparisan of version_compare()
     *
     * @param string $version Supposed to be the output of phpversion()
     *
     * @return string
     * @throws Exception
     */
    function getVersion($version)
    {
        // Ensure php version without os related stuff
        if (preg_match('/^\d[\d.]*/', $version, $matches) === 1) {
            return $matches[0];
        }

        // Let executer handle exceptions
        throw new Exception('Unable to determine valid version by given version information: \'' . $version . '\'');
    }
}

if (!function_exists('unpackAddon')) {
    /**
     * Function for unpacking an add-on.
     *
     * This might be necessary, if a add-on HAS TO BE UPDATED during an i-doit update.
     * For example when "add-onizing" some functionality we initially use this
     * to force the add-on installation (in the next best major update).
     *
     * This function is almost identical to "install_module_by_zip" from
     * "<i-doit>/admin/src/functions.inc.php" but will not perform any database actions.
     *
     * @param string $packageZip
     *
     * @return bool
     * @throws Exception
     */
    function unpackAddon($packageZip)
    {
        global $g_absdir;

        // Checking for zlib and the ZipArchive class to solve #4853
        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
        }

        // Unzip the package.
        if (!(new isys_update_files())->read_zip($packageZip, $g_absdir, false, true)) {
            throw new Exception('Error: Could not read zip package.');
        }

        // @see  ID-8566  Check for any package.json files.
        $addonPackageFile = $g_absdir . '/package.json';
        $bundlePackageFiles = glob($g_absdir . '/package-*.json');
        $moduleDirectory = $g_absdir . '/src/classes/modules/';

        // Go sure that we have an array of package files.
        if (!is_array($bundlePackageFiles)) {
            $bundlePackageFiles = [];
        }

        if (empty($bundlePackageFiles) && file_exists($addonPackageFile)) {
            $bundlePackageFiles[] = $addonPackageFile;
        }

        if (count($bundlePackageFiles) === 0) {
            throw new Exception('The zip file contains no package.json file(s).');
        }

        foreach ($bundlePackageFiles as $bundlePackageFile) {
            $bundlePackage = json_decode(file_get_contents($bundlePackageFile), true);

            // Remove any existing package files.
            if (file_exists($moduleDirectory . $bundlePackage['identifier'] . '/package.json')) {
                unlink($moduleDirectory . $bundlePackage['identifier'] . '/package.json');
            }

            // Move the package file to the add-on directory.
            rename($bundlePackageFile, $moduleDirectory . $bundlePackage['identifier'] . '/package.json');
        }

        return true;
    }
}
