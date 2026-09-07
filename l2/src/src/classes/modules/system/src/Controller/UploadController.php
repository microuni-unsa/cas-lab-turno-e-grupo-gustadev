<?php

namespace idoit\Module\System\Controller;

use Exception;
use idoit\Component\Helper\Purify;
use isys_application;
use isys_library_fileupload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * System relevant add-on controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class UploadController
{
    public function process(Request $request, string $type): Response
    {
        try {
            $uploadService = isys_application::instance()->container->get('upload');

            if (!$uploadService->hasUploadType($type)) {
                throw new Exception('The selected upload type "' . $type . '" does not exit.');
            }

            $uploadType = $uploadService->getUploadType($type);

            $fileUploader = new isys_library_fileupload($uploadType->getValidExtensions(), $uploadType->getSizeLimit());

            $fileName = $fileUploader->getName();
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileDirectory = rtrim(isys_application::instance()->app_path, '/\\') . '/' . trim($uploadType->getUploadDirectory(), '/\\') . '/';
            $fileWwwDirectory = rtrim(isys_application::instance()->www_path, '/\\') . '/' . trim($uploadType->getUploadDirectory(), '/\\') . '/';

            if (count($uploadType->getValidExtensions()) > 0 && !in_array($fileExtension, $uploadType->getValidExtensions())) {
                throw new Exception('The filetype "' . $fileExtension . '" is not allowed.');
            }

            $uploadResult = $fileUploader->handleUpload($fileDirectory);
            $callbackResult = null;

            if (isset($uploadResult['error']) && trim($uploadResult['error']) !== '') {
                throw new Exception($uploadResult['error']);
            }

            // Prepare the filename to not include special chars and so on.
            $normalizedFileName = Purify::formatFilename($fileName);

            // Rename the filename.
            rename($fileDirectory . $fileName, $fileDirectory . $normalizedFileName);

            // Process the "after upload" callback, if available.
            if ($uploadType->getCallbackAfterUpload() !== null) {
                try {
                    $callbackResult = call_user_func($uploadType->getCallbackAfterUpload(), $fileDirectory . $normalizedFileName, $request->query->all());
                } catch (Exception $e) {
                    // When a callback fails, we remove the file and re-throw the exception to trigger an error.
                    @unlink($fileDirectory . $normalizedFileName);

                    throw $e;
                }
            }

            return new JsonResponse([
                'success' => isset($uploadResult['success']) && $uploadResult['success'],
                'data'    => [
                    'type' => $type,
                    'filePath' => $fileWwwDirectory . $normalizedFileName,
                    'callbackResult' => $callbackResult
                ],
                'message' => ''
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ]);
        }
    }
}
