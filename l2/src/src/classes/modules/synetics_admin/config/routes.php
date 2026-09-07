<?php

use idoit\Module\SyneticsAdmin\Controller\AddonController;
use idoit\Module\SyneticsAdmin\Controller\IndexController;
use idoit\Module\SyneticsAdmin\Controller\ModalController;
use idoit\Module\SyneticsAdmin\Controller\ProxyController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    // Index page.
    $routes->add('synetics_admin.index', '/center')
        ->methods(['GET'])
        ->controller([IndexController::class, 'page']);

    $routes->add('synetics_admin.modal.advertise', '/center/modal/advertise')
        ->methods(['GET'])
        ->controller([ModalController::class, 'advertiseContent']);

    $routes->add('synetics_admin.proxy_api', '/portal/api/{slug}')
        ->methods(['GET', 'POST', 'PUT', 'DELETE'])
        ->requirements(['slug' => '.*'])
        ->controller([ProxyController::class, 'proxyApi']);

    $routes->add('synetics_admin.proxy_static', '/portal/static/{slug}')
        ->methods(['GET'])
        ->requirements(['slug' => '.*'])
        ->controller([ProxyController::class, 'proxyStatic']);

    $routes->add('synetics_admin.iframe', '/portal/{slug}')
        ->methods(['GET'])
        ->requirements(['slug' => '.*'])
        ->controller([IndexController::class, 'iframe']);

    $routes->add('synetics_admin.index-with-slug', '/center/{slug}')
        ->methods(['GET'])
        ->requirements(['slug' => '.*'])
        ->controller([IndexController::class, 'page']);

    $routes->add('synetics_addon.api.instance-data', '/synetics_admin/api/instance')
        ->methods(['GET'])
        ->controller([IndexController::class, 'getInstanceData']);

    $routes->add('synetics_addon.api.environment-data', '/synetics_admin/api/environment')
        ->methods(['GET'])
        ->controller([IndexController::class, 'getEnvironmentData']);

    $routes->add('synetics_addon.api.get-token', '/synetics_admin/api/get-token')
        ->methods(['GET'])
        ->controller([IndexController::class, 'getToken']);

    $routes->add('synetics_addon.api.update-token', '/synetics_admin/api/update-token')
        ->methods(['POST'])
        ->controller([IndexController::class, 'updateToken']);

    $routes->add('synetics_addon.api.refresh-token', '/synetics_admin/api/refresh-token')
        ->methods(['POST'])
        ->controller([IndexController::class, 'refreshToken']);

    $routes->add('synetics_addon.api.delete-token', '/synetics_admin/api/delete-token')
        ->methods(['POST'])
        ->controller([IndexController::class, 'deleteToken']);

    $routes->add('synetics_addon.api.update-idoit', '/synetics_admin/api/update-idoit')
        ->methods(['POST'])
        ->controller([IndexController::class, 'updateIdoit']);

    $routes->add('synetics_admin.addons.install', '/synetics_admin/api/addons')
        ->methods(['POST'])
        ->controller([AddonController::class, 'installAddon']);

    $routes->add('synetics_admin.addons.uninstall', '/synetics_admin/api/addons')
        ->methods(['DELETE'])
        ->controller([AddonController::class, 'uninstallAddon']);

    $routes->add('synetics_admin.addons.status', '/synetics_admin/api/addons')
        ->methods(['PUT'])
        ->controller([AddonController::class, 'setAddonStatus']);

    $routes->add('synetics_admin.api.set-license-token', '/synetics_admin/api/licenseToken')
        ->methods(['POST'])
        ->controller([IndexController::class, 'saveLicenseToken']);
};
