<?php

namespace idoit\Module\Import;

use idoit\Component\Download\DownloadType;
use isys_application;
use isys_component_session;
use isys_tenantsettings;

/**
 * i-doit Import file download type.
 *
 * @package     Modules
 * @subpackage  Import
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ImportFileDownloadType extends DownloadType
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct([self::class, 'download']);
    }

    /**
     * @param string $fileName
     * @return string|null
     */
    public static function download(string $fileName): ?string
    {
        if ($fileName === 'h-inventory') {
            return BASE_DIR . '/imports/scripts/inventory.zip';
        }

        $importFolder = isys_tenantsettings::get(
            'system.dir.import-uploads',
            rtrim(BASE_DIR, '/') . '/imports/' // Default folder
        );

        /** @var isys_component_session $session */
        $session = isys_application::instance()->container->get('session');

        $importTenantFolder = rtrim($importFolder, '/') . '/' . $session->get_mandator_id();

        if ($fileName === 'cabling') {
            return "{$importTenantFolder}/cabling_import.csv";
        }

        $importFile = basename($fileName);

        if (!file_exists("{$importTenantFolder}/{$importFile}")) {
            return null;
        }

        return "{$importTenantFolder}/{$importFile}";
    }
}
