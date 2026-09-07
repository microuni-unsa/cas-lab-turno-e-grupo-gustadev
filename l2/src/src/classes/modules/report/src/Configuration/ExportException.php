<?php

namespace idoit\Module\Report\Configuration;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ExportException extends Exception
{
    /**
     * @param string $message
     *
     * @return ExportException
     */
    public static function couldNotBuildExport(string $message)
    {
        return new self(
            "Something went wrong while building the XML Document: " . $message,
            Response::HTTP_BAD_REQUEST
        );
    }
}
