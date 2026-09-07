<?php

namespace idoit\Component;

/**
 * i-doit Autoloader class for classmaps.
 *
 * @see       ID-9145
 * @package   idoit\AddOn
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Autoloader
{
    /**
     * @var array
     */
    private static array $classMap;

    /**
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Initialize your autoloader by preparing the classmap.
     *
     * @param array $classmap
     *
     * @return void
     */
    public static function init(array $classmap): void
    {
        if (self::$initialized) {
            return;
        }

        self::$classMap = $classmap;

        include_once BASE_DIR . 'src/classes/cache/isys_cache.class.php';

        spl_autoload_register(function (string $className): void {
            $moduleClassName = str_replace('\\', '', $className);

            if (strpos($moduleClassName, 'isys_module') === 0) {
                $path = BASE_DIR . 'src/classes/modules/' . substr($moduleClassName, 12) . '/';

                if (file_exists($path . $moduleClassName . '.class.php') && include_once($path . $moduleClassName . '.class.php')) {
                    return;
                }
            }

            if (!isset(self::$classMap[$className])) {
                return;
            }

            $path = BASE_DIR . self::$classMap[$className];

            if (!file_exists($path)) {
                return;
            }

            include_once $path;
        });

        self::$initialized = true;
    }

    /**
     * @param array $classmap
     *
     * @return void
     */
    public static function appendClassmap(array $classmap): void
    {
        self::$classMap = array_merge(self::$classMap, $classmap);
    }
}
