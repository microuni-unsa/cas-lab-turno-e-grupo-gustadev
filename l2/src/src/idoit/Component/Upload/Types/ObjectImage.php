<?php

namespace idoit\Component\Upload\Types;

use idoit\Component\Upload\UploadType;
use isys_application;
use isys_cmdb_dao_category_g_image;
use isys_exception_filesystem;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ObjectTypeImage
 *
 * @package idoit\Component\Upload\Types
 */
class ObjectImage extends UploadType
{
    /**
     *
     */
    public function __construct()
    {
        $this
            ->setUploadDirectory("/temp/")
            ->setValidExtensions(isys_application::ALLOWED_IMAGE_EXTENSIONS)
            ->setSizeLimit(1048576) // 1 MB
            ->setCallbackAfterUpload([self::class, 'processUpload']);
    }

    /**
     * Method for processing the object type icon.
     *
     * @param string $imagePath
     * @param array  $query
     * @return string|null
     * @throws isys_exception_filesystem
     */
    public static function processUpload(string $imagePath, array $query): ?string
    {
        $objectId = (int)($query['object-id'] ?? 0);

        if ($objectId === 0) {
            return null;
        }

        $fileName = isys_cmdb_dao_category_g_image::prepareFilename($objectId, basename($imagePath));

        $uploadDir = isys_application::instance()->getOrCreateUploadImageDir($fileName);

        (new Filesystem())->rename(
            $imagePath,
            $uploadDir . $fileName,
            true,
        );

        return $fileName;
    }
}
