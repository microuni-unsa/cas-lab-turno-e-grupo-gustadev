<?php

namespace idoit\Component\Upload;

use Exception;
use isys_register;

/**
 * Class Upload
 *
 * @package idoit\Component\Upload
 */
class Upload
{
    private array $uploadTypes = [];

    /**
     * @param string     $identifier
     * @param UploadType $uploadType
     *
     * @return $this
     */
    public function registerUploadType(string $identifier, UploadType $uploadType): static
    {
        $this->uploadTypes[$identifier] = $uploadType;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasUploadType(string $identifier): bool
    {
        if (isset($this->uploadTypes[$identifier])) {
            return true;
        }

        // Use this for legacy code:
        return isys_register::factory('ajax-file-upload')->has($identifier);
    }

    /**
     * @param string $identifier
     *
     * @return UploadType
     * @throws Exception
     */
    public function getUploadType(string $identifier): UploadType
    {
        if (!$this->hasUploadType($identifier)) {
            throw new Exception("The requested upload type '{$identifier}' does not exist!");
        }

        return $this->uploadTypes[$identifier] ?? isys_register::factory('ajax-file-upload')->get($identifier);
    }
}
