<?php
/**
 * i-doit
 *
 * @package     i-doit
 * @copyright   synetics GmbH
 * @license     www.i-doit.com
 */

use idoit\Psr4AutoloaderClass;

Psr4AutoloaderClass::factory()->addNamespace('idoit\Module\UserSettings', __DIR__ . '/src/');
