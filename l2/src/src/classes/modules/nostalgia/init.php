<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     modules
 * @subpackage  nostalgia
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Autoloader.
\idoit\Component\Autoloader::appendClassmap(require_once __DIR__ . '/classmap.php');

if (file_exists(__DIR__ . '/functions.inc.php')) {
    include_once __DIR__ . '/functions.inc.php';
}

if (file_exists(__DIR__ . '/constants.inc.php')) {
    include_once __DIR__ . '/constants.inc.php';
}
