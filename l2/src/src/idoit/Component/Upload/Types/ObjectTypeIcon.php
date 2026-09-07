<?php

namespace idoit\Component\Upload\Types;

use Exception;
use idoit\Component\Upload\UploadType;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use isys_application;

/**
 * Class ObjectTypeIcon
 *
 * @package idoit\Component\Upload\Types
 */
class ObjectTypeIcon extends UploadType
{
    /**
     * @param int $tenantId
     */
    public function __construct(int $tenantId)
    {
        $this
            ->setUploadDirectory("/upload/images/{$tenantId}/object-type/icons/")
            ->setValidExtensions(isys_application::ALLOWED_IMAGE_EXTENSIONS)
            ->setSizeLimit(1048576) // 1 MB
            ->setCallbackAfterUpload([self::class, 'processUpload']);
    }

    /**
     * Method for processing the object type icon.
     *
     * @param string $imagePath
     * @return string
     * @throws Exception
     */
    public static function processUpload(string $imagePath): string
    {
        // @see ID-9226 No need to process SVG files.
        if (pathinfo($imagePath, PATHINFO_EXTENSION) !== 'svg') {
            (new ImageManager(new Driver()))
                ->read($imagePath)
                ->resize(16, 16, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($imagePath);
        }

        return isys_application::instance()->container->get('route_generator')
            ->generate('cmdb.object-type.icon-name', ['filename' => basename($imagePath)]);
    }
}
