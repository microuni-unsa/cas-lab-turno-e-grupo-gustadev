<?php

namespace idoit\Component\Download;

use GuzzleHttp\Psr7\MimeType;

/**
 * Class DownloadFile.
 * This can be used as callback result instead of a static file path.
 * It contains the file content and filename (and thereby also MIME type).
 * Use this for files that do not exist physically on the server.
 *
 * @package idoit\Component\Download
 */
class DownloadFile
{
    private string $filename;

    private string $content;

    public function __construct(string $filename, string $content)
    {
        $this->filename = $filename;
        $this->content = $content;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMimeType(): string
    {
        $extension = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));

        return MimeType::fromExtension($extension) ?? 'application/octet-stream';
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
