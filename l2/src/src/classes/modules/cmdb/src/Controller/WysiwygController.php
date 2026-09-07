<?php

namespace idoit\Module\Cmdb\Controller;

use DateTime;
use Exception;
use FilesystemIterator;
use GuzzleHttp\Psr7\MimeType;
use idoit\Component\Helper\Purify;
use isys_application;
use isys_component_template;
use isys_component_template_language_manager;
use isys_convert;
use isys_exception_filesystem;
use isys_exception_general;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Throwable;

class WysiwygController
{
    private isys_component_template_language_manager $language;
    private UrlGenerator $routeGenerator;
    private string $targetPath = '';
    private string $targetWwwPath = '';
    private isys_component_template $template;

    public function __construct()
    {
        $this->language = isys_application::instance()->container->get('language');
        $this->routeGenerator = isys_application::instance()->container->get('route_generator');
        $this->template = isys_application::instance()->container->get('template');

        $tenantId = (int)isys_application::instance()->container->get('session')->get_mandator_id();
        $this->targetPath = isys_application::instance()->app_path . "upload/images/{$tenantId}/wysiwyg/";
        $this->targetWwwPath = isys_application::instance()->www_path . "upload/images/{$tenantId}/wysiwyg/";
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function uploadImage(Request $request): Response
    {
        try {
            $filesystem = new Filesystem();

            /** @var UploadedFile $upload */
            $upload = $request->files->get('upload');

            if ($upload->getError() !== UPLOAD_ERR_OK) {
                throw new Exception($upload->getErrorMessage());
            }

            $fileExtension = $upload->getClientOriginalExtension();
            $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'svg'];

            if (strpos($upload->getClientMimeType(), 'image/') !== 0 || !in_array($fileExtension, $allowedExtensions, true)) {
                $errorMessage = $this->language->get('LC__UNIVERSAL__FILE_UPLOAD__EXTENSION_ERROR');
                $allowed = implode(', ', $allowedExtensions);

                throw new isys_exception_general("{$errorMessage} ({$allowed})");
            }

            $fileName = $upload->getClientOriginalName();

            $hashPrefix = substr(md5($fileName . microtime(true)), 0, 8);
            $cleanFileName = Purify::formatFilename(substr($fileName, 0, -(strlen($fileExtension) + 1)));

            $targetFileName = "{$hashPrefix}_{$cleanFileName}.{$fileExtension}";

            // Create the directory.
            $filesystem->mkdir($this->targetPath, 0775);
            // Move the uploaded file.
            $filesystem->rename($upload->getPathname(), $this->targetPath . $targetFileName);

            $imageUrl = $this->routeGenerator->generate('cmdb.wysiwyg.image-name', ['filename' => $targetFileName]);
            $message = '';

            $responseData = [
                "uploaded" => true,
                "filename" => $fileName,
                "url" => $imageUrl
            ];

        } catch (Throwable $e) {
            $imageUrl = '';
            $message = htmlentities($e->getMessage());

            $responseData = [
                "uploaded" => false,
                "error" => $message
            ];
        }

        return new JsonResponse($responseData);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \SmartyException
     */
    public function browseImages(Request $request): Response
    {
        $images = [];
        $message = '';

        // Only iterate the directory if it exists.
        if (file_exists($this->targetPath)) {
            $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->targetPath, FilesystemIterator::SKIP_DOTS));

            foreach ($directoryIterator as $file) {
                /** @var SplFileInfo $file */
                $images[] = $this->routeGenerator->generate('cmdb.wysiwyg.image-name', ['filename' => $file->getFilename()]);
            }
        }

        if (count($images) === 0) {
            $message = $this->language->get('LC__UNIVERSAL__FILE__NOT_FOUND');
        }

        $responseContent = $this->template
            ->assign('title', isys_application::instance()->container->get('language')->get('LC_UNIVERSAL__FILE_BROWSER'))
            ->assign('files', $images)
            ->assign('file_body', 'popup/ckeditor_filebrowser.tpl')
            ->assign('ckeditor_func_num', (int)$request->query->get('CKEditorFuncNum'))
            ->assign('message', $message)
            ->assign('delete_url', $this->routeGenerator->generate('cmdb.wysiwyg.delete-image'))
            ->fetch('popup/main.tpl');

        return new Response($responseContent);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteImage(Request $request): Response
    {
        try {
            $baseUploadPath = $this->targetPath;

            $filesystem = new Filesystem();
            $file = $request->request->get('file');

            $fileName = str_replace('file:', '', basename($file));

            $fileExistsInBase = file_exists($baseUploadPath . $fileName);

            // Check if the file is located in the correct directory.
            if (!$fileExistsInBase) {
                throw new isys_exception_filesystem($this->language->get('LC_FILEBROWSER__NO_FILE_FOUND'));
            }

            $filesystem->remove($baseUploadPath . $fileName);

            $result = [
                'success' => true,
                'data' => null,
                'message' => ''
            ];
        } catch (Throwable $e) {
            $result = [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($result);
    }


    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return Response
     */
    public function getImageByFilename(Request $request, string $filename): Response
    {
        try {
            $filename = basename($filename);
            $filename = "{$this->targetPath}{$filename}";

            $file = new SplFileInfo($filename);
            $mimetype = MimeType::fromExtension($file->getExtension()) ?? 'application/octet-stream';

            $response = new BinaryFileResponse($file, Response::HTTP_OK, ['Content-Type' => $mimetype], true, null, true);
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
        } catch (Throwable $e) {
            // do nothing
        }
        return new Response('The requested image could not be found!', Response::HTTP_NOT_FOUND);
    }
}
