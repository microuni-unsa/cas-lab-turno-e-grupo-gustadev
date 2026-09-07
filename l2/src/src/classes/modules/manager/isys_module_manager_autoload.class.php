<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_manager_autoload
{
    /**
     * @param string $classname
     *
     * @return void
     */
    public static function init($classname)
    {
    }

    /**
     * Method for including the given file.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public static function include_file(string $filePath): bool
    {
        return !empty($filePath) && file_exists(BASE_DIR . $filePath) && (include_once BASE_DIR . $filePath);
    }
}
