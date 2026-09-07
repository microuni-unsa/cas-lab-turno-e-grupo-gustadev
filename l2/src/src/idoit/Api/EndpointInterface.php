<?php

namespace idoit\Api;

use Symfony\Component\HttpFoundation\Request;

/**
 * Endpoint interface.
 *
 * This is a mandatory interface for the new API structure.
 *
 * @see       API-484
 * @package   idoit\Api
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface EndpointInterface
{
    public function getDefinition(): EndpointDefinition;

    public function request(Request $request): JsonRpcResponse;
}
