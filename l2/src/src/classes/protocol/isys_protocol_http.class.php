<?php

use GuzzleHttp\Exception\GuzzleException;
use idoit\Component\HttpClient;

/**
 * i-doit
 *
 * HTTP protocol
 *
 * @package     i-doit
 * @subpackage  Protocol
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_protocol_http extends isys_protocol
{
    const C__HTTP      = 'http';
    const C__HTTPS     = 'https';
    const HTTP_OK      = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;

    private $m_base_url = '/';

    private array $headers = [];

    /* HTTP types */
    private $m_host = null;

    private $m_pass = null;

    /* HTTP codes */
    private $m_port = null;

    private $m_requests = [];

    private $m_user = null;

    /**
     * @param string $host
     * @param int $port
     * @param string $protocol
     * @return static
     */
    public static function get_instance(string $host, int $port = 80, string $protocol = self::C__HTTP): static
    {
        return parent::get_instance($host, $port, $protocol);
    }

    public function get_port()
    {
        return $this->m_port;
    }

    public function set_port($p_port)
    {
        $this->m_port = $p_port;

        return $this;
    }

    /**
     * Returns the Host without any information
     */
    public function get_host()
    {
        return $this->m_protocol . "://" . $this->m_host;
    }

    /**
     * Sets user
     *
     * @param   string $p_user
     *
     * @return  isys_protocol_http
     */
    public function set_user($p_user)
    {
        $this->m_user = $p_user;

        return $this;
    }

    /**
     * Sets password
     *
     * @param string $p_pass
     *
     * @return isys_protocol_http
     */
    public function set_pass($p_pass)
    {
        $this->m_pass = $p_pass;

        return $this;
    }

    /**
     * Sets the base url. This url is added in front of every request.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_http
     */
    public function set_base_url($p_base_url)
    {
        $this->m_base_url = $p_base_url;

        return $this;
    }

    /**
     * Retrieve base url.
     *
     * @return  string
     */
    public function get_base_url()
    {
        return $this->m_base_url;
    }

    /**
     * Attach something to the base URL.
     *
     * @param   string $p_base_url
     *
     * @return  isys_protocol_http
     */
    public function attach_base_url($p_base_url)
    {
        $this->m_base_url .= $p_base_url;

        return $this;
    }

    /**
     * Set headers.
     *
     * @param   string $p_headers
     *
     * @return  isys_protocol_http
     */
    public function set_headers(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Opens a standard get connection to the base url.
     *
     * @return string
     */
    public function open()
    {
        return $this->get('');
    }

    /**
     * Post a request
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     * @throws GuzzleException
     * @throws isys_exception_api
     * @throws isys_exception_general
     */
    public function post(?string $path = null, array $params = []): string
    {
        $this->m_requests[] = $this->request('POST', $this->url($path), $params);

        return $this->m_requests[count($this->m_requests) - 1];
    }

    /**
     * Gets a request
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     * @throws GuzzleException
     * @throws isys_exception_api
     * @throws isys_exception_general
     */
    public function get(?string $path = null, array $params = []): string
    {
        $this->m_requests[] = $this->request('GET', $this->url($path), $params);

        return $this->m_requests[count($this->m_requests) - 1];
    }

    /**
     * Get request array.
     *
     * @return  array
     */
    public function get_requests()
    {
        return $this->m_requests;
    }

    /**
     * url()-Wrapper
     *
     * @return  string
     */
    public function get_url()
    {
        return $this->url();
    }

    /**
     * @return  string
     */
    public function get_protocol()
    {
        return $this->m_protocol;
    }

    /**
     * Starts the HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @return string
     * @throws GuzzleException
     * @throws isys_exception_api
     * @throws isys_exception_general
     */
    private function request(string $method, string $url, array $params = []): string
    {
        if (!function_exists('curl_init')) {
            throw new isys_exception_general('php-curl extension is required.');
        }

        /** @var HttpClient $client */
        $client = isys_application::instance()->container->get('http_client');

        $options = [];

        if (isset($this->m_user)) {
            $options['auth'] = [$this->m_user];

            if (isset($this->m_pass)) {
                $options['auth'][] = $this->m_pass;
            }
        }

        if ($method === 'GET') {
            $options['query'] = $params;
        }

        if ($method === 'POST') {
            $options['form_params'] = $params;
        }

        if (isset($this->headers) && count($this->headers)) {
            $options['headers'] = $this->headers;
        }

        if ($this->tlsCertificate !== null) {
            $file = isys_cmdb_dao_category_s_file::instance(isys_application::instance()->container->get('database'))
                ->get_file_by_obj_id($this->tlsCertificate)
                ->get_row();

            $filePath = isys_cmdb_dao_category_g_file::getDownloadPath($file['isys_file_version__isys_file_physical__id'] ?? '');

            if (!file_exists($filePath)) {
                throw new Exception('The given TLS certificate file (object #' . $this->tlsCertificate . ') does not exist!');
            }

            $options['verify'] = $filePath;
        }

        $response = $client->request($url, $method, $options);

        return match ($response->getStatusCode()) {
            self::HTTP_OK, self::HTTP_CREATED, self::HTTP_ACEPTED => $response->getBody()->getContents(),
            default => throw new isys_exception_api("http error: {$response->getStatusCode()}", $response->getStatusCode()),
        };
    }

    /**
     * @param string|null $p_path
     * @return string
     */
    private function url(?string $p_path = null): string
    {
        return "{$this->m_protocol}://{$this->m_host}:{$this->m_port}{$this->m_base_url}{$p_path}";
    }

    /**
     * Singleton constructor.
     *
     * @param  string  $host
     * @param  integer $port
     * @param  string  $protocol
     */
    protected function __construct(string $host, int $port, string $protocol)
    {
        $this->m_host = $host;
        $this->m_port = $port;
        $this->m_protocol = $protocol;
    }
}
