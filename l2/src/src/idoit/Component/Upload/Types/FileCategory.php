<?php

namespace idoit\Component\Upload\Types;

use idoit\Component\Upload\UploadType;
use idoit\Module\Cmdb\Controller\ObjectController;
use isys_application;
use isys_cmdb_dao_category_s_file;
use isys_convert;
use isys_exception_dao;
use isys_exception_database;
use isys_exception_filesystem;
use isys_helper_upload;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FileCategory
 *
 * @package idoit\Component\Upload\Types
 */
class FileCategory extends UploadType
{
    /**
     *
     */
    public function __construct()
    {
        $this
            ->setUploadDirectory("/upload/files/")
            ->setSizeLimit(max([isys_convert::to_bytes(ini_get('upload_max_filesize')), isys_convert::to_bytes(ini_get('post_max_size'))]) ?? 0)
            ->setCallbackAfterUpload([self::class, 'processUpload']);
    }

    /**
     * Method for uploading a file and creating an object.
     *
     * @param string $filePath
     * @param array  $getParameters
     * @return string
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_filesystem
     */
    public static function processUpload(string $filePath, array $getParameters): string
    {
        $database = isys_application::instance()->container->get('database');
        $userObjectId = isys_application::instance()->container->get('session')->get_user_id();

        $createObjectRequest = new Request([], [
            'object-type-id' => $getParameters['object-type-id'],
            'object-title'   => trim($getParameters['object-title']) === '' ? $getParameters['qqfile'] : $getParameters['object-title']
        ]);

        $categoryId = (int)$getParameters['file-category'];

        $responseJson = json_decode((new ObjectController())->createObject($createObjectRequest)
            ->getContent(), true);

        if (!$responseJson['success']) {
            return false;
        }

        $objectId = $responseJson['data'];

        $cleanedFileName = isys_helper_upload::prepare_filename($objectId . '__' . time() . '__' . basename($filePath));
        $newUploadDirPath = isys_application::instance()->getOrCreateUploadFileDir($cleanedFileName);

        // @see ID-3932 When uploading a file via qqFileUploader we need to rename it!
        if (!file_exists($filePath)) {
            return false;
        }

        if (!rename($filePath, $newUploadDirPath . $cleanedFileName)) {
            return false;
        }

        $fileDao = isys_cmdb_dao_category_s_file::instance($database);

        // Retrieve the md5 checksum.
        $md5Hash = md5_file($newUploadDirPath . $cleanedFileName);

        $versionEntryId = null;
        $physicalFileEntry = $fileDao->create_physical_file($cleanedFileName, basename($filePath), $md5Hash, $userObjectId);

        if ($physicalFileEntry > 0) {
            $versionEntryId = $fileDao->create_version($objectId, $physicalFileEntry, '', '');
        }

        $fileEntry = $fileDao->create_connector('isys_cats_file_list', $objectId);

        $fileDao->update_cats_file_list($fileEntry, $versionEntryId, $categoryId > 0 ? $categoryId : null);

        return $objectId;
    }
}
