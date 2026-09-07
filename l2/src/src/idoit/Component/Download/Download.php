<?php

namespace idoit\Component\Download;

use Exception;
use isys_register;

/**
 * Class Download
 *
 * @package idoit\Component\Download
 */
class Download
{
    private array $downloadTypes = [];

    /**
     * @param string     $identifier
     * @param DownloadType $downloadType
     *
     * @return $this
     */
    public function registerDownloadType(string $identifier, DownloadType $downloadType): static
    {
        $this->downloadTypes[$identifier] = $downloadType;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasDownloadType(string $identifier): bool
    {
        return isset($this->downloadTypes[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return DownloadType
     * @throws Exception
     */
    public function getDownloadType(string $identifier): DownloadType
    {
        if (!$this->hasDownloadType($identifier)) {
            throw new Exception("The requested download type '{$identifier}' does not exist!");
        }

        return $this->downloadTypes[$identifier];
    }
}
