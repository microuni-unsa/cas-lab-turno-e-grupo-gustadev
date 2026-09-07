<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Autoloader;
use idoit\Module\Import\ImportFileDownloadType;
use idoit\Module\Import\ImportFileUploadType;
use idoit\Psr4AutoloaderClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

// Adding the PSR-4 autoloader.
Psr4AutoloaderClass::factory()->addNamespace('idoit\Module\Import', __DIR__ . '/src/');

// Add classmap for legacy code.
Autoloader::appendClassmap(include(__DIR__ . '/classmap.php'));

define('C__IMPORT__GET__IMPORT', 1);
define('C__IMPORT__GET__FINISHED_IMPORTS', 2);
define('C__IMPORT__GET__SCRIPTS', 3);
define('C__IMPORT__GET__CSV', 5);
define('C__IMPORT__GET__JDISC', 6);
define('C__IMPORT__GET__LDAP', 7);
define('C__IMPORT__GET__SHAREPOINT', 8);
define('C__IMPORT__GET__CABLING', 9);
define('C__IMPORT__GET__LOGINVENTORY', 10);
define('C__CMDB__GET__CSV_AJAX', 'call_csv_handler_action');
// Path to log files.
define('C__IMPORT__LOG_DIRECTORY', BASE_DIR . '/log/');

// Append import config how to handle with validation errors
isys_tenantsettings::extend([
    'LC__MODULE__IMPORT' => [
        'import.validation.break-on-error' => [
            'title'       => 'LC__MODULE__IMPORT__VALIDATION_BREAK_ON_ERROR',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '1',
            'description' => 'LC__MODULE__IMPORT__VALIDATION_BREAK_ON_ERROR_DESCRIPTION'
        ],
        'import.validation.empty-attribute-on-error' => [
            'title'       => 'LC__MODULE__IMPORT__VALIDATION_EMPTY_ATTRIBUTE_ON_ERROR',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '0',
            'description' => 'LC__MODULE__IMPORT__VALIDATION_EMPTY_ATTRIBUTE_ON_ERROR_DESCRIPTION'
        ],
        'import.csv.overwrite-objecttype' => [
            'title'       => 'LC__MODULE__IMPORT__CSV_OVERWRITE_OBJECT_TYPE',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '0',
            'description' => 'LC__MODULE__IMPORT__CSV_OVERWRITE_OBJECT_TYPE_DESCRIPTION'
        ],
        'import.csv.import-limit' => [
            'title'       => 'LC__MODULE__IMPORT__CSV_IMPORT_ROW_LIMIT',
            'type'        => 'int',
            'placeholder' => 25,
            'default'     => 25,
            'description' => 'LC__MODULE__IMPORT__CSV_IMPORT_ROW_LIMIT_DESCRIPTION'
        ]
    ]
]);

/** @var idoit\Component\Upload\Upload $upload */
$upload = isys_application::instance()->container->get('upload', ContainerInterface::NULL_ON_INVALID_REFERENCE);
$upload?->registerUploadType('import.file-upload', new ImportFileUploadType());

/** @var idoit\Component\Download\Download $download */
$download = isys_application::instance()->container->get('download', ContainerInterface::NULL_ON_INVALID_REFERENCE);
$download?->registerDownloadType('import.file-download', new ImportFileDownloadType());
