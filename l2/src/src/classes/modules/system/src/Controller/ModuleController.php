<?php

namespace idoit\Module\System\Controller;

use idoit\Component\FeatureManager\FeatureCheckInterface;
use isys_application;
use isys_auth;
use isys_auth_cmdb;
use isys_format_json;
use isys_helper_link;
use isys_module_itservice;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * System relevant module controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ModuleController
{
    public function getForMenu(): Response
    {
        $moduleManager = isys_application::instance()->container->get('moduleManager');
        $language = isys_application::instance()->container->get('language');
        $wwwPath = isys_application::instance()->www_path;

        // Fetch the modules.
        $addonResult = $moduleManager->get_modules(null, null, true);

        $entries = [];

        // Iterate through the modules and display each.
        while ($addon = $addonResult->get_row()) {
            try {
                // Check if the class exists.
                if (!class_exists($addon['isys_module__class'])) {
                    continue;
                }

                // Check if the 'display in main menu' constant is set.
                if (!constant($addon['isys_module__class'] . '::DISPLAY_IN_MAIN_MENU')) {
                    continue;
                }

                $interfaces = class_implements($addon['isys_module__class']);

                if (in_array(FeatureCheckInterface::class, $interfaces) && !$addon['isys_module__class']::isFeatureEnabled()) {
                    continue;
                }

                // Check if the add-on has a package.json.
                $packageJsonPath = BASE_DIR . 'src/classes/modules/' . $addon['isys_module__identifier'] . '/package.json';

                if (file_exists($packageJsonPath)) {
                    $packageJsonContent = file_get_contents($packageJsonPath);

                    if (!isys_format_json::is_json_array($packageJsonContent)) {
                        continue;
                    }

                    $packageJsonData = isys_format_json::decode($packageJsonContent);

                    if ($packageJsonData['type'] === 'addon') {
                        continue;
                    }
                }

                // Get auth class
                $authInstance = $moduleManager->get_module_auth($addon['isys_module__id']);

                // Check for rights if module is authable otherwise display it
                if ((is_a($authInstance, 'isys_auth') && $authInstance->has_any_rights_in_module()) || $authInstance === false) {
                    $url = $wwwPath . isys_helper_link::create_url([C__GET__MODULE_ID => $addon['isys_module__id']]);

                    if (defined($addon['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK') && constant($addon['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK')) {
                        $url = $wwwPath . $addon['isys_module__identifier'];
                    }

                    // @see ID-10716 We only skip the 'CMDB' entry itself, so that 'Relations' gets shown.
                    if ($addon['isys_module__const'] !== 'C__MODULE__CMDB') {
                        $entries[] = [
                            'id'     => (int)$addon['isys_module__id'],
                            'url'    => $url,
                            'title'  => $language->get($addon['isys_module__title']),
                            'icon'   => strstr($addon['isys_module__icon'], '/') ? $wwwPath . $addon['isys_module__icon'] : $addon['isys_module__icon']
                        ];
                    }

                    $additionalEntries = $addon['isys_module__class']::get_additional_links();

                    foreach ($additionalEntries as $key => $entry) {
                        if ($key === 'RELATION') {
                            $allowedToViewRelations = isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__RELATION');
                            $allowedToViewParallelRelations = isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__PARALLEL_RELATION');

                            if (!$allowedToViewRelations && !$allowedToViewParallelRelations) {
                                continue;
                            }
                        } else {
                            $subAuthInstance = $moduleManager->get_module_auth($addon['isys_module__id']);

                            if (is_a($subAuthInstance, 'isys_auth') && !$subAuthInstance->is_allowed_to(isys_auth::VIEW, $key)) {
                                continue;
                            }
                        }

                        $entries[] = [
                            'id'     => 0,
                            'url'    => $entry[1],
                            'title'  => $language->get($entry[0]),
                            'icon'   => $entry[3]
                        ];
                    }
                }
            } catch (Throwable $e) {
                // Do nothing.
            }
        }

        // IP address management.
        if (defined('C__OBJTYPE__LAYER3_NET') && isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__LAYER3_NET')) {
            $entries[] = [
                'id'     => 123,
                'url'    => $wwwPath . isys_helper_link::create_url([
                        C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_OBJECT,
                        C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__LAYER3_NET
                    ]),
                'title'  => $language->get('LC__CMDB__IP__ADDRESS_MANAGEMENT'),
                'icon'   => $wwwPath . 'images/axialis/hardware-network/ip-range.svg'
            ];
        }

        $provideItServiceLink = defined('C__OBJTYPE__IT_SERVICE')
            && defined('C__MODULE__ITSERVICE')
            && class_exists('isys_module_itservice')
            && array_filter($entries, fn ($entry) => $entry['id'] == C__MODULE__ITSERVICE)
            && isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__IT_SERVICE');

        if ($provideItServiceLink) {
            $entries = array_map(function ($entry) use ($wwwPath) {
                if ($entry['id'] != C__MODULE__ITSERVICE) {
                    return $entry;
                }

                $entry['url'] = $wwwPath . isys_helper_link::create_url([
                    C__GET__MODULE_ID        => C__MODULE__ITSERVICE,
                    C__GET__TREE_NODE        => C__MODULE__ITSERVICE . 2,
                    C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__IT_SERVICE,
                    C__GET__SETTINGS_PAGE    => isys_module_itservice::PAGE__TYPE_LIST
                ]);

                return $entry;
            }, $entries);
        }

        // Sort the add-ons.
        usort($entries, fn ($a, $b) => strcasecmp($a['title'], $b['title']));

        return new JsonResponse([
            'success' => true,
            'data'    => $entries,
            'message' => ''
        ]);
    }
}
