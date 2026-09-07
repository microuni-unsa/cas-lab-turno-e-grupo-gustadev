<?php

namespace idoit\Module\Manager\Addon;

use DateTime;
use idoit\AddOn\AddonVerify;
use isys_application;
use isys_component_dao_mandator;
use isys_component_database;

class BundleInstaller
{
    /**
     * @var array
     */
    private array $addonsToBeInstalled = [];

    /**
     * @var int
     */
    private int $tenantId = 1;

    /**
     * @return bool
     */
    public static function hasBundle()
    {
        $addonsDir = BASE_DIR . 'addons';
        if (!file_exists($addonsDir)) {
            return false;
        }
        $addons = scandir($addonsDir);
        $addons = array_filter($addons, fn ($item) => strpos($item, '.'));
        if (!count($addons)) {
            return false;
        }

        return true;
    }

    /**
     * @return array|false
     */
    public static function readAddonBundle()
    {
        if (!self::hasBundle()) {
            return false;
        }

        $addonsDir = BASE_DIR . 'addons';
        if (($addons =  scandir($addonsDir))) {
            $addons = array_filter(array_map(function ($addon) use ($addonsDir) {
                if (strlen($addon) < 3) {
                    return null;
                }

                $addonZip = $addonsDir . '/' . $addon;
                $packageJsonRaw = file_get_contents('zip://' . $addonZip .'#package.json', false);

                if (!$packageJsonRaw) {
                    return null;
                }

                $packageJsonContent = json_decode($packageJsonRaw, true);
                if (!isset($packageJsonContent['identifier'])) {
                    return null;
                }

                return $packageJsonContent['identifier'];
            }, $addons), fn ($item) => $item !== null);
        }

        return $addons;
    }

    /**
     * @param isys_component_database $systemDb
     * @param array $addonsToBeInstalled
     * @param int $tenantId
     *
     * @return $this
     */
    public function prepareEnvironment(isys_component_database $systemDb, array $addonsToBeInstalled = [], int $tenantId = 1)
    {
        global $g_comp_database_system, $g_dirs, $g_product_info, $l_dao_mandator;

        $this->addonsToBeInstalled = $addonsToBeInstalled;
        $this->tenantId = $tenantId;

        if ($g_comp_database_system === null) {
            $g_comp_database_system = $systemDb;
            isys_application::instance()->container->set('database_system', $systemDb);
        }

        if ($l_dao_mandator === null) {
            $l_dao_mandator = new isys_component_dao_mandator($g_comp_database_system);
        }

        if (!isset($g_dirs['temp'])) {
            $g_dirs['temp'] = BASE_DIR . 'temp';
        }

        if ($g_product_info === null) {
            include_once BASE_DIR . 'src/version.inc.php';
            global $g_product_info;
        }
        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function installBundle()
    {
        if (!self::hasBundle()) {
            return false;
        }
        require_once BASE_DIR . '/admin/src/functions.inc.php';

        if (!function_exists('install_module_by_zip') || !function_exists('connect_mandator')) {
            return false;
        }

        global $g_comp_database;

        $tenantId = $this->tenantId;
        $g_comp_database = connect_mandator($tenantId);

        $addonsDir = BASE_DIR . 'addons';
        $addonsToBeInstalledFile = BASE_DIR . \isys_module_manager::ADDON_INSTALL_INSTRUCTIONS_FILE;
        $addonsToBeInstalled = $this->addonsToBeInstalled;

        if (empty($addonsToBeInstalled)) {
            if (!file_exists($addonsToBeInstalledFile)) {
                return false;
            }

            $handler = fopen($addonsToBeInstalledFile, "rb");
            while (($line = fgets($handler)) !== false) {
                $addonsToBeInstalled[] = trim($line);
            }
        }

        $addons = scandir($addonsDir);
        $addons = array_filter($addons, function ($e) {return strpos($e, '.zip') !== false;});
        foreach ($addons as $addon) {
            $addonZip = $addonsDir . '/' . $addon;
            $packageJsonRaw = file_get_contents('zip://' . $addonZip . '#package.json', false);

            if (!$packageJsonRaw) {
                continue;
            }

            $packageJsonContent = json_decode($packageJsonRaw, true);
            if (!isset($packageJsonContent['identifier'])) {
                continue;
            }

            if (in_array($packageJsonContent['identifier'], $addonsToBeInstalled)) {
                install_module_by_zip($addonsDir . '/' . $addon, $tenantId);
            }
        }

        return true;
    }
}
