<?php

use idoit\Module\Report\Controller\DeleteController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('report.delete.check', '/report/check-delete')
        ->methods(['POST'])
        ->controller([DeleteController::class, 'checkDeletion']);

    $routes->add('report.delete', '/report/delete')
        ->methods(['POST'])
        ->controller([DeleteController::class, 'deleteReport']);
};
