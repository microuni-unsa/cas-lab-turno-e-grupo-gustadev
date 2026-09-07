<?php

use idoit\Component\Helper\Purify;

define('CONST_FILES_RECURSIVE', 1);
define('CONST_FILES_NORECURSIVE', 0);

define('C_FILES__MODE_DOWNLOAD', "binary");
define('C_FILES__MODE_TEXT', "text");
define('C_FILES__MODE_VIEW', "image");

/**
 * i-doit
 *
 * Files
 *
 * @deprecated
 * @package     i-doit
 * @subpackage  Components
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_filemanager extends isys_component
{
    const ALLOWED_PATHS = [
        'upload/',
        'images/',
        'rfc/application/uploads/',
        'rfc/media/img/',
        'src/classes/modules/document/resources/',
        'src/classes/modules/packager/build',
        'imports/qcw/',
        'imports/scripts/'
    ];

    public const MIMETYPES = [
        // Common image types (see https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types).
        'apng'  => 'image/apng',
        'avif'  => 'image/avif',
        'gif'   => 'image/gif',
        'jpg'   => 'image/jpg',
        'jpeg'  => 'image/jpg',
        'jfif'  => 'image/jpg',
        'pjpeg' => 'image/jpg',
        'pjp'   => 'image/jpg',
        'png'   => 'image/png',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        // More uncommon image types (see https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types).
        'bmp'   => 'image/bmp',
        'ico'   => 'image/x-icon',
        'cur'   => 'image/x-icon',
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
    ];

    /**
     * @var array
     */
    private static $m_errors;

    /**
     * @var array
     */
    private $m_disallowed_ext;

    /**
     * @var string
     */
    private $m_target_dir;

    /**
     * @var array
     */
    private $m_warnings;

    /**
     * Returns true/false if errors occured.
     *
     * @return boolean
     * @deprecated
     */
    public static function are_errors_occured()
    {
        return (is_countable(self::$m_errors) && count(self::$m_errors) > 0);
    }

    /**
     * Get errors.
     *
     * @return array
     * @deprecated
     */
    public static function get_errors()
    {
        return (self::are_errors_occured() ? self::$m_errors : false);
    }

    /**
     * Member of isys_component_filemanager.
     *
     * @static
     * @param string  $p_filename
     * @param integer $p_obj_id
     * @return string
     * @deprecated Please use 'isys_cmdb_dao_category_g_image::prepareFilename(...)'
     */
    public static function create_new_filename($p_filename, $p_obj_id): string
    {
        return isys_cmdb_dao_category_g_image::prepareFilename((int)$p_obj_id, $p_filename);
    }

    /**
     * Returns true/false if warnings occured.
     *
     * @return boolean
     * @deprecated
     */
    public function are_warnings_occured()
    {
        return (is_countable($this->m_warnings) && count($this->m_warnings) > 0);
    }

    /**
     * Add an error.
     *
     * @param string $p_error
     *
     * @return isys_component_filemanager
     * @deprecated
     */
    public function _add_error($p_error)
    {
        self::$m_errors[] = $p_error;

        return $this;
    }

    /**
     * Add an error.
     *
     * @param string $p_warning
     *
     * @return isys_component_filemanager
     * @deprecated
     */
    public function _add_warning($p_warning)
    {
        $this->m_warnings[] = $p_warning;

        return $this;
    }

    /**
     * Get warnings.
     *
     * @return array
     * @deprecated
     */
    public function get_warnings()
    {
        return ($this->are_warnings_occured() ? $this->m_warnings : false);
    }

    /**
     * ################################### *
     *  FILE DOWNLOAD FUNCTIONS            *
     *                                     *
     * ################################### *
     */

    /**
     * Uploads a file to a directory.
     *
     * @param array $p_filesArray Information about the uploading file.
     * @param string $p_new_filename (optional) Write file's content to this file.
     *
     * @return boolean
     * @throws ErrorException
     * @deprecated
     */
    public function receive($p_filesArray, $p_new_filename = null)
    {
        $l_filepath = $p_filesArray['tmp_name'];
        $l_upload_err = $p_filesArray['error'];

        $language = isys_application::instance()->container->get('language');
        $l_new_filename = $p_new_filename === null ? $p_filesArray['name'] : $p_new_filename; // if

        if (!$l_upload_err) {
            if (file_exists($l_filepath)) {
                $l_move_file = move_uploaded_file($l_filepath, $this->m_target_dir . $l_new_filename);

                if ($l_move_file == false) {
                    $this->_add_error($language->get('LC__UNIVERSAL__FILE_UPLOAD__MOVE_UPLOADED_FILE_FAILED', [
                            $l_filepath,
                            $this->m_target_dir
                        ]));

                    return false;
                }

                return true;
            }

            throw new ErrorException($language->get('LC__UNIVERSAL__FILE_UPLOAD__COMMON_PROBLEM'));
        } else {
            switch ($l_upload_err) {
                case 1:
                case 2:
                    $l_strupload_err = $language->get('LC__UNIVERSAL__FILE_UPLOAD__FILE_SIZE_TOO_BIG', $p_filesArray['size']);
                    break;
                case 3:
                    $l_strupload_err = $language->get('LC__UNIVERSAL__FILE_UPLOAD__FILE_UPLOADED_PARTIALLY');
                    break;
                case 4:
                    $l_strupload_err = $language->get('LC__UNIVERSAL__FILE_UPLOAD__NO_FILE_SELECTED');
                    break;
                default:
                    $l_strupload_err = $language->get('LC__UNIVERSAL__FILE_UPLOAD__COMMON_PROBLEM');
                    break;
            }

            $this->_add_error($l_strupload_err);

            return false;
        }
    }

    /**
     * @return array|string[]
     * @deprecated
     */
    public static function getAllowedPaths()
    {
        return array_merge(
            self::ALLOWED_PATHS,
            array_map('constant', array_filter(['C__IMPORT__DIRECTORY'], 'defined')),
            [
                isys_settings::get('system.dir.file-upload', isys_application::instance()->app_path . '/upload/files/'),
                isys_application::instance()->getImageDir(),
            ]
        );
    }

    /**
     * ################################### *
     *  FILE HANDLING                      *
     *                                     *
     * ################################### *
     */

    /**
     * Other than 'send' this method will stream data directly without the need of a file in the filesystem.
     *
     * @param string      $data
     * @param string      $filename
     * @param string|null $mimetype
     *
     * @return void
     */
    public static function sendInline(string $data, string $filename, string|null $mimetype = null)
    {
        if ($mimetype === null) {
            $mimetype = "application/octet-stream";
        }

        set_time_limit(0);
        ob_end_clean();
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: ' . gmdate("D, d M Y H:i:s", (time() + 2 * isys_convert::HOUR)) . " GMT");
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . " GMT");
        header('Content-Type: ' . $mimetype);
        header('Content-length: ' . strlen($data));
        // header('Content-Disposition: inline; filename="' . isys_helper_upload::prepare_filename($filename) . '"');
        header('Content-Disposition: attachment; filename="' . Purify::formatFilename($filename) . '"');
        header("Content-transfer-encoding: binary");

        echo $data;
        die;
    }

    /**
     * Sends a specified file as a new header to the user's browser.
     *
     * @param string $p_filename
     * @param object $p_daoFile
     * @param string $p_mode
     *
     * @return boolean
     *
     * @throws Exception
     * @deprecated
     */
    public function send($p_filename, $p_daoFile = null, $p_mode = C_FILES__MODE_DOWNLOAD)
    {
        // Clear file stat cache.
        clearstatcache();
        $l_content_disposition = '';

        // @see ID-9789 Don't ltrim slashes (introduced in ID-9704).
        // @see ID-10032 ID-10033 Unify paths more strictly to prevent path traversal.
        $p_filename = Purify::preventPathTraversal($p_filename);

        if (is_object($p_daoFile)) {
            $l_arFile = $p_daoFile->get_row();
            $l_physical_file = $this->m_target_dir . $l_arFile["isys_physical_filename"];
            $l_filename = $l_arFile["isys_filename"];
        } else {
            if ($p_mode == C_FILES__MODE_DOWNLOAD) {
                $l_content_disposition = "Content-Disposition: attachment; filename=\"" . htmlentities(basename($p_filename), ENT_COMPAT, BASE_ENCODING) . "\";";
            }

            $l_filename = !file_exists($p_filename) ? $this->m_target_dir . $p_filename : $p_filename;

            $l_physical_file = $l_filename;
        }

        $fileInfo = new SplFileInfo($l_physical_file);
        $fileAllowed = false;
        $allowedPaths = self::getAllowedPaths();

        foreach ($allowedPaths as $allowedPath) {
            // @see ID-9704 Check if 'realpath($allowedPath)' contains a valid string.
            if (strpos($fileInfo->getPathname(), $allowedPath) === 0 || (realpath($allowedPath) !== false && strpos($fileInfo->getRealPath(), realpath($allowedPath)) === 0)) {
                $fileAllowed = true;
                break;
            }
        }

        if ($fileAllowed === false) {
            throw new \Exception('Accessing file within not accessable directory');
        }

        // @todo DS: I know this is really cruel, but if someone clicks on the download button, we need this MIME-Type!
        $l_mimetype = "application/octet-stream";

        // @see ID-9226 Use specific MIME types.
        if (isset(self::MIMETYPES[$fileInfo->getExtension()])) {
            $l_mimetype = self::MIMETYPES[$fileInfo->getExtension()];
        }

        if (!is_file($l_physical_file)) {
            return false;
        }

        // @todo Arbitrary File Download Vulnerability
        if (!is_readable($l_physical_file)) {
            $this->_add_error("File: " . $l_physical_file . " exists but is not readable");

            return false;
        }

        /**
         * @desc download/view:
         */
        set_time_limit(0);
        ob_end_clean();
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: " . gmdate("D, d M Y H:i:s", (time() + 2 * isys_convert::HOUR)) . " GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-Type: " . $l_mimetype);
        header("Content-length: " . filesize($l_physical_file));
        header("Content-Disposition: inline; filename=\"" . basename($l_filename) . "\"");
        header("Content-transfer-encoding: binary");

        if (!empty($l_content_disposition)) {
            header($l_content_disposition);
        }

        if (connection_status() != 0) {
            return false;
        }

        if ($l_fp = fopen($l_physical_file, 'rb')) {
            // @phpstan-ignore-next-line
            while (!feof($l_fp) and (connection_status() == 0)) {
                echo fread($l_fp, 1024 * 8);
                flush();
            }

            fclose($l_fp);
        }

        die;
    }

    /**
     * Setter for "disallowed" filetypes.
     *
     * @param array $p_filetypes
     *
     * @return isys_component_filemanager
     * @deprecated
     */
    public function set_disallowed_filetypes(array $p_filetypes = [])
    {
        $this->m_disallowed_ext = $p_filetypes;

        return $this;
    }

    /**
     * Getter for "disallowed" filetypes.
     *
     * @return array
     * @deprecated
     */
    public function get_disallowed_filetypes()
    {
        return $this->m_disallowed_ext;
    }

    /**
     * Method for checking, if the given filetype is allowed to be handled.
     *
     * @param string $p_filetype
     *
     * @return boolean
     * @deprecated
     */
    public function check_file_type($p_filetype)
    {
        if (!in_array($p_filetype, $this->get_disallowed_filetypes())) {
            $this->_add_error('Filetype ".' . $p_filetype . '" not allowed');

            return false;
        }

        return true;
    }

    /**
     * Method for (re-) setting the upload path.
     *
     * @param string $p_directory
     *
     * @return $this
     * @throws Exception
     * @deprecated
     */
    public function set_upload_path($p_directory)
    {
        if (!is_dir($p_directory)) {
            $this->_add_error("Directory " . $p_directory . " is not existing!");
        }

        if (!is_readable($p_directory)) {
            $this->_add_error("Directory " . $p_directory . " is not readable!");
        }

        if (!is_writable($p_directory)) {
            $this->_add_error("Directory " . $p_directory . " is not writeable!");
        }

        $this->m_target_dir = $p_directory;

        if (!$this->m_target_dir) {
            throw new Exception('Error: File upload target dir is not set.');
        }
        if (!file_exists($this->m_target_dir)) {
            throw new Exception('Error: File upload target dir does not exist.');
        }

        return $this;
    }

    /**
     * Method for deleting a given file.
     *
     * @param string $p_file
     * @param string $p_dir
     *
     * @return boolean
     * @deprecated
     */
    public function delete($p_file, $p_dir = null)
    {
        if ($p_dir === null) {
            $l_dir = $this->get_upload_path();
        } else {
            $l_dir = $p_dir;
        }

        $l_dir = rtrim($l_dir, '\\/');

        // This is necessary, if "$p_file" already contains the path.
        if (!empty($l_dir)) {
            $l_dir .= '/';
        }

        if (file_exists($l_dir . $p_file)) {
            return unlink($l_dir . $p_file);
        }

        return true;
    }

    /**
     * Returns directory where the manager is about to upload to
     *
     * @return string _target_dir
     * @deprecated
     */
    public function get_upload_path()
    {
        return $this->m_target_dir;
    }

    /**
     * Copy files recursively.
     *
     * @param string $p_source
     * @param string $p_dest
     *
     * @return mixed
     * @deprecated
     */
    public function copy_files($p_source, $p_dest)
    {
        if (is_dir($p_source)) {
            $l_folder = opendir($p_source);
            while ($l_file = readdir($l_folder)) {
                if ($l_file == '.' || $l_file == '..') {
                    continue;
                }

                if (is_dir($p_source . '/' . $l_file)) {
                    mkdir($p_dest . '/' . $l_file, 0775);
                    $this->copy_files($p_source . '/' . $l_file, $p_dest . '/' . $l_file);
                } else {
                    copy($p_source . '/' . $l_file, $p_dest . '/' . $l_file);
                }
            }

            closedir($l_folder);

            return true;
        } else {
            copy($p_source, $p_dest);
        }

        return true;
    }

    /**
     * Delete files and their sub-directories recursively.
     *
     * @param string  $p_source
     * @param integer $p_opt
     *
     * @return boolean
     * @deprecated
     */
    public function remove_files($p_source, $p_opt = CONST_FILES_RECURSIVE)
    {
        if (is_dir($p_source)) {
            $l_folder = opendir($p_source);
            while ($l_file = readdir($l_folder)) {
                if ($l_file == '.' || $l_file == '..') {
                    continue;
                }

                if (is_dir($p_source . '/' . $l_file) && $p_opt == CONST_FILES_RECURSIVE) {
                    $this->remove_files($p_source . '/' . $l_file);
                } else {
                    unlink($p_source . '/' . $l_file);
                }
            }

            closedir($l_folder);
            rmdir($p_source);
        } else {
            if (file_exists($p_source)) {
                unlink($p_source);
            }
        }

        return true;
    }

    /**
     * Constructor.
     *
     * @deprecated
     */
    public function __construct()
    {
        global $g_dirs;

        // Setting some INI configurations.
        ini_set("upload_max_filesize", "100M");
        ini_set("file_uploads", "On");
        ini_set("upload_tmp_dir", $g_dirs["fileman"]["temp_dir"]);

        // Preparing some variables.
        self::$m_errors = [];
        $this->m_warnings = [];
        $this->m_disallowed_ext = ["exe"];
        $this->m_target_dir = $g_dirs["fileman"]["target_dir"];
    }
}
