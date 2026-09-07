<?php

use Symfony\Component\Filesystem\Filesystem;

/**
 * AJAX for file actions.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_file extends isys_ajax_handler
{
    /**
     * Initialization.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // For proper internal error handling, we need to disable the PHP errors.
        error_reporting(0);

        $l_return = [];

        switch ($_GET['func']) {
            case 'upload':
                $l_return = $this->upload();
                break;

            case 'upload_by_ckeditor':
                $l_return = $this->upload_by_ckeditor($_GET['upload_handler']);
                break;

            case 'browse_by_ckeditor':
                $l_return = $this->browse_by_ckeditor($_GET['upload_handler']);
                break;

            case 'delete_by_ckeditor':
                $l_return = $this->delete_by_ckeditor($_POST['file'], $_GET['upload_handler']);
                break;
        }

        // The IE has problems to handle any other content type but "text/html".
        if ($_GET['is_ie'] == 'true') {
            header('Content-Type: text/plain; charset=UTF-8');
        } else {
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * Simple upload method, which returns the file-path or an error message.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    private function upload()
    {
        global $g_dirs;

        // We store the name of our upload-field in this GET parameter.
        $l_field = $_GET['uploadfield'];

        $fileName = isys_helper_upload::append_hash_prefix($_FILES[$l_field]['name']);
        $uploadDirectoryPath = isys_application::instance()->getOrCreateUploadFileDir($fileName);

        try {
            return [
                'success' => true,
                'path'    => isys_helper_upload::save($_FILES[$l_field], $fileName, $uploadDirectoryPath)
            ];
        } catch (isys_exception_filesystem $e) {
            return [
                'success'           => false,
                'message'           => $e->getMessage(),
                'secondary_message' => isys_helper_upload::get_error($l_field)
            ];
        }
    }

    /**
     * This method will be used by the CKEditor, when uploading files.
     * @param string $p_upload_handler
     *
     * @return void
     * @throws isys_exception_filesystem
     * @deprecated See ID-10497
     */
    private function upload_by_ckeditor($p_upload_handler = 'isys_module_cmdb')
    {
        $message = '';
        $filename = isys_helper_upload::append_hash_prefix($_FILES['upload']['name']);
        $destination = isys_application::instance()->getOrCreateUploadFileDir($filename);

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir')) {
            $destination = $p_upload_handler::get_upload_dir();
        }

        // @see ID-9303 Unify the paths to prevent issues between windows and other operating systems.
        $wwwPath = rtrim(isys_application::instance()->www_path, '/');
        $destination = str_replace('\\', '/', $destination);
        $uploadDirectory = str_replace(str_replace('\\', '/', BASE_DIR), '', $destination);

        try {
            isys_helper_upload::save($_FILES['upload'], $filename, $destination);
        } catch (isys_exception_filesystem $e) {
            $message = isys_glob_htmlentities($e->getMessage() . ' - ' . isys_helper_upload::get_error('upload'));
        }

        $ckeditorFunctionNumber = (int)$_GET['CKEditorFuncNum'];
        $wwwFilePath = isys_glob_htmlentities(str_replace('\\', '/', "{$wwwPath}/{$uploadDirectory}/{$filename}"));

        echo '<script type="text/javascript">' .
            "window.parent.CKEDITOR.tools.callFunction({$ckeditorFunctionNumber}, '{$wwwFilePath}', '{$message}');".
            '</script>';

        // We let the script die, before the content-type is set.
        die;
    }

    /**
     * This method will be used by the CKEditor, when uploading files.
     *
     * @param string $p_upload_handler
     *
     * @return void
     * @throws Exception
     * @deprecated See ID-10497
     */
    private function browse_by_ckeditor($p_upload_handler = 'isys_module_cmdb')
    {
        $l_files = [];
        $l_message = '';

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir_files')) {
            $l_files = $p_upload_handler::get_upload_dir_files();
        } else {
            $l_message = isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FILE__NO_UPLOAD_HANDLER', [
                    $p_upload_handler,
                    'get_upload_dir_files'
                ]);
        }

        if (is_countable($l_files) && count($l_files)) {
            $wwwPath = rtrim(isys_application::instance()->www_path, '/');

            // This is used change the absolute path to a "www" path.
            $l_files = array_map(function ($p_file_path) use ($wwwPath) {
                // @see ID-9303 Unify the paths to prevent issues between windows and other operating systems.
                $filePath = str_replace(str_replace('\\', '/', BASE_DIR), '', str_replace('\\', '/', $p_file_path));

                return str_replace('\\', '/', "{$wwwPath}/{$filePath}");
            }, $l_files);
        } else {
            $l_message = isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FILE__NOT_FOUND');
        }

        isys_application::instance()->template
            ->assign('title', isys_application::instance()->container->get('language')->get('LC_UNIVERSAL__FILE_BROWSER'))
            ->assign('files', $l_files)
            ->assign('file_body', 'popup/ckeditor_filebrowser.tpl')
            ->assign('ckeditor_func_num', (int)$_GET['CKEditorFuncNum'])
            ->assign('message', $l_message)
            ->assign('delete_url', isys_helper_link::create_url([
                C__GET__AJAX      => 1,
                C__GET__AJAX_CALL => 'file',
                'func'            => 'delete_by_ckeditor',
                'upload_handler'  => $p_upload_handler
            ]))
            ->display('popup/main.tpl');

        die;
    }

    /**
     * This method will delete a selected image from the server.
     *
     * @param string $p_file
     * @param string $p_upload_handler
     *
     * @return array
     * @throws Exception
     * @deprecated See ID-10497
     */
    private function delete_by_ckeditor($p_file, $p_upload_handler = 'isys_module_cmdb')
    {
        $l_files = [];

        $p_file = str_replace('\\', '/', BASE_DIR . $p_file);

        if (class_exists($p_upload_handler) && method_exists($p_upload_handler, 'get_upload_dir_files')) {
            $l_files = $p_upload_handler::get_upload_dir_files();

            // This is used change the absolute path to a "www" path.
            $l_files = array_map(function ($p_file_path) {
                return str_replace('\\', '/', $p_file_path);
            }, $l_files);
        }

        if (count($l_files) === 0) {
            return [
                'success' => false,
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FILE__NOT_FOUND'),
                'data'    => null
            ];
        }

        if (!in_array($p_file, $l_files)) {
            return [
                'success' => false,
                'message' => isys_application::instance()->container->get('language')
                    ->get('LC_FILEBROWSER__NO_FILE_FOUND'),
                'data'    => null
            ];
        }

        try {
            (new Filesystem())->remove($p_file);

            return [
                'success' => true,
                'message' => null,
                'data'    => null
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        }
    }
}
