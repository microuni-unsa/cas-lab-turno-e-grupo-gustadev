<?php

namespace idoit\Module\Import;

use idoit\Component\Upload\UploadType;
use isys_application;
use isys_component_session;
use isys_convert;
use isys_tenantsettings;

/**
 * i-doit Import file upload type.
 *
 * @package     Modules
 * @subpackage  Import
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ImportFileUploadType extends UploadType
{
    /**
     *
     */
    public function __construct()
    {
        $importFolder = isys_tenantsettings::get(
            'system.dir.import-uploads',
            rtrim(BASE_DIR, '/') . '/imports/' // Default folder
        );

        /** @var isys_component_session $session */
        $session = isys_application::instance()->container->get('session');

        // The 'UploadType' will prepend the app path, so we need to strip it here.
        $importTenantFolder = str_replace(
            rtrim(isys_application::instance()->app_path, '/\\'),
            '',
            rtrim($importFolder, '/') . '/' . $session->get_mandator_id() . '/'
        );

        $sizeLimit = min(isys_convert::to_bytes(ini_get('upload_max_filesize')), isys_convert::to_bytes(ini_get('post_max_size')));

        $this
            ->setUploadDirectory($importTenantFolder)
            ->setValidExtensions(['csv', 'xml'])
            ->setSizeLimit($sizeLimit);
    }
}
