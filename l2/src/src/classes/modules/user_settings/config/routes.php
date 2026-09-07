<?php

use idoit\Module\UserSettings\Controller\TfaController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('tfa.activate', '/tfa/activate')
        ->methods(['POST'])
        ->controller([TfaController::class, 'activate']);

    $routes->add('tfa.activation-modal', '/tfa/activation-modal')
        ->methods(['GET'])
        ->controller([TfaController::class, 'activationModal']);

    $routes->add('tfa.deactivate', '/tfa/deactivate')
        ->methods(['POST'])
        ->controller([TfaController::class, 'deactivate']);

    $routes->add('tfa.deactivation-modal', '/tfa/deactivation-modal')
        ->methods(['GET'])
        ->controller([TfaController::class, 'deactivationModal']);

    $routes->add('tfa.management.deactivate', '/tfa/deactivate/{userId}')
        ->methods(['POST'])
        ->requirements(['userId' => '\d+'])
        ->controller([TfaController::class, 'deactivateForUser']);

    $routes->add('tfa.deactivation-modal-for-user', '/tfa/deactivation-modal-for-user')
        ->methods(['GET'])
        ->controller([TfaController::class, 'deactivationModalForUser']);

    $routes->add('tfa.verify', '/tfa/verify')
        ->methods(['POST'])
        ->controller([TfaController::class, 'verify']);
};
