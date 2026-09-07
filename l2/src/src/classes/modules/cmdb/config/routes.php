<?php

use idoit\Module\Cmdb\Controller\Category\ContactController;
use idoit\Module\Cmdb\Controller\Category\NetController;
use idoit\Module\Cmdb\Controller\FileBrowserController;
use idoit\Module\Cmdb\Controller\ImageController;
use idoit\Module\Cmdb\Controller\ObjectBrowserController;
use idoit\Module\Cmdb\Controller\ObjectController;
use idoit\Module\Cmdb\Controller\ValidationController;
use idoit\Module\Cmdb\Controller\WysiwygController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    // Collect allowed image extensions
    $allowedExtensionsRegex = '.+\.(' . implode('|', isys_application::ALLOWED_IMAGE_EXTENSIONS) . ')';

    // CMDB object routes.
    $routes->add('cmdb.object.image', '/cmdb/object/image/{objectId}')
        ->methods(['GET'])
        ->requirements(['objectId' => '\d*'])
        ->controller([ImageController::class, 'getObjectImageById']);

    $routes->add('cmdb.object.image-name', '/cmdb/object/image/file:{filename}')
        ->methods(['GET'])
        ->requirements(['filename' => $allowedExtensionsRegex])
        ->controller([ImageController::class, 'getObjectImageByFilename']);

    $routes->add('cmdb.object.redirect', '/cmdb/object/{objectId}')
        ->methods(['GET'])
        ->requirements(['objectId' => '\d+'])
        ->controller([ObjectController::class, 'redirectToObject']);

    $routes->add('cmdb.ajax.create-object', '/ajax/cmdb/create-object')
        ->methods(['POST'])
        ->controller([ObjectController::class, 'createObject']);

    // CMDB object type routes
    $routes->add('cmdb.object-type.image', '/cmdb/object-type/image/{objectTypeId}')
        ->methods(['GET'])
        ->requirements(['objectTypeId' => '\d*'])
        ->controller([ImageController::class, 'getObjectTypeImageById']);

    $routes->add('cmdb.object-type.image-name', '/cmdb/object-type/image/file:{filename}')
        ->methods(['GET'])
        ->requirements(['filename' => $allowedExtensionsRegex])
        ->controller([ImageController::class, 'getObjectTypeImageByFilename']);

    $routes->add('cmdb.object-type.icon', '/cmdb/object-type/icon/{objectTypeId}')
        ->methods(['GET'])
        ->requirements(['objectTypeId' => '\d*'])
        ->controller([ImageController::class, 'getObjectTypeIconById']);

    $routes->add('cmdb.object-type.icon-name', '/cmdb/object-type/icon/file:{filename}')
        ->methods(['GET'])
        ->requirements(['filename' => $allowedExtensionsRegex])
        ->controller([ImageController::class, 'getObjectTypeIconByFilename']);

    $routes->add('cmdb.category.contact.change-role', '/cmdb/category/contact/change-role')
        ->methods(['POST'])
        ->controller([ContactController::class, 'changeRole']);

    $routes->add('cmdb.category.contact.change-primary', '/cmdb/category/contact/change-primary')
        ->methods(['POST'])
        ->controller([ContactController::class, 'changePrimary']);

    // @see ID-11854 Create new routes for 'net' category endpoints.
    $routes->add('cmdb.category.net.check-net-collision', '/cmdb/category/net/check-net-collision')
        ->methods(['POST'])
        ->controller([NetController::class, 'checkNetCollision']);

    $routes->add('cmdb.category.net.get-net-info', '/cmdb/category/net/get-net-information/{objectId}')
        ->methods(['GET'])
        ->requirements(['objectId' => '\d+'])
        ->controller([NetController::class, 'getNetInformation']);

    // @see ID-9755 New route for new validation structure
    $routes->add('cmdb.validate.category-attribute-value', '/cmdb/validate/category-attribute-value')
        ->methods(['POST'])
        ->controller([ValidationController::class, 'validateValue']);

    $routes->add('cmdb.wysiwyg.upload-image', '/cmdb/wysiwyg/upload-image')
        ->methods(['POST'])
        ->controller([WysiwygController::class, 'uploadImage']);

    $routes->add('cmdb.wysiwyg.browse-images', '/cmdb/wysiwyg/browse-images')
        ->methods(['GET'])
        ->controller([WysiwygController::class, 'browseImages']);

    $routes->add('cmdb.wysiwyg.delete-image', '/cmdb/wysiwyg/delete-image')
        ->methods(['POST'])
        ->controller([WysiwygController::class, 'deleteImage']);

    $routes->add('cmdb.wysiwyg.image-name', '/cmdb/wysiwyg/image/file:{filename}')
        ->methods(['GET'])
        ->requirements(['filename' => '.+\.(jpeg|jpg|png|gif|bmp|svg)'])
        ->controller([WysiwygController::class, 'getImageByFilename']);

    // @see ID-10645 New routes for file browser.
    $routes->add('cmdb.browse-files.categories', '/cmdb/browse-files/categories')
        ->methods(['GET'])
        ->controller([FileBrowserController::class, 'getCategories']);

    $routes->add('cmdb.browse-files.files', '/cmdb/browse-files/files/{category}')
        ->methods(['GET'])
        ->requirements(['category' => '(all|\d+)'])
        ->controller([FileBrowserController::class, 'getFiles']);

    $routes->add('cmdb.browse-files.upload-modal', '/cmdb/browse-files/upload-modal')
        ->methods(['GET'])
        ->controller([FileBrowserController::class, 'getUploadModal']);

    // @see ID-10645 New route for file browser suggestion.
    $routes->add('cmdb.browse-files.suggestion', '/cmdb/browse-files/suggestion')
        ->methods(['POST'])
        ->controller([FileBrowserController::class, 'getSuggestion']);

    // @see ID-10734 Use more performant way of fetching object meta data.
    $routes->add('cmdb.browse.object-metadata', '/cmdb/browse/get-object-metadata')
        ->methods(['GET'])
        ->controller([ObjectBrowserController::class, 'getMetaData']);
};
