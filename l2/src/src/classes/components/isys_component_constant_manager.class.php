<?php

use idoit\Component\ConstantManager;

/**
 * i-doit
 *
 * Constant manager class for managing the dynamic constant caches.
 * Read out several database tables and assign the '*__id'  to the specified '*__const'
 *
 * @package     i-doit
 * @subpackage  Components
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @deprecated  Please refer to the 'constant_manager' service. Will be removed in i-doit 40!
 */
class isys_component_constant_manager extends isys_component
{
    private const CACHE_FILE = 'const_cache.inc.php';
    private ConstantManager $constantManager;
    private static self $instance;
    private string $directory = '';

    /**
     * @return isys_component_constant_manager
     * @deprecated Please refer to 'constant_manager' service. Will be removed in i-doit 40!
     */
    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return string
     * @deprecated Please use 'ConstantManager->getTenantCacheFilePath()'. Will be removed in i-doit 40!
     */
    public function get_fullpath_name(): string
    {
        return $this->get_fullpath() . self::CACHE_FILE;
    }

    /**
     * @return string
     * @deprecated Please use 'ConstantManager->getSystemCacheFilePath()'. Will be removed in i-doit 40!
     */
    public function get_dcs_path(): string
    {
        return isys_glob_get_temp_dir() . self::CACHE_FILE;
    }

    /**
     * Method for returning the full path to the temp dir (including mandator sub-dir, if set).
     *
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_fullpath(): string
    {
        $fullPath = isys_glob_get_temp_dir();

        if (trim($this->directory) !== '') {
            $fullPath .= $this->directory . '/';
        }

        return $fullPath;
    }

    /**
     * Build the filename and full path for the const cache.
     *
     * @param string $directory
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    public function set_subdir(string $directory): string
    {
        $this->directory = $directory;

        return $this->get_fullpath_name();
    }

    /**
     * @return void
     * @deprecated Please use 'ConstantManager->deleteTenantCacheFile()'. Will be removed in i-doit 40!
     */
    public function clear_dcm_cache(): void
    {
        $this->constantManager->deleteTenantCacheFile();
    }

    /**
     * @return void
     * @deprecated Please use 'ConstantManager->deleteSystemCacheFile()'. Will be removed in i-doit 40!
     */
    public function clear_dcs_cache(): void
    {
        $this->constantManager->deleteSystemCacheFile();
    }

    /**
     * @return ConstantManager
     * @deprecated Please use 'ConstantManager->createTenantCacheFile()'. Will be removed in i-doit 40!
     */
    public function create_dcm_cache(): ConstantManager
    {
        return $this->constantManager->createTenantCacheFile();
    }

    /**
     * @return ConstantManager
     * @deprecated Please use 'ConstantManager->createSystemCacheFile()'. Will be removed in i-doit 40!
     */
    public function create_dcs_cache(): ConstantManager
    {
        return $this->constantManager->createSystemCacheFile();
    }

    /**
     * @return ConstantManager
     * @throws Exception
     * @deprecated Please use 'ConstantManager->includeSystemCache()'. Will be removed in i-doit 40!
     */
    public function include_dcs(): ConstantManager
    {
        return $this->constantManager->includeSystemCache();
    }

    /**
     * @param string $directory
     * @return bool
     * @throws Exception
     * @deprecated Please use 'ConstantManager->includeTenantCache()'. Will be removed in i-doit 40!
     */
    public function include_dcm(string $directory): bool
    {
        $this->set_subdir($directory);

        $this->constantManager->includeTenantCache();

        return true;
    }

    private function __construct()
    {
        $this->constantManager = isys_application::instance()->container->get('constant_manager');
    }
}
