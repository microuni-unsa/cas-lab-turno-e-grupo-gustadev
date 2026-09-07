<?php

use idoit\AddOn\ActivatableInterface;
use idoit\AddOn\AuthableInterface;
use idoit\AddOn\InstallableInterface;
use idoit\AddOn\RoutingAwareInterface;
use idoit\Exception\JsonException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\PhpFileLoader;

/**
 * i-doit
 *
 * Add-on synetics_admin module class.
 *
 * @package   synetics_admin
 * @copyright synetics
 * @license   https://www.i-doit.com/
 */
class isys_module_synetics_admin extends isys_module implements InstallableInterface, ActivatableInterface, AuthableInterface, RoutingAwareInterface
{
    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @return void
     * @throws \Exception
     */
    public function start()
    {
        $indexUrl = isys_application::instance()->container->get('route_generator')->generate('synetics_admin.index');

        header('Location: ' . $indexUrl);
        die;
    }

    /**
     * Initializes the module.
     *
     * @param isys_module_request $p_req
     */
    public function init(isys_module_request $p_req)
    {
    }

    /**
     * Checks if a add-on is installed.
     *
     * @return int|bool
     */
    public static function isInstalled()
    {
        return isys_module_manager::instance()
            ->is_installed('synetics_admin');
    }

    /**
     * Basic installation process for all mandators.
     *
     * @param isys_component_database $tenantDatabase
     * @param isys_component_database $systemDatabase
     * @param int                     $moduleId
     * @param string                  $type
     * @param int                     $tenantId
     *
     * @return bool
     * @throws JsonException
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public static function install($tenantDatabase, $systemDatabase, $moduleId, $type, $tenantId)
    {
        return true;
    }

    /**
     * Uninstall add-on for all mandators.
     *
     * @param isys_component_database $tenantDatabase
     *
     * @return boolean
     * @throws JsonException
     * @throws isys_exception_dao
     */
    public static function uninstall($tenantDatabase)
    {
        return true;
    }

    /**
     * Checks if a add-on is active.
     *
     * @return integer|bool
     */
    public static function isActive()
    {
        return isys_module_manager::instance()
            ->is_installed('synetics_admin', true);
    }

    /**
     * Method that is called after clicking "activate" in admin center for specific mandator.
     *
     * @param isys_component_database $tenantDatabase
     *
     * @return boolean
     * @throws JsonException
     * @throws isys_exception_dao
     */
    public static function activate($tenantDatabase)
    {
        return true;
    }

    /**
     * Method that is called after clicking "deactivate" in admin center for specific mandator.
     *
     * @param isys_component_database $tenantDatabase
     *
     * @return boolean
     * @throws isys_exception_dao
     * @throws JsonException
     */
    public static function deactivate($tenantDatabase)
    {
        return true;
    }

    public static function getAuth()
    {
        return isys_auth_synetics_admin::instance();
    }

    public static function registerRouting(): void
    {
        isys_application::instance()->container->get('routes')
            ->addCollection((new PhpFileLoader(new FileLocator(__DIR__)))->load('config/routes.php'));
    }
}
