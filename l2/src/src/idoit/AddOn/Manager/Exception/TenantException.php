<?php

namespace idoit\AddOn\Manager\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class TenantException extends Exception
{
    /**
     * @return self
     */
    public static function dbUpdateException(string $message): TenantException
    {
        return new self(
            "Could not update database with following error: {$message}",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public static function installScriptException(string $message)
    {
        return new self(
            "An error occurred while executing an install script with the following message: {$message}",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $identifier
     *
     * @return self
     */
    public static function uninstallCoreModule(string $identifier)
    {
        return new self(
            "Could not delete module {$identifier}. Only add-ons can be uninstalled.",
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $identifier
     * @param string $message
     *
     * @return self
     */
    public static function uninstallFailed(string $identifier, string $message)
    {
        return new self(
            "Uninstalling module {$identifier} failed with message: {$message}.",
            Response::HTTP_BAD_REQUEST
        );
    }
}
