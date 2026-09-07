<?php

namespace idoit\Module\Templates\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ValidationException extends Exception
{
    /**
     * @return self
     */
    public static function titleAlreadyInUse(string $title, int $objectId): Exception
    {
        return new self("Object title '{$title}' is already in use for object id {$objectId}.", Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param string $placeholder
     * @param int    $objectId
     *
     * @return Exception
     */
    public static function iterationLimitExceeded(string $placeholder, int $objectId): Exception
    {
        return new self("Iteration limit has been exceeded in generating a title for object id {$objectId} with placeholder {$placeholder}.", Response::HTTP_BAD_REQUEST);
    }
}
