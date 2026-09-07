<?php

namespace idoit\Api;

use isys_format_json;
use Symfony\Component\HttpFoundation\Response;

/**
 * JsonRpc Response.
 *
 * This Response needs to be used by any endpoints which implement the 'EndpointInterface'.
 *
 * @see       API-484
 * @package   idoit\Api
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class JsonRpcResponse extends Response
{
    protected array $data;

    public function __construct(array $data, int $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->data = $data;

        $this->headers->set('Content-Type', 'application/json');

        $this->setContent(isys_format_json::encode($data));
    }

    public function getData(): array
    {
        return $this->data;
    }
}
