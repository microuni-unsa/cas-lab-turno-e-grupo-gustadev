<?php
/**
 * i-doit
 *
 * Admin Center 2.0
 *
 * @package   synetics_admin add-on
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */

use idoit\Component\Autoloader;
use idoit\Psr4AutoloaderClass;

if (isys_module_manager::instance()->is_active('synetics_admin')) {
    define('C__SFM_STANDALONE_URL', 'https://center.i-doit.com');

    Psr4AutoloaderClass::factory()->addNamespace('idoit\Module\SyneticsAdmin', __DIR__ . '/src/');

    Autoloader::appendClassmap(include_once __DIR__ . '/classmap.php');

    // @see ID-11116 Show 'one-time' advertisement for new 'Subscription & Addons' area.
    if (isys_application::instance()->container->get('session')->is_logged_in()) {
        isys_application::instance()->container->get('signals')
            ->connect('system.gui.beforeRender', function () {
                if ((int)isys_application::instance()->container->get('settingsUser')->get('synetics_admin.news.subscription-and-addons', 0) === 0) {
                    isys_application::instance()->container->get('template')
                        ->appendJavascript(isys_module_synetics_admin::getWwwPath() . 'assets/js/news-modal.js');
                }
            });
    }
}
