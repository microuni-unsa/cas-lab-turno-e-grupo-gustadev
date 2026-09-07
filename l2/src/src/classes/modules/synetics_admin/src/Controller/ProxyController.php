<?php

namespace idoit\Module\SyneticsAdmin\Controller;

use idoit\Module\SyneticsAdmin\Service\SfmRequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy controller
 *
 * @package   idoit\Module\Synetics_admin\Processor
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class ProxyController
{
    private SfmRequestService $service;

    public function __construct()
    {
        $this->service = SfmRequestService::getInstance();
    }

    public function proxyApi(Request $request, string $slug): Response
    {
        if (!\isys_auth_synetics_admin::instance()->canAccessAdminCenter()) {
            return JSONResponseFactory::permissionError();
        }

        $params = [];
        $mappedHeaders = array_map(fn ($e) => $e[0], $request->headers->all());
        unset($mappedHeaders['host']);
        unset($mappedHeaders['cookie']);
        if ($request->getContent()) {
            $params = [
                'body' => $request->getContent(),
                'headers' => $mappedHeaders,
            ];
        }
        $path = 'api/' . $slug;
        if ($request->getQueryString()) {
            $path .= '?' . $request->getQueryString();
        }
        $response = $this->service->request($path, $request->getMethod(), $params);

        return new Response($response->getBody()
            ->getContents(), $response->getStatusCode(), [
            'Content-Type' => $response->getHeader('Content-Type'),
        ]);
    }

    public function proxyStatic(Request $request, string $slug): Response
    {
        $response = $this->service->proxy('portal/' . $slug);
        $content = $response->getBody()
            ->getContents();
        $content = preg_replace('/\/portal\/([\w*\/-]+\.(css|svg|png|jpg|jpeg|woff|woff2)+)/', rtrim(\isys_application::instance()->www_path, '/') . '/portal/static/$1', $content);

        return new Response($content, $response->getStatusCode(), [
            'Content-Type' => $response->getHeader('Content-Type'),
        ]);
    }
}
