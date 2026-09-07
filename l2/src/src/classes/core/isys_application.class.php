<?php

use idoit\AddOn\ExtensionProviderInterface;
use idoit\Module\Cmdb\Model\CiTypeCategoryAssigner;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Filesystem\Filesystem;

/**
 * i-doit main application controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @property isys_component_template $template
 */
final class isys_application
{
    /**
     * @see ID-11468 Define required PHP extensions (taken from composer.json, please keep up-to-date).
     */
    public const REQUIRED_PHP_EXTENSIONS = [
        'curl',
        'gd',
        'json',
        'ldap',
        'libxml',
        'mbstring',
        'mysqli',
        'pcre',
        'pdo',
        'phar',
        'session',
        'simplexml',
        'sockets',
        'spl',
        'xml',
        'zip',
        'zlib',
    ];

    /**
     * @var isys_application
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    public $app_path = './';

    /**
     * Dependency injection container
     *
     * @var ContainerBuilder
     */
    public $container = null;

    /**
     * Also known as $g_product_info
     *
     * @var isys_array
     */
    public $info = null;

    /**
     * @var string
     */
    public $language = 'en';

    /**
     * @var isys_module
     */
    public $module = null;

    /**
     * @var isys_tenant
     */
    public $tenant = null;

    /**
     * @var string
     */
    public $www_path = '/';

    /**
     * @see MDN list: https://developer.mozilla.org/en-US/docs/Web/Media/Guides/Formats/Image_types
     */
    public const ALLOWED_IMAGE_EXTENSIONS = [
        'apng',
        'avif',
        'gif',
        'jpg',
        'jpeg',
        'jfif',
        'pjpeg',
        'pjp',
        'png',
        'svg',
        'webp',
        // 'bmp', @see MDN says these should be avoided
        // 'ico', @see MDN says these should be avoided
        // 'cur', @see MDN says these should be avoided
        // 'tif', @see MDN says these should be avoided
        // 'tiff', @see MDN says these should be avoided
    ];

    /**
     * @return isys_application|null
     */
    final public static function instance()
    {
        if (!self::$m_instance) {
            self::$m_instance = new self();
        }

        return self::$m_instance;
    }

    /**
     * "The Run Loop"
     *
     * @param isys_request_controller $p_req
     *
     * @throws Exception
     */
    final public static function run(isys_request_controller $p_req)
    {
        /**
         * Parse routes
         */
        if (!$p_req->parse()) {
            // If request controller parsing fails, this means we're not using a path URI right now
            // So, fall back to the "old" request handling

            // If no module has been selected, select the CMDB.
            if (isset($_GET[C__GET__MODULE_ID]) && trim($_GET[C__GET__MODULE_ID]) !== '') {
                $l_mod_id = $_GET[C__GET__MODULE_ID];
            } else {
                $l_mod_id = defined_or_default('C__MODULE__CMDB');
            }

            // @see ID-9711 Go sure to include the init.php to be able to work with some of the routes.
            // @see ID-11073 Use 'include_once' instead of 'include' to prevent duplicated signals.
            include_once BASE_DIR . '/src/classes/modules/cmdb/init.php';

            // Boot load the legacy module
            \idoit\Legacy\ModuleLoader::factory(self::instance()->container)
                ->boot($l_mod_id, isys_register::factory('request'));
        }
    }

    /**
     * 404 handler
     *
     * @param isys_register $p_request
     */
    public static function error404(isys_register $p_request)
    {
        isys_application::instance()->container->get('notify')->error('Error 404: Path not found.');
    }

    /**
     * Set custom warnings handler
     */
    public function overrideErrorHandler()
    {
        // Set custom warnings handler and deactivate assertions.
        set_error_handler([
            'isys_core',
            'warning_handler'
        ], E_WARNING & E_USER_WARNING);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        if (property_exists($this, $id)) {
            return $this->$id;
        }
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        //@todo: must be thowed Exception for getting non existed parameter
        return null;
    }

    public function __set($id, $value)
    {
        if (!property_exists($this, $id)) {
            //@todo: must be thowed Exception for setting non existed parameter
            $this->container->set($id, $value);
        } else {
            $this->$id = $value;
        }
    }

    /**
     * The beginning of a structured bootstrapping
     *
     * @return  $this
     * @throws  Exception
     */
    final public function bootstrap()
    {
        try {
            global $g_comp_database_system, $g_modman, $g_page_limit;
            $useCache = defined('WEB_CONTEXT') && WEB_CONTEXT;

            // Initialize directories - this needs to be done, BEFORE the settings, template, and constant components are initialized, since they all use the temp directory.
            $this->init_config_directories();

            // Load .env file and populate $_ENV
            if (file_exists($this->app_path . '/src/.env')) {
                $dotenv = new Dotenv();
                $dotenv->loadEnv($this->app_path . '/src/.env');
            }

            isys_component_session::instance()->start_session();

            $useCache &= isset($_SESSION["user_mandator"]);

            $userMandatorId = (isset($_SESSION["user_mandator"]) ? $_SESSION["user_mandator"] : 'none');

            $diCacheFile = $this->app_path . '/temp/di_container_cache_mandator_' . $userMandatorId . '.php';
            $diCache = new ConfigCache($diCacheFile, true); //debug=true says that cache will be always check changing time for every component

            if ($useCache && $diCache->isFresh()) {
                require_once($diCacheFile);
                // @phpstan-ignore-next-line
                $this->container = new \idoit\Component\ContainerCompiled();
                $this->container->set('application', $this);
            } else {
                $this->container = new ContainerBuilder();
                $this->container->set('application', $this);
                $loader = new YamlFileLoader($this->container, new FileLocator($this->app_path));
                $loader->load($this->app_path . '/src/di_services.yml');

                $this->container->setParameter('app.app_path', $this->app_path);
                $this->container->setParameter('app.www_path', $this->www_path);
                $this->container->setParameter('log.path', "{$this->app_path}/log/exception.log");
                $this->container->addCompilerPass(new RegisterListenersPass());

                foreach ($_ENV ?? [] as $key => $value) {
                    $this->container->setParameter("{$key}", (string)$value);
                }
            }
            $g_comp_database_system = $this->container->get('database_system');
            $session = $this->container->get('session');
            global $g_db_system;
            if (is_array($g_db_system) && isset($g_db_system['type'],
                $g_db_system['host'],
                $g_db_system['port'],
                $g_db_system['user'],
                $g_db_system['pass'],
                $g_db_system['name'])) {
                // initialize the system DB
                $systemDb = isys_component_database::factory(
                    $g_db_system['type'],
                    $g_db_system['host'],
                    $g_db_system['port'],
                    $g_db_system['user'],
                    $g_db_system['pass'],
                    $g_db_system['name']
                );
                // initialize the tenant with tenant DB
                $session->initMandatorSession($systemDb);
            }

            // Initialize directories which needs setting data
            $this->initConfigSettingDirectories();

            // Set default timezone.
            //@todo: check tenant settings after init_session
            date_default_timezone_set($this->container->get('settingsSystem')->get('system.timezone', 'Europe/Berlin'));

            // Initialize system constants.
            $this->init_constant_manager();

            // Load module manager.
            $g_modman = $this->container->get('moduleManager');

            // Preserve backward compatibility
            \idoit\Legacy\BackwardCompatibility::factory($this->container)
                ->preserve();

            // Initialize session.
            $this->init_session_data();

            // Initialize some config variables.
            $this->init_config_variables();
            header('i-doit-Authorized: ' . $this->container->get('session')->is_logged_in());

            // @see  ID-2855  Set some TCPDF settings for better performance
            define('K_TCPDF_EXTERNAL_CONFIG', true);

            $this->init_tcpdf_constants();

            // Obtain page limit from the settings. This can only work after initializing the session.
            $g_page_limit = isys_usersettings::get('gui.objectlist.rows-per-page', 50);

            //check if modules cache is actual
            $extendedModulesClasses = [];

            // Reload only if already loaded - otherwise init.php's will not be loaded in isys_component_session::post_init_session
            if ($g_modman->isLoaded()) {
                $g_modman->module_loader(true);
            }

            foreach ($g_modman->modules() as $module) {
                /** @var isys_module_register $module */
                if ($module->get_object() instanceof ExtensionProviderInterface) {
                    $extendedModulesClasses[] = get_class($module->get_object());
                }
            }

            // @see ID-9461 Assign the session to the template, when both services exist.
            if ($this->container->has('template') && $this->container->has('session')) {
                $this->container->get('template')->assign('session', $this->container->get('session'));
            }

            if ($useCache && $this->container->isCompiled() && (!$this->container->hasParameter('modules') || !($this->container->getParameter('modules') === $extendedModulesClasses))) {
                unlink($diCacheFile);
                //@todo: here could be recursive call of itself for cleaning cache right now: return $this->bootstrap();
            }

            if (!$this->container->isCompiled()) {
                $this->container->setParameter('modules', $extendedModulesClasses);

                foreach ($this->container->get('moduleManager')->modules() as $module) {
                    /** @var isys_module_register $module */
                    if ($module->get_object() instanceof ExtensionProviderInterface) {
                        /** @var \Symfony\Component\DependencyInjection\Extension\ExtensionInterface $moduleExtension */
                        $moduleExtension = $module->get_object()
                            ->getContainerExtension();
                        $moduleExtension->load([], $this->container);
                    }
                }

                // @see ID-9383 Because we access the container during login, we need to go sure that it is compiled.
                $this->container->compile();

                if ($useCache) {
                    $dumper = new PhpDumper($this->container);
                    $diCache->write(
                        $dumper->dump(['namespace' => 'idoit\Component', 'class' => 'ContainerCompiled']),
                        $this->container->getResources()
                    );
                    chmod($diCache->getPath(), 0664);
                }
            }

            $this->container->get('database_system')->set_autocommit(true);
            $this->container->get('database')->set_autocommit(true);
        } catch (Exception $e) {
            throw $e;
        }

        if (isset($_SERVER['HTTP_X_I_DOIT_TENANT_ID']) && $_SERVER['HTTP_X_I_DOIT_TENANT_ID'] != $userMandatorId) {
            isys_notify::error('Changes were interrupted. You have logged in with a different tenant. Please, log in again!', ['sticky' => true]);
            die;
        }

        return $this;
    }

    /**
     * @param string $envVar
     * @param ?string $defaultValue
     *
     * @return string|null
     */
    public function getEnv(string $envVar, ?string $defaultValue = null): ?string
    {
        $result = getenv($envVar);

        if (is_string($result)) {
            return $result;
        }

        return $_ENV[$envVar] ?? $defaultValue;
    }

    /**
     * Set application's language
     *
     * @param string $language
     *
     * @return isys_application
     */
    final public function language($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Destructor.
     *
     * @throws  Exception
     */
    final public function __destruct()
    {
        if ($this->container->get('signals', ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            $this->container->get('signals')->emit('system.shutdown');
        }
    }

    /**
     * Initialize application's session
     *
     * @global $g_comp_database
     * @global $g_comp_template_language_manager
     * @global $g_mandator_info
     *
     * @throws Exception
     */
    private function init_session_data()
    {
        global $g_comp_database, $g_mandator_info, $g_comp_template_language_manager;

        $session = $this->container->get('session');

        $GLOBALS['g_comp_session'] = $session;
        //this inits basic mandator info in session service
        $g_mandator_info = $session->get_mandator_data();
        //this creates and inits user database
        $g_comp_database = $this->container->get('database');
        //this finally inits session data using database
        $session->get_session_data($session->get_session_id());

        /**
         * isys_tenantsettings are available from now on!
         */

        // Initialize template language manager if not already initialized by isys_component_session::post_init_session

        // Assign language manager from container to global for legacy calls
        $g_comp_template_language_manager = $this->container->get('language');

        // Leave global g_comp_template here for backward compatibility for legacy modules
        $GLOBALS['g_comp_template'] = $this->container->get('template');

        if ($g_mandator_info && is_object($this->container->get('database')) && $this->container->get('database')->is_connected()) {
            // Save Tenant Info.
            $this->tenant = new isys_tenant(
                $g_mandator_info['isys_mandator__title'],
                $g_mandator_info['isys_mandator__description'],
                $g_mandator_info['isys_mandator__id'],
                $g_mandator_info['isys_mandator__db_name'],
                $g_mandator_info['isys_mandator__dir_cache']
            );

            // ------------------------------------------------ OVERRIDE USER CONFIG ---
            isys_glob_override_user_settings();

            // Backward compatibility for older modules
            // @todo Should be removed in one of the next major versions
            $GLOBALS['g_active_modreq'] = $GLOBALS['g_modreq'] = isys_module_request::get_instance();
            // ---------------------------------------------------------------------------------------

            // Initialize module manager.
            $this->container->get('moduleManager')->init(isys_module_request::get_instance());
        } else {
            // Initialize Pro module, if existent. This case happens when there is no login.
            if (file_exists($this->app_path . '/src/classes/modules/pro/init.php')) {
                include_once($this->app_path . '/src/classes/modules/pro/init.php');
            }
        }
    }

    /**
     * Create and include system constants (temp/const_cache.inc.php)
     *
     * @global $g_dcs
     */
    private function init_constant_manager()
    {
        global $g_dcs;

        // Include Global constant cache.
        $g_dcs = isys_component_constant_manager::instance();
        $g_dcs->include_dcs();
    }

    /**
     * Initialize some config variables
     *
     * @global $g_config
     */
    private function init_config_variables()
    {
        global $g_config;

        $tenantSettings = $this->container->get('settingsTenant');

        // Sysid prefix for isys_obj__sysid.
        define("C__CMDB__SYSID__PREFIX", $tenantSettings->get('cmdb.sysid.prefix', 'SYSID_'));

        // Maximum  amount of objects which are loaded into the tree of the object browser, the browser will not load at all if limit is reached.
        define("C__TREE_MAX_OBJECTS", $tenantSettings->get('cmdb.object-browser.max-objects', 1500)); // Numeric value

        $g_config["wiki_url"] = $tenantSettings->get('gui.wiki-url', '');
        $g_config["wysiwyg"] = $tenantSettings->get('gui.wysiwyg', '1');
        $g_config["use_auth"] = $tenantSettings->get('auth.active', '1');
        $g_config['devmode'] = $tenantSettings->get('system.devmode', false);

        // SYS-ID Readonly?
        define("C__SYSID__READONLY", (bool)$tenantSettings->get('cmdb.registry.sysid_readonly', 0));

        // Default date format (php-dateformat: http://php.net/date).
        define("C__INFOBOX__DATEFORMAT", $tenantSettings->get('gui.infobox.dateformat', 'd.m.Y H:i :'));

        // Enable locking of datasets (objects)?
        define("C__LOCK__DATASETS", (bool)$tenantSettings->get('cmdb.registry.lock_dataset', 0));

        // Timeout of locked datasets in seconds.
        define("C__LOCK__TIMEOUT", $tenantSettings->get('cmdb.registry.lock_timeout', 120));
        define("C__TEMPLATE__COLORS", $tenantSettings->get('cmdb.template.colors', 1));
        define("C__TEMPLATE__COLOR_VALUE", isys_helper_color::unifyHexColor($tenantSettings->get('cmdb.template.color_value', '#cc0000')));
        define("C__TEMPLATE__STATUS", $tenantSettings->get('cmdb.template.status', 0));
        // define("C__TEMPLATE__SHOW_ASSIGNMENTS", $tenantSettings->get('cmdb.template.show_assignments', 1));
    }

    /**
     * Set some TCPDF constants to increase performance.
     *
     * @see     ID-2855
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    private function init_tcpdf_constants()
    {
        $definedConstants = [
            // Define the default TCPDF font directory. This is necessary, because we copy all TCPDF fonts in our own "<i-doit>/upload/fonts" dir.
            'K_PATH_FONTS'                  => rtrim($this->app_path, '/') . '/upload/fonts/',
            // Generic name for a blank image.
            'K_BLANK_IMAGE'                 => '_blank.png',
            // Page format.
            'PDF_PAGE_FORMAT'               => 'A4',
            // Page orientation (P=portrait, L=landscape).
            'PDF_PAGE_ORIENTATION'          => 'P',
            // Document creator.
            'PDF_CREATOR'                   => 'TCPDF',
            // Document author.
            'PDF_AUTHOR'                    => 'i-doit',
            // Header title.
            'PDF_HEADER_TITLE'              => 'i-doit PDF Dokument',
            // Header description string.
            'PDF_HEADER_STRING'             => "For more information, visit www.i-doit.com",
            // Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
            'PDF_UNIT'                      => 'mm',
            // Header margin.
            'PDF_MARGIN_HEADER'             => 5,
            // Footer margin.
            'PDF_MARGIN_FOOTER'             => 10,
            // Top margin.
            'PDF_MARGIN_TOP'                => 27,
            // Bottom margin.
            'PDF_MARGIN_BOTTOM'             => 25,
            // Left margin.
            'PDF_MARGIN_LEFT'               => 15,
            // Right margin.
            'PDF_MARGIN_RIGHT'              => 15,
            // Default main font name.
            'PDF_FONT_NAME_MAIN'            => 'helvetica',
            // Default main font size.
            'PDF_FONT_SIZE_MAIN'            => 10,
            // Default data font name.
            'PDF_FONT_NAME_DATA'            => 'helvetica',
            // Default data font size.
            'PDF_FONT_SIZE_DATA'            => 8,
            // Default monospaced font name.
            'PDF_FONT_MONOSPACED'           => 'courier',
            // Ratio used to adjust the conversion of pixels to user units.
            'PDF_IMAGE_SCALE_RATIO'         => 1.25,
            // Magnification factor for titles.
            'HEAD_MAGNIFICATION'            => 1.1,
            // Height of cell respect font height.
            'K_CELL_HEIGHT_RATIO'           => 1.25,
            // Title magnification respect main font size.
            'K_TITLE_MAGNIFICATION'         => 1.3,
            // Reduction factor for small font.
            'K_SMALL_RATIO'                 => 2 / 3,
            // Set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language.
            'K_THAI_TOPCHARS'               => false,
            // If true allows to call TCPDF methods using HTML syntax
            // IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
            'K_TCPDF_CALLS_IN_HTML'         => false,
            // If true and PHP version is greater than 5, then the Error() method throw new exception instead of terminating the execution.
            'K_TCPDF_THROW_EXCEPTION_ERROR' => false,
            // Default timezone for datetime functions
            'K_TIMEZONE'                    => 'UTC'
        ];

        foreach ($definedConstants as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }

    /**
     * Small function to load directories which use isys_tenantsettings or isys_settings.
     * Should only be called AFTER initializing the settings.
     */
    private function initConfigSettingDirectories()
    {
        global $g_dirs;

        $g_dirs["fileman"] = [
            "target_dir" => $this->container->get('settingsSystem')->get('system.dir.file-upload', $this->app_path . '/upload/files/'),
            "temp_dir"   => $g_dirs['temp'],
            "image_dir"  => $this->container->get('settingsSystem')->get('system.dir.image-upload', $this->app_path . '/upload/images/'),
            "font_dir"   => $this->app_path . '/upload/fonts/',
        ];
    }

    /**
     * Returns the path of the object images.
     *
     * @return string
     */
    public function getImageDir(): string
    {
        $appPath = rtrim($this->app_path, '/');

        return "{$appPath}/upload/images/{$this->tenant->id}/object-images/";
    }

    /**
     * Return uploaded image path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getUploadImagePath(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        return $this->getOrCreateUploadImageDir($fileName) . $fileName;
    }

    /**
     * Return uploaded file path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getUploadFilePath(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        global $g_dirs;

        return $this->getOrCreateUploadFileDir($fileName) . $fileName;
    }

    /**
     * Return uploaded file's direcory path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getOrCreateUploadImageDir(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        $directoryName = $this->getImageDir() . substr(md5($fileName), 0, 2) . '/';

        if (!is_dir($directoryName) && !mkdir($directoryName, 0775, true)) {
            throw new isys_exception_filesystem("Can't create subdirectory '$directoryName' for uploading file. Please check rights");
        }

        return $directoryName;
    }

    /**
     * Return uploaded file's direcory path
     *
     * @param string $fileName
     *
     * @return string
     * @throws isys_exception_filesystem
     */
    public function getOrCreateUploadFileDir(string $fileName)
    {
        if (!$fileName) {
            throw new isys_exception_filesystem("Filename can't be empty");
        }

        global $g_dirs;

        // @see ID-9396 Unify the path for all OSs.
        $directoryName = rtrim(str_replace('\\', '/', $g_dirs['fileman']['target_dir']), '/') . '/' . substr(md5($fileName), 0, 2) . '/';

        if (!is_dir($directoryName) && !mkdir($directoryName, 0775, true)) {
            throw new isys_exception_filesystem("Can't create subdirectory '$directoryName' for uploading file. Please check rights");
        }

        return $directoryName;
    }

    /**
     * Small method for specifically configuring the directories.
     */
    private function init_config_directories()
    {
        global $g_dirs;

        $appPath = rtrim($this->app_path, '/');
        $wwwPath = rtrim($this->www_path, '/');

        /*
         * Directory configuration
         * -------------------------------------------------------------------------
         * Array of required global directory structure, the rest is read and set by the system registry.
         *
         * NOTE: You should NOT modify this!
         * FILE MANAGER SETTINGS
         *
         * Target directory must be absolute and tailed by '/'.
         * Furthermore, your apache-user (f.e. 'www-data') needs full access rights (RWX) to the directory.
         */
        if (!isset($g_dirs['temp'])) {
            $g_dirs['temp'] = $appPath . '/temp/';
        }

        $g_dirs['class'] = "{$appPath}/src/classes/";
        $g_dirs['handler'] = "{$appPath}/src/handler/";
        $g_dirs['css_abs'] = "{$appPath}/src/themes/default/css/";
        $g_dirs['js_abs'] = "{$appPath}/src/tools/js/";
        $g_dirs['smarty'] = "{$appPath}/src/themes/default/smarty/";
        $g_dirs['utils'] = "{$appPath}/src/utils/";
        $g_dirs['import'] = "{$appPath}/src/classes/import/";
        $g_dirs['log'] = "{$appPath}/log/";
        $g_dirs['images'] = "{$wwwPath}/images/";
        $g_dirs['theme'] = "{$wwwPath}/src/themes/default/";
        $g_dirs['tools'] = "{$wwwPath}/src/tools/";
    }

    /**
     * Private clone method to ensure singleton.
     */
    private function __clone()
    {
        ;
    }

    /**
     * Private constructor
     *
     * @global $g_absdir
     * @global $g_product_info
     */
    private function __construct()
    {
        global $g_absdir, $g_product_info, $g_config;

        $this->overrideErrorHandler();

        $this->container = new ContainerBuilder();
        $this->container->set('application', $this);

        $this->app_path = $g_absdir;
        $this->www_path = $g_config['www_dir'];

        if (!isset($g_product_info) || !is_array($g_product_info)) {
            include_once($this->app_path . '/src/version.inc.php');
        }

        $this->info = ($g_product_info = new isys_array($g_product_info ?: []));
    }

    /**
     * Find out if this is a 'pro' installation.
     *
     * @return bool
     */
    public static function isPro(): bool
    {
        return defined('C__MODULE__PRO') && C__MODULE__PRO && class_exists('isys_module_pro');
    }

    /**
     * @return void
     * @throws isys_exception_filesystem
     */
    public function renewCache(): void
    {
        global $g_dcs;

        // @see ID-11119 Always renew properties and remove duplicated category assignments.
        $tenantId = $this->tenant->id;
        $database = $this->container->get('database');
        $systemDatabase = $this->container->get('database_system');

        // Mark this tenant that the properties have to be renewed
        $sql = "REPLACE INTO isys_settings SET
            isys_settings__key = 'cmdb.renew-properties',
            isys_settings__value = 1,
            isys_settings__isys_mandator__id = '{$tenantId}';";
        $systemDatabase->query($sql);

        // @see ID-6684 Always remove duplicated category assignments after add-on installation (noticed via CMK2-16).
        (new CiTypeCategoryAssigner($database))->deleteDuplicateAssignments();

        (new Filesystem())->remove(isys_glob_get_temp_dir());
        (new Filesystem())->mkdir(isys_glob_get_temp_dir(), 0755);

        // Re-Create constant cache.
        $g_dcs = isys_component_constant_manager::instance()
            ->create_dcs_cache();

        isys_application::instance()->bootstrap();
    }
}
