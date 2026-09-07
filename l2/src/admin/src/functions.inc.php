<?php

/**
 * @author      Dennis Stuecken
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use Composer\Semver\Semver;
use idoit\AddOn\AddonVerify;
use idoit\Component\ConstantManager;
use idoit\Module\Cmdb\Model\CiTypeCategoryAssigner;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @param  $p_tenant_id
 */
function connect_mandator($p_tenant_id)
{
    global $g_db_system, $l_dao_mandator;

    $l_dbdata = $l_dao_mandator->get_mandator($p_tenant_id, 0)->get_row();

    // Create connection to mandator DB.
    return isys_component_database::get_database(
        $g_db_system["type"],
        $l_dbdata["isys_mandator__db_host"],
        $l_dbdata["isys_mandator__db_port"],
        $l_dbdata["isys_mandator__db_user"],
        isys_component_dao_mandator::getPassword($l_dbdata),
        $l_dbdata["isys_mandator__db_name"]
    );
}

/**
 * Install a module zip file.
 *
 * @param   string $zipFilePath
 * @param   int|string|null $tenantId Input '0' for all.
 *
 * @throws  Exception
 * @return  boolean
 */
function install_module_by_zip($zipFilePath, $tenantId = null)
{
    global $g_product_info;

    $tempDirectory = BASE_DIR . 'temp/addon-' . substr(md5(microtime()), 0, 8) . '/';
    $filesystem = new Filesystem();
    $addonChecker = new AddonVerify();

    // Checking for zlib and the ZipArchive class to solve #4853
    if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
        throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
    }

    if (!is_dir($tempDirectory) && !mkdir($tempDirectory, 0775, true)) {
        throw new Exception("Can't create subdirectory '$tempDirectory' for uploading file. Please check rights");
    }

    if (!(new isys_update_files())->read_zip($zipFilePath, $tempDirectory, false, true)) {
        throw new Exception('Error: Could not read zip package.');
    }

    // @see  ID-8566  Check for any package.json files.
    $bundlePackageFiles = glob($tempDirectory . 'package-*.json');
    $moduleDirectory = BASE_DIR . 'src/classes/modules/';

    // Go sure that we have an array of package files.
    if (!is_array($bundlePackageFiles)) {
        $bundlePackageFiles = [];
    }

    if (empty($bundlePackageFiles) && file_exists($tempDirectory . 'package.json')) {
        $bundlePackageFiles[] = $tempDirectory . 'package.json';
    }

    if (count($bundlePackageFiles) === 0) {
        throw new Exception('The zip file contains no package.json file(s).');
    }

    // @see ID-9066 First we check if we can install all add-ons, based on simple checks.
    foreach ($bundlePackageFiles as $bundlePackageFile) {
        $bundlePackage = json_decode(file_get_contents($bundlePackageFile), true);

        try {
            // Check if the add-on is allowed to be installed.
            if (isset($bundlePackage['requirements']['core']) && !Semver::satisfies($g_product_info['version'], $bundlePackage['requirements']['core'])) {
                [, $version] = explode(' ', $bundlePackage['requirements']['core']);

                throw new Exception('Error: i-doit Version requirement for this add-on does not match. Update to version ' . $version . ' and try again.');
            }

            // @see ID-9075 Check if the provided add-on is allowed in this i-doit version.
            if (!$addonChecker->canInstall($bundlePackage['identifier'], $bundlePackage['version'])) {
                $addonTitle = $bundlePackage['title'] ?: ucfirst($bundlePackage['identifier']);
                $compatibleVersion = $addonChecker->getCompatibleVersion($bundlePackage['identifier']);
                $givenVersion = $bundlePackage['version'];

                throw new Exception("{$addonTitle} can not be installed, please try to install at least version {$compatibleVersion} (you provided {$givenVersion})");
            }

            // Prevent an add-on from being downgraded.
            $addonPath = $moduleDirectory . $bundlePackage['identifier'] . '/package.json';

            if (file_exists($addonPath)) {
                $addonInfo = json_decode(file_get_contents($addonPath), true);

                // Check if the installed add-on version is bigger than the provided one
                if (Semver::satisfies($addonInfo['version'], '> ' . $bundlePackage['version'])) {
                    $addonTitle = $addonInfo['title'] ?: ucfirst($addonInfo['identifier']);
                    throw new Exception("Error: You can not install a lower version of the add-on {$addonTitle}, you need to provide a higher or equal version.");
                }
            }
        } catch (Exception $e) {
            // Delete any add-on remains.
            $filesystem->remove($tempDirectory);

            isys_component_template::instance()
                ->assign('error', $e->getMessage());

            return false;
        }
    }

    /*
     * Now we move add-on related files to their destination folders.
     * Please note that this only affects the 'src/classes/modules/{add-on}' folders - other files will be moved at the end.
     */

    foreach ($bundlePackageFiles as $bundlePackageFile) {
        $bundlePackage = json_decode(file_get_contents($bundlePackageFile), true);

        try {
            // Move the add-on related files...
            $sourceFolder = $tempDirectory . 'src/classes/modules/' . $bundlePackage['identifier'];
            $targetFolder = $moduleDirectory . $bundlePackage['identifier'];

            if (file_exists($sourceFolder)) {
                // Move add-on specific files.
                $filesystem->mirror($sourceFolder, $targetFolder);

                // Afterwards, delete the folder from temp.
                $filesystem->remove($sourceFolder);
            }

            // Start module installation.
            $result = install_module($bundlePackage, (int)$tenantId, false);

            // Move the package file to the add-on directory.
            $filesystem->rename($bundlePackageFile, $targetFolder . '/package.json', true);

            if (!$result) {
                throw new Exception('Error: The Add-on could not be installed successfully.');
            }
        } catch (Exception $e) {
            // Delete any add-on remains.
            $filesystem->remove($tempDirectory);

            isys_component_template::instance()
                ->assign('error', $e->getMessage());

            return false;
        }
    }

    // Once all add-on related files where copied, copy the rest from the temporary directory.
    $filesystem->mirror($tempDirectory, BASE_DIR);

    // Delete any add-on remains.
    $filesystem->remove($tempDirectory);

    return true;
}

/**
 * Install module by it's identifier.
 *
 * @param array $p_packageJSON
 * @param int   $p_tenant
 * @param bool  $clearTempDir
 *
 * @return true
 * @throws isys_exception_dao
 * @throws isys_exception_database
 * @throws isys_exception_database_mysql
 * @throws isys_exception_filesystem
 * @throws isys_exception_general
 */
function install_module(array $p_packageJSON, $p_tenant = null, bool $clearTempDir = true)
{
    /**
     * Initialize
     */
    global $g_absdir, $g_product_info, $l_dao_mandator, $g_comp_database_system;
    $l_db_update = new isys_update_xml();

    $l_tenants = [];

    $addonChecker = new AddonVerify();

    // @see ID-9075 Check if the provided add-on is allowed in this i-doit version.
    if (!$addonChecker->canInstall($p_packageJSON['identifier'], $p_packageJSON['version'])) {
        $addonTitle = $p_packageJSON['title'] ?: ucfirst($p_packageJSON['identifier']);
        $compatibleVersion = $addonChecker->getCompatibleVersion($p_packageJSON['identifier']);
        $givenVersion = $p_packageJSON['version'];

        throw new Exception("{$addonTitle} can not be installed, please try to install at least version {$compatibleVersion} (you provided {$givenVersion})");
    }

    if (isset($p_packageJSON['requirements']['core'])) {
        $l_requirements = explode(' ', $p_packageJSON['requirements']['core']);

        if (!isset($l_requirements[1])) {
            throw new Exception('Invalid package.json format. Could not read requirements');
        }

        $l_current_version = $g_product_info['version'];
        $l_version_requirement = $l_requirements[1];
        $l_operator = $l_requirements[0];

        if (!version_compare($l_current_version, $l_version_requirement, $l_operator)) {
            switch ($l_requirements[0]) {
                case '>=':
                    throw new Exception(sprintf(
                        'Error: i-doit Version requirement for this add-on does not match: Core %s. Update to version %s and try again.',
                        $p_packageJSON['requirements']['core'],
                        $l_requirements[1]
                    ));
                case '<=':
                    throw new Exception(sprintf(
                        'Error: i-doit Version requirement for this add-on does not match: Core %s. Update to version %s and try again.',
                        $p_packageJSON['requirements']['core'],
                        $l_requirements[1]
                    ));
            }
        }
    } else {
        throw new Exception('Invalid package.json format. Core requirement missing');
    }

    if (isset($p_packageJSON['dependencies']['php']) && is_array($p_packageJSON['dependencies']['php'])) {
        foreach ($p_packageJSON['dependencies']['php'] as $l_dependency) {
            /**
             * @todo Remove this special mysql handling if it is not needed anymore
             */
            if ($l_dependency === 'mysql' && version_compare(PHP_VERSION, '5.6') === 1) {
                if (!extension_loaded('mysqli') && !extension_loaded('mysqlnd')) {
                    throw new Exception(sprintf('Error: PHP extension mysqli or mysqlnd needed for this add-on. Please install the extension and try again.'));
                }
            } else {
                if (!extension_loaded($l_dependency)) {
                    throw new Exception(sprintf('Error: PHP extension %s needed for this add-on. Please install the extension and try again.', $l_dependency));
                }
            }
        }
    }

    // Prepare mandator array.
    if ($p_tenant) {
        $l_tenants = [$p_tenant];
    } else {
        $l_tenant_result = $l_dao_mandator->get_mandator();

        while ($l_row = $l_tenant_result->get_row()) {
            $l_tenants[] = $l_row['isys_mandator__id'];
        }
    }

    // Include module installscript if available.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php')) {
        include_once($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/isys_module_' . $p_packageJSON['identifier'] . '_install.class.php');
    }

    // Delete files if necessary.
    if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_files.xml')) {
        (new isys_update_files())->delete($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install');
    }

    // Iterate through prepared mandators and install module into each of them.
    foreach ($l_tenants as $tenantId) {
        if ($tenantId > 0) {
            // Connect mandator database
            $mandatorDatabase = connect_mandator($tenantId);

            /**
             * Module manager needs to be initialized for each tenant because it is possible that a new tenant
             * has been added and the isys_module entry does not exists for the uploaded module.
             *
             * @see ID-3547
             */

            // Install module with package.
            $moduleId = (new isys_module_manager($mandatorDatabase))->installAddOn($p_packageJSON, $clearTempDir);

            if ($moduleId === false) {
                throw new Exception('Add-on ' . $p_packageJSON['title'] ?: ucfirst($p_packageJSON['identifier']) . ' could not be installed.');
            }

            // Update Databases.
            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml')) {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_data.xml', $mandatorDatabase);
            }

            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml')) {
                $l_db_update->update_database($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/install/update_sys.xml', $g_comp_database_system);
            }

            // When a package.json already exists, this is an update.
            if (file_exists($g_absdir . '/src/classes/modules/' . $p_packageJSON['identifier'] . '/package.json')) {
                $type = 'update';
            } else {
                $type = 'install';
            }

            $moduleClassName = 'isys_module_' . $p_packageJSON['identifier'];
            $updateSettings = false;

            if (class_exists($moduleClassName) && is_a($moduleClassName, 'idoit\AddOn\InstallableInterface', true)) {
                $moduleClassName::install($mandatorDatabase, $g_comp_database_system, $moduleId, $type, $tenantId);
                $updateSettings = true;
            } else {
                // Call module installscript if available.
                $l_installclass = 'isys_module_' . $p_packageJSON['identifier'] . '_install';
                if (class_exists($l_installclass)) {
                    call_user_func([$l_installclass, 'init'], $mandatorDatabase, $g_comp_database_system, $moduleId, $type, $tenantId);

                    $updateSettings = true;
                }
            }

            if ($updateSettings && is_object($g_comp_database_system)) {
                // Set installdate in system settings
                $sql = "REPLACE INTO isys_settings SET
                    isys_settings__key = 'admin.module." . $p_packageJSON['identifier'] . ".installed',
                    isys_settings__value = '" . time() . "',
                    isys_settings__isys_mandator__id = '" . $tenantId . "';";
                $g_comp_database_system->query($sql);

                // Mark this tenant that the properties have to be renewed
                $sql = "REPLACE INTO isys_settings SET
                    isys_settings__key = 'cmdb.renew-properties',
                    isys_settings__value = 1,
                    isys_settings__isys_mandator__id = '" . $tenantId . "';";
                $g_comp_database_system->query($sql);

                // @see ID-6684 Always remove duplicated category assignments after add-on installation (noticed via CMK2-16).
                (new CiTypeCategoryAssigner($mandatorDatabase))->deleteDuplicateAssignments();
            }
        }
    }

    if ($clearTempDir) {
        // Delete cache.
        $l_deleted = 0;
        $l_undeleted = 0;
        isys_glob_delete_recursive(isys_glob_get_temp_dir(), $l_deleted, $l_undeleted);
    }

    // Re-Create constant cache.
    ConstantManager::instance(
        $g_comp_database_system,
        new isys_component_database_proxy(),
        isys_component_session::instance()
    )->createSystemCacheFile();

    return true;
}
