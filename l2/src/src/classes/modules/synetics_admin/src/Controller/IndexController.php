<?php

namespace idoit\Module\SyneticsAdmin\Controller;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use idoit\AddOn\Manager\Downloader;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Helper\Purify;
use idoit\Module\License\Exception\LicenseExistsException;
use idoit\Module\License\Exception\LicenseInvalidException;
use idoit\Module\License\Exception\LicenseParseException;
use idoit\Module\License\Exception\LicenseServerAuthenticationException;
use idoit\Module\License\Exception\LicenseServerConnectionException;
use idoit\Module\License\Exception\LicenseServerNoLicensesException;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;
use idoit\Module\SyneticsAdmin\Service\EnvironmentService;
use idoit\Module\SyneticsAdmin\Service\InstanceService;
use idoit\Module\SyneticsAdmin\Service\SfmRequestService;
use idoit\Response\IdoitResponse;
use isys_application;
use isys_auth;
use isys_auth_synetics_admin;
use isys_auth_system;
use isys_helper_link;
use isys_module_licence;
use isys_module_synetics_admin;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Index controller
 *
 * @package   idoit\Module\Synetics_admin\Processor
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class IndexController
{
    /** @var isys_auth_synetics_admin */
    private isys_auth_synetics_admin $auth;

    /**
     * @var SfmRequestService
     */
    private SfmRequestService $sfmRequestService;

    public function __construct()
    {
        $this->auth = isys_module_synetics_admin::getAuth();
        $this->sfmRequestService = SfmRequestService::getInstance();
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function iframe(): Response
    {
        $template = isys_application::instance()->container->get('template');
        $template->assign('wwwPath', rtrim(isys_application::instance()->www_path, '/'));
        $guardResult = $this->executeGuards();
        if ($guardResult !== true) {
            return $guardResult;
        }

        $this->sfmRequestService->renewSession();
        return new Response($template->fetch(isys_module_synetics_admin::getPath() . 'templates/portal.tpl'));
    }

    /**
     * @return IdoitResponse|true
     * @throws \Exception
     */
    private function executeGuards()
    {
        global $g_license_token;

        $template = isys_application::instance()->container->get('template');
        if (!$this->auth->canAccessAdminCenter()) {
            $template->assign('error', 'permission-missing');

            return (new IdoitResponse(isys_module_synetics_admin::getPath() . 'templates/error.tpl'))->showNavbar(false)
                ->showBreadcrumb(false);
        }

        if (!internetAvailable()) {
            $template->assign('error', 'system-is-offline');

            return (new IdoitResponse(isys_module_synetics_admin::getPath() . 'templates/error.tpl'))->showNavbar(false)
                ->showBreadcrumb(false);
        }

        if (!$g_license_token) {
            $template->assign('error', 'license-token-missing');

            return (new IdoitResponse(isys_module_synetics_admin::getPath() . 'templates/error.tpl'))->showNavbar(false)
                ->showBreadcrumb(false);
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function page(Request $request): Response
    {
        global $g_license_token;
        $template = isys_application::instance()->container->get('template');
        $logger = isys_application::instance()->container->get('logger');

        $customerPortalQuery = $request->attributes->get('slug');
        $queryString  = $request->getQueryString();
        $lang = isys_application::instance()->container->get('language')->get_loaded_language();
        $customerPortalQuery .= "?lang={$lang}";
        if ($queryString) {
            $customerPortalQuery .= '&' . $queryString;
        }

        // Assign some necessary data.
        $template->assign('jsDirectory', isys_module_synetics_admin::getWwwPath() . 'js/')
            ->assign('wwwPath', rtrim(isys_application::instance()->www_path, '/'))
            ->assign('customerPortalHost', isys_helper_link::getCurrentDomain())
            ->assign('customerPortalUrl', isys_helper_link::get_base() . 'portal')
            ->assign('customerPortalIframe', 'customerPortalIframe')
            ->assign('customerPortalSlug', $customerPortalQuery)
            ->assign('title', isys_application::instance()->container->get('language')
                ->get('LC__SYNETICS_ADMIN'));

        $guardResult = $this->executeGuards();

        if ($guardResult !== true) {
            return $guardResult;
        }

        try {
            $this->sfmRequestService->renewSession();
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            if ($e instanceof RequestException && $e->hasResponse() && $e->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN) {
                $logger->error("Add-on and Subscription Center login failed: {$message}");
                $template->assign('error', 'portal-auth-failed');
            } elseif ($e instanceof ConnectException) {
                $logger->error("Add-on and Subscription Center connection failed: {$message}");

                $url = parse_url($e->getRequest()->getUri());

                $template
                    ->assign('error', 'portal-connection-failed')
                    ->assign('errorParamA', $e->getMessage())
                    ->assign('errorParamB', $url['scheme'] . '://' . $url['host']);
            } else {
                $responseStatus = null;

                if ($e instanceof RequestException && $e->hasResponse()) {
                    $responseStatus = $e->getResponse()->getStatusCode();
                }

                $logger->error("Add-on and Subscription Center encountered an error: {$message}");
                $template->assign('error', $responseStatus === null ? $message : "Status {$responseStatus}: {$message}");
            }

            return (new IdoitResponse(isys_module_synetics_admin::getPath() . 'templates/error.tpl'))
                ->showNavbar(false)
                ->showBreadcrumb(false);
        }

        return (new IdoitResponse(isys_module_synetics_admin::getPath() . 'templates/index.tpl'))
            ->showNavbar(false)
            ->showBreadcrumb(false);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateIdoit(Request $request): JsonResponse
    {
        try {
            global $g_absdir;

            if (!isys_auth_system::instance()
                    ->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/IDOITUPDATE') || FeatureManager::isCloud()) {
                return JSONResponseFactory::error('You do not have necessary rights to perform i-doit updates.');
            }

            $body = $request->toArray();

            $downloadLink = $body['downloadLink'] ?: null;
            if (!is_string($downloadLink) || !filter_var($downloadLink, FILTER_VALIDATE_URL)) {
                return JSONResponseFactory::error('Unable to download i-doit update package because given url is invalid.');
            }

            if (!is_writable($g_absdir)) {
                return JSONResponseFactory::error('i-doit root is not writable. This is required to be able to write the new i-doit update package.');
            }

            $filename = 'i-doit-update-' . microtime() . '.zip';
            $target = $g_absdir . $filename;
            $downloader = new Downloader();
            $downloader->download($downloadLink, $target);

            if (!file_exists($target)) {
                return JSONResponseFactory::error('Unable to download i-doit update package.');
            }

            if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
                return JSONResponseFactory::error('Update procedure requires php extension \'zLib\'.');
            }

            $zip = new ZipArchive();
            if ($zip->open($target) !== true) {
                return JSONResponseFactory::error('Unable to open i-doit update package.');
            }
            $zip->extractTo($g_absdir);
            $zip->close();

            unlink($target);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            isys_application::instance()->container->get('logger')
                ->error('Update over Add-on & Subscription Center failed: ' . $e->getMessage());

            return JSONResponseFactory::error('Update procedure failed unexpectedly. Please check \'exception.log\' for detailed information.');
        }
    }

    /**
     * @return JsonResponse
     */
    public function getInstanceData(): JsonResponse
    {
        if (!$this->auth->canAccessAdminCenter()) {
            return JSONResponseFactory::permissionError();
        }

        $instanceService = new InstanceService();

        return JSONResponseFactory::successWithData([
            'user'         => $instanceService->getUserInformation(),
            'rights'       => $instanceService->getRights(),
            'idoitVersion' => $instanceService->getIdoitVersion(),
            'addons'       => $instanceService->getAddons(),
            'license'      => $instanceService->getLicenseInformation(),
            'tenantCount'  => $instanceService->getTenantCount(),
            'idoitUrl'     => isys_helper_link::get_base(),
            'isCloud'      => FeatureManager::isCloud(),
        ]);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function getEnvironmentData(): JsonResponse
    {
        return JSONResponseFactory::successWithData([
            'idoitVersion'    => (new InstanceService())->getIdoitVersion(),
            'phpVersion'      => phpversion(),
            'cloudInstance'         => FeatureManager::isCloud(),
            'databaseVersion' => \isys_application::instance()->container->get('database')
                ->get_version()['server'],
            'operatingSystem' => php_uname('s'),
            'environmentData' => (new EnvironmentService())->getEnvironmentData()
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getToken(): JsonResponse
    {
        global $g_license_token;

        if (!$this->auth->canManageLicense() || FeatureManager::isCloud()) {
            return JSONResponseFactory::permissionError();
        }

        return JSONResponseFactory::successWithData([
            'token' => $g_license_token
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateToken(Request $request): JsonResponse
    {
        try {
            $requestBody = $request->toArray();

            if (!$this->auth->canManageLicense() || FeatureManager::isCloud()) {
                return JSONResponseFactory::permissionError();
            }

            $licenseToken = $requestBody['newToken'];
            $this->setLicenseToken($licenseToken);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function refreshToken(Request $request): JsonResponse
    {
        global $g_license_token;
        try {
            $this->setLicenseToken($g_license_token);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteToken(Request $request): JsonResponse
    {
        if (!$this->auth->canManageLicense() || FeatureManager::isCloud()) {
            return JSONResponseFactory::permissionError();
        }

        try {
            saveLicenseToken('');

            LicenseServiceFactory::createDefaultLicenseService(\isys_application::instance()->container->get('database_system'), '')
                ->deleteLicenses([LicenseService::C__LICENCE_TYPE__NEW__IDOIT, LicenseService::C__LICENCE_TYPE__NEW__ADDON]);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }

    /**
     * @param string|null $licenseToken
     *
     * @return bool
     * @throws \Exception
     */
    private function setLicenseToken(?string $licenseToken): bool
    {
        $language = isys_application::instance()->container->get('language');
        if (!$licenseToken) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_EMPTY'));
        }
        $licenseService = LicenseServiceFactory::createDefaultLicenseService(\isys_application::instance()->container->get('database_system'), $licenseToken ?? '');

        if (Purify::purifyValueByRegex($licenseToken, '/[^[:alnum:]]+/') !== $licenseToken) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_INVALID'));
        }

        $licenseService->setEncryptionToken($licenseToken);

        try {
            $webLicensesString = $licenseService->getLicensesFromServer(true);
        } catch (GuzzleException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_CONNECT'));
        } catch (LicenseServerAuthenticationException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_AUTHENTICATE'));
        } catch (LicenseServerConnectionException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_CONNECT'));
        } catch (LicenseServerNoLicensesException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_FIND'));
        }

        try {
            foreach ($licenseService->parseEncryptedLicenses($webLicensesString) as $license) {
                $licenseService->installLicense($license);
            }
            saveLicenseToken($licenseToken);

            if (class_exists('isys_module_licence')) {
                // Go sure to set the add-on licence information.
                $licence = new isys_module_licence();
                $licence->verify();
            }
        } catch (LicenseExistsException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_ALREADY_EXISTS'));
        } catch (LicenseInvalidException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_INVALID_LICENSE'));
        } catch (LicenseParseException $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_PARSE'));
        } catch (\Throwable $exception) {
            throw new \Exception($language->get('LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNKNOWN'));
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function saveLicenseToken(Request $request): JsonResponse
    {
        try {
            if (!$this->auth->canAccessAdminCenter() || !$this->auth->canManageLicense() || FeatureManager::isCloud()) {
                return JSONResponseFactory::permissionError();
            }
            $licenseToken = $request->request->get('licenseToken');
            $this->setLicenseToken($licenseToken);

            return JSONResponseFactory::success();
        } catch (\Throwable $e) {
            return JSONResponseFactory::error($e->getMessage());
        }
    }
}
