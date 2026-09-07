<?php

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @version    1.10
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Api;

/**
 * Class JsonRpcError
 *
 * @package idoit\Module\Api
 */
class JsonRpcError
{
    public const PARSE      = -32700;
    public const REQUEST    = -32600;
    public const METHOD     = -32601;
    public const PARAMETERS = -32602;
    public const INTERNAL   = -32603;
    public const AUTH       = -32604;
    public const SYSTEM     = -32099;

    public const ERRORS = [
        self::PARSE      => 'Parse error',
        self::REQUEST    => 'Invalid request',
        self::METHOD     => 'Method not found',
        self::PARAMETERS => 'Invalid parameters',
        self::INTERNAL   => 'Internal error',
        self::AUTH       => 'Authentication error',
        self::SYSTEM     => 'i-doit system error'
    ];
}
