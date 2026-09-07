<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (include_once('isys_module_templates_autoload.class.php')) {
    spl_autoload_register('isys_module_templates_autoload::init');
}

\idoit\Psr4AutoloaderClass::factory()->addNamespace('idoit\Module\Templates', __DIR__ . '/src/');

// @see ID-8772 register signal for autoinventory
$database = isys_application::instance()->container->get('database');
isys_application::instance()->container->get('signals')
    ->connect('mod.cmdb.templatesApplied', [isys_cmdb_dao_category_g_accounting::instance($database), 'templateAppliedAutoInventory']);
