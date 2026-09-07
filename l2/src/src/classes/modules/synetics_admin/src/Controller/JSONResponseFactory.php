<?php

namespace idoit\Module\SyneticsAdmin\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSONResponseFactory
 *
 * @package   idoit\Module\Synetics_admin\Controller
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class JSONResponseFactory
{
    public static function error(string $error)
    {
        return new JsonResponse([
            'success' => false,
            'type' => 'error',
            'error' => $error
        ], Response::HTTP_BAD_REQUEST);
    }

    public static function permissionError(?string $message = null)
    {
        // @see ID-12077 Update response shape.
        return new JsonResponse([
            'success' => false,
            'type' => 'permission',
            'error' => $message ?? 'You do not have permission to access this page.'
        ], Response::HTTP_FORBIDDEN);
    }

    public static function serverError(string $error)
    {
        return new JsonResponse([
            'success' => false,
            'type'=> 'server-error',
            'error' => $error
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function successWithData(array $data)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ], Response::HTTP_OK);
    }

    public static function success()
    {
        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
