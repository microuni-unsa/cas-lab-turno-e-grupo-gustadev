<?php

namespace idoit\Module\SyneticsAdmin\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Modal controller
 *
 * @package   idoit\Module\Synetics_admin\Controller
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class ModalController
{
    public function advertiseContent(Request $request): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => null,
            'message' => '',
        ];

        try {
            \isys_application::instance()->container->get('settingsUser')->set('synetics_admin.news.subscription-and-addons', 1);

            $response['data'] = \isys_application::instance()->container->get('template')
                ->assign('cssPath', \isys_module_synetics_admin::getPath() . 'assets/css/advertise-subscription-and-addons.css')
                ->fetch(\isys_module_synetics_admin::getPath() . 'templates/modal/advertiseSubscriptionAndAddons.tpl');
        } catch (\Throwable $exception) {
            $response['success'] = false;
            $response['message'] = $exception->getMessage();
        }

        return new JsonResponse($response);
    }
}
