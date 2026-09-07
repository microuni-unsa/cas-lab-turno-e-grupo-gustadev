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

use idoit\Component\FeatureManager\FeatureManager;
use idoit\Psr4AutoloaderClass;

if (include_once('isys_module_tts_autoload.class.php')) {
    spl_autoload_register('isys_module_tts_autoload::init');
}

Psr4AutoloaderClass::factory()->addNamespace(
    'idoit\Module\Tts',
    __DIR__ . '/src/'
);

if (FeatureManager::isFeatureActive('tts-category')) {
    isys_application::instance()->container->get('signals')
        ->connect('mod.cmdb.processMenuTreeLinks', ['isys_module_tts', 'process_menu_tree_links']);
}

if (FeatureManager::isFeatureActive('tts-settings')) {
    isys_application::instance()->container->get('signals')
        ->connect('mod.settings.extendTree.interfaces', ['isys_module_tts', 'extendInterfacesTree']);
}
