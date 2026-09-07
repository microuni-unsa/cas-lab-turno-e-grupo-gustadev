<?php

use idoit\Module\System\Controller\AddOnController;
use idoit\Module\System\Controller\DownloadController;
use idoit\Module\System\Controller\ModuleController;
use idoit\Module\System\Controller\SettingsController;
use idoit\Module\System\Controller\UpdateCheckController;
use idoit\Module\System\Controller\UploadController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('modules.load', '/ajax/load-modules')
        ->methods(['GET'])
        ->controller([ModuleController::class, 'getForMenu']);

    $routes->add('add-ons.load', '/ajax/load-addons')
        ->methods(['GET'])
        ->controller([AddOnController::class, 'getForMenu']);

    $routes->add('system.ajax-upload', '/ajax/upload/{type}')
        ->methods(['POST'])
        ->requirements(['type' => '[a-z0-9\._-]+'])
        ->controller([UploadController::class, 'process']);

    $routes->add('system.file-download', '/download/{type}/{identifier}')
        ->methods(['GET'])
        ->requirements(['type' => '[a-z0-9\._-]+', 'identifier' => '.*'])
        ->controller([DownloadController::class, 'process']);

    $routes->add('system.update-check', '/ajax/update-check')
        ->methods(['GET'])
        ->controller([UpdateCheckController::class, 'check']);

    $routes->add('system.set-user-setting', '/system/set-user-setting')
        ->methods(['POST'])
        ->controller([SettingsController::class, 'setUserSetting']);
};
