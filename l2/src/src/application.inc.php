<?php

/**
 * i-doit
 *
 * Application controller
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = isys_application::instance();

try {
    $app->container->get('kernel')
        ->handle($app->container->get('request'))
        ->send();

    die;
} catch (NotFoundHttpException $e) {
    $catchallController = \idoit\Controller\CatchallController::factory($app->container);

    // Continue with the legacy code.
    $requestController = isys_request_controller::instance()
        ->route('GET|POST', '/[s:module]/[s:action]/[c:method]/[i:id]', [
            $catchallController,
            'handle'
        ])
        ->route('GET|POST', '/[s:module]/[s:action]/[c:method]', [
            $catchallController,
            'handle'
        ])
        ->route('GET|POST', '/[s:module]?/[s:action]?/[i:id]?', [
            $catchallController,
            'handle'
        ]);

    $app::run($requestController);
}
