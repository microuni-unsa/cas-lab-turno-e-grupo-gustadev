<?php

use idoit\AddOn\AddonVerify;

/**
 * i-doit - Updates
 *
 * @package     i-doit
 * @subpackage  Update
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

$template = isys_application::instance()->container->get('template');

$www_dir = rtrim(str_replace(['src/jsonrpc.php', 'index.php', 'console.php'], '', @$_SERVER['SCRIPT_NAME']), '/') . '/';

// Necessary for i-doit 1.11 update
$template->debugging = false;
$template->debugging_ctrl = 'NONE';

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
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return bool
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
     * @author Selcuk Kekec <skekec@i-doit.com>
     * @return mixed
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
     * @param  string $packageZip
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

if (!function_exists('isys_file_put_contents')) {
    /**
     * A wrapper for the @link https://php.net/manual/en/function.file-put-contents.php to set the right permissions
     *
     * @param      $filename
     * @param      $data
     * @param int  $flags
     * @param null $context
     *
     * @return bool|int
     */
    function isys_file_put_contents($filename, $data, $flags = 0, $context = null)
    {
        if (!file_exists($filename)) {
            $directory = dirname($filename);
            if (!is_writable($directory)) {
                return false;
            }
            touch($filename);
            chmod($filename, 0664);
        }
        if (is_writable($filename)) {
            return file_put_contents($filename, $data, $flags, $context);
        }

        return false;
    }
}

if (!function_exists('checkPhpCompatibility')) {
    /**
     * @param isys_component_template $template
     *
     * @return void
     */
    function checkPhpCompatibility($template)
    {
        $stop = false;
        $message = '';
        $messageColor = 'red';

        try {
            // Get clean php version
            $l_php_version = getVersion(phpversion());

            if (version_compare($l_php_version, UPDATE_PHP_VERSION_MAXIMUM, '>')) {
                // PHP version is above maximum.
                $message = 'You are using <u>PHP ' . $l_php_version . '</u>. You are about to install i-doit with a PHP version that is currently not officially supported.
                    Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/en/installation/system-requirements.html" target="_blank">Knowledge Base</a>';
            } elseif (version_compare($l_php_version, UPDATE_PHP_VERSION_MINIMUM, '<')) {
                // PHP version is below minimum.
                $stop = true;
                $message = 'You are using <u>PHP ' . $l_php_version . '</u>. For updating i-doit to the next version you need at least PHP ' . UPDATE_PHP_VERSION_MINIMUM . '!';
            } elseif (version_compare($l_php_version, UPDATE_PHP_VERSION_DEPRECATED_BELOW, '<')) {
                // PHP version is deprecated.
                $messageColor = 'yellow';
                $message = 'WARNING! You are using <u>PHP ' . $l_php_version . '</u>. We discourage the use of PHP version below ' . UPDATE_PHP_VERSION_DEPRECATED_BELOW . '. We will drop support for it in a future release.
                    We urgently advise you to update your system to PHP ' . UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED . ', since the PHP version you are using is not supported
                    for any security issues and/or does not get any updates.
                    See <a href="http://php.net/supported-versions.php" target="_blank">http://php.net/supported-versions.php</a> for details.';
            } elseif (version_compare($l_php_version, UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED, '<')) {
                // PHPversion is not recommended.
                $messageColor = 'blue';
                $message = 'Attention! You are using <u>PHP ' . $l_php_version . '</u> and not the recommended PHP version ' . UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED . ' on your system.
                    We urgently advise you to update your system to PHP ' . UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED . '.
                    See <a href="http://php.net/supported-versions.php" target="_blank">http://php.net/supported-versions.php</a> for details.';
            }
        } catch (Exception $exception) {
            // Version information was not detectable
            $messageColor = 'yellow';
            $message = 'Please notice that i-doit was not able to determine a valid php version information.
                 You can check your system to identify the problem or resume the installation process on your own risk.
                 Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/en/installation/system-requirements.html" target="_blank">Knowledge Base</a>';
        }

        $template->assign('php_version_message', $message)
            ->assign('php_version_message_color', $messageColor);

        if ($stop) {
            $template->assign('g_stop', $stop);
        }
    }
}

if (!function_exists('checkMissingAddons')) {
    function checkMissingAddons(isys_update $update)
    {
        $checks = [
            [
                'table' => 'isys_ocs_db',
                'class' => 'isys_module_sectornord_ocs',
                'message' => 'Hello! In order to further improve the quality of the integrated OCS functionality,
                    we have transferred the OCS interface in i-doit to a separate i-doit add-on OCS with immediate effect.
                    This means, the OCS interface is no longer part of the i-doit core! If you would like to continue using this feature,
                    please contact our sales team directly at <a href="mailto:sales@i-doit.com">sales@i-doit.com</a>. As always, we have a solution for you! Thank you very much!
                    <br /><br />
                    If you do not use the OCS connection, you can ignore this information',
                'version' => '1.19'
            ]
        ];

        $missingAddons = [];

        $databases = $update->get_databases();
        $idoitVersion = $update->get_isys_info();
        $checksNecessary = array_filter($checks, function ($item) use ($idoitVersion) {
            return version_compare($idoitVersion['version'], $item['version'], '<=');
        });

        if (empty($checksNecessary)) {
            return [];
        }

        foreach ($databases as $tenantDb) {
            $dbObj = $update->get_database($tenantDb['name']);

            foreach ($checks as $val) {
                $tableExistsResult = $dbObj->query("SHOW TABLES LIKE '{$val['table']}';");
                if ($dbObj->num_rows($tableExistsResult) === 0 || isset($missingAddons[$val['class']])) {
                    continue;
                }
                $tableHasEntriesResult = $dbObj->query("SELECT * FROM {$val['table']} limit 1;");
                if (!$dbObj->num_rows($tableHasEntriesResult) === 0) {
                    continue;
                }

                if (class_exists($val['class'])) {
                    continue;
                }

                $missingAddons[$val['class']] = $val['message'];
            }
        }

        return $missingAddons;
    }
}

global $g_config, $g_absdir, $g_product_info, $g_comp_database, $g_comp_database_system;

// i-doit Temp Directory.
$g_temp_dir = $g_absdir . '/temp/';
$g_log_dir = $g_absdir . '/log/';

// Log File.
$idoitVersion = $g_product_info['version'];
$currentDate = date('Y-m-d');

// Update log.
$updateLogFile = "idoit_update_{$idoitVersion}_{$currentDate}.log";
$updateLogFilePath = $g_log_dir . $updateLogFile;
$wwwUpdateLogFilePath = $g_config['www_dir'] . 'log/' . $updateLogFile;

// Migration log.
$migrationLogFile = "idoit_migration_{$idoitVersion}_{$currentDate}.log";

// Update Temp file.
$g_tempfile = $g_temp_dir . "tmp_update.zip";

// Place where the i-doit update information are stored.
// @see  ID-6872  System settings can now provide a update-XML URL.
if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
    $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
} else {
    $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
}

// Your Apache user (Currently unused).
$g_apache_user = "www-data";

$g_updatedir = str_replace("\\", "/", dirname(__FILE__) . "/");

$g_versiondir = $g_updatedir . "versions/";
$g_upd_dir = $g_versiondir . $_SESSION["update_directory"];
$g_file_dir = $g_upd_dir . "/" . C__DIR__FILES;

$g_post = $_POST;
$g_get = $_GET;

$_SESSION["error"] = 0;

/* Increase session time while updating */
if (method_exists('isys_component_session', 'instance')) {
    isys_component_session::instance()
        ->set_session_time(999999999);
}

if (intval(ini_get('display_errors')) != 1) {
    ini_set("display_errors", "1");
}

if (intval(ini_get('memory_limit')) < 512) {
    ini_set("memory_limit", "512M");
}

set_time_limit(0);

$g_windows = false;
$g_unix = false;
if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
    $g_windows = true;
} else {
    $g_unix = true;
}

/**
 * Terminates the execution and shows an error.
 *
 * @param  string  $p_text
 * @param  string  $p_file
 * @param  integer $p_line
 */
function __die($p_text, $p_file, $p_line)
{
    die("An error occured in <b>" . $p_file . "</b>: <b>" . $p_line . "</b>:<br />" . $p_text);
}

/**
 * Get required classes
 *
 * @return  boolean
 */
function get_includes()
{
    global $g_updatedir;

    include_once($g_updatedir . "classes/isys_update.class.php");

    $l_fh = opendir($g_updatedir . "classes");

    while ($l_file = readdir($l_fh)) {
        if (strpos($l_file, ".") !== 0 && !include_once($g_updatedir . "classes/" . $l_file)) {
            __die("Could not load " . $g_updatedir . $l_file, __FILE__, __LINE__);
        }
    }

    return true;
}

if (get_includes()) {
    try {
        isys_auth_system_tools::instance()->idoitupdate(isys_auth::EXECUTE);

        if (isset($_POST['copy-files']) && $_POST['copy-files']) {
            // @see  ID-6346  New "sub-request" to copy all update related files.
            $updateLogger = isys_update_log::get_instance();
            $_SESSION['error'] = 0;

            $logEntryId = $updateLogger->add('Copying files to ' . $g_absdir . '..', C__MESSAGE, 'bold');

            $updateLogger->debug('Source-Directory: ' . $g_file_dir);
            $updateLogger->debug('--');

            $copySuccess = (new isys_update_files($g_file_dir))->copy();
            $message = '';

            if ($copySuccess) {
                $updateLogger->result($logEntryId, C__DONE);
            } else {
                $updateLogger->result($logEntryId, C__ERR, C__HIGH);
                $message = $_SESSION['error'] . ' file(s) where not copied correctly, please read the log located at <br /><code>' . $updateLogFilePath . '</code><br /> or retry this step!';
            }

            $updateLogger->write_debug(basename($updateLogFilePath));

            header('Content-Type: application/json');
            echo json_encode([
                'success' => $copySuccess,
                'data' => null,
                'message' => $message
            ]);
            die;
        }

        // Prepare Steps.
        $g_steps = [
            0 => "error.tpl",
            1 => "steps/1.tpl",
            2 => "steps/2.tpl",
            3 => "steps/4.tpl",
            4 => "steps/5.tpl",
            5 => "steps/6.tpl",
            6 => "steps/7.tpl",
            7 => "steps/8.tpl",
            8 => "steps/9.tpl"
        ];

        $l_steps = count($g_steps);

        $g_current_step = 1;
        if (empty($g_post["step"])) {
            $g_current_step = 1;
        } else {
            if ($g_post["step"] > $l_steps) {
                $g_current_step = $l_steps;
            } elseif ($g_post["step"] <= $l_steps) {
                $g_current_step = $g_post["step"];
            }
        }

        // Debug log.
        if (isset($_POST['debug_log']) && is_string($_POST['debug_log']) && !empty($_POST['debug_log'])) {
            $updateLogFilePath = $_POST['debug_log'];
        }

        // Debug log path.
        if (isset($_POST['debug_log_www']) && is_string($_POST['debug_log_www']) && !empty($_POST['debug_log_www'])) {
            $wwwUpdateLogFilePath = $_POST['debug_log_www'];
        }

        // Migration log.
        if (isset($_POST['migration_log_file']) && is_string($_POST['migration_log_file']) && !empty($_POST['migration_log_file'])) {
            $migrationLogFile = $_POST['migration_log_file'];
        }

        // Smarty assignments.
        $template->assign("g_steps", $g_steps)
            ->assign("g_config", $g_config)
            ->assign("debug_log", $updateLogFilePath)
            ->assign("debug_log_www", $wwwUpdateLogFilePath)
            ->assign("migration_log_file", $migrationLogFile);

        // Get isys_update.
        $l_update = new isys_update();

        // Get log component.
        $l_log = isys_update_log::get_instance();

        // Get and assign system information.
        $l_info = $l_update->get_isys_info();

        $template->assign("g_info", $l_info);
        $template->assign("g_product_info", $g_product_info);

        if ($g_current_step == 1) {
            /**
             * PHP Version Check
             */
            checkPhpCompatibility($template);

            /**
             * Database Version Check
             */
            try {
                // Get database version
                $result = $g_comp_database->query('SELECT VERSION() AS v;');
                $row = $g_comp_database->fetch_row_assoc($result);
                $rawDbVersion = $row['v'];

                $dbVersion = getVersion($rawDbVersion);

                // Detect MariaDB
                $l_is_mariadb = stripos($rawDbVersion, 'maria') !== false;

                // Setting check related variables based on DBMS
                if ($l_is_mariadb) {
                    $dbTitle = 'MariaDB';
                    $dbMinimumVersion = UPDATE_MARIADB_VERSION_MINIMUM;
                    $dbMaximumVersion = UPDATE_MARIADB_VERSION_MAXIMUM;
                    $dbRecommendedVersion = UPDATE_MARIADB_VERSION_MINIMUM_RECOMMENDED;
                    $dbDeprecatedBelowVersion = UPDATE_MARIADB_VERSION_DEPRECATED_BELOW;
                    $dbKbLink = '';
                } else {
                    $dbTitle = 'MySQL';
                    $dbMinimumVersion = UPDATE_MYSQL_VERSION_MINIMUM;
                    $dbMaximumVersion = UPDATE_MYSQL_VERSION_MAXIMUM;
                    $dbRecommendedVersion = UPDATE_MYSQL_VERSION_MINIMUM_RECOMMENDED;
                    $dbDeprecatedBelowVersion = UPDATE_MYSQL_VERSION_DEPRECATED_BELOW;
                    $dbKbLink = '';
                }

                $template
                    ->assign('currentDbVersion', $dbVersion)
                    ->assign('miniumDbVersion', $dbMinimumVersion)
                    ->assign('maxiumDbVersion', $dbMaximumVersion)
                    ->assign('recommendedDbVersion', $dbRecommendedVersion)
                    ->assign('dbTitle', $dbTitle);

                // Check version is supported
                if (!checkVersion($dbVersion, $dbMinimumVersion, $dbMaximumVersion)) {
                    // Check version is above supported version
                    if (checkVersionIsAbove($dbVersion, $dbMaximumVersion)) {
                        $template->assign(
                            "sql_version_error",
                            "You are using <u>{$dbTitle} {$dbVersion}</u>. You are about to install i-doit with a ". $dbTitle ." version that is currently not officially supported.
                            please have a look at the official system requirements in the <a href=\"https://kb.i-doit.com/en/installation/system-requirements.html\">Knowledge Base</a>"
                        )
                            ->assign("g_stop", false);
                    } else {
                        // Version is below of supported version
                        $template->assign(
                            "sql_version_error",
                            "You are using <u>{$dbTitle} {$dbVersion}</u>. For updating i-doit to the next version you need at least ". $dbTitle ." " . $dbMinimumVersion .
                            "!<br /><a href=\"https://kb.i-doit.com/en/installation/system-requirements.html\" target=\"_blank\">See our Knowledge Base article for help!</a>"
                        )
                            ->assign("g_stop", true);
                    }
                } elseif (version_compare($dbVersion, $dbDeprecatedBelowVersion, '<')) {
                    $template->assign(
                        "sql_version_error",
                        "We discourage the use of " . $dbTitle . " version below " . $dbDeprecatedBelowVersion . ".
                        We will drop support for it in a future release.
                        Please upgrade " . $dbTitle . " to one of the stable versions.
                        We recommend version " . $dbRecommendedVersion . " for the best user experience."
                    )
                        ->assign("g_stop", false);
                }
            } catch (Exception $exception) {
                // Version information was not detectable
                $template->assign(
                    "sql_version_error",
                    'Please notice that i-doit was not able to determine a valid MySQL/MariaDB version information.
                     You can check your system to identify the problem or resume the installation process on your own risk.
                     Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/en/installation/system-requirements.html">Knowledge Base</a>'
                )
                    ->assign("g_stop", false);
            }

            $l_php_settings = [
                'magic_quotes_gpc' => [
                    'check'   => !ini_get('magic_quotes_gpc'),
                    'value'   => (ini_get('magic_quotes_gpc') ? 'ON' : 'OFF'),
                    'message' => 'You should turn magic_quotes_gpc <b>off</b> in order to update i-doit.'
                ],
                'max_input_vars'   => [
                    'check'   => (ini_get('max_input_vars') == 0 || ini_get('max_input_vars') >= 10000),
                    'value'   => ini_get('max_input_vars'),
                    'message' => 'You should set max_input_vars to at least <b>10000</b> in order to update i-doit.'
                ],
                'post_max_size'    => [
                    'check'   => (ini_get('post_max_size') == 0 || isys_convert::to_bytes(ini_get('post_max_size')) >= isys_convert::to_bytes('128M')),
                    'value'   => ini_get('post_max_size'),
                    'message' => 'You should set post_max_size to at least <b>128M</b> in order to update i-doit.'
                ]
            ];

            $l_failed_php_settings = array_filter($l_php_settings, function ($p_setting) {
                return !$p_setting['check'];
            });

            // Disable the updater, if one or more PHP settings do not match.
            if (count($l_failed_php_settings)) {
                $template->assign("g_stop", true);
            }

            $template
                ->assign('php_settings', $l_php_settings)
                ->assign('dependencies', isys_update::get_module_dependencies())
                ->assign('apache_dependencies', isys_update::get_module_dependencies(null, 'apache'));
        }

        if ($g_current_step == 2 || $g_current_step == 4) {
            // Databases (prev|next)
            if (isset($g_post["system_database"])) {
                $_SESSION["system_database"] = $g_post["system_database"];
            } else {
                $_SESSION["system_database"] = -1;
            }

            if (isset($g_post["databases"])) {
                $_SESSION["mandant_databases"] = $g_post["databases"];
            } else {
                $_SESSION["mandant_databases"] = -1;
            }
        }

        if ($g_current_step == 3 || $g_current_step == 5) {
            if (isset($g_post["no_file_update"])) {
                $_SESSION["no_file_update"] = $g_post["no_file_update"];
            }

            if (isset($g_post["no_temp"])) {
                $_SESSION["no_temp"] = $g_post["no_temp"];
            }

            if (isset($g_post["no_config"])) {
                $_SESSION["no_config"] = $g_post["no_config"];
            }
        }

        // Switch GUI-Steps.
        switch ($g_current_step) {
            case 2:
                /**
                 * #########################################################
                 *  Step 2 - Available Updates
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Check for available updates
                 *     - Download an update if available
                 *     - Unzip the downloaded update to $g_updatedir
                 *   - Show current version, revision and changelog
                 *   - Show a selectable list with current downloaded
                 *     updates
                 * ---------------------------------------------------------
                 */

                /**
                 * Get file-component
                 */
                $l_files = new isys_update_files();

                /**
                 * Check for updates on i-doit.org
                 */
                if (isset($g_post["check_update"]) && $g_post["check_update"] == "true") {
                    try {
                        // Get available update from our server.
                        $onlineUpdates = array_reverse($l_update->get_new_versions($l_update->fetch_file($updateXmlUrl)));

                        // First we'd like to filter out any unusable updates.
                        $filteredUpdates = array_filter($onlineUpdates, function ($update) use ($l_info) {
                            if ($update['revision'] <= $l_info['revision']) {
                                return false;
                            }

                            if (isset($update['requirement']['revision']) && $update['requirement']['revision'] > $l_info['revision']) {
                                return false;
                            }

                            return true;
                        });

                        $isUnskippable = fn (array $update): bool => intval($update['version']) % 5 === 0;

                        if (count($filteredUpdates) > 0) {
                            // Sort them back to "old -> new".
                            $filteredUpdates = array_reverse($filteredUpdates);

                            $updates = [];
                            $foundNextUnskippable = false;

                            // @see ID-11896 Show every update up to the next "unskippable" version (35, 40, 45, ...).
                            foreach ($filteredUpdates as $update) {
                                if (!$foundNextUnskippable) {
                                    $updates[] = $update;

                                    $foundNextUnskippable = $isUnskippable($update);
                                }
                            }

                            $template->assign("g_update", $updates);
                        } else {
                            $template->assign("g_update_message", "No updates available (for your version).");
                        }
                    } catch (Exception $e) {
                        $template
                            ->assign("g_update_message_class", "red")
                            ->assign("g_update_message", $e->getMessage());
                    }
                }

                // Assign curresponding url for update notices.
                if (defined('C__ENABLE__LICENCE')) {
                    $template->assign('site_url', C__URL__PRO);

                    if (isset($_SESSION['licenced']) && $_SESSION['licenced'] === false) {
                        $template->assign('licence_error', 'Error. Your license does not allow any updates');
                    }
                } else {
                    $template->assign('site_url', C__URL__OPEN);
                }

                // Download the new version.
                if (isset($g_post["dl_file"]) && strlen($g_post["dl_file"]) > 0) {
                    $revision = $g_post["dl_file"];

                    // @see ID-10023 Refer to the download version via revision.
                    $updatePackage = null;
                    $updates = array_reverse($l_update->get_new_versions($l_update->fetch_file($updateXmlUrl)));

                    foreach ($updates as $update) {
                        if ($update['revision'] == $revision) {
                            $updatePackage = $update;
                            break;
                        }
                    }

                    if ($updatePackage === null) {
                        $template
                            ->assign("g_update_message_class", "box-yellow p5")
                            ->assign("g_update_message", "Download could not be found, please try again.");
                    } else {
                        // Dowload the update and store it in $g_tempfile.
                        // @see ID-6872 Remove regex to check for "valid" host. This is no secure check.
                        if (isys_file_put_contents($g_tempfile, $l_update->fetch_file($updatePackage['filename'])) > 0) {
                            // Read and extract the zipfile to $g_updatedir.
                            if ($l_files->read_zip($g_tempfile, $g_absdir, false, true)) {
                                // Delete the temp file.
                                unlink($g_tempfile);

                                // The template should also know whether the download was successful or not.
                                $template->assign("g_downloaded", true);
                            } else {
                                $template->assign("g_update_message", "Extracting failed.");
                            }
                        } else {
                            $template
                                ->assign("g_update_message_class", "red")
                                ->assign("g_update_message", "Download failed. Check your internet connection.");
                        }
                    }
                }

                // Get Update-Directories.
                $l_avail_updates = $l_update->get_available_updates($g_versiondir);
                $template->assign("updates", $l_avail_updates);

                // Disable Button: Next, if no update available.
                if (is_array($l_avail_updates) && count($l_avail_updates) == 0) {
                    $template->assign("g_stop", true);
                }

                break;
            case 3:
                /**
                 * #########################################################
                 *  Step 3 - Databases
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Database selection to apply the update to
                 *   - Show a list with updatable system and tenant databases
                 *   - Preselect ALL databases (recommendation)
                 * ---------------------------------------------------------
                 */

                /**
                 * Prepare database list for GUI
                 */
                $template->assign("g_databases", $l_update->get_databases());

                /**
                 * Assign name of the system database
                 */
                $template->assign("g_system_database", $g_comp_database_system->get_db_name());
                $template->assign("sql_mode", $g_comp_database_system->get_strictmode());

                /**
                 * Store selected update directory to session
                 */
                /**
                 * Save the selected directory in a Session varible
                 */
                if (strlen($g_post["dir"]) > 0) {
                    $_SESSION["update_directory"] = $g_post["dir"];
                }

                if (!isset($_SESSION["update_directory"])) {
                    $g_current_step = 0;
                    $template->assign("g_message", "<p>No Directory selected.<p/>" . "<p>Update aborted..</p>");
                }

                /**
                 * PHP Version Check
                 */
                checkPhpCompatibility($template);

                $missingAddons = checkMissingAddons($l_update);
                if (!empty($missingAddons)) {
                    $template->assign(
                        'missingAddonNotification',
                        implode(', ', $missingAddons)
                    );
                }

                break;
            case 4:
                /**
                 * #########################################################
                 *  Step 4 - File Update
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Show files which will be updated (Overview)
                 *   - Last step before the _real_ update begins
                 * ---------------------------------------------------------
                 */

                if (!is_writable($g_absdir) || !is_writable($g_absdir . '/src') || !is_writable($g_absdir . '/src/config.inc.php') || !is_writable($g_absdir . '/admin') ||
                    (file_exists($g_absdir . '/.htaccess') && !is_writable($g_absdir . '/.htaccess'))) {
                    $template->assign("g_not_writeable", true);
                }

                if (strlen($_SESSION["update_directory"]) > 0) {
                    if (is_dir($g_file_dir)) {
                        $l_files = new isys_update_files($g_file_dir);
                        $l_files_array = $l_files->getdir();
                        $count = 0;

                        $l_files_html = "";
                        if ($l_files_array !== null && $l_files_array instanceof RecursiveIteratorIterator) {
                            foreach ($l_files_array as $l_current_file) {
                                $count++;
                                $l_files_html .= "[+] " . $l_current_file . "\n";
                            }
                        } else {
                            $l_files_html = "No Files to update available";
                        }

                        $template->assign("g_files", $l_files_html);
                        $template->assign("g_filecount", $count);
                    } else {
                        $template->assign("g_filecount", 0);
                        $template->assign("g_files", "No files will be updated.");
                    }
                } else {
                    $template->assign("g_stop", true);
                }

                /**
                 * Assign bool value to verify the os
                 */
                $template->assign("g_unix", $g_unix);

                break;
            case 5:
                /**
                 * #########################################################
                 *  Step 5 - The allmighty i-doit Update
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Apply the selected update
                 *   - Update the previously selected databases
                 *   - Copy new files into the i-doit directory
                 *   - Show a log with notice/error messages
                 *   - write a debug log to i-doit/log/idoit-update-time.log
                 * ---------------------------------------------------------
                 *
                 * @todo backup i-doit (files and/or database)
                 */

                $l_system_database = $_SESSION["system_database"];
                $l_mandant_databases = $_SESSION["mandant_databases"];

                /* break, if session is clear unstead of skipping the databases */
                if (is_null($l_system_database) || is_null($l_mandant_databases)) {
                    $g_current_step = 0;
                    $template->assign("g_message", "<p>Your browser session was cleared somehow.<p/>" . "<p>Update aborted..</p>");
                    $template->assign("g_debug_info", "<p>Concrete debug information can be found at " . $updateLogFilePath . "</p>");
                }

                // Okay, let's go!
                if ($l_update->update($l_system_database, $l_mandant_databases)) {
                    // If the main update process has worked, we try to "install" (or "update") the PRO module (Just in case we update from i-doit OPEN).
                    if (is_array($l_mandant_databases) && count($l_mandant_databases)) {
                        $l_db_update = new isys_update_xml();

                        if (file_exists($g_absdir . '/src/classes/modules/pro/install/update_sys.xml') &&
                            is_readable($g_absdir . '/src/classes/modules/pro/install/update_sys.xml')) {
                            // Update the SYSTEM database.
                            $l_db_update->update_database($g_absdir . '/src/classes/modules/pro/install/update_sys.xml', $g_comp_database_system);
                        }

                        if (file_exists($g_absdir . '/src/classes/modules/pro/install/update_data.xml') &&
                            is_readable($g_absdir . '/src/classes/modules/pro/install/update_data.xml')) {
                            // Now update all (selected) TENANT databases.
                            foreach ($l_mandant_databases as $l_mandant_db_name) {
                                $l_db_update->update_database($g_absdir . '/src/classes/modules/pro/install/update_data.xml', $l_update->get_database($l_mandant_db_name));
                            }
                        }
                    }
                }

                /**
                 * Write debug log
                 */
                register_shutdown_function(function () use ($l_log, $updateLogFilePath) {
                    $l_log->write_debug(basename($updateLogFilePath));
                });

                /* Assign debug file */
                //$template->assign("debug_log", $updateLogFilePath);

                break;
            case 6:
                /**
                 * #########################################################
                 *  Step 6 - Migration
                 * #########################################################
                 * ---------------------------------------------------------
                 */
                $l_migration = new isys_update_migration();
                $l_migration_log = [];
                $l_update->get_databases();

                try {
                    if (is_array($_SESSION["mandant_databases"])) {
                        foreach ($_SESSION["mandant_databases"] as $l_db) {
                            $g_comp_database = $l_update->get_database($l_db);
                            $l_migration_log[$l_db] = $l_migration->migrate($g_upd_dir . "/" . C__DIR__MIGRATION);
                        }
                    }
                } catch (Exception $e) {
                    $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                }

                if (count($l_migration_log) <= 0) {
                    $l_migration_log[$g_product_info["version"]][] = "No migration code needed this time.";
                }
                $template->assign("migration_log", $l_migration_log);
                break;

            case 7:
                $l_update->get_databases();
                $l_migration = new isys_update_property_migration();

                try {
                    if (is_array($_SESSION["mandant_databases"])) {
                        foreach ($_SESSION["mandant_databases"] as $l_db) {
                            $l_result[$l_db] = $l_migration->set_database($l_update->get_database($l_db))
                                ->reset_property_table()
                                ->collect_category_data()
                                ->prepare_sql_queries('g')
                                ->prepare_sql_queries('s')
                                ->prepare_sql_queries('g_custom')
                                ->execute_sql()
                                ->get_results();

                            // We only want to display the successfully migrated classes.
                            $l_result[$l_db] = array_keys($l_result[$l_db]['migrated']);
                            sort($l_result[$l_db]);

                            // ID-2797 Refreshing the configured lists to use the latest property data :)
                            isys_cmdb_dao_object_type::instance($l_update->get_database($l_db))
                                ->refresh_objtype_list_config(null, true);

                            try {
                                // Set default categories for all object types.
                                \idoit\Module\Cmdb\Model\CiTypeCategoryAssigner::factory($l_update->get_database($l_db))
                                    ->setAllCiTypes()
                                    ->setDefaultCategories()
                                    ->assign();
                            } catch (Exception $e) {
                                $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                            }
                        }
                    }

                    $template->assign('result', $l_result);
                } catch (Exception $e) {
                    $l_log->add($e->getMessage(), C__MESSAGE, "bold red indent");
                }
                break;

            case 8:
                if ($_SESSION["error"] >= 1) {
                    $l_message = "There are <strong>" . $_SESSION["error"] . "</strong> errors occurred. Your i-doit could run unstable now. <br />" .
                        "Visit our support forum at http://www.i-doit.org/ for any help.<br /><br />" .
                        "Detailed debug information can be found at <br /><u>{$updateLogFilePath}</u> on your i-doit web-server.";

                    $template->assign("g_message", "<strong>Error!</strong><br /><br />" . $l_message);
                } else {
                    $l_message = "Your i-doit installation has been successfully updated to a newer version.<br /><br />";

                    if (file_exists($updateLogFilePath)) {
                        $l_message .= "Detailed debug information can be found at <u>{$updateLogFilePath}</u> on your i-doit web-server.<br /></br />";
                    }

                    if (file_exists($g_log_dir . $migrationLogFile)) {
                        $l_message .= "Detailed migration information can be found at <u>" . $g_log_dir . $migrationLogFile . "</u> on your i-doit web-server.";
                    }

                    if (isset($_POST["config_backup"]) && file_exists($_POST["config_backup"])) {
                        $l_message .= "<br /><br />A backup of your old config file can be found at: <u>" . $_POST["config_backup"] . "</u>";
                    }

                    $template->assign("g_message", "<strong>Congratulations!</strong><br /><br />" . $l_message);
                }

                // Call system has changed post notification.
                isys_component_signalcollection::get_instance()
                    ->emit('system.afterChange');

                break;
            case 1:
            default:
                /**
                 * #########################################################
                 *  Step 1 - "Welcome Step"
                 * #########################################################
                 * ---------------------------------------------------------
                 *   - Welcome message
                 *   - Show i-doit and system information
                 * ---------------------------------------------------------
                 */

                /**
                 * Get OS information
                 */
                $l_os = [
                    "name"    => php_uname("s"),
                    "version" => php_uname("r") . " " . php_uname("v")
                ];

                $template->assign("g_os", $l_os);

                break;
        }

        /**
         * HTTPS Url
         */
        $template->assign("g_https", "https://" . $_SERVER["HTTP_HOST"] . $g_config["www_dir"] . "?load=update");

        /**
         * Load Smarty and display index file: update.tpl
         */
        $template->assign("g_current_step", $g_current_step);
        $template->assign("www_dir", $www_dir);
        $template->setTemplateDir($g_updatedir . "tpl/");
        $template->display("update.tpl");
    } catch (isys_exception_auth $e) {
        isys_glob_display_error($e->getMessage() . '<br /><a href="index.php">back</a></p>');
        die;
    } catch (Exception $e) {
        isys_glob_display_error($e->getMessage());
    }
}
