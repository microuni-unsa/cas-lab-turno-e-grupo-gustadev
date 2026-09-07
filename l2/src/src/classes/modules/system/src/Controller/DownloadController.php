<?php

namespace idoit\Module\System\Controller;

use Exception;
use GuzzleHttp\Psr7\MimeType;
use idoit\Component\Download\DownloadFile;
use isys_application;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * System relevant download controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DownloadController
{
    /**
     * @param Request $request
     * @param string  $type
     * @param string  $identifier
     *
     * @return Response
     * @throws Exception
     */
    public function process(Request $request, string $type, string $identifier): Response
    {
        $downloadService = isys_application::instance()->container->get('download');

        if (!$downloadService->hasDownloadType($type)) {
            throw new Exception("The given download type '{$type}' does not exist!");
        }

        $callbackData = $downloadService->getDownloadType($type)->getCallback()($identifier);

        if (is_string($callbackData)) {
            if (!file_exists($callbackData)) {
                throw new Exception('The given file does not exist!');
            }

            $file = new SplFileInfo($callbackData);

            return new BinaryFileResponse(
                $file,
                200,
                ['Content-Type' => MimeType::fromExtension(strtolower($file->getExtension())) ?? 'application/octet-stream'],
                true,
                ResponseHeaderBag::DISPOSITION_ATTACHMENT
            );
        }

        if ($callbackData instanceof DownloadFile) {
            return new StreamedResponse(
                function () use ($callbackData) {
                    echo $callbackData->getContent();
                },
                200,
                [
                    'Content-Type' => $callbackData->getMimeType(),
                    'Content-Disposition' => "attachment; filename*=UTF-8''" . rawurlencode($callbackData->getFilename()),
                ]
            );
        }

        throw new Exception("The download callback for type '{$type}' needs to be a string or instance of 'DownloadFile'!");
    }
}
