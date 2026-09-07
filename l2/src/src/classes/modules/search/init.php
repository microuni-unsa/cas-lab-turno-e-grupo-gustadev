<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Autoloader;
use idoit\Event\System\Settings\ExtendTenantSettings;
use idoit\Psr4AutoloaderClass;

// Add PSR4 autoloader.
Psr4AutoloaderClass::factory()
    ->addNamespace('idoit\Module\Search', __DIR__ . '/src/');

// Add classmap for legacy code.
Autoloader::appendClassmap([
    'isys_auth_search' => 'src/classes/modules/search/auth/isys_auth_search.class.php',
    'isys_module_search' => 'src/classes/modules/search/isys_module_search.class.php',
]);

// Extend the settings using events.
isys_application::instance()->container->get('event_dispatcher')
    ->addListener(ExtendTenantSettings::NAME, ['isys_module_search', 'extendTenantSettings']);

// Hide the menu tree, when opening the 'search' module.
isys_application::instance()->container->get('components.registry')
    ->register('menu_tree.config.search.showMenu', false);
