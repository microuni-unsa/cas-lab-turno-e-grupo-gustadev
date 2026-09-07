<?php

namespace idoit\Module\SyneticsAdmin\Controller;

use idoit\AddOn\Manager\Downloader;
use idoit\AddOn\Manager\ManagePackage;
use idoit\AddOn\Manager\ManageTenant;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Module\License\Entity\License;
use isys_application;
use isys_component_database;
use isys_component_template_language_manager;
use isys_module_manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Addon controller
 *
 * @package   idoit\Module\Synetics_admin\Controller
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class AddonController
{
    private string $appPath;
    private isys_component_database $database;
    private isys_component_template_language_manager $language;
    private isys_module_manager $moduleManager;

    public function __construct()
    {
        $app = isys_application::instance();

        $this->appPath = $app->app_path;
        $this->database = $app->container->get('database');
        $this->language = $app->container->get('language');
        $this->moduleManager = $app->container->get('moduleManager');
    }

    public function installAddon(Request $request): Response
    {
        global $g_license_token;

        $filename = BASE_DIR . 'temp/addon-' . substr(md5(microtime()), 0, 8) . '.zip';

        try {
            if (!\isys_auth_synetics_admin::instance()->canManageAddons()) {
                return JSONResponseFactory::permissionError($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_INSTALL'));
            }

            if (FeatureManager::isCloud()) {
                // @see ID-12077 Give specific 'cloud' feedback.
                return JSONResponseFactory::permissionError($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_INSTALL_CLOUD'));
            }

            $body = $request->toArray();
            $identifier = $body['identifier'];
            $downloadLink = $body['downloadLink'];

            if (!$identifier || !$downloadLink) {
                return JSONResponseFactory::error("Unable to find add-on with identifier {$identifier}");
            }

            $downloader = new Downloader();
            $downloader->download($downloadLink, $filename);
            $package = new ManagePackage("{$this->appPath}src/classes/modules/");
            $package->load($filename);

            if (!$package->isAddonValid()) {
                return JSONResponseFactory::error('Invalid add-on structure.');
            };

            if (!$package->checkRequirements()) {
                return JSONResponseFactory::error('Requirements for add-on are not met.');
            }

            if (!$package->unpack()) {
                return JSONResponseFactory::error('Unable to unpack add-on.');
            }

            if (!$package->moveToaddonRootPath()) {
                return JSONResponseFactory::error('Unable to move add-on folder to i-doit.');
            }

            $tenantPackage = new ManageTenant($this->database, "{$this->appPath}src/classes/modules/");
            $tenantPackage->setIdentifier($identifier);
            $tenantPackage->install();
            unlink($filename);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            unlink($filename);
            return JSONResponseFactory::error($e->getMessage());
        }
    }

    public function uninstallAddon(Request $request)
    {
        try {
            if (!\isys_auth_synetics_admin::instance()->canManageAddons()) {
                return JSONResponseFactory::permissionError($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_UNINSTALL'));
            }

            if (FeatureManager::isCloud()) {
                // @see ID-12077 Give specific 'cloud' feedback.
                return JSONResponseFactory::error($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_UNINSTALL_CLOUD'));
            }

            $body = $request->toArray();
            $identifier = $body['identifier'];

            if (!$this->moduleManager->is_installed($identifier)) {
                return JSONResponseFactory::error('Add-on is not installed.');
            }
            $addon = new ManageTenant($this->database, "{$this->appPath}src/classes/modules/");
            $addon->setIdentifier($identifier);
            if (!$addon->uninstall() || !$addon->deleteAddonFiles()) {
                return JSONResponseFactory::error('Uninstalling add-on failed.');
            }

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }

    public function setAddonStatus(Request $request)
    {
        try {
            if (!\isys_auth_synetics_admin::instance()->canManageAddons()) {
                return JSONResponseFactory::permissionError($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_MANAGE'));
            }

            if (FeatureManager::isCloud()) {
                // @see ID-12077 Give specific 'cloud' feedback.
                return JSONResponseFactory::error($this->language->get('LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_MANAGE_CLOUD'));
            }

            $body = $request->toArray();
            $identifier = $body['identifier'];
            $status = $body['status'];

            if (!$this->moduleManager->is_installed($identifier)) {
                return JSONResponseFactory::error('Add-on is not installed.');
            }
            $addon = new ManageTenant($this->database, "{$this->appPath}src/classes/modules/");
            $addon->setIdentifier($identifier);

            if (!$status) {
                $result = $addon->deactivate();
            } else {
                $result = $addon->activate();
            }

            if ($result !== true) {
                return JSONResponseFactory::error('Unable to perform action.');
            }

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }
}
