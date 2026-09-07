<?php

namespace idoit\Module\SyneticsAdmin\Service;

use Carbon\Carbon;
use idoit\AddOn\Manager\ManageCandidates;
use idoit\Module\License\Entity\License;
use idoit\Module\License\LicenseServiceFactory;
use isys_component_dao_mandator;
use isys_module_statistics;
use isys_module_synetics_admin;

/**
 * InstanceService
 *
 * @package   idoit\Module\Synetics_admin\Service
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class InstanceService
{
    public function getAddons(): array
    {
        $database = \isys_application::instance()->container->get('database');
        /** @var \isys_module_manager $moduleManager */
        $moduleManager = \isys_application::instance()->container->get('moduleManager');
        $addons = $moduleManager->get_modules();
        $results = [];
        while ($addon = $addons->get_row()) {
            $addonInstance = new ManageCandidates($database, \isys_application::instance()->app_path . 'src/classes/modules/');
            $addonInstance->setIdentifier($addon['isys_module__identifier']);
            $packageJson = $addonInstance->getPackageJson();
            $results[] = [
                'identifier' => $addon['isys_module__identifier'],
                'isActive' => (int)$addon['isys_module__status'] === C__RECORD_STATUS__NORMAL,
                'version' => $packageJson['version'] ?: '0.0'
            ];
        }

        return $results;
    }

    public function getUserInformation(): array
    {
        /** @var \isys_component_session $session */
        $session = \isys_application::instance()->container->get('session');
        $data = $session->get_session_data();

        return [
            'id'        => (int)$data['isys_obj__id'],
            'username'  => $data['isys_obj__title'],
            'firstname' => $data['isys_cats_person_list__first_name'],
            'lastname'  => $data['isys_cats_person_list__last_name'],
            'email'     => $data['isys_cats_person_list__mail_address'],
        ];
    }

    public function getRights(): array
    {
        $auth = isys_module_synetics_admin::getAuth();

        return [
            'can-access' => $auth->canAccessAdminCenter(),
            'can-view-addons' => $auth->canViewAddons(),
            'can-manage-addons' => $auth->canManageAddons(),
            'can-manage-license' => $auth->canManageLicense(),
        ];
    }

    public function getIdoitVersion(): string
    {
        global $g_product_info;

        return $g_product_info['version'];
    }

    public function getLicenseInformation(): array
    {
        global $g_license_token;

        $session = \isys_application::instance()->container->get('session');
        $licenseService = LicenseServiceFactory::createDefaultLicenseService(\isys_application::instance()->container->get('database_system'), $g_license_token);
        $earliestExpiringLicense = $licenseService->getEarliestExpiringLicense();

        $isLicensed = true;
        $requestedAt = null;
        $expiresAt = null;
        $remaining = null;

        $now = Carbon::now();
        $isUnlimited = false;

        if ($earliestExpiringLicense instanceof License) {
            // @see ID-11965 Do not allow german date format.
            $requestedAt = $earliestExpiringLicense->getValidityFrom()->format('Y-m-d');
            $expiresAt = $earliestExpiringLicense->getValidityTo()->format('Y-m-d');

            $remaining = $earliestExpiringLicense->getValidityTo()->diff($now)->days;
            $isUnlimited = $earliestExpiringLicense->isUnlimited();
        }

        $expiresWithinSixMonths = Carbon::parse($expiresAt)
            ->between(
                $now,
                $now->copy()->addMonths(6)
            );

        if (!$licenseService->isTenantLicensed($session->get_mandator_id())) {
            $isLicensed = false;
        }

        $tenant = $licenseService->getTenants(true, [$session->get_mandator_id()]);
        $licensedObjects = (int) $tenant[0]['isys_mandator__license_objects'];

        $statisticsModule = new isys_module_statistics();
        $statisticsModule->init_statistics();
        $objectUsage = $statisticsModule->get_counts();

        return [
            'isLicensed' => $isLicensed,
            'starts' => $requestedAt,
            'expires' => $expiresAt,
            'isUnlimited' => $isUnlimited,
            'licensedObjects' => $licensedObjects,
            'expiresWithinSixMonth' => $expiresWithinSixMonths,
            'remainingDays' => $remaining,
            'usage' => [
                'used' => (int)$objectUsage['objects'],
                'licensed' => $licensedObjects
            ]
        ];
    }

    public function getTenantCount(): int
    {
        return (new isys_component_dao_mandator)->get_mandator()->count();
    }
}
