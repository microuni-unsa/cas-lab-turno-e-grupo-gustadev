<?php declare(strict_types=1);

namespace idoit\Module\SyneticsAdmin\Service;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use idoit\Component\HttpClient;
use isys_application;
use Psr\Http\Message\ResponseInterface;

class SfmRequestService
{
    private const BEARER_KEY = 'SFM-BEARER-KEY';

    private HttpClient $client;

    private string $sfmUrl;

    private static ?SfmRequestService $instance = null;

    private function __construct(string $sfmUrl = 'https://center.i-doit.com')
    {
        $this->sfmUrl = $sfmUrl;

        $client = isys_application::instance()->container->get('http_client');
        if ($client instanceof HttpClient) {
            $this->client = $client;
        }
    }

    /**
     * @param string $sfmUrl
     *
     * @return SfmRequestService
     */
    public static function getInstance(string $sfmUrl = 'https://center.i-doit.com'): SfmRequestService
    {
        if (null === static::$instance) {
            static::$instance = new SfmRequestService(isys_application::instance()
                ->getEnv('SFM_URL') ?? $sfmUrl);
        }

        return static::$instance;
    }

    public function proxy(string $path): ResponseInterface
    {
        try {
            $result = $this->client->request($this->sfmUrl . '/' . $path, 'GET', $this->getRequestOptions());
        } catch (ClientException $e) {
            $result = $e->getResponse();
        }

        return $result;
    }

    public function request(string $path, string $method, array $parameters = []): ResponseInterface
    {
        try {
            $url = $this->sfmUrl . '/' . $path;
            $hostHeader = parse_url($this->sfmUrl, PHP_URL_HOST) . ':' . (parse_url($this->sfmUrl, PHP_URL_PORT) ?? 80);

            $mergedParameters = array_replace_recursive($parameters, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getToken(),
                    'Host' => $hostHeader,
                ],
                ...$this->getRequestOptions()
            ]);
            $result = $this->client->request($url, $method, $mergedParameters);
        } catch (ClientException $e) {
            $result = $e->getResponse();
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function renewSession(): bool
    {
        return !!$this->getToken(true);
    }

    /**
     * @param bool $force
     *
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getToken(bool $force = false): ?string
    {
        global $g_license_token;

        if ($_SESSION[self::BEARER_KEY] && !$force) {
            return $_SESSION[self::BEARER_KEY];
        }

        $response = $this->client->request($this->sfmUrl . '/api/api-portal-login', 'POST', [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::BODY    => json_encode(['licenseKey' => isys_application::instance()
                ->getEnv('SFM_LICENSE_TOKEN') ?? $g_license_token]),
            ...$this->getRequestOptions()
        ]);

        $data = json_decode($response->getBody()
            ->getContents(), true);

        $_SESSION[self::BEARER_KEY] = $data['access_token'];

        return $data['access_token'];
    }

    /**
     * @return int[]
     */
    private function getRequestOptions()
    {
        return [
            RequestOptions::TIMEOUT => (float)isys_application::instance()->getEnv('SFM_REQUEST_TIMEOUT', '15')
        ];
    }
}
