<?php

namespace idoit\Module\Cmdb\Controller;

use DateTime;
use Exception;
use GuzzleHttp\Psr7\MimeType;
use isys_application;
use isys_cmdb_dao;
use isys_component_session;
use isys_convert;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * CMDB relevant image controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ImageController
{
    /** @var array  */
    private static array $additionalObjectTypeImagePaths = [];

    /** @var isys_component_session  */
    private isys_component_session $session;

    /** @var isys_cmdb_dao|object|null  */
    private isys_cmdb_dao $cmdbDao;

    public function __construct()
    {
        $this->session = isys_application::instance()->container->get('session');
        $this->cmdbDao = isys_application::instance()->container->get('cmdb_dao');
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public static function addObjectTypeImagePath(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        self::$additionalObjectTypeImagePaths[] = $path;
    }

    /**
     * @param string $fileName
     *
     * @return array
     */
    public static function getAdditionalObjectTypeImagePaths(string $fileName): array
    {
        return array_map(fn ($e) => $e . "/{$fileName}", self::$additionalObjectTypeImagePaths);
    }

    /**
     * @param Request  $request
     * @param int|null $objectId
     *
     * @return Response
     */
    public function getObjectImageById(Request $request, ?int $objectId): Response
    {
        try {
            $tenantId = (int)$this->session->get_mandator_id();
            $imagePriority = [];

            $sql = "SELECT isys_catg_image_list__image_link AS 'objectImage', isys_obj_type__obj_img_name AS 'objectTypeImage'
                FROM isys_obj
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                LEFT JOIN isys_catg_image_list ON isys_catg_image_list__isys_obj__id = isys_obj__id
                WHERE isys_obj__id = {$objectId}
                LIMIT 1;";

            $imageData = $this->cmdbDao->retrieve($sql)->get_row();

            if (isset($imageData['objectImage']) && !empty($imageData['objectImage'])) {
                $prefix = substr(md5($imageData['objectImage']), 0, 2);

                // This is the new upload directory.
                $imagePriority[] = "upload/images/{$tenantId}/object-images/{$prefix}/{$imageData['objectImage']}";
                // This is the old upload directory.
                $imagePriority[] = "upload/images/{$tenantId}/{$prefix}/{$imageData['objectImage']}";
            }

            if (isset($imageData['objectTypeImage']) && !empty($imageData['objectTypeImage'])) {
                // This is the new object type image, in case no custom object image was uploaded.
                $imagePriority[] = "upload/images/{$tenantId}/object-type/images/{$imageData['objectTypeImage']}";
                // This is the old object type image, in case no custom object image was uploaded.
                $imagePriority[] = "images/objecttypes/{$imageData['objectTypeImage']}";
                $imagePriority = array_merge($imagePriority, self::getAdditionalObjectTypeImagePaths($imageData['objectTypeImage']));
            }
        } catch (Throwable $e) {
            // Do nothing.
        }

        // Use the default image as fallback.
        $imagePriority[] = 'images/objecttypes/empty.png';

        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param Request $request
     * @param string  $filename
     * @return Response
     * @throws Exception
     */
    public function getObjectImageByFilename(Request $request, string $filename): Response
    {
        $tenantId = (int)$this->session->get_mandator_id();
        $imagePriority = [];

        try {
            $prefix = substr(md5($filename), 0, 2);
            $imagePriority[] = "upload/images/{$tenantId}/object-images/{$prefix}/{$filename}";
            $imagePriority[] = "upload/images/{$tenantId}/{$prefix}/{$filename}";
        } catch (Throwable $e) {
            // Do nothing.
        }

        // Use the default image as fallback.
        $imagePriority[] = 'images/objecttypes/empty.png';
        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param Request  $request
     * @param int|null $objectTypeId
     *
     * @return Response
     */
    public function getObjectTypeImageById(Request $request, ?int $objectTypeId): Response
    {
        $imagePriority = [];

        try {
            $tenantId = (int)$this->session->get_mandator_id();

            $sql = "SELECT isys_obj_type__obj_img_name AS 'objectTypeImage'
                FROM isys_obj_type
                WHERE isys_obj_type__id = {$objectTypeId}
                LIMIT 1;";

            $imageName = $this->cmdbDao->retrieve($sql)->get_row_value('objectTypeImage');

            if ($imageName !== null && !empty($imageName)) {
                // This is the new object type image, in case no custom object image was uploaded.
                $imagePriority[] = "upload/images/{$tenantId}/object-type/images/{$imageName}";
                // This is the old object type image, in case no custom object image was uploaded.
                $imagePriority[] = "images/objecttypes/{$imageName}";
            }
        } catch (Throwable $e) {
            // Do nothing.
        }

        // Use the default image as fallback.
        $imagePriority[] = 'images/objecttypes/empty.png';

        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return Response
     */
    public function getObjectTypeImageByFilename(Request $request, string $filename): Response
    {
        $imagePriority = [];

        try {
            $tenantId = (int)$this->session->get_mandator_id();

            // @see ID-9704 Prevent path traversal.
            $filename = basename($filename);

            // This is the new object type image, in case no custom object image was uploaded.
            $imagePriority[] = "upload/images/{$tenantId}/object-type/images/{$filename}";
            // This is the old object type image, in case no custom object image was uploaded.
            $imagePriority[] = "images/objecttypes/{$filename}";
        } catch (Throwable $e) {
            // Do nothing.
        }

        $imagePriority = array_merge($imagePriority, self::getAdditionalObjectTypeImagePaths($filename));
        // Use the default image as fallback.
        $imagePriority[] = 'images/objecttypes/empty.png';
        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param Request  $request
     * @param int|null $objectTypeId
     *
     * @return Response
     */
    public function getObjectTypeIconById(Request $request, ?int $objectTypeId): Response
    {
        $imagePriority = [];

        try {
            $tenantId = (int)$this->session->get_mandator_id();

            $sql = "SELECT isys_obj_type__icon  AS 'objectTypeIcon'
                FROM isys_obj_type
                WHERE isys_obj_type__id = {$objectTypeId}
                LIMIT 1;";

            $iconName = $this->cmdbDao->retrieve($sql)->get_row_value('objectTypeIcon');

            if ($iconName !== null && !empty($iconName)) {
                // This is the new object type image, in case no custom object image was uploaded.
                $imagePriority[] = "upload/images/{$tenantId}/object-type/icons/{$iconName}";
                // Previously the complete object type icon path was stored in the database.
                $imagePriority[] = $iconName;
                // This is for old object type icons, which are located in a specific folder.
                $imagePriority[] = "images/tree/{$iconName}";
                $imagePriority = array_merge($imagePriority, self::getAdditionalObjectTypeImagePaths($iconName));
            }
        } catch (Throwable $e) {
            // Do nothing.
        }
        // Use the default object type icon as fallback.
        $imagePriority[] = 'images/axialis/documents-folders/document-color-grey.svg';

        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return Response
     */
    public function getObjectTypeIconByFilename(Request $request, string $filename): Response
    {
        $imagePriority = [];

        try {
            $tenantId = (int)$this->session->get_mandator_id();

            // @see ID-9704 Prevent path traversal.
            $filename = ltrim(str_replace('../', '', str_replace('\\', '/', $filename)), '/');

            $imagePriority[] = "upload/images/{$tenantId}/object-type/icons/{$filename}";
            // Usually the complete object type icon path is stored in the database.
            $imagePriority[] = $filename;
            // This is for olf object type icons, which are located in a specific folder.
            $imagePriority[] = "images/tree/{$filename}";
            $imagePriority = array_merge($imagePriority, self::getAdditionalObjectTypeImagePaths($filename));
        } catch (Throwable $e) {
            // Do nothing.
        }

        // Use the default object type icon as fallback.
        $imagePriority[] = 'images/axialis/documents-folders/document-color-grey.svg';

        // Run through the image priority array.
        return $this->fetchImage($imagePriority);
    }

    /**
     * @param array $imagePriority
     *
     * @return Response
     * @throws \Exception
     */
    private function fetchImage(array $imagePriority): Response
    {
        $this->session->write_close();

        $appPath = isys_application::instance()->app_path;
        $wwwPath = isys_application::instance()->www_path;

        foreach ($imagePriority as $image) {
            if (file_exists($appPath . $image)) {
                // Use a redirect for 'public' images.
                if (strpos($image, 'images/') === 0) {
                    $response = new RedirectResponse($wwwPath . $image);
                } else {
                    $file = new SplFileInfo($appPath . $image);
                    $mimetype = MimeType::fromExtension($file->getExtension()) ?? 'application/octet-stream';

                    $response = new BinaryFileResponse($file, Response::HTTP_OK, ['Content-Type' => $mimetype], true, null, true);
                }

                // Force caching, see https://www.php.net/manual/de/function.session-cache-limiter.php
                header_remove('Expires');
                header_remove('Cache-Control');
                header_remove('Pragma');

                return $response
                    ->setExpires((new DateTime())->modify('+1 month'))
                    ->setCache([
                        'must_revalidate'  => false,
                        'no_cache'         => false,
                        'no_store'         => false,
                        'no_transform'     => false,
                        'public'           => true,
                        'private'          => false,
                        'proxy_revalidate' => false,
                        'max_age'          => isys_convert::MONTH,
                        's_maxage'         => isys_convert::MONTH,
                        'immutable'        => true,
                    ]);
            }
        }

        return new Response('The requested image could not be found!', Response::HTTP_NOT_FOUND);
    }
}
