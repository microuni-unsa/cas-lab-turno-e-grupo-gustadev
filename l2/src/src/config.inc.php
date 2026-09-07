<?php

/**
 * i-doit
 *
 * Basic configuration
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/*
 * Database configuration
 *
 * This configuration is for the system database. Don't forget to use MySQL with the InnoDB table-driver.
 * Only TCP/IP Hosts are supported here, no UNIX sockets!
 */
$g_db_system = [
    'type' => 'mysqli',
    'host' => 'idoit-db',
    'port' => '3306',
    'user' => 'idoit',
    'pass' => 'idoitpass',
    'name' => 'idoit_system'
];

/*
 * Security configuration
 */
$g_security =[
    'passwords_encryption_method' => 'argon2i'
];

/*
 * This login is used for the i-doit administration GUI. Note that an empty password will not work.
 * Leave the password empty to disable the admin center.
 *
 * Use the GUI or bcrypt to crypt your password.
 *
 * Syntax: 'username' => 'bcrypt-encrypted-password'
 */
$g_admin_auth = [
    'admin' => '$2y$10$rGl42ByoUgZYeN8CsBuMC.9RqHNZuAsnPynMGnelHQgHILTcgYouG',
];

/*
 * Crypto hash used as key for encryption with phpseclib.
 */
$g_crypto_hash = '52e10f9082799d8c18679719720dea88a4971027';

/*
 * It is possible to deactivate add-on uploads for the admin-center.
 */
$g_disable_addon_upload = '0';

/*
 * It is possible to deactivate update procedure for the GUI.
 */
$g_enable_gui_update = '1';

/*
 * i-doit License token.
 */
$g_license_token = '';

/**
 * Is cloud indicator
 */
$g_is_cloud = '0';

/**
 * Active features
 */
$g_active_features = [''];
