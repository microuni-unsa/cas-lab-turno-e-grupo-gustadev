<?php

/**
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Set error reporting.
use idoit\Component\ConstantManager;

$l_errorReporting = E_ALL & ~E_NOTICE;

if (defined('E_WARNING')) {
    $l_errorReporting &= ~E_WARNING;
}

if (defined('E_DEPRECATED')) {
    $l_errorReporting &= ~E_DEPRECATED;
}

if (defined('E_STRICT')) {
    $l_errorReporting &= ~E_STRICT;
}

error_reporting($l_errorReporting);

// @see  ID-8647  Deny usage of admin center in frames.
header('X-Frame-Options: deny');

// Start session.
session_start();

/**
 * @param string $value
 *
 * @return int
 */
function compute_bytes($value)
{
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    $value = (int)$value;

    switch ($last) {
        case 'g':
            $value *= 1024;
            // no break
        case 'm':
            $value *= 1024;
            // no break
        case 'k':
            $value *= 1024;
    }

    return (int)$value;
}

// Set maximal execution time.
if (ini_get("max_execution_time") < 600) {
    set_time_limit(600);
}

$memory_limit = compute_bytes(ini_get('memory_limit'));

if ($memory_limit < (128 * 1024 * 1024)) {
    ini_set('memory_limit', '128M');
}

$upload_max_filesize = compute_bytes(ini_get('upload_max_filesize'));

if ($upload_max_filesize < (8 * 1024 * 1024)) {
    ini_set('upload_max_filesize', '8M');
}

// Disable asserts.
assert_options(ASSERT_ACTIVE, 0);

// Publish admin center.
define("C__ADMIN_CENTER", true);

// Determine our directory.
global $g_config;

$g_absdir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/') . '/';

$g_config = [
    'base_dir'      => $g_absdir,
    'www_dir'       => rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/') . '/',
    'theme'         => 'default',
    'startpage'     => 'index.php',
    'html-encoding' => 'utf-8'
];

$g_dirs = [
    'temp'         => $g_absdir . 'temp/',
    'class'        => $g_absdir . 'src/classes/',
    'handler'      => $g_absdir . 'src/handler/',
    'css_abs'      => $g_absdir . 'src/themes/default/css/',
    'js_abs'       => $g_absdir . 'src/tools/js/',
    'smarty'       => $g_absdir . 'src/themes/default/smarty/',
    'utils'        => $g_absdir . 'src/utils/',
    'import'       => $g_absdir . 'src/classes/import/',
    'log'          => $g_absdir . 'log/',
    'images'       => $g_absdir . 'images/',
    'theme'        => $g_absdir . 'src/themes/default/',
    'tools'        => $g_absdir . 'src/tools/',
];

// Set default timezone.
date_default_timezone_set('Europe/Berlin');
//setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

if (!@include_once($g_absdir . "/src/config.inc.php")) {
    header("Location: ..");
}

if (!@include_once($g_absdir . "/src/constants.inc.php")) {
    die("Error loading file: " . $g_absdir . "/src/constants.inc.php");
}

if (!@include_once($g_absdir . "/src/convert.inc.php")) {
    die("Error loading file: " . $g_absdir . "/src/convert.inc.php");
}

if (!@include_once($g_absdir . "/src/autoload.inc.php")) {
    die("Could not load " . $g_absdir . "src/autoload.inc.php");
}

if (!@include_once($g_absdir . "/vendor/autoload.php")) {
    die("Could not load " . $g_absdir . "/vendor/autoload.php");
}

if (!@include_once($g_absdir . "/src/functions.inc.php")) {
    die("Could not load " . $g_absdir . "src/functions.inc.php");
}

// Include the "version.inc.php".
include_once($g_absdir . 'src/version.inc.php');

// Try to include the PRO init.php
// @see  ID-4551 + ID-6834  Adding condition to add footer attachment, when i-doit is a pro version
if (!defined('C__MODULE__PRO') && file_exists($g_absdir . '/src/classes/modules/pro/init.php')) {
    // Including the pro/init.php will result in multiple issues due to an unfinished container and the isys_application bootstrapping.
    define('C__MODULE__PRO', true);
} else {
    define('C__MODULE__PRO', false);
}

\idoit\Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\License', __DIR__ . '/../src/classes/modules/licence/src/');

// Include english language file
@include_once($g_absdir . "/src/lang/en.inc.php");

// Logout.
if (isset($_GET["logout"])) {
    unset($_SESSION);
    session_destroy();

    $parsed = parse_url((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], PHP_URL_PATH);

    header('Location: ' . ($parsed ?: '/admin'));
}

// Globalization.
global $g_db_system;

try {
    // Set custom warnings handler.
    set_error_handler(['isys_core', 'warning_handler'], $l_errorReporting);

    // Connect system database.
    $g_comp_database_system = isys_component_database::get_database(
        $g_db_system["type"],
        $g_db_system["host"],
        $g_db_system["port"],
        $g_db_system["user"],
        $g_db_system["pass"],
        $g_db_system["name"]
    );

    /**
     * @var \idoit\Module\License\LicenseService $licenseService
     */
    global $licenseService, $g_license_token;

    // @see  ID-6834  Only work with licenses in context of i-doit PRO.
    if (defined('C__MODULE__PRO') && C__MODULE__PRO && class_exists('idoit\\Module\\License\\LicenseServiceFactory')) {
        $licenseService = idoit\Module\License\LicenseServiceFactory::createDefaultLicenseService($g_comp_database_system, $g_license_token);
    }

    ConstantManager::instance(
        $g_comp_database_system,
        new isys_component_database_proxy(),
        isys_component_session::instance()
    )->includeSystemCache();

    // Get template engine.
    $g_dirs["smarty"] = $g_absdir . "/src/themes/default/smarty/";

    $l_template = isys_component_template::instance();
    $l_template->default_template_handler_func = null;

    $l_template
        ->setConfigDir($g_dirs["smarty"] . "configs/")
        ->setCompileDir($g_dirs["temp"] . "smarty/compiled/")
        ->setCacheDir($g_dirs["temp"] . "smarty/cache/");

    if (!defined("C__RECORD_STATUS__NORMAL")) {
        $l_template->assign(
            "system_error",
            'Constant cache not available. Delete the content of your temp/ directory and login to <a href="../">i-doit</a> in order to re-create the cache.'
        );
    }

    if (isset($_POST["username"]) && isset($_POST["password"])) {
        // @see  ID-6957  Empty submissions will immediately fail without checking the actual "admin-auth" values.
        if (is_null($g_admin_auth) || trim($_POST["username"]) === '' || trim($_POST["password"]) === '') {
            $l_template->assign('error', 'Admin login is not configured, yet. <br />Specify an admin password in your config.inc.php (Section: $g_admin_auth).');
        } else {
            global $g_crypto_hash;
            $loggedIn = false;

            if (isset($g_admin_auth[$_POST['username']])) {
                // if no crypto hash is set - the legacy compatibility mode. Passwords are stored as key-value pairs
                if (empty($g_crypto_hash)) {
                    $loggedIn = $_POST['password'] === $g_admin_auth[$_POST['username']];
                } else {
                    $verifyService = \idoit\Component\Security\Hash\PasswordVerify::instance();
                    $loggedIn = $verifyService->verify($_POST['password'], $g_admin_auth[$_POST['username']]);
                }
            }

            if ($loggedIn) {
                $_SESSION["logged_in"] = md5($_SERVER['SCRIPT_FILENAME'] . json_encode($g_admin_auth));
                $_SESSION["username"] = $_POST["username"];
            } else {
                $l_template->assign('error', 'Error logging in: <strong>Username or password incorrect!</strong>');
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
    die;
}

try {
    if (file_exists("src/functions.inc.php")) {
        include_once("src/functions.inc.php");
    }

    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === md5($_SERVER['SCRIPT_FILENAME'] . json_encode($g_admin_auth))) {
        if (!@include_once($g_absdir . "/src/bootstrap.inc.php")) {
            die("Error loading file: " . $g_absdir . "/src/bootstrap.inc.php");
        }

        $l_template
            ->assign('logged_in', true)
            ->assign('version', $g_product_info);

        if (isset($_GET["req"])) {
            $_GET["req"] = str_replace(chr(0), '', addslashes($_GET["req"]));
            if (file_exists("src/" . $_GET["req"] . ".inc.php")) {
                // Process requests
                include_once("src/" . $_GET["req"] . ".inc.php");
            }

            if (file_exists("templates/pages/" . $_GET["req"] . ".tpl")) {
                // Include template.
                $l_template->assign("request", "pages/" . $_GET["req"] . ".tpl");
            }
        }
    } else {
        if (php_sapi_name() == 'cli' && $argc > 1) {
            if (!@include_once($g_absdir . "/src/bootstrap.inc.php")) {
                die("Error loading file: " . $g_absdir . "/src/bootstrap.inc.php");
            }

            include_once('cli.inc.php');
            die;
        } else {
            $parameterParts = $_GET;

            unset($parameterParts['logout']);

            // Remember user agent and ip address.
            isys_component_session::instance()->remember_user();

            $l_template
                ->assign('loginAction', isys_helper_link::create_url($parameterParts))
                ->assign("request", "pages/login.tpl");
        }
    }
} catch (InvalidArgumentException $e) {
    ;
} catch (Exception $e) {
    $l_template->assign("system_error", $e->getMessage());
}

try {
    // Display content.
    $l_template
        ->setTemplateDir(__DIR__ . '/templates/')
        ->display('index.tpl');
} catch (Exception $e) {
    echo $e->getMessage();
}
