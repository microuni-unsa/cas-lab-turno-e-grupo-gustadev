<?php

namespace idoit\Module\System\Controller;

use isys_application;
use isys_format_json;
use isys_helper_link;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * System relevant add-on controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class AddOnController
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

                // Check if the add-on has a package.json.
                $packageJsonPath = BASE_DIR . 'src/classes/modules/' . $addon['isys_module__identifier'] . '/package.json';

                if (!file_exists($packageJsonPath)) {
                    continue;
                }

                $packageJsonContent = file_get_contents($packageJsonPath);

                if (!isys_format_json::is_json_array($packageJsonContent)) {
                    continue;
                }

                $packageJsonData = isys_format_json::decode($packageJsonContent);

                if ($packageJsonData['type'] !== 'addon') {
                    continue;
                }

                // Get auth class
                $authInstance = $moduleManager->get_module_auth($addon['isys_module__id']);

                // Check for rights if module is authable otherwise display it
                if ((is_a($authInstance, 'isys_auth') && $authInstance->has_any_rights_in_module()) || $authInstance === false) {
                    $url = $wwwPath . isys_helper_link::create_url([C__GET__MODULE_ID => $addon['isys_module__id']]);

                    if (defined($addon['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK') && constant($addon['isys_module__class'] . '::MAIN_MENU_REWRITE_LINK')) {
                        $url = $wwwPath . $addon['isys_module__identifier'];
                    }

                    $entries[] = [
                        'id'     => $addon['isys_module__id'],
                        'url'    => $url,
                        'title'  => $language->get($addon['isys_module__title']),
                        'icon'   => strstr($addon['isys_module__icon'], '/') ? $wwwPath . $addon['isys_module__icon'] : $addon['isys_module__icon'],
                        'parent' => null
                    ];
                }
            } catch (Throwable $e) {
                // Do nothing.
            }
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
