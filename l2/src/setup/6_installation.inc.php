<?php
/**
 * i-doit
 *
 * Installer
 * Step 6
 * Installation procedure
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
global $g_absdir, $g_config;

include_once($g_absdir . "/setup/functions.inc.php");
include_once($g_absdir . "/src/classes/helper/isys_helper_install.php");

/* Connection to mysql database */
$g_dbLink = new mysqli(
    $g_config["config.db.host"]["content"],
    $g_config["config.db.root.username"]["content"],
    $g_config["config.db.root.password"]["content"],
    "",
    $g_config["config.db.port"]["content"]
);
$g_dbLink->query("SET sql_mode=''");

function process_after_posttransfer()
{
    global $g_config, $g_tpl_main, $l_next_disabled, $g_settings, $g_dbLink;

    $l_rollback = [];

    $addStatus = function (&$p_out, $p_text, $p_status, $p_error = null) {
        $l_error = "ERROR";

        if ($p_error) {
            $l_error .= " (" . $p_error . ")";
        }

        $p_out .= "<tr>" . "<td>&nbsp;</td>" . "<td class=\"stepLineData\">" . $p_text . "</td>" . "<td class=\"" .
            (($p_status) ? "stepLineStatusGood" : "stepLineStatusBad") . "\">" . (($p_status) ? "OK" : $l_error) . "</td>" . "</tr>";
        $p_out .= "<tr>" . "<td colspan=\"3\" class=\"stepLineSeperator\">" . "</td>" . "</tr>";
    };

    $rollBack = function ($p_rollback, &$p_out) use ($addStatus) {
        if (is_array($p_rollback) && count($p_rollback) > 0) {
            foreach ($p_rollback as $l_rbInfo) {
                $l_rbType = $l_rbInfo["type"];

                switch ($l_rbType) {
                    case "database":
                        // desc, query, link
                        {
                            $l_rbDesc = $l_rbInfo["desc"];
                            $l_rbQuery = $l_rbInfo["query"];
                            $l_rbLink = $l_rbInfo["link"];

                            if ($l_rbLink->query($l_rbQuery)) {
                                $addStatus($p_out, "<b>Rollback (DB-Query):</b> " . $l_rbDesc, true);
                            } else {
                                $addStatus($p_out, "<b>Rollback (DB-Query):</b> " . $l_rbDesc, false, $l_rbLink->error);
                            }
                        }
                        break;
                    case "file":
                        // desc, action, filename
                        {
                            $l_rbDesc = $l_rbInfo["desc"];
                            $l_rbAction = $l_rbInfo["action"];
                            $l_rbFilename = $l_rbInfo["filename"];

                            if ($l_rbAction == "delete") {
                                if (@unlink($l_rbFilename)) {
                                    $addStatus($p_out, "<b>Rollback (File-Delete):</b> " . $l_rbDesc . " (" . $l_rbFilename . ")", true);
                                } else {
                                    $addStatus($p_out, "<b>Rollback (File-Delete):</b> " . $l_rbDesc . " (" . $l_rbFilename . ")", false);
                                }
                            }
                        }
                        break;
                    default:
                        $addStatus($p_out, "Invalid rollback action! ($l_rbType)", false);
                        break;
                }
            }

            $addStatus($p_out, "Finished installation rollback!", true);
        }
    };

    if ($_POST["install_now"] == "1") {
        $l_nErrors = 0;
        $l_status = "";

        set_time_limit(0);

        if (is_object($g_dbLink)) {
            $addStatus($l_status, "Creating database connection", true);
        } else {
            $l_nErrors++;
            $addStatus($l_status, "Creating database connection", false, "LOST CONNECTION TO DATABASE");
        }

        /* Checking database names */
        //first, trim them
        $g_config["config.db.name"]["content"] = trim($g_config["config.db.name"]["content"]);
        $g_config["config.mandant.name"]["content"] = trim($g_config["config.mandant.name"]["content"]);

        if ($l_nErrors == 0) {
            /* We accept a-z, 0-9 and _ as database namens! */
            if (!preg_match("/^[a-z0-9_]+$/i", $g_config["config.db.name"]["content"])) {
                $addStatus($l_status, "Checking name of system database", false, "Only a-z, 0-9 and _ allowed!");
                $l_nErrors++;
            }

            if (!preg_match("/^[a-z0-9_]+$/i", $g_config["config.mandant.name"]["content"])) {
                $addStatus($l_status, "Checking name of mandator database", false, "Only a-z, 0-9 and _ allowed!");
                $l_nErrors++;
            }

            if ($l_nErrors == 0) {
                /* Both names are ok, good boy. */
                $addStatus($l_status, "Checking database names", true);
            }
        }

        /* Testing existence of Databases */
        if ($l_nErrors == 0) {
            if ($g_dbLink->query("SHOW DATABASES LIKE '" . $g_config["config.db.name"]["content"] . "'")->num_rows) {
                $addStatus($l_status, "System database already exists!", false);
                $l_nErrors++;
            }

            if ($g_dbLink->query("SHOW DATABASES LIKE '" . $g_config["config.mandant.name"]["content"] . "'")->num_rows) {
                $addStatus($l_status, "Mandator database already exists!", false);
                $l_nErrors++;
            }
        }

        /* Create system DB */
        if ($l_nErrors == 0) {
            $l_ret = $g_dbLink->query("CREATE DATABASE IF NOT EXISTS " . $g_config["config.db.name"]["content"] . " DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            if ($l_ret === false) {
                $l_nErrors++;
            }
            $addStatus($l_status, "Creating system database", $l_ret, $g_dbLink->error);
        }

        $l_rollback[] = [
            "type"  => "database",
            "desc"  => "Dropping system database",
            "link"  => $g_dbLink,
            "query" => "DROP DATABASE IF EXISTS `" . $g_config["config.db.name"]["content"] . "`"
        ];
        $l_rollback[] = [
            "type"  => "database",
            "desc"  => "Dropping mandant database",
            "link"  => $g_dbLink,
            "query" => "DROP DATABASE IF EXISTS `" . $g_config["config.mandant.name"]["content"] . "`"
        ];

        /* Create mandator DB */
        if ($l_nErrors == 0) {
            $l_ret = $g_dbLink->query("CREATE DATABASE IF NOT EXISTS `" . $g_config["config.mandant.name"]["content"] .
                "` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            if ($l_ret === false) {
                $l_nErrors++;
            }
            $addStatus($l_status, "Creating mandator database", $l_ret, $g_dbLink->error);
        }

        $l_out = false;

        /* Import system DB */
        if ($l_nErrors == 0) {
            /* @phpstan-ignore-next-line */
            $l_ret = mysql_import($g_config["config.db.name"]["content"], $g_settings["mysqlDumpSystem"], $l_out, $g_dbLink);

            if ($l_ret != 1) {
                $l_nErrors++;
            }
            $addStatus($l_status, "Importing system database", $l_ret, $l_out);
        }

        /* Import mandator DB */
        if ($l_nErrors == 0) {
            /* @phpstan-ignore-next-line */
            $l_ret = mysql_import($g_config["config.mandant.name"]["content"], $g_settings["mysqlDumpMandator"], $l_out, $g_dbLink);

            if ($l_ret != 1) {
                $l_nErrors++;
            }
            $addStatus($l_status, "Importing mandator database", $l_ret, $l_out);
        }

        /* Set Auto-Increment start value */
        if ($l_nErrors == 0) {
            $l_ret = $g_dbLink->query("ALTER TABLE " . $g_config["config.mandant.name"]["content"] . ".isys_obj " . "AUTO_INCREMENT = " .
                $g_dbLink->escape_string($g_config["config.mandant.autoinc"]['content']) . ";");

            if ($l_ret != 1) {
                $l_nErrors++;
            }
            $addStatus($l_status, "Setting Auto-Increment start value for objects", $l_ret, $l_out);
        }

        /* Do some system operations for mandator database */
        if ($l_nErrors == 0) {
            $l_ret = $g_dbLink->query('UPDATE isys_cmdb_status_changes SET isys_cmdb_status_changes__timestamp = NOW();');
            if ($l_ret != 1) {
                $l_nErrors++;
            }

            $l_ret = $g_dbLink->query('UPDATE isys_obj SET isys_obj__created = NOW(), isys_obj__updated = NOW();');
            if ($l_ret != 1) {
                $l_nErrors++;
            }
        }

        require_once __DIR__ . '/../src/autoload.inc.php';

        global $g_crypto_hash;

        $g_crypto_hash = $g_config['config.crypt.hash']['content'];

        /* Add mandator entries */
        if ($l_nErrors == 0) {
            /* @phpstan-ignore-next-line */
            $l_ret = add_mandator(
                $g_config["config.mandant.title"]["content"],
                $g_config["config.mandant.title"]["content"],
                $g_config["config.mandant.name"]["content"],
                $g_config["config.base.theme"]["content"],
                $g_config["config.db.host"]["content"],
                $g_config["config.db.port"]["content"],
                $g_config["config.mandant.name"]["content"],
                $g_config["config.db.username"]["content"],
                $g_config["config.db.password"]["content"],
                1,
                $g_config["config.db.name"]["content"],
                null,
                0,
                isys_helper_crypt::encrypt($g_config["config.db.password"]["content"])
            );

            if ($l_ret === false) {
                $l_nErrors++;
            }

            $addStatus($l_status, "Adding mandator to system database", $l_ret, $g_dbLink->error);
        }

        $possibleLocalhost = [
            'localhost',
            '127.0.0.1',
            '::1'
        ];

        $grantToHost = '%';
        if (in_array($g_config["config.db.host"]["content"], $possibleLocalhost)) {
            $grantToHost = 'localhost';
        }

        // Set rights for system database (localhost).
        if ($l_nErrors == 0) {
            $createUser = "create user if not exists '".$g_config["config.db.username"]["content"]."'@'".$grantToHost."'";

            if ($g_config["config.db.password"]["content"] != "") {
                $createUser .= " identified by '".$g_config["config.db.password"]["content"]."'";
            }

            $l_ret = $g_dbLink->query($createUser);

            if ($l_ret) {
                // @see ID-12310 Granting privileges (instead 'GRANT ALL') to tenant database.
                $l_grant = "GRANT ALL PRIVILEGES ON " . $g_config["config.db.name"]["content"] . ".* TO '" . $g_config["config.db.username"]["content"] . "'@'{$grantToHost}';";
                $l_ret = $g_dbLink->query($l_grant);
            }

            if ($l_ret === false) {
                $l_nErrors++;
            }

            $addStatus($l_status, "Adding system database privileges to " . $g_config["config.db.username"]["content"], $l_ret, $g_dbLink->error);
        }

        // Set rights for mandator database (localhost).
        if ($l_nErrors == 0) {
            // @see ID-12310 Granting privileges (instead 'GRANT ALL') to tenant database.
            $l_grant = "GRANT ALL PRIVILEGES ON " . $g_config["config.mandant.name"]["content"] . ".* TO '" . $g_config["config.db.username"]["content"] . "'@'{$grantToHost}';";
            $l_ret = $g_dbLink->query($l_grant);

            if ($l_ret === false) {
                $l_nErrors++;
            }

            $addStatus($l_status, "Adding mandator database privileges to " . $g_config["config.db.username"]["content"], $l_ret, $g_dbLink->error);

            $l_flush = "FLUSH PRIVILEGES;";
            $g_dbLink->query($l_flush);
        }
        $g_config["config.security.passwords_encryption_method"]["content"] = defined('PASSWORD_ARGON2I')?'argon2i':'bcrypt';
        $g_config["config.cloud.active"]["content"] = 0;
        $g_config["config.active_features.list"]["content"] = '';

        /* Write configuration file */
        if ($l_nErrors == 0) {
            $l_configData = @file_get_contents($g_settings["configTemplate"]);

            // Crypting admin center password
            if (trim($g_config['config.adminauth.password']['content']) !== '') {
                $g_config['config.adminauth.password']['content'] = addslashes(\idoit\Component\Security\Hash\Password::instance()
                    ->setPassword($g_config['config.adminauth.password']['content'])
                    ->hash());
            }

            if ($l_configData !== false) {
                /* Set configuration parameters now */
                foreach ($g_config as $l_key => $l_data) {
                    if ($l_key == "config.modules") {
                        continue;
                    }

                    $l_data["content"] = str_replace("\\", "\\\\", $l_data["content"]);

                    $l_configData = str_replace("%" . $l_key . "%", $l_data["content"], $l_configData);
                }

                $l_ret = $g_dbLink->query("REPLACE INTO isys_settings SET isys_settings__key = 'system.dir.file-upload', isys_settings__value = '" .
                    $g_dbLink->escape_string(@$g_config['config.dir.fileman.file']["content"]) . "';");
                if ($l_ret === false) {
                    $l_nErrors++;
                }

                $l_ret = $g_dbLink->query("REPLACE INTO isys_settings SET isys_settings__key = 'system.dir.image-upload', isys_settings__value = '" .
                    $g_dbLink->escape_string(@$g_config['config.dir.fileman.image']["content"]) . "';");
                if ($l_ret === false) {
                    $l_nErrors++;
                }

                $addStatus($l_status, "Setting config variables", $l_ret, $g_dbLink->error);

                /* Write configuration */
                $l_configFile = rtrim($g_config["config.dir.src"]["content"], "/") . DIRECTORY_SEPARATOR . $g_settings["configDestination"];

                $l_configFile = preg_replace("/[\\\\]+/i", "/", $l_configFile);

                $l_rollback[] = [
                    "type"     => "file",
                    "action"   => "delete",
                    "filename" => $l_configFile,
                    "desc"     => "Deleting configuration file"
                ];

                if (is_writable(dirname($l_configFile))) {
                    if (@isys_file_put_contents($l_configFile, $l_configData)) {
                        $addStatus($l_status, "i-doit configuration has been written to " . $l_configFile . "!", true);
                    } else {
                        $l_nErrors++;
                        $addStatus($l_status, "Cannot write configuration destination file!", false);
                    }
                } else {
                    $l_nErrors++;
                    $addStatus(
                        $l_status,
                        "Could not write i-doit config file. Directory " . dirname($l_configFile) . " is not writeable for the Apache/PHP process.",
                        false
                    );
                }
            } else {
                $l_nErrors++;
                $addStatus($l_status, "Cannot open configuration template!", false);
            }
        }

        //Insert persons to DB
        $persons_insertion_result = isys_helper_install::insertPersons(
            $g_config["config.db.host"]["content"],
            $g_config["config.db.username"]["content"],
            $g_config["config.db.password"]["content"],
            $g_config["config.mandant.name"]["content"],
            $g_config["config.db.port"]["content"]
        );

        if (!$persons_insertion_result['result']) {
            $l_nErrors++;
            $addStatus($l_status, $persons_insertion_result['message'], false);
        }

        try {
            if (isys_module_manager::hasBundle() && isys_module_manager::hasAddonInstallInstructions()) {
                $systemDb = new isys_component_database_mysqli(
                    $g_config["config.db.host"]["content"],
                    $g_config["config.db.port"]["content"],
                    $g_config["config.db.username"]["content"],
                    $g_config["config.db.password"]["content"],
                    $g_config["config.db.name"]["content"]
                );

                $l_nErrors += isys_module_manager::bundleInstall($systemDb, [], 1) ? 0 : 1;
            }
        } catch (Exception $e) {
            $l_nErrors++;
        }

        if ($l_nErrors > 0) {
            /* There were errors - rollback and cancel the installation */
            $rollBack($l_rollback, $l_status);
            $addStatus($l_status, "Installation failed!", false, $l_nErrors . "&nbsp;errors");
            $l_next_disabled = true;
        } else {
            // We want to add a link to the "quick objecttype configurator", therefore we need the module-ID.
            $l_res = $g_dbLink->query('SELECT isys_module__id FROM ' . $g_config["config.mandant.name"]["content"] .
                '.isys_module WHERE isys_module__const = "C__MODULE__QCW";');
            $l_qcw_row = $l_res->fetch_assoc();

            $addStatus(
                $l_status,
                "Installation done - please continue with 'Next' and login with username '<strong>admin</strong>' and password '<strong>admin</strong>'!",
                true
            );
            $l_next_disabled = false;

            /* @phpstan-ignore-next-line */
            tpl_set($g_tpl_main, [
                "FORM_ACTION" => '?',
                'QCW'         => isset($l_qcw_row['isys_module__id']) && $l_qcw_row['isys_module__id'] ? '<p>
                                            Please use the <strong><a href="?moduleID=' . $l_qcw_row['isys_module__id'] . '">Quick Object-Type Configurator</a></strong>
                                            for preparing your i-doit installation with all necessary object type groups, types and categories.
                                        </p>' : ''
            ]);
        }

        // Automatic license installation
        try {
            global $g_absdir;
            $bootLicenseFile = $g_absdir . '/boot-license.key';

            if (file_exists($bootLicenseFile)) {
                include_once($g_absdir . '/src/classes/modules/licence/init.php');
                isys_application::instance()->bootstrap();

                $systemDatabase = new isys_component_database_mysqli(
                    $g_config["config.db.host"]["content"],
                    $g_config["config.db.port"]["content"],
                    $g_config["config.db.root.username"]["content"],
                    $g_config["config.db.root.password"]["content"],
                    $g_config["config.db.name"]["content"]
                );

                $license = \idoit\Module\License\LicenseServiceFactory::createDefaultLicenseService(
                    $systemDatabase,
                    ''
                );

                $licenseArray = $license->parseLicenseFile($bootLicenseFile);
                $license->installLegacyLicense($licenseArray);

                $addStatus(
                    $l_status,
                    "Automatic license installation",
                    true
                );
            }
        } catch (\Throwable $e) {
            $addStatus(
                $l_status,
                "Automatic license installation",
                false
            );
        }

        /* @phpstan-ignore-next-line */
        tpl_set($g_tpl_main, [
            "INSTALL_STATUS" => $l_status
        ]);

        $g_dbLink->close();
    }
}

$l_previous_disabled = true;
$l_next_disabled = true;
