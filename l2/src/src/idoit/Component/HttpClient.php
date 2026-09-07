<?php

namespace idoit\Component;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use idoit\Component\Settings\Settings;
use Psr\Http\Message\ResponseInterface;

/**
 * HttpClient class for executing various HTTP actions,
 * using the customers' internally configured proxy settings.
 *
 * Check out https://docs.guzzlephp.org/en/stable/request-options.html to see all available request options.
 *
 * @package   Component
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class HttpClient
{
    private Client $client;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $options = [
            'timeout' => 5.0
        ];

        if ($settings->get('proxy.active', false)) {
            $proxyHost = $settings->get('proxy.host');
            $proxyPort = $settings->get('proxy.port');
            $proxyUser = $settings->get('proxy.username');
            $proxyPass = $settings->get('proxy.password');

            $options['proxy'] = "{$proxyHost}:{$proxyPort}";

            if ($proxyUser && $proxyPass) {
                $options['proxy'] = "{$proxyUser}:{$proxyPass}@{$proxyHost}:{$proxyPort}";
            }
        }

        $this->client = new Client($options);
    }

    /**
     * @param string $url
     * @param string $type
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $url, string $type = 'GET', array $options = []): ResponseInterface
    {
        return $this->client->request($type, $url, $options);
    }

    /**
     * @param string $url
     * @param string $type
     * @param array  $options
     *
     * @return PromiseInterface
     */
    public function requestAsync(string $url, string $type = 'GET', array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($type, $url, $options);
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
