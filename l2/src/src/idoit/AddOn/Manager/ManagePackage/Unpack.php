<?php

namespace idoit\AddOn\Manager\ManagePackage;

use Exception;
use idoit\AddOn\Manager\ManageActionInterface;
use isys_update_files;

class Unpack implements ManageActionInterface
{
    /**
     * @var string
     */
    private string $fileName = '';

    /**
     * @var string
     */
    private string $temporaryDirectory = '';

    /**
     * @param string $fileName
     * @param string $temporaryDirectory
    */
    public function __construct(string $fileName, string $temporaryDirectory)
    {
        $this->fileName = $fileName;
        $this->temporaryDirectory = $temporaryDirectory;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function process(): bool
    {
        $tempDirectory = BASE_DIR . 'temp/addon-' . substr(md5(microtime()), 0, 8) . '/';

        // Checking for zlib and the ZipArchive class to solve #4853
        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
        }

        if (!is_dir($this->temporaryDirectory) && !mkdir($this->temporaryDirectory, 0775, true)) {
            throw new Exception("Can't create subdirectory '$tempDirectory' for uploading file. Please check rights");
        }

        if (!(new isys_update_files())->read_zip($this->fileName, $this->temporaryDirectory, false, true)) {
            throw new Exception('Error: Could not read zip package.');
        }

        return true;
    }
}
