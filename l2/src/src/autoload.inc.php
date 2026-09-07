<?php

/**
 * i-doit - class autoloader
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Autoloader;
use idoit\Psr4AutoloaderClass;

// Include composer's autoloader
if (file_exists(BASE_DIR . 'vendor/autoload.php')) {
    include_once BASE_DIR . 'vendor/autoload.php';
} else {
    throw new Exception("Composers autoloader not found. Composer could not be initialized! Run 'composer install' in root directory! (https://getcomposer.org)");
}

include_once __DIR__ . '/autoload-psr4.inc.php';
include_once __DIR__ . '/classes/modules/manager/isys_module_manager_autoload.class.php';

try {
    // Register our PSR4 autoloader
    $psr4Autoloader = Psr4AutoloaderClass::factory()->register();

    // @see ID-9145 Initialize our autoloader for legacy classes.
    Autoloader::init(include_once(__DIR__ . '/classmap.php'));

    $updatesClassMap = dirname(__DIR__) . '/updates/classmap.php';

    if (file_exists($updatesClassMap)) {
        Autoloader::appendClassmap(include_once($updatesClassMap));
    }

    // @see ID-11340 Register report related autoloader for add-ons that bring reports (not only packager).
    if (class_exists(\isys_module_report::class)) {
        $psr4Autoloader->addNamespace('idoit\Module\Report', \isys_module_report::getPath() . 'src/');

        if (file_exists($reportClassmapPath = \isys_module_report::getPath() . 'classmap.php')) {
            Autoloader::appendClassmap(include($reportClassmapPath));
        }
    }
} catch (\Exception $e) {
    // Do nothing.
}
