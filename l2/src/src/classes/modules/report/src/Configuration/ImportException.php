<?php

namespace idoit\Module\Report\Configuration;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ImportException extends Exception
{
    /**
     * @param string $message
     *
     * @return ImportException
     */
    public static function importFileIsNotValid(string $message)
    {
        return new self(
            "Could not read XML Document: " . $message,
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $message
     *
     * @return ImportException
     */
    public static function expectedNodeMissing(string $message)
    {
        return new self(
            "XML Document is missing a node: " . $message,
            Response::HTTP_BAD_REQUEST
        );
    }
}
