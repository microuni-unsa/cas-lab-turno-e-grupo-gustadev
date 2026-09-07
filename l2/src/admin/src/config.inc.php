<?php
/**
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Helper\Purify;
use idoit\Component\Security\Hash\Password;

global $g_comp_database, $g_config, $g_absdir, $g_comp_database_system, $g_db_system, $g_disable_addon_upload, $g_enable_gui_update, $g_license_token, $g_is_cloud, $g_active_features;

$l_template = isys_component_template::instance();

if (isset($_GET['action']) && $_GET['action'] === 'save') {
    $post = $_POST;
    $post = Purify::purifyParams($post);

    try {
        $licenseToken = $post['license_token'];
        if (Purify::purifyValueByRegex($licenseToken, '/[^[:alnum:]]+/') !== $licenseToken) {
            throw new Exception('Invalid license token');
        }

        $l_result = [
            'success' => true,
            'message' => 'Config successfully overwritten.'
        ];

        $l_new_pass = '';

        if ($post['admin_password'] != '' && $post['admin_password'] != '***') {
            $l_new_pass = $post['admin_password'];
        } elseif (isset($g_admin_auth)) {
            foreach ($g_admin_auth as $l_username => $l_password) {
                $l_new_pass = $l_password;
            }
        }

        if ($post['db_pass'] != '' && $post['db_pass'] != '***') {
            $l_db_pass = $post['db_pass'];
        } else {
            $l_db_pass = $g_db_system['pass'];
        }

        /**
         * Writing backup file
         */
        if (is_writable($g_absdir . 'src/')) {
            $l_backupfile = $g_absdir . 'src/config.bak.inc.php';
            if (@copy($g_absdir . '/src/config.inc.php', $l_backupfile)) {
                $l_result['message'] .= ' A backup was stored here: ' . $l_backupfile . '. Please move it to a save location.';
            } else {
                $l_result['message'] .= ' A backup file could not be created. The apache process may have a file permission problem writing to ' . $l_backupfile;
            }
        } else {
            $l_result['message'] .= ' <strong>A backup file could not be created. The apache process has no write permissions to ' . $g_absdir . 'src/</strong>';
        }

        $alreadyHashed = password_get_info($l_new_pass)['algo'];

        try {
            $l_connectiontest = new isys_component_database_mysqli($post['db_host'], $post['db_port'], $post['db_user'], $l_db_pass, $post['db_name']);

            global $g_crypto_hash;

            /*
            // @see ID-4822  Do not simply create a hash if none exists!
            if (empty($g_crypto_hash)) {
                $g_crypto_hash = sha1(uniqid('', true));
            }
            */

            $adminCenterPassword = !$alreadyHashed
                ? addslashes(Password::instance()->setPassword($l_new_pass)->hash())
                : $l_new_pass;

            // @see ID-9422 Don't use a hashed password if the crypto-hash is empty!
            if (trim($g_crypto_hash) === '') {
                $adminCenterPassword = $l_new_pass;
            }

            // Before writing the config, backup the old one.
            $configDir = $g_absdir . '/src';
            $updater = new isys_update_config();
            $updater->backup($configDir);

            $configMap = [
                '%config.adminauth.username%'                   => $post['admin_username'],
                '%config.adminauth.password%' => $adminCenterPassword,
                '%config.db.type%'                              => $post['db_type'],
                '%config.db.host%'                              => $post['db_host'],
                '%config.db.port%'                              => $post['db_port'],
                '%config.db.username%'                          => $post['db_user'],
                '%config.db.password%'                          => $l_db_pass,
                '%config.db.name%'                              => $post['db_name'],
                '%config.security.passwords_encryption_method%' => $post['password_encryption_method'],
                '%config.crypt.hash%'                           => $g_crypto_hash,
                '%config.admin.disable_addon_upload%'           => (int)$g_disable_addon_upload,
                '%config.admin.enable_gui_update%'              => (int)$g_enable_gui_update ?? 1,
                '%config.license.token%'                        => !empty($post['license_token']) ? $post['license_token'] : $g_license_token,
                '%config.cloud.active%'                         => is_numeric($g_is_cloud) ? (int)$g_is_cloud : 0,
                '%config.active_features.list%'                 => is_array($g_active_features) ? implode("','", $g_active_features) : ''
            ];
            $excludedKeys = [
                '%config.crypt.hash%',
                '%config.license.token%',
                '%config.admin.disable_addon_upload%',
                '%config.admin.enable_gui_update%',
                '%config.cloud.active%',
                '%config.active_features.list%'
            ];

            $configMap = Purify::purifyParamsByCallback($configMap, 'addslashes', $excludedKeys);
            write_config($g_absdir . '/setup/config_template.inc.php', $g_absdir . '/src/config.inc.php', $configMap);
        } catch (isys_exception_database $e) {
            $l_result['success'] = false;
            $l_result['message'] = 'Connection check failed! Please review your configuration. ' . $e->getMessage();
        }
    } catch (Exception $e) {
        $l_result['success'] = false;
        $l_result['message'] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($l_result);
    die;
}

$l_config = [];
$l_configFile = $g_absdir . '/src/config.inc.php';

if (is_writable($l_configFile)) {
    global $g_security;

    $l_template->assign('configWriteable', true);

    foreach ($g_admin_auth as $l_username => $l_password) {
        $l_config['admin'] = [
            'username' => $l_username,
            'password' => $l_password
        ];
    }

    $l_config['db'] = $g_db_system;
    $l_config['license_token'] = $g_license_token;
    $l_config['security'] = $g_security;
} else {
    $l_template->assign('configFilePath', $l_configFile);
}
$l_template->assign('is_argon_installed', defined('PASSWORD_ARGON2I'));
$l_template->assign('config', $l_config);
$l_template->assign('config_admin_disable_addon_upload', $g_disable_addon_upload);
