<?php

namespace idoit\Api;

use Symfony\Component\HttpFoundation\Request;

/**
 * Endpoint container.
 *
 * This container will contain all registered endpoints of the new API structure.
 * Each endpoint needs to implement the 'EndpointInterface'.
 *
 * @see       API-484
 * @package   idoit\Api
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class EndpointContainer
{
    private array $endpoints;

    public function __construct(iterable $endpoints)
    {
        foreach ($endpoints as $endpoint) {
            if ($endpoint instanceof EndpointInterface) {
                $this->registerEndpoint($endpoint);
            }
        }
    }

    public function all(): array
    {
        return $this->endpoints;
    }

    public function registerEndpoint(EndpointInterface $endpoint): self
    {
        $this->endpoints[$endpoint->getDefinition()->getName()] = $endpoint;

        return $this;
    }

    public function hasEndpoint(string $requestedEndpoint): bool
    {
        return isset($this->endpoints[$requestedEndpoint]);
    }

    public function run(string $requestedEndpoint, array $parameters): JsonRpcResponse
    {
        /** @var EndpointInterface $endpoint */
        $endpoint = $this->endpoints[$requestedEndpoint];

        // @todo Use named parameters, once PHP 7.4 is not maintained.
        $request = new Request([], $parameters);

        $endpoint->getDefinition()
            ->validateRequest($request);

        return $endpoint->request($request);
    }
}
