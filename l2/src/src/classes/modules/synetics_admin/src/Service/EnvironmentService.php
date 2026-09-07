<?php

namespace idoit\Module\SyneticsAdmin\Service;

use idoit\AddOn\Manager\ManageCandidates;
use idoit\Module\License\Entity\License;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;

/**
 * Environment server
 *
 * @package   idoit\Module\Synetics_admin\Service
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class EnvironmentService
{
    private \isys_component_template_language_manager $language;
    private \isys_component_database $database;
    private \isys_component_database $databaseSystem;
    private \isys_component_session $session;
    private \isys_cmdb_dao $cmdbDao;
    private \isys_module_manager $moduleManager;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->language = \isys_application::instance()->container->get('language');
        $this->database = \isys_application::instance()->container->get('database');
        $this->databaseSystem = \isys_application::instance()->container->get('database_system');
        $this->session = \isys_application::instance()->container->get('session');
        $this->cmdbDao = \isys_application::instance()->container->get('cmdb_dao');
        $this->moduleManager = \isys_application::instance()->container->get('moduleManager');
    }

    /**
     * Convert ini sizes to something readable like '500 MB'.
     *
     * @param string $value
     *
     * @return string
     */
    private function iniToX(string $value): string
    {
        if (!$value || !is_numeric(substr($value, 0, -1))) {
            return 0;
        }

        $numberValue = (int)$value;

        $bytes = match (strtolower(substr($value, -1))) {
            'g' => $numberValue * (1024 ** 3),
            'm' => $numberValue * (1024 ** 2),
            'k' => $numberValue * 1024,
            default => $numberValue,
        };

        return $this->byteToX($bytes);
    }

    /**
     * Convert unreadable byte numbers to something like '2.5 GB'.
     *
     * @param int $bytes
     *
     * @return string
     */
    private function byteToX(int $bytes): string
    {
        $kiloByte = 1024;
        $megaByte = 1024 ** 2;
        $gigaByte = 1024 ** 3;

        if ($bytes >= $gigaByte) {
            return round(($bytes / $gigaByte), 2) . ' GB';
        }

        if ($bytes >= $megaByte) {
            return round(($bytes / $megaByte), 2) . ' MB';
        }

        if ($bytes >= $kiloByte) {
            return round(($bytes / $kiloByte), 2) . ' KB';
        }

        return $bytes . ' Byte';
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getSystemConfiguration(): array
    {
        // Fetch timing diff.
        $mySQLTimestamp = strtotime($this->cmdbDao->retrieve('SELECT CURRENT_TIMESTAMP() AS t;')->get_row_value('t'));
        $phpTime = time();
        $diff = abs($phpTime - $mySQLTimestamp);

        // Fetch database size.
        $databaseSize = 0;
        $result = $this->cmdbDao->retrieve('SHOW TABLE STATUS');

        while ($row = $result->get_row()) {
            $databaseSize += $row['Data_length'] + $row['Index_length'];
        }

        return [
            'Time difference PHP / database' => $diff === 1 ? $diff . ' second' : $diff . ' seconds',
            'Architecture'                   => strlen(decbin(-1)) . ' bit',
            'Database size'                  => $this->byteToX($databaseSize),
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getLicenseInfo(): array
    {
        global $g_license_token;

        // Fetch license data.
        $licenseService = LicenseServiceFactory::createDefaultLicenseService($this->databaseSystem, $g_license_token);
        $tenant = $licenseService->getTenants(true, [$this->session->get_mandator_id()]);

        $earliestExpiringLicense = $licenseService->getEarliestExpiringLicense();

        if ($earliestExpiringLicense instanceof License) {
            $registrationDate = $earliestExpiringLicense->getValidityFrom();
            $expires = $earliestExpiringLicense->getValidityTo();
        } elseif (isset($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE])) {
            if ($earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] == LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE ||
                $earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] == LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING) {
                $registrationDate = new \DateTime($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);
                $expires = (clone $registrationDate)->modify("+99 years");
            }

            if (isset($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME])) {
                $days = (int)round(abs((($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME] / 60 / 60 / 24))));

                $registrationDate = new \DateTime($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);
                $expires = (clone $registrationDate)->modify("+{$days} days");
            }
        }

        $freeObjects = (int)$tenant[0]['isys_mandator__license_objects'];

        // Fetch object count.
        $statistics = new \isys_module_statistics();
        $statistics->init_statistics();
        $documentedObjects = (int)$statistics->get_counts()['objects'];

        return [
            'Documented objects' => $documentedObjects,
            'Free objects'       => $freeObjects - $documentedObjects,
            'Registration date'  => $registrationDate->format('d.m.Y'),
            'Expiration date'    => $expires->format('d.m.Y'),
        ];
    }

    /**
     * @return array
     * @throws \isys_exception_database
     */
    private function getAddonInfos(): array
    {
        $results = [];
        $addons = $this->moduleManager->get_modules();

        while ($addon = $addons->get_row()) {
            $addonInstance = new ManageCandidates($this->database, \isys_application::instance()->app_path . 'src/classes/modules/');
            $addonInstance->setIdentifier($addon['isys_module__identifier']);

            $packageJson = $addonInstance->getPackageJson();

            $key = $this->language->get($addon['isys_module__title']) . ' (' . ($addon['isys_module__identifier'] ?: '-') . ')';

            if ($packageJson['type'] !== 'addon') {
                continue;
            }

            $results[$key] = ($packageJson['version'] ?: '0.0') . ' (' . ($addon['isys_module__status'] == C__RECORD_STATUS__NORMAL ? 'active' : 'inactive') . ')';
        }

        ksort($results);

        return $results;
    }

    /**
     * @return array
     */
    private function getPhpSettings(): array
    {
        $settings = [
            'max_execution_time'  => ini_get('max_execution_time') . ' (>180 recommended)',
            'upload_max_filesize' => $this->iniToX(ini_get('upload_max_filesize')) . ' (>=128 MB recommended, 64 MB OK)',
            'post_max_size'       => $this->iniToX(ini_get('post_max_size')) . ' (>=128 MB recommended, 64 MB OK)',
            'allow_url_fopen'     => ini_get('allow_url_fopen') . ' (should be "on")',
            'max_input_vars'      => ini_get('max_input_vars') . ' (>= 10000)',
            'file_uploads'        => ini_get('file_uploads') . ' (should be "on")',
            'memory_limit'        => $this->iniToX(ini_get('memory_limit')) . ' (>=256 MB recommended)',
        ];

        return $settings;
    }

    /**
     * @return array
     */
    private function getPhpExtensions(): array
    {
        // See composer.json for mandatory extensions.
        $mandatoryExtensions = \isys_application::REQUIRED_PHP_EXTENSIONS;

        // See package.json files from each add-on.
        $optionalExtensions = [];

        foreach (glob(BASE_DIR . 'src/classes/modules/*/package.json') as $packageJsonFile) {
            $packageJsonData = json_decode(file_get_contents($packageJsonFile), true);

            if (!isset($packageJsonData['dependencies']['php']) || !is_array($packageJsonData['dependencies']['php'])) {
                continue;
            }

            $optionalExtensions = array_merge($optionalExtensions, $packageJsonData['dependencies']['php']);
        }

        // Fetch all optional extensions that are not part of our required extensions.
        $optionalExtensions = array_diff(array_filter(array_unique($optionalExtensions)), $mandatoryExtensions);

        $installedExtensions = get_loaded_extensions();

        // Calculate missing required and optional extensions.
        $missingMandatoryExtenions = array_diff($mandatoryExtensions, array_map('strtolower', $installedExtensions));
        $missingOptionalExtenions = array_diff($optionalExtensions, array_map('strtolower', $installedExtensions));

        $extensions = [];

        foreach ($missingOptionalExtenions as $extension) {
            $extensions[$extension] = 'Missing (optional)';
        }

        foreach ($missingMandatoryExtenions as $extension) {
            $extensions[$extension] = 'Missing (mandatory)';
        }

        ksort($extensions);

        if (empty($extensions)) {
            $extensions['-'] = 'All necessary extensions are installed';
        }

        return $extensions;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getDatabaseSettings(): array
    {
        $settings = [
            'innodb_buffer_pool_size' => $this->byteToX($this->database->get_config_value('innodb_buffer_pool_size')) . ' (>=1024 MB recommended)',
            'max_allowed_packet'      => $this->byteToX($this->database->get_config_value('max_allowed_packet')) . ' (>=128 MB recommended)',
            'tmp_table_size'          => $this->byteToX($this->database->get_config_value('tmp_table_size')) . ' (32 MB recommended)',
            'max_heap_table_size'     => $this->byteToX($this->database->get_config_value('max_heap_table_size')) . ' (32 MB recommended)',
            'join_buffer_size'        => $this->byteToX($this->database->get_config_value('join_buffer_size')) . ' (256 KB recommended)',
            'sort_buffer_size'        => $this->byteToX($this->database->get_config_value('sort_buffer_size')) . ' (256 KB recommended)',
            'innodb_sort_buffer_size' => $this->byteToX($this->database->get_config_value('innodb_sort_buffer_size')) . ' (64 MB recommended)',
            'innodb_log_file_size'    => $this->byteToX($this->database->get_config_value('innodb_log_file_size')) . ' (>=512 MB recommended)',
        ];

        return $settings;
    }

    public function getEnvironmentData(): array
    {
        return [
            'System config'     => $this->getSystemConfiguration(),
            'License'           => $this->getLicenseInfo(),
            'Add-ons'           => $this->getAddonInfos(),
            'PHP settings'      => $this->getPhpSettings(),
            'PHP extensions'    => $this->getPhpExtensions(),
            'Database settings' => $this->getDatabaseSettings(),
        ];
    }
}
