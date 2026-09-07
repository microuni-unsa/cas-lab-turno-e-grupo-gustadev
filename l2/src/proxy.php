<?php
/**
 * i-doit Proxy implementation using cURL Extension
 *
 * @version 1.1
 * @desc    Fetching AJAX requests via www
 * @package i-doit Report Manager
 * @uses    cURL Extension
 *
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT & ~E_WARNING);

// Some config initializations.
$g_absdir = rtrim(str_replace('\\', '/', __DIR__), '/') . '/';

if (!defined('ISYS_LANGUAGE_ENGLISH')) {
    define('ISYS_LANGUAGE_ENGLISH', 1);
}

try {
    $g_config = [
        'base_dir'      => $g_absdir,
        'www_dir'       => rtrim(str_replace(['src/jsonrpc.php', 'index.php', 'console.php'], '', @$_SERVER['SCRIPT_NAME']), '/') . '/',
        'theme'         => 'default',
        'startpage'     => 'index.php',
        'html-encoding' => 'utf-8'
    ];

    // Include global functions.
    include_once($g_absdir . 'src/functions.inc.php');
    // Include global constants.
    include_once($g_absdir . 'src/constants.inc.php');
    // Include autoloader
    include_once($g_absdir . 'src/autoload.inc.php');
    // Get i-doit configuration (for proxy settings).
    include_once($g_absdir . 'src/config.inc.php');

    isys_application::instance()
        ->language(null)
        ->bootstrap();

    if (!extension_loaded('curl')) {
        die('<strong>PHP curl extension is needed for fetching the reports via HTTP!<br /><br />Install/activate php-module: curl first.</strong>');
    }

    $l_sess_curl = curl_init('https://reports-ng.i-doit.org/');

    if (isys_settings::get('proxy.active', false)) {
        // curl_setopt($l_sess_curl, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($l_sess_curl, CURLOPT_PROXY, isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));

        if (isys_settings::get('proxy.username', false)) {
            curl_setopt($l_sess_curl, CURLOPT_PROXYUSERPWD, isys_settings::get('proxy.username') . ':' . isys_settings::get('proxy.password'));
        }
    }

    // ---------------------------------------------------------------------
    // Process post parameters */
    // ---------------------------------------------------------------------

    // @see ID-10031 Only pass 'version' as dynamic postfield.
    $version = (int)$_GET['version'];

    curl_setopt($l_sess_curl, CURLOPT_POST, true);
    curl_setopt($l_sess_curl, CURLOPT_POSTFIELDS, "json=1&version={$version}");

    // ---------------------------------------------------------------------
    // Set cURL-Options */
    // ---------------------------------------------------------------------
    curl_setopt($l_sess_curl, CURLOPT_HEADER, false);
    curl_setopt($l_sess_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($l_sess_curl, CURLOPT_SSL_VERIFYPEER, false);

    // ---------------------------------------------------------------------
    // Perform cURL.Session */
    // ---------------------------------------------------------------------
    $l_responseTEXT = curl_exec($l_sess_curl);

    $l_error = curl_error($l_sess_curl);
    if (!empty($l_error)) {
        $l_proxy_config = '';

        if (isys_settings::get('proxy.active')) {
            $l_proxy_config = str_replace('array', '<strong>Proxy configuration</strong>: ', isys_settings::get('proxy.host') . ':' . isys_settings::get('proxy.port'));
        }

        $l_error_message = "<strong>Error while connecting</strong>: " . curl_errno($l_sess_curl) . " - " . $l_error . "<br />\n" .
            "<strong>URL</strong>: https://reports-ng.i-doit.org/<br />\n" .
            $l_proxy_config;
        die($l_error_message);
    }

    // ---------------------------------------------------------------------
    // Set Content-Type and do output */
    // ---------------------------------------------------------------------
    header('Content-Type: application/json');
    echo $l_responseTEXT;

    // Close cURL Session.
    curl_close($l_sess_curl);
} catch (Exception $e) {
    echo $e->getMessage();
}
