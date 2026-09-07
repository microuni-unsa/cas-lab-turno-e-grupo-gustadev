<?php

namespace idoit\Module\System\Controller;

use isys_application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * System relevant controller to read and write settings.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SettingsController
{
    public function setUserSetting(Request $request): Response
    {
        try {
            $key = $request->get('key');
            $value = $request->get('value');

            isys_application::instance()->container->get('settingsUser')->set($key, $value);

            return new JsonResponse([
                'success' => true,
                'data'    => null,
                'message' => ''
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ]);
        }
    }
}
