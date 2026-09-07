<?php

use Carbon\Carbon;
use idoit\AddOn\AdministratableInterface;
use idoit\AddOn\AuthableInterface;
use idoit\AddOn\RoutingAwareInterface;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Logger;
use idoit\Model\QuickInfo;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Search\Index\Data\CategoryCollector;
use idoit\Module\License\Entity\License;
use idoit\Module\License\LicenseService;
use idoit\Module\License\LicenseServiceFactory;
use idoit\Module\Report\Refresher\ReportsRefresher;
use idoit\Module\Search\Index\Engine\Mysql;
use idoit\Module\Search\Index\Manager;
use idoit\Module\System\Cleanup\DeleteDuplicateSingleValueEntries;
use idoit\Module\System\Cleanup\DeleteExportedCheckMkTags;
use idoit\Module\System\Cleanup\MigrateDbObjectsToCategory;
use idoit\Module\System\Cleanup\MigrateOldObjectTypeIcons;
use idoit\Module\System\Cleanup\ReformatColorValues;
use idoit\Module\System\Cleanup\RemoveOrphanedCustomCategoryData;
use idoit\Module\System\Cleanup\TenantDatabaseEncryption;
use idoit\Module\System\Model\RelationType;
use idoit\Module\System\SettingPage\CustomCounter\CustomCounter;
use idoit\Module\System\SettingPage\CustomizeObjectBrowser\CustomizeObjectBrowser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\PhpFileLoader;

/**
 * i-doit
 *
 * System settings
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_system extends isys_module implements AuthableInterface, RoutingAwareInterface
{
    // Defines whether this module will be displayed in the extras-menu.
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines, if this module shall be displayed in the systme-menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var boolean
     */
    protected static $m_licenced = true;

    /**
     * Stores "additional options"...
     * @var array
     */
    private array $m_additional_options = [];

    /**
     * The current module request instance.
     * @var isys_module_request
     */
    private $m_userrequest;

    /**
     * @var bool
     */
    private bool $isPro = false;

    /**
     * @var string
     */
    private string $imageDir = '';

    /** @var isys_component_template_language_manager */
    protected $language;

    private isys_cmdb_dao $dao;

    /**
     * Module constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = isys_application::instance()->container->get('language');
        $this->dao = isys_application::instance()->container->get('cmdb_dao');
    }

    /**
     * @param $p_mandator_id
     * @param $mandatorDatabase
     *
     * @return void
     * @throws Exception
     */
    public static function handle_licence_installation($p_mandator_id = null, $mandatorDatabase = null)
    {
        $language = isys_application::instance()->container->get('language');
        $template = isys_application::instance()->container->get('template');
        $session  = isys_application::instance()->container->get('session');

        if (!class_exists('isys_module_licence')) {
            return;
        }

        // Check only in i-doit not in admin-center
        if (defined('C__MODULE__SYSTEM') && class_exists('isys_auth_system_licence')) {
            isys_auth_system_licence::instance()
                ->installation(isys_auth::EXECUTE);
        }

        $l_mandator_id = $p_mandator_id;

        if (is_null($p_mandator_id)) {
            if (is_object($session)) {
                $l_mandator_id = $session->get_mandator_id();
            }
        }

        if (is_object($mandatorDatabase)) {
            $tenant_database = $mandatorDatabase->get_db_name();
        } elseif ($l_mandator_id > 0) {
            $mandatorDatabase = isys_application::instance()->container->get('database');
            $tenant_database = $mandatorDatabase->get_db_name();
        } else {
            $tenant_database = '';
        }

        $template
            ->assign('tenant_database', $tenant_database)
            ->assign('encType', 'multipart/form-data');

        try {
            if (is_array($_POST) && count($_POST) > 0) {
                if (!empty($_FILES['licence_file']['tmp_name'])) {
                    if (class_exists('isys_module_licence')) {
                        global $g_license_token;

                        $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token);

                        // Validate uploaded licence.
                        $l_licence = new isys_module_licence();

                        // We try to catch a certain exception.
                        try {
                            $l_lic_parse = $licenseService->parseLicenseFile($_FILES["licence_file"]["tmp_name"]);
                        } catch (\idoit\Module\License\Exception\LegacyLicenseExpiredException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXPIRED'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseInvalidKeyException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseInvalidTypeException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        } catch (\idoit\Module\License\Exception\LegacyLicenseParseException $e) {
                            $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                        }

                        if (is_array($l_lic_parse)) {
                            $template->assign("licence_info", $l_lic_parse);

                            try {
                                $licenseService->installLegacyLicense($l_lic_parse);

                                $template->assign("note", $language->get('LC__LICENCE__INSTALL__SUCCESSFULL'));

                                // Include the admin-center functions.
                                include_once isys_application::instance()->app_path . '/admin/src/functions.inc.php';

                                // @see ID-8908 Remove token, once a license file was successfully installed.
                                saveLicenseToken('');
                                $licenseService->deleteLicenses([LicenseService::C__LICENCE_TYPE__NEW__IDOIT, LicenseService::C__LICENCE_TYPE__NEW__ADDON]);
                            } catch (\idoit\Module\License\Exception\LegacyLicenseInstallException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL'));
                            } catch (\idoit\Module\License\Exception\LegacyLicenseExistingException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXISTS'));
                            } catch (\idoit\Module\License\Exception\LegacyLicenseExpiredException $e) {
                                $template->assign("error", $language->get('LC__LICENCE__INSTALL__FAIL_EXPIRED'));
                            }
                        }
                    }
                } else {
                    // No licence file uploaded.
                    $template->assign("error", $language->get('LC__LICENCE__NO_UPLOAD'));
                }
            }
        } catch (isys_exception_licence $e) {
            $template
                ->assign("error", $e->getMessage())
                ->assign("errorcode", $e->get_errorcode());
        }

        $template->include_template('contentbottomcontent', 'modules/system/licence_installation.tpl');
    }

    /**
     * Method for generating a string.
     *
     * @param string $what
     *
     * @return string
     */
    private static function generate_link(string $what): string
    {
        return isys_helper_link::create_url([
            C__GET__MODULE_ID     => C__MODULE__SYSTEM,
            C__GET__SETTINGS_PAGE => $what
        ]);
    }

    /**
     * Initializes the CMDB module.
     *
     * @param   isys_module_request &$p_req
     *
     * @return  Boolean
     */
    public function init(isys_module_request $p_req)
    {
        $this->isPro = isys_application::isPro();
        $this->imageDir = isys_application::instance()->www_path . 'images/axialis/';

        $this->m_additional_options = [
            'tenant-settings' => [
                'func' => 'processTenantSettings',
                'text' => $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]),
                'icon' => ''
            ],
            'system-overview' => [
                'func' => 'processSystemOverview',
                'text' => $this->language->get('LC__MODULE__SYSTEM__OVERVIEW', [isys_application::instance()->tenant->name]),
                'icon' => ''
            ],
            'cache'                => [
                'func' => 'processCacheDatabase',
                'text' => $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__REPAIR_AND_CLEANUP'),
                'icon' => "{$this->imageDir}basic/cleanup.svg"
            ],
            'object-lock'      => [
                'func' => 'processObjectLock',
                'text' => $this->language->get('LC__LOCKED__OBJECTS'),
                'icon' => "{$this->imageDir}basic/gear.svg"
            ],
            'relation_types'       => [
                'func' => 'handle_relation_types',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES'),
                'icon' => "{$this->imageDir}database/server-database.svg"
            ],
            'roles_administration' => [
                'func' => 'handle_roles_administration',
                'text' => $this->language->get('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION'),
                'icon' => "{$this->imageDir}user-management/user-group.svg"
            ],
            'object_matching'      => [
                'func' => 'handle_object_matching',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__OBJECT_MATCHING'),
                'icon' => "{$this->imageDir}documents-folders/file-copy-1.svg"
            ],
            'hinventory'           => [
                'func' => 'handle_hinventory',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY__CONFIGURATION'),
                'icon' => ''
            ],
            'customizeObjectBrowser'           => [
                'func' => 'handle_customizeObjectBrowser',
                'text' => $this->language->get('LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER'),
                'icon' => ''
            ],
            'customCounter'           => [
                'func' => 'handle_customCounter',
                'text' => $this->language->get('LC__UNIVERSAL__CUSTOM_COUNTER'),
                'icon' => ''
            ],
            'smtp-configuration'           => [
                'func' => 'processSmtpConfiguration',
                'text' => $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_CONFIGURATION'),
                'icon' => ''
            ],
        ];

        if (is_object($p_req)) {
            $this->m_userrequest = &$p_req;

            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws isys_exception_auth
     */
    public function processObjectLock()
    {
        if (class_exists('isys_auth_system')) {
            isys_auth_system::instance()->check(isys_auth::VIEW, 'GLOBALSETTINGS/SYSTEMSETTING');
        }

        $lockTable = '';
        $daoLock = new isys_component_dao_lock(isys_application::instance()->container->get('database'));

        $navbar = isys_component_template_navbar::getInstance();
        $template = isys_application::instance()->container->get('template');
        $editRight = class_exists('isys_auth_system')
            ? isys_auth_system::instance()->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/SYSTEMSETTING')
            : true;

        try {
            if (isset($_GET['delete_locks']) && isset($_POST['id']) && is_array($_POST['id']) && count($_POST['id'])) {
                foreach ($_POST['id'] as $l_lockID) {
                    $daoLock->delete($l_lockID);
                }
            }

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                $navbar
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            } elseif ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                isys_tenantsettings::set('cmdb.registry.lock_dataset', $_POST['lock']);
                isys_tenantsettings::set('cmdb.registry.lock_timeout', $_POST['lock_timeout']);

                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            }
        } catch (isys_exception $e) {
            isys_notify::error($e->getMessage(), ['sticky' => true]);
        }

        $daoLock->delete_expired_locks();

        $l_locks = $daoLock->get_lock_information(null, null);

        if ($l_locks->num_rows() > 0) {
            $l_objList = new isys_component_list(null, $l_locks);

            $l_objList->set_row_modifier($this, 'modify_lock_row')
                ->setIdField('id')
                ->config([
                    'isys_cats_person_list__title' => 'LC__CMDB__LOGBOOK__USER',
                    'isys_user_session__ip'        => 'LC__CATP__IP__ADDRESS',
                    'isys_obj_type__title'         => 'LC__CMDB__OBJTYPE',
                    'isys_obj__title'              => 'LC__UNIVERSAL__TITLE',
                    'table_data'                   => 'LC__UNIVERSAL__TOPIC',
                    'isys_lock__datetime'          => 'LC__LOCKED__AT',
                    'expires'                      => 'LC__EXPIRES_IN',
                ], null, "[{isys_lock__id}]");

            $l_objList->createTempTable();
            $lockTable = $l_objList->getTempTableHtml();
        }

        if (isys_glob_get_param(C__GET__NAVMODE) != C__NAVMODE__EDIT) {
            $navbar
                ->set_active($editRight, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        }

        $rules = [
            'lock' => [
                'p_arData' => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => isys_tenantsettings::get('cmdb.registry.lock_dataset', 0),
                'p_strClass' => 'input-small',
                'p_bDbFieldNN' => true
            ],
            'lock_timeout'  => [
                'p_strValue' => isys_tenantsettings::get('cmdb.registry.lock_timeout', 120),
                'p_strClass' => 'input-small'
            ]
        ];

        $template
            ->assign('g_list', $lockTable)
            ->assign('content_title', $this->language->get('LC__LOCKED__OBJECTS'))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
            ->include_template('contentbottomcontent', __DIR__ . '/templates/object-lock.tpl');
    }

    public function processSmtpConfiguration()
    {
        if (!FeatureManager::isFeatureActive('smtp-settings')) {
            header('Location: ?');
            die;
        }

        // Auth?
        $allowedToEdit = self::getAuth()->is_allowed_to(isys_auth::EDIT, 'SMTP_CONFIGURATION');
        $tenantSettings = isys_application::instance()->container->get('settingsTenant');

        try {
            $l_navbar = isys_component_template_navbar::getInstance();

            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                $l_navbar
                    ->set_active($allowedToEdit, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
            } elseif ($_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                self::getAuth()->check(isys_auth::EDIT, 'SMTP_CONFIGURATION');

                $timeout = (int)$_POST['C__SMTP_CONFIGURATION__SMTP_TIMEOUT'];
                $port = (int)$_POST['C__SMTP_CONFIGURATION__SMTP_PORT'];

                // @see ID-9721 Notify the user that timeouts < 300 are not recommended
                if ($timeout < 300) {
                    if ($timeout < 30) {
                        $timeout = 30;
                        isys_notify::warning($this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_TIMEOUT_LIMIT'), ['sticky' => true]);
                    } else {
                        isys_notify::warning($this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_TIMEOUT_WARNING'), ['sticky' => true]);
                    }
                }

                if ($port === 0 && trim($_POST['C__SMTP_CONFIGURATION__SMTP_PORT']) === '') {
                    // Set the default port, if none was passed.
                    $port = 25;
                }

                $tenantSettings
                    ->set('system.email.smtp-host', $_POST['C__SMTP_CONFIGURATION__SMTP_HOST'])
                    ->set('system.email.port', $port)
                    ->set('system.email.username', $_POST['C__SMTP_CONFIGURATION__SMTP_USERNAME'])
                    ->set('system.email.smtp-auto-tls', (int)$_POST['C__SMTP_CONFIGURATION__SMTP_TLS'])
                    ->set('system.email.from', $_POST['C__SMTP_CONFIGURATION__SMTP_FROM'])
                    ->set('system.email.name', $_POST['C__SMTP_CONFIGURATION__SMTP_NAME'])
                    ->set('system.email.connection-timeout', $timeout)
                    ->set('system.email.smtpdebug', (int)$_POST['C__SMTP_CONFIGURATION__SMTP_DEBUG'])
                    ->set('system.email.subject-prefix', $_POST['C__SMTP_CONFIGURATION__SMTP_SUBJECT_PREFIX'])
                    ->set('email.template.maintenance', $_POST['C__SMTP_CONFIGURATION__MAINTENANCE_TEMPLATE']);

                if ($_POST['C__SMTP_CONFIGURATION__SMTP_PASSWORD__action'] === isys_smarty_plugin_f_password::PASSWORD_CHANGED) {
                    $tenantSettings->set('system.email.password', $_POST['C__SMTP_CONFIGURATION__SMTP_PASSWORD']);
                }

                if ($_POST['C__SMTP_CONFIGURATION__SMTP_PASSWORD__action'] === isys_smarty_plugin_f_password::PASSWORD_SET_EMPTY) {
                    $tenantSettings->set('system.email.password', '');
                }

                isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));

                $l_navbar
                    ->set_active($allowedToEdit, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
            } else {
                $l_navbar
                    ->set_active($allowedToEdit, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
            }
        } catch (isys_exception $e) {
            isys_notify::error($e->getMessage());
        }

        $rules = [
            'C__SMTP_CONFIGURATION__SMTP_HOST'            => [
                'p_strValue'       => $tenantSettings->get('system.email.smtp-host'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'mail.i-doit.com'
            ],
            'C__SMTP_CONFIGURATION__SMTP_PORT'            => [
                'p_strValue'       => $tenantSettings->get('system.email.port'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 25
            ],
            'C__SMTP_CONFIGURATION__SMTP_USERNAME'        => [
                'p_strValue'       => $tenantSettings->get('system.email.username'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'username'
            ],
            'C__SMTP_CONFIGURATION__SMTP_PASSWORD'        => [
                'p_strValue'       => $tenantSettings->get('system.email.password'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'password'
            ],
            'C__SMTP_CONFIGURATION__SMTP_TLS'             => [
                'p_strSelectedID' => $tenantSettings->get('system.email.smtp-auto-tls'),
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ],
            'C__SMTP_CONFIGURATION__SMTP_FROM'            => [
                'p_strValue'       => $tenantSettings->get('system.email.from'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'i-doit@i-doit.com'
            ],
            'C__SMTP_CONFIGURATION__SMTP_NAME'            => [
                'p_strValue'       => $tenantSettings->get('system.email.name'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'i-doit'
            ],
            'C__SMTP_CONFIGURATION__SMTP_TIMEOUT'         => [
                'p_strValue'       => $tenantSettings->get('system.email.connection-timeout'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 30
            ],
            'C__SMTP_CONFIGURATION__SMTP_DEBUG'           => [
                'p_strSelectedID' => $tenantSettings->get('system.email.smtpdebug'),
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ],
            'C__SMTP_CONFIGURATION__SMTP_SUBJECT_PREFIX'  => [
                'p_strValue'       => $tenantSettings->get('system.email.subject-prefix'),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => '[i-doit]'
            ],
            'C__SMTP_CONFIGURATION__MAINTENANCE_TEMPLATE' => [
                'p_strValue' => $tenantSettings->get('email.template.maintenance'),
                'keepHtml'   => true
            ]
        ];

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_CONFIGURATION'))
            ->include_template('contentbottomcontent', isys_module_system::getPath() . '/templates/smtp-configuration.tpl')
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }

    /**
     * Method for handling the system overview.
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function processSystemOverview()
    {
        global $g_absdir, $g_product_info, $g_enable_gui_update;

        isys_auth_system::instance()->check(isys_auth::VIEW, 'SYSTEMTOOLS/SYSTEMOVERVIEW');
        $isSupervisor = isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/SYSTEMOVERVIEW');

        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');
        $systemDatabase = isys_application::instance()->container->get('database_system');
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $session = isys_application::instance()->container->get('session');

        // @see ID-9404
        $allowUpdate = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/IDOITUPDATE')
            && (!isset($g_enable_gui_update) || (isset($g_enable_gui_update) && $g_enable_gui_update))
            && FeatureManager::isFeatureActive('update-gui');

        if ($allowUpdate && $isSupervisor) {
            isys_component_template_navbar::getInstance()
                ->append_button('LC__MODULE__SYSTEM__TREE__IDOIT_UPDATE', 'identifier', [
                    'active'              => true,
                    'visible'             => true,
                    'tooltip'             => 'LC__MODULE__SYSTEM__TREE__IDOIT_UPDATE_DESCRIPTION',
                    'icon'                => "{$this->imageDir}web-email/internet-download.svg",
                    'icon_inactive'       => "{$this->imageDir}web-email/internet-download.svg",
                    'url'                 => '?load=update',
                    'content'             => 'Content?',
                ]);
        }

        // Find the operating system.
        $template
            ->assign('os', php_uname('s') . ' (' . PHP_OS . ')')
            ->assign('isWindows', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        $phpVersionStatus = 'ok';

        $phpVersion = getVersion(phpversion());

        if (version_compare($phpVersion, PHP_VERSION_MAXIMUM, '>')) {
            // above maximum.
            $phpVersionStatus = 'unsupported';
        } elseif (version_compare($phpVersion, PHP_VERSION_MINIMUM, '<')) {
            // Below minimum.
            $phpVersionStatus = 'failed';
        } elseif (version_compare($phpVersion, PHP_VERSION_DEPRECATED_BELOW, '<')) {
            // Deprecated.
            $phpVersionStatus = 'deprecated';
        } elseif (version_compare($phpVersion, PHP_VERSION_MINIMUM_RECOMMENDED, '<')) {
            // Not recommended.
            $phpVersionStatus = 'not_recommended';
        }

        isys_component_template_navbar::getInstance()
            ->append_button('LC_UNIVERSAL__EXPAND_ALL', 'expand-all', [
                'icon'                => 'axialis/user-interface/angle-down-small.svg',
                'js_onclick'          => '',
                'navmode'             => 'expand-all'
            ])
            ->append_button('LC_UNIVERSAL__COLLAPSE_ALL', 'collapse-all', [
                'icon'                => 'axialis/user-interface/angle-up-small.svg',
                'js_onclick'          => '',
                'navmode'             => 'collapse-all'
            ]);

        $template
            ->assign("php_version", phpversion())
            ->assign("php_version_recommended", PHP_VERSION_MINIMUM_RECOMMENDED)
            ->assign("php_version_deprecated", PHP_VERSION_DEPRECATED_BELOW)
            ->assign("php_version_minimum", PHP_VERSION_MINIMUM)
            ->assign("idoit_version", $g_product_info)
            ->assign('php_version_status', $phpVersionStatus);

        /**
         * Database Version Check
         */
        try {
            // Get database version
            $result = $database->query('SELECT VERSION() AS v;');
            $row = $database->fetch_row_assoc($result);
            $rawDbVersion = $row['v'];

            $dbVersion = getVersion($rawDbVersion);

            // Detect MariaDB
            $isMariadb = stripos($rawDbVersion, 'maria') !== false;

            // Setting check related variables based on DBMS
            if ($isMariadb) {
                $dbTitle = 'MariaDB';
                $dbMinimumVersion = MARIADB_VERSION_MINIMUM;
                $dbMaximumVersion = MARIADB_VERSION_MAXIMUM;
                $dbRecommendedVersion = MARIADB_VERSION_MINIMUM_RECOMMENDED;
                $dbDeprecatedBelodVersion = MARIADB_VERSION_DEPRECATED_BELOW;
            } else {
                $dbTitle = 'MySQL';
                $dbMinimumVersion = MYSQL_VERSION_MINIMUM;
                $dbMaximumVersion = MYSQL_VERSION_MAXIMUM;
                $dbRecommendedVersion = MYSQL_VERSION_MINIMUM_RECOMMENDED;
                $dbDeprecatedBelodVersion = MYSQL_VERSION_DEPRECATED_BELOW;
            }

            $template
                ->assign('db_version', $dbVersion)
                ->assign('db_version_recommended', $dbRecommendedVersion)
                ->assign('db_title', $dbTitle);

            // Check version is supported
            if (!checkVersion($dbVersion, $dbMinimumVersion, $dbMaximumVersion)) {
                $template->assign('db_unsupported_version', true);
            } elseif (version_compare($dbVersion, $dbDeprecatedBelodVersion, '<')) {
                $template->assign('db_deprecated_version', $dbDeprecatedBelodVersion);
            }
        } catch (Exception $exception) {
            // Version information was not detectable
            $template->assign(
                "sql_version_error",
                'Please notice that i-doit was not able to determine a valid MySQL/MariaDB version information.
                     You can check your system to identify the problem or resume the installation process on your own risk.
                     Please have a look at the official system requirements in the <a href="https://kb.i-doit.com/en/installation/system-requirements.html">Knowledge Base</a>'
            );
        }

        if ($isSupervisor && file_exists($g_absdir . "/updates/classes/isys_update.class.php")) {
            $template->assign("updateCheckUrl", isys_application::instance()->container->get('route_generator')->generate('system.update-check'));
        }

        if ($this->isPro && class_exists('isys_module_licence')) {
            global $g_license_token;
            $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token);

            $objectCount = $licenseService->getTenants(true, [$session->get_mandator_id()])[0]['isys_mandator__license_objects'];
        } else {
            $dao = new isys_statistics_dao($database, new isys_cmdb_dao($database));
            $objectCount = $dao->count_objects();
        }

        // @see ID-7293 Calculate time difference between PHP and SQL.
        $mySQLTimestamp = strtotime($dao->retrieve('SELECT CURRENT_TIMESTAMP() AS t;')->get_row_value('t'));
        $phpTime = time();
        $timeDiffOut = [
            'php' => date('Y-m-d H:i:s', $phpTime),
            'mysql' => date('Y-m-d H:i:s', $mySQLTimestamp),
            'diff' => abs($phpTime - $mySQLTimestamp),
        ];

        // @see ID-9634 Assign template and variables, in case the script jumps out in the next lines (i-doit OPEN).
        $template
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]))
            ->assign('showLicenseData', false)
            ->assign('showSystemInfo', FeatureManager::isFeatureActive('system-settings'))
            ->assign('rights', $this->getDirectoryRights(isys_application::instance()))
            ->assign('tenant', $session->get_mandator_name())
            ->assign('objectCount', $objectCount)
            ->assign('php', $this->getPhpSettings())
            ->assign('mysql', $this->getDatabaseSettings($database))
            ->assign('db_size', $this->get_database_size())
            ->assign('systemArchitecture', strlen(decbin(-1)))
            ->assign('php_dependencies', $this->getPhpDependencies())
            ->assign('apache_dependencies', $this->getApacheDependencies())
            ->assign('timeDiffOut', $timeDiffOut)
            ->include_template('contentbottomcontent', __DIR__ . '/templates/system-overview.tpl');

        if (!class_exists('isys_module_licence')) {
            return;
        }

        $isAllowedToSeeLicense = false;

        if (class_exists('isys_auth_system_licence')) {
            $isAllowedToSeeLicense = isys_auth_system_licence::instance()->is_allowed_to(isys_auth::EXECUTE, 'licencesettings/overview');
        }

        // Get licence module.
        $l_licence = new isys_module_licence();

        global $g_license_token;

        $licenseService = LicenseServiceFactory::createDefaultLicenseService($systemDatabase, $g_license_token);

        $l_exceeding["objects"] = $this->language->get("LC__UNIVERSAL__NO");

        $earliestExpiringLicense = $licenseService->getEarliestExpiringLicense();

        $requestedAt = null;
        $expiresAt = null;
        $remaining = null;

        $now = Carbon::now();
        $licenseUnlimited = false;

        $locale = isys_application::instance()->container->get('locales');
        $dateFormat = 'Y-m-d 00:00:00';

        if ($earliestExpiringLicense instanceof License) {
            $requestedAt = $earliestExpiringLicense->getValidityFrom()->format($dateFormat);
            $expiresAt = $earliestExpiringLicense->getValidityTo()->format($dateFormat);

            $remaining = $earliestExpiringLicense->getValidityTo()->diff($now);
            $licenseUnlimited = $earliestExpiringLicense->isUnlimited();
        } elseif (isset($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE])) {
            if (
                $earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] == LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE ||
                $earliestExpiringLicense[LicenseService::LEGACY_LICENSE_TYPE] == LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING
            ) {
                $licenseUnlimited = true;

                $registrationDate = Carbon::createFromTimestamp($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);
                $requestedAt = $registrationDate->format($dateFormat);
                $expires = $registrationDate->copy()->modify("+99 years");
                $expiresAt = $expires->format($dateFormat);
                $remaining = $expires->diff($now);
            }

            if (isset($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME])) {
                $days = (int) round(abs((($earliestExpiringLicense[LicenseService::C__LICENCE__RUNTIME] / 60 / 60 / 24))));
                $registrationDate = Carbon::createFromTimestamp($earliestExpiringLicense[LicenseService::C__LICENCE__REG_DATE]);
                $requestedAt = $registrationDate->format($dateFormat);
                $expires = $registrationDate->copy()->modify("+{$days} days");
                $expiresAt = $expires->format($dateFormat);
                $remaining = $expires->diff($now);
            }
        }

        $expiresWithinSixMonths = Carbon::parse($expiresAt)
            ->between(
                $now,
                $now->copy()->addMonths(6)
            );

        $remainingTimePercentage = 0;

        if ($expiresWithinSixMonths) {
            $remainingTimePercentage = 100 / $now->diffInDays($now->copy()->addMonths(6)) * $now->diffInDays(Carbon::parse($expiresAt));
        }

        $stringTimeLimit = $this->language->get('LC__WIDGET__EVAL__TIME_LIMIT', [
            $locale->fmt_date($requestedAt),
            $locale->fmt_date($expiresAt),
            $remaining->y,
            $remaining->m,
            $remaining->d
        ]);

        if (!$licenseService->isTenantLicensed($session->get_mandator_id())) {
            $l_exceeding["objects"] = $this->language->get("LC__UNIVERSAL__YES");
            $l_exceeding["objects_class"] = "text-red text-bold";
            $template->assign("error", $this->language->get("LC__LICENCE__NO_LICENCE"));

            $stringTimeLimit = $this->language->get('LC__WIDGET__EVAL__TIME_LIMIT_EXCEEDED', [
                $locale->fmt_date($requestedAt),
                $locale->fmt_date($expiresAt),
                $remaining->y,
                $remaining->m,
                $remaining->d
            ]);
        }

        if ($licenseUnlimited) {
            $stringTimeLimit = $this->language->get('LC__WIDGET__EVAL__TIME_LIMIT_BUYERS');
        }

        $tenant = $licenseService->getTenants(true, [$session->get_mandator_id()]);

        $l_free_objects = (int) $tenant[0]['isys_mandator__license_objects'];

        // Statistics.
        $l_mod_stat = new isys_module_statistics();
        $l_mod_stat->init_statistics();
        $l_mod_stat_counts = $l_mod_stat->get_counts();
        $l_mod_stat_stats = $l_mod_stat->get_stats();

        if (is_numeric($l_free_objects)) {
            $l_counts = $l_mod_stat->get_counts();
            $l_mod_stat_counts["free_objects"] = $l_free_objects - $l_counts["objects"];
            if ($l_mod_stat_counts["free_objects"] < 0) {
                $l_mod_stat_counts["free_objects"] = 0;
            }
        } else {
            $l_mod_stat_counts["free_objects"] = $l_free_objects;
        }

        // @see ID-9634 Assign license variables.
        $template
            ->assign('showLicenseData', $isAllowedToSeeLicense)
            ->assign("stats", $l_mod_stat)
            ->assign("stat_counts", $l_mod_stat_counts)
            ->assign("stat_stats", $l_mod_stat_stats)
            ->assign("exceeding", $l_exceeding)
            ->assign("licensedAddOns", $licenseService->getLicensedAddOns())
            ->assign("stringTimeLimit", $stringTimeLimit)
            ->assign("expiresWithinSixMonths", $expiresWithinSixMonths)
            ->assign("remainingTimePercentage", $remainingTimePercentage)
            ->assign("licenseUnlimited", $licenseUnlimited);
    }

    private function getDirectoryRights(isys_application $app): array
    {
        if (!FeatureManager::isFeatureActive('system-settings')) {
            return [];
        }

        global $g_dirs;

        $baseDirectory = rtrim($app->app_path, '/');

        return [
            'i-doit directory'             => [
                'chk'  => is_writable($baseDirectory),
                'dir'  => $baseDirectory,
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'Source directory'             => [
                'chk'  => is_writable($baseDirectory . '/src'),
                'dir'  => $baseDirectory . '/src',
                'msg'  => 'WRITEABLE',
                'note' => 'Must be writeable for i-doit updates!'
            ],
            'Temp'                         => [
                'chk' => is_dir($baseDirectory . '/temp') ? is_writable($baseDirectory . '/temp') : mkdir($baseDirectory . '/temp', 0775, true),
                'dir' => $baseDirectory . '/temp',
                'msg' => 'WRITEABLE'
            ],
            'File upload'                  => [
                'chk' => is_dir($g_dirs['fileman']['target_dir'])
                    ? is_writable($g_dirs['fileman']['target_dir'])
                    : mkdir($g_dirs['fileman']['target_dir'], 0775, true),
                'dir' => $g_dirs['fileman']['target_dir'],
                'msg' => 'WRITEABLE'
            ],
            'Image upload'                 => [
                'chk' => is_dir($app->getImageDir())
                    ? is_writable($app->getImageDir())
                    : mkdir($app->getImageDir(), 0775, true),
                'dir' => $app->getImageDir(),
                'msg' => 'WRITEABLE'
            ],
            'Default theme template cache' => [
                'chk' => is_dir($baseDirectory . '/temp/smarty/compiled')
                    ? is_writable($baseDirectory . '/temp/smarty/compiled')
                    : mkdir($baseDirectory . '/temp/smarty/compiled', 0775, true),
                'dir' => $baseDirectory . '/temp/smarty/compiled',
                'msg' => 'WRITEABLE'
            ],
            'Default theme smarty cache'   => [
                'chk' => is_dir($baseDirectory . '/temp/smarty/cache')
                    ? is_writable($baseDirectory . '/temp/smarty/cache')
                    : mkdir($baseDirectory . '/temp/smarty/cache', 0775, true),
                'dir' => $baseDirectory . '/temp/smarty/cache',
                'msg' => 'WRITEABLE'
            ]
        ];
    }

    private function getPhpSettings(): array
    {
        if (!FeatureManager::isFeatureActive('system-settings')) {
            return [];
        }

        return [
            'max_execution_time'  => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'allow_url_fopen'     => ini_get('allow_url_fopen'),
            'max_input_vars'      => ini_get('max_input_vars'),
            'post_max_size'       => ini_get('post_max_size'),
            'file_uploads'        => ini_get('file_uploads'),
            'memory_limit'        => ini_get('memory_limit')
        ];
    }

    private function getPhpDependencies(): array
    {
        if (!FeatureManager::isFeatureActive('system-settings')) {
            return [];
        }

        $phpDependencies = isys_module_manager::instance()->getPackageDependencies('php');
        ksort($phpDependencies);

        return array_map(fn ($modules) => is_array($modules) ? implode(', ', $modules) : $modules, $phpDependencies);
    }

    private function getApacheDependencies(): array
    {
        if (!FeatureManager::isFeatureActive('system-settings')) {
            return [];
        }

        $apacheDependencies = isys_module_manager::instance()->getPackageDependencies('apache');
        ksort($apacheDependencies);

        return array_map(fn ($modules) => is_array($modules) ? implode(', ', $modules) : $modules, $apacheDependencies);
    }

    private function getDatabaseSettings(isys_component_database $database): array
    {
        if (!FeatureManager::isFeatureActive('system-settings')) {
            return [];
        }

        return [
            'innodb_log_file_size'    => $database->get_config_value('innodb_log_file_size'),
            'tmp_table_size'          => $database->get_config_value('tmp_table_size'),
            'innodb_sort_buffer_size' => $database->get_config_value('innodb_sort_buffer_size'),
            'max_allowed_packet'      => $database->get_config_value('max_allowed_packet'),
            'join_buffer_size'        => $database->get_config_value('join_buffer_size'),
            'sort_buffer_size'        => $database->get_config_value('sort_buffer_size'),
            'max_heap_table_size'     => $database->get_config_value('max_heap_table_size'),
            'innodb_buffer_pool_size' => $database->get_config_value('innodb_buffer_pool_size'),
            'datadir'                 => $database->get_config_value('datadir')
        ];
    }

    /**
     * Retrieves the current database size
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_database_size()
    {
        $database = isys_application::instance()->container->get('database');

        $databaseSize = 0;
        $l_result = $database->query('SHOW TABLE STATUS');

        while ($l_row = $database->fetch_row_assoc($l_result)) {
            $databaseSize += $l_row['Data_length'] + $l_row['Index_length'];
        }

        $kiloByte = 1024;
        $gigaByte = $kiloByte ** 3;
        $megaByte = $kiloByte ** 2;

        if ($databaseSize >= $gigaByte) {
            return round(($databaseSize / $gigaByte), 2) . ' GB';
        }

        if ($databaseSize >= $megaByte) {
            return round(($databaseSize / $megaByte), 2) . ' MB';
        }

        if ($databaseSize >= $kiloByte) {
            return round(($databaseSize / $kiloByte), 2) . ' KB';
        }

        return $databaseSize . ' Byte';
    }


    /**
     * Modify the "lock" row.
     *
     * @param array $lockRow
     */
    public function modify_lock_row(&$lockRow)
    {
        $lockTimer = time() - strtotime($lockRow['isys_lock__datetime']);
        $remainingLockSeconds = (C__LOCK__TIMEOUT - $lockTimer);

        if ($remainingLockSeconds <= 0) {
            $lockRow['expires'] = 'already expired';
        } else {
            $lockRow['expires'] = $remainingLockSeconds . 's';
        }

        if ($lockRow['isys_lock__table_name'] && $lockRow['isys_lock__table_field'] && $lockRow['isys_lock__field_value']) {
            $value = is_numeric($lockRow['isys_lock__field_value'])
                ? $this->dao->convert_sql_id($lockRow['isys_lock__field_value'])
                : $this->dao->convert_sql_text($lockRow['isys_lock__field_value']);

            $sql = 'SELECT ' . $lockRow['isys_lock__table_name'] . '__title
                FROM ' . $lockRow['isys_lock__table_name'] . '
                WHERE ' . $lockRow['isys_lock__table_field'] . ' = ' . $value . '
                LIMIT 1;';

            try {
                $title = $this->dao->retrieve($sql)->get_row_value($lockRow['isys_lock__table_name'] . '__title');

                if (!empty($title)) {
                    $lockRow['table_data'] = ($lockRow['isys_lock__table_label'] ? $this->language->get($lockRow['isys_lock__table_label']) . ': ' : '') . $title;
                }
            } catch (Exception $exception) {
                // Do nothing.
            }
        }
    }

    /**
     * Callback function for construction of breadcrumb navigation.
     *
     * @param array $gets
     *
     * @return array|null
     * @throws isys_exception_database
     */
    public function breadcrumb_get(&$gets)
    {
        if (isset($gets[C__GET__MODULE_SUB_ID]) && $gets[C__GET__MODULE_SUB_ID]) {
            try {
                $register = isys_module_manager::instance()->get_by_id($gets[C__GET__MODULE_SUB_ID]);

                if (is_object($register) && is_object($register->get_object())) {
                    $result = $register->get_object()->breadcrumb_get($gets);

                    if (is_array($result)) {
                        return $result;
                    }
                }
            } catch (Exception $e) {
                // Do nothing and let the remaining logic run.
            }
        }

        $what = $gets[C__GET__SETTINGS_PAGE];

        if ($what === 'system-overview') {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]) => null
                ]
            ];
        }

        $userSettingMapping = [
            'object-lock'     => 'LC__LOCKED__OBJECTS',
        ];

        if (isset($userSettingMapping[$what])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS') => [
                        C__GET__MODULE_ID => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS
                    ]
                ],
                [
                    $this->language->get($userSettingMapping[$what]) => null
                ]
            ];
        }

        $tenantSettingMapping = [
            'system-overview' => $this->language->get('LC__MODULE__SYSTEM__OVERVIEW', [isys_application::instance()->tenant->name]),
            'cache'           => 'LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__REPAIR_AND_CLEANUP',
        ];

        if (isset($tenantSettingMapping[$what])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]) => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__SETTINGS_PAGE => 'system-overview'
                    ]
                ],
                [
                    $this->language->get_in_text($tenantSettingMapping[$what]) => null
                ]
            ];
        }

        $dataViewMapping = [
            'customizeObjectBrowser'   => 'LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER',
        ];

        if (isset($dataViewMapping[$what])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__DATA_VIEW') => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__SETTINGS_PAGE => 'customizeObjectBrowser'
                    ]
                ],
                [
                    $this->language->get_in_text($dataViewMapping[$what]) => null
                ]
            ];
        }

        $importAndInterfaceMapping = [
            'hinventory'               => 'LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY__CONFIGURATION',
            'jdisc_configuration'      => 'LC__MODULE__JDISC__CONFIGURATION',
            'jdisc_profiles'           => 'LC__MODULE__JDISC__PROFILES',
            'object_matching'          => 'LC__CMDB__TREE__SYSTEM__INTERFACE__OBJECT_MATCHING',
            'smtp-configuration'       => 'LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_CONFIGURATION',
        ];

        if (isset($importAndInterfaceMapping[$what])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES') => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__SETTINGS_PAGE => 'object_matching'
                    ]
                ],
                [
                    $this->language->get_in_text($importAndInterfaceMapping[$what]) => null
                ]
            ];
        }

        $predefinedContentMapping = [
            'customCounter'        => 'LC__UNIVERSAL__CUSTOM_COUNTER',
            'relation_types'       => 'LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES',
            'roles_administration' => 'LC__MODULE__SYSTEM__ROLES_ADMINISTRATION',
        ];

        if (isset($predefinedContentMapping[$what])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__PREDEFINED_CONTENT') => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                        C__GET__SETTINGS_PAGE => 'cmdb-status'
                    ]
                ],
                [
                    $this->language->get_in_text($predefinedContentMapping[$what]) => null
                ]
            ];
        }

        return null;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param isys_component_tree $tree
     * @param bool                $isSystemModule
     * @param int                 $parentNodeId
     *
     * @return void
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function build_tree(isys_component_tree $tree, $isSystemModule = true, $parentNodeId = null)
    {
        if (!defined('C__MODULE__SYSTEM') || !defined('C__MODULE__SYSTEM_SETTINGS')) {
            return;
        }

        // Start with the root node.
        $rootNode = $tree->add_node(
            0,
            -1,
            $this->language->get('LC__MODULE__SYSTEM__TREE__ADMINISTRATION'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID => C__MODULE__SYSTEM
            ]),
            '',
            "{$this->imageDir}basic/application-window.svg"
        );

        $this->buildUserSettingsTree($tree, $rootNode);
        $this->buildTenantManagementTree($tree, $rootNode);
        $this->buildDataStructureTree($tree, $rootNode);
        $this->buildDataViewTree($tree, $rootNode);
        $this->buildPredefinedContentTree($tree, $rootNode);

        if ($this->isPro && defined('C__MODULE__AUTH') && class_exists(isys_module_auth::class)) {
            (new isys_module_auth())
                ->init(isys_module_request::get_instance())
                ->build_tree($tree, true, $rootNode);
        }

        $this->buildLogbookTree($tree, $rootNode);
        $this->buildInterfacesTree($tree, $rootNode);
        $this->buildAddOnsTree($tree, $rootNode);

        if (isys_application::isPro()) {
            if (isys_module_system::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'tfa-management')) {
                $currentCount = $tree->count() + 1;

                $tree->add_node(
                    $currentCount,
                    $rootNode,
                    $this->language->get('LC__USER_SETTINGS__TFA__RIGHT'),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__PRO,
                        C__GET__SETTINGS_PAGE => 'tfa-management'
                    ]),
                    '',
                    "{$this->imageDir}security/key.svg",
                    $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__PRO && $_GET[C__GET__SETTINGS_PAGE] === 'tfa-management'
                );
            }
        }
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildUserSettingsTree(isys_component_tree $tree, int $parentId): void
    {
        if (!defined('C__MODULE__USER_SETTINGS')) {
            return;
        }

        $nextId = $tree->count();
        $isUserSettings = $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__USER_SETTINGS;

        $userSettingsNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS')
        );

        $tree->add_node(
            ++$nextId,
            $userSettingsNode,
            $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOGIN_CREDENTIALS'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__SETTINGS_PAGE => 'login'
            ]),
            null,
            "{$this->imageDir}security/key.svg",
            $isUserSettings && $_GET[C__GET__SETTINGS_PAGE] === 'login'
        );

        $tree->add_node(
            ++$nextId,
            $userSettingsNode,
            $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOCALE_FORMATTING'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__SETTINGS_PAGE => 'locale-formatting'
            ]),
            null,
            "{$this->imageDir}basic/location.svg",
            $isUserSettings && $_GET[C__GET__SETTINGS_PAGE] === 'locale-formatting'
        );

        $tree->add_node(
            ++$nextId,
            $userSettingsNode,
            $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__USER_LANGUAGE'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__SETTINGS_PAGE => 'user-language'
            ]),
            null,
            "{$this->imageDir}basic/comment-empty.svg",
            $isUserSettings && $_GET[C__GET__SETTINGS_PAGE] === 'user-language'
        );

        $viewNode = $tree->add_node(
            ++$nextId,
            $userSettingsNode,
            $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__SETTINGS_PAGE => 'views'
            ]),
            null,
            "{$this->imageDir}basic/gear.svg",
            $isUserSettings && $_GET[C__GET__SETTINGS_PAGE] === 'views'
        );

        $cmdbAuth = isys_auth_cmdb::instance();
        $isCmdb = $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__CMDB;

        if ($cmdbAuth->is_allowed_to(isys_auth::EXECUTE, 'multilist_config')) {
            $tree->add_node(
                ++$nextId,
                $viewNode,
                $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS__LISTS__CATEGORY_LIST'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                    C__GET__SETTINGS_PAGE => 'catlist'
                ]),
                null,
                "{$this->imageDir}database/data.svg",
                $isCmdb && ($_GET[C__GET__SETTINGS_PAGE] === 'catlist' || $_GET[C__GET__SETTINGS_PAGE] === 'cat_list')
            );
        }

        if ($cmdbAuth->is_allowed_to(isys_auth::EXECUTE, 'list_config')) {
            $tree->add_node(
                ++$nextId,
                $viewNode,
                $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS__LISTS__OBJECT_LIST'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__CMDB,
                    C__GET__SETTINGS_PAGE => 'list'
                ]),
                null,
                "{$this->imageDir}database/data.svg",
                $isCmdb && $_GET[C__GET__SETTINGS_PAGE] === 'list'
            );
        }

        $tree->add_node(
            ++$nextId,
            $userSettingsNode,
            $this->language->get('LC__LOCKED__OBJECTS'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__SETTINGS_PAGE => 'object-lock'
            ]),
            '',
            "{$this->imageDir}basic/padlock.svg",
            $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] == 'object-lock'
        );
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildTenantManagementTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();
        $systemAuth = isys_auth_system::instance();
        $isSystemSettings = $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__SYSTEM_SETTINGS;

        $allowSystemtools = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS');

        $tenantSettingsNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__SETTINGS_PAGE => 'system-overview'
            ]),
            null,
            "{$this->imageDir}basic/tool-wrench.svg",
            $_GET[C__GET__SETTINGS_PAGE] === 'system-overview',
            '',
            '',
            self::getAuth()->is_allowed_to(isys_auth::VIEW, 'SYSTEMTOOLS/SYSTEMOVERVIEW')
        );

        $tree->add_node(
            ++$nextId,
            $tenantSettingsNode,
            $this->language->get('LC__MODULE__SYSTEM__OVERVIEW', [isys_application::instance()->tenant->name]),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                C__GET__SETTINGS_PAGE => 'tenant-settings'
            ]),
            null,
            "{$this->imageDir}basic/application-window.svg",
            $isSystemSettings && $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] == 'tenant-settings',
            null,
            null,
            $allowSystemtools && self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'LICENCESETTINGS')
        );

        $tree->add_node(
            ++$nextId,
            $tenantSettingsNode,
            $this->m_additional_options['cache']['text'],
            self::generate_link('cache'),
            null,
            $this->m_additional_options['cache']['icon'],
            $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] === 'cache',
            '',
            '',
            $allowSystemtools && $systemAuth->is_allowed_to(isys_auth::VIEW, 'SYSTEMTOOLS/CACHE')
        );

        if (FeatureManager::isFeatureActive('expert-settings')) {
            $tree->add_node(
                ++$nextId,
                $tenantSettingsNode,
                $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__EXPERT_SETTINGS'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                    C__GET__SETTINGS_PAGE => 'expert-settings'
                ]),
                null,
                "{$this->imageDir}basic/gear.svg",
                $isSystemSettings && $_GET[C__GET__SETTINGS_PAGE] === 'expert-settings',
                '',
                '',
                $systemAuth->is_allowed_to(isys_auth::VIEW, 'SYSTEMTOOLS/CACHE')
            );
        }

        // Signal to extend the tree.
        isys_application::instance()->container->get('signals')->emit('mod.settings.extendTree.tenant-settings', $tree, $tenantSettingsNode);
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildDataStructureTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();

        $dataStructureNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__DATA_STRUCTURE')
        );

        // Global settings -> QCW
        if ($this->isPro && class_exists('isys_module_quick_configuration_wizard') && self::getAuth()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/QCW')) {
            $tree->add_node(
                ++$nextId,
                $dataStructureNode,
                $this->language->get('LC__CMDB__TREE__SYSTEM__CMDB_CONFIGURATION__QCW'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__QCW
                ]),
                null,
                "{$this->imageDir}basic/magic-wand.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__QCW
            );
        }

        $l_allowed_objtype_groups = isys_auth_cmdb_object_types::instance()->get_allowed_objecttype_group_configs();

        if ($l_allowed_objtype_groups || is_array($l_allowed_objtype_groups)) {
            $objectTypeConfigurationNode = $tree->add_node(
                ++$nextId,
                $dataStructureNode,
                $this->language->get('LC__MODULE__SYSTEM__TREE__DATA_STRUCTURE__OBJECT_TYPES')
            );

            $l_groups = isys_application::instance()->container->get('cmdb_dao')->get_object_group_by_id();

            while ($l_grp = $l_groups->get_row()) {
                if (is_array($l_allowed_objtype_groups) && !in_array($l_grp['isys_obj_type_group__id'], $l_allowed_objtype_groups)) {
                    continue;
                }

                $tree->add_node(
                    ++$nextId,
                    $objectTypeConfigurationNode,
                    $this->language->get($l_grp['isys_obj_type_group__title']),
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID         => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID     => C__MODULE__CMDB,
                        C__GET__SETTINGS_PAGE     => 'objectTypeListConfig',
                        C__CMDB__GET__OBJECTGROUP => $l_grp['isys_obj_type_group__id'],
                        C__CMDB__GET__VIEWMODE    => C__CMDB__VIEW__LIST_OBJECTTYPE,
                    ]),
                    null,
                    "{$this->imageDir}development/module.svg",
                    $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM
                        && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__CMDB
                        && $_GET[C__GET__SETTINGS_PAGE] == 'objectTypeListConfig'
                        && $_GET[C__CMDB__GET__OBJECTGROUP] == $l_grp['isys_obj_type_group__id']
                );
            }
        }

        if ($this->isPro && class_exists('isys_module_custom_fields') && self::getAuth()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMFIELDS')) {
            $tree->add_node(
                ++$nextId,
                $dataStructureNode,
                $this->language->get('LC__CMDB__TREE__SYSTEM__CUSTOM_CATEGORIES'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__CUSTOM_FIELDS
                ]),
                '',
                "{$this->imageDir}basic/window-font.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__CUSTOM_FIELDS
            );
        }
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildDataViewTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();

        $dataViewNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__DATA_VIEW')
        );

        // @see  ID-6307 Adding auth check.
        if (isys_module_cmdb::getAuth()->is_allowed_to(isys_auth::VIEW, 'object_browser_configuration')) {
            // @see  ID-677 Customizable object browser.
            $tree->add_node(
                ++$nextId,
                $dataViewNode,
                $this->m_additional_options['customizeObjectBrowser']['text'],
                self::generate_link('customizeObjectBrowser'),
                '',
                "{$this->imageDir}spreadsheet/formating-data-bars-blue.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] === 'customizeObjectBrowser'
            );
        }

        // Signal to extend the tree.
        isys_application::instance()->container->get('signals')->emit('mod.settings.extendTree.data-view', $tree, $dataViewNode);
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws isys_exception_database
     */
    private function buildPredefinedContentTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();

        $predefinedContentNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__PREDEFINED_CONTENT')
        );

        // Global settings -> CMDB-Status
        if ($this->isPro && self::getAuth()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/CMDBSTATUS')) {
            $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                    C__GET__SETTINGS_PAGE => 'cmdb-status'
                ]),
                '',
                "{$this->imageDir}imaging/adjust-colors-filled.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM
                    && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__SYSTEM_SETTINGS
                    && $_GET[C__GET__SETTINGS_PAGE] == 'cmdb-status'
            );
        }

        if (isys_auth_system_globals::instance()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMCOUNTER')) {
            $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->m_additional_options['customCounter']['text'],
                self::generate_link('customCounter'),
                '',
                "{$this->imageDir}basic/number-9-plus.svg",
                $_GET[C__GET__SETTINGS_PAGE] === 'customCounter'
            );
        }

        // Global settings -> Dialog-Admin
        if (isys_auth_dialog_admin::instance()->is_allowed_to(isys_auth::VIEW, 'TABLE') || isys_auth_dialog_admin::instance()->is_allowed_to(isys_auth::VIEW, 'CUSTOM')) {
            $dialogAdminNode = $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN')
            );

            (new isys_module_dialog_admin())
                ->init(isys_module_request::get_instance())
                ->build_tree($tree, true, $dialogAdminNode);

            $nextId = $tree->count();
        }

        if (self::getAuth()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/RELATIONSHIPTYPES')) {
            $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->m_additional_options['relation_types']['text'],
                self::generate_link('relation_types'),
                '',
                "{$this->imageDir}database/relations.svg",
                $_GET[C__GET__SETTINGS_PAGE] === 'relation_types'
            );
        }

        if (self::getAuth()->is_allowed_to(isys_auth::VIEW, 'GLOBALSETTINGS/ROLESADMINISTRATION')) {
            $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->m_additional_options['roles_administration']['text'],
                self::generate_link('roles_administration'),
                '',
                "{$this->imageDir}user-management/user-group.svg",
                $_GET[C__GET__SETTINGS_PAGE] === 'roles_administration'
            );
        }

        if (defined('C__MODULE__QRCODE') && class_exists('isys_module_qrcode') && (self::getAuth()->is_allowed_to(isys_auth::VIEW, 'QR_CONFIG/GLOBAL') || self::getAuth()->is_allowed_to(isys_auth::VIEW, 'QR_CONFIG/OBJTYPE'))) {
            $tree->add_node(
                ++$nextId,
                $predefinedContentNode,
                $this->language->get('LC__MODULE__QRCODE'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__QRCODE
                ]),
                '',
                "{$this->imageDir}transportation/label-qrcode.svg",
                $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__QRCODE,
                '',
                '',
                isys_auth_system::instance()->has('qr_config')
            );
        }
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildLogbookTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();

        if (isys_auth_logbook::instance()->is_allowed_to(isys_auth::VIEW, 'LOGBOOK')) {
            $logbookNode = $tree->add_node(
                ++$nextId,
                $parentId,
                $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__LOGBOOK'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__LOGBOOK,
                    C__GET__SETTINGS_PAGE => C__PAGE__LOGBOOK_VIEW
                ]),
                '',
                "{$this->imageDir}basic/book-open.svg",
                $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__LOGBOOK && $_GET[C__GET__SETTINGS_PAGE] == C__PAGE__LOGBOOK_VIEW
            );

            (new isys_module_logbook())->build_tree($tree, true, $logbookNode);
        }
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildInterfacesTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();

        $interfacesNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES')
        );

        $tree->add_node(
            ++$nextId,
            $interfacesNode,
            $this->m_additional_options['object_matching']['text'],
            self::generate_link('object_matching'),
            null,
            $this->m_additional_options['object_matching']['icon'],
            $_GET[C__GET__SETTINGS_PAGE] === 'object_matching',
            '',
            '',
            isys_auth_system::instance()->is_allowed_to(isys_auth::VIEW, 'OBJECT_MATCHING')
        );

        if (self::getAuth()->is_allowed_to(isys_auth::VIEW, 'HINVENTORY')) {
            $tree->add_node(
                ++$nextId,
                $interfacesNode,
                $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__SETTINGS_PAGE => 'hinventory'
                ]),
                null,
                "{$this->imageDir}basic/gear.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] === 'hinventory',
                '',
                '',
                self::getAuth()->is_allowed_to(isys_auth::VIEW, 'HINVENTORY/CONFIG')
            );
        }
        if (FeatureManager::isFeatureActive('smtp-settings')) {
            $tree->add_node(
                ++$nextId,
                $interfacesNode,
                $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES__SMTP_CONFIGURATION'),
                isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__SETTINGS_PAGE => 'smtp-configuration'
                ]),
                null,
                "{$this->imageDir}web-email/mail-back.svg",
                $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__SETTINGS_PAGE] === 'smtp-configuration',
                '',
                '',
                self::getAuth()->is_allowed_to(isys_auth::VIEW, 'SMTP_CONFIGURATION')
            );
        }

        // Signal to extend the tree.
        isys_application::instance()->container->get('signals')->emit('mod.settings.extendTree.data-import', $tree, $interfacesNode);

        $nextId = $tree->count();

        // Signal to extend the tree.
        isys_application::instance()->container->get('signals')->emit('mod.settings.extendTree.interfaces', $tree, $interfacesNode);
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $parentId
     *
     * @return void
     * @throws Exception
     */
    private function buildAddOnsTree(isys_component_tree $tree, int $parentId): void
    {
        $nextId = $tree->count();
        $moduleRequest = isys_module_request::get_instance();
        $moduleManager = isys_module_manager::instance();

        $addonsNode = $tree->add_node(
            ++$nextId,
            $parentId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__ADDONS')
        );

        $alreadyIterated = [];

        foreach ($moduleManager->modules() as $addon) {
            if ($addon->get_object() instanceof AdministratableInterface) {
                $alreadyIterated[] = get_class($addon->get_object());
                $addon->get_object()->buildAdministrationTree($tree, $addonsNode);
            }
        }

        // @todo  Remove in i-doit 25
        if (class_exists('isys_module_events') && isys_module_events::DISPLAY_IN_SYSTEM_MENU === true) {
            if ($moduleManager->is_active('events') && !in_array('isys_module_events', $alreadyIterated, true)) {
                isys_factory::get_instance('isys_module_events')
                    ->init($moduleRequest)
                    ->build_tree($tree, true, $addonsNode);
            }
        }

        // @todo  Remove in i-doit 25
        if (class_exists('isys_module_cmk2') && isys_module_cmk2::DISPLAY_IN_SYSTEM_MENU === true) {
            if ($moduleManager->is_active('cmk2') && !in_array('isys_module_cmk2', $alreadyIterated, true)) {
                (new isys_module_cmk2())
                    ->init($moduleRequest)
                    ->build_tree($tree, true, $addonsNode);
            }
        }

        // @todo  Remove in i-doit 25
        if (class_exists('isys_module_loginventory') && isys_module_loginventory::DISPLAY_IN_SYSTEM_MENU === true) {
            if ($moduleManager->is_active('loginventory') && !in_array('isys_module_loginventory', $alreadyIterated, true)) {
                (new isys_module_loginventory())
                    ->init($moduleRequest)
                    ->build_tree($tree, true, $addonsNode);
            }
        }

        $nextId = $tree->count();

        // @todo  Remove in i-doit 25
        if (class_exists('isys_module_rfc') && isys_module_rfc::DISPLAY_IN_SYSTEM_MENU === true) {
            if ($moduleManager->is_active('rfc') && !in_array('isys_module_rfc', $alreadyIterated, true)) {
                $tree->add_node(
                    ++$nextId,
                    $addonsNode,
                    $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__RFC')
                );

                $l_rfc_module = new isys_module_rfc();
                $l_rfc_module->init($moduleRequest);
                $l_rfc_module->build_tree($tree, true, $nextId);
            }
        }

        $nextId = $tree->count();

        // @todo  Remove in i-doit 25
        if (class_exists('isys_module_swapci') && isys_module_swapci::DISPLAY_IN_SYSTEM_MENU === true) {
            if ($moduleManager->is_active('swapci') && !in_array('isys_module_swapci', $alreadyIterated, true)) {
                $tree->add_node(
                    ++$nextId,
                    $addonsNode,
                    $this->language->get('LC__MODULE__SWAPCI')
                );

                $l_swapci = new isys_module_swapci();
                $l_swapci->init($moduleRequest);
                $l_swapci->build_tree($tree, true, $nextId);
            }
        }

        // Hide if no new nodes where added (= no add-ons).
        if ($addonsNode == $tree->count()) {
            $tree->remove_node($addonsNode);
        }
    }

    /**
     * @return isys_module_system|void
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function start()
    {
        $navbar = isys_component_template_navbar::getInstance();

        // @see ID-9459 Force the 'quick' save mode (instead of 'log').
        if ($navbar->get_save_mode() === 'log') {
            $navbar->set_save_mode('quick');
        }

        // Unpack request package.
        $l_gets = $this->m_userrequest->get_gets();
        $template = isys_application::instance()->container->get('template');
        $tree = $this->m_userrequest->get_menutree();

        $this->build_tree($tree);
        $tree->set_tree_sort(false);
        $template
            ->assign('bMenuTreeSearcheable', true)
            ->assign('menu_tree', $tree->set_tree_search(true)->process());

        try {
            if (isset($l_gets[C__GET__MODULE_SUB_ID]) && is_numeric($l_gets[C__GET__MODULE_SUB_ID])) {
                isys_module_request::get_instance()
                    ->get_module_manager()
                    ->get_by_id($l_gets[C__GET__MODULE_SUB_ID])
                    ->get_object()
                    ->start();

                return;
            }

            if (!isset($l_gets[C__GET__SETTINGS_PAGE]) && !isys_core::is_ajax_request()) {
                // $template
                //     ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__ADMINISTRATION'))
                //     ->include_template('contentbottomcontent', __DIR__ . '/templates/index.tpl');
                // return;

                $redirectUrl = isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                    C__GET__SETTINGS_PAGE => 'tenant-settings'
                ], true);

                header('Location: ' . $redirectUrl);
                die;
            }

            if (!isset($this->m_additional_options[$l_gets[C__GET__SETTINGS_PAGE]]) && !isys_core::is_ajax_request()) {
                $redirectUrl = isys_helper_link::create_url([
                    C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                    C__GET__SETTINGS_PAGE => 'system-overview'
                ], true);

                header('Location: ' . $redirectUrl);
                die;
            }

            $option = $this->m_additional_options[$l_gets[C__GET__SETTINGS_PAGE]];

            if (is_array($option) && method_exists($this, $option['func'])) {
                call_user_func([$this, $option['func']]);

                return;
            }
        } catch (isys_exception_general $e) {
            throw $e;
        } catch (isys_exception_auth $e) {
            $template
                ->assign('exception', $e->write_log())
                ->assign('content_title', $this->language->get('LC__MODULE__AUTH'))
                ->include_template('contentbottomcontent', 'exception-auth.tpl');
        }
    }

    /**
     * Deletes all objects with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL) {
            die('We will not purge all objects with status \'normal\'.');
        }

        $l_dao = new isys_cmdb_dao(isys_application::instance()->container->get('database'));

        $l_res = $l_dao->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;');
        $l_count = $l_res->count();

        if ($l_count > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_dao->delete_object_and_relations($l_row['isys_obj__id']);
            }

            return $l_count;
        }

        return 0;
    }

    /**
     * Deletes all dialog entries with the given status.
     *
     * @throws Exception
     */
    public function cleanup_dialog(int $type = C__RECORD_STATUS__BIRTH): int
    {
        $count = 0;
        $log = Logger::factory('dialog_cleanup', BASE_DIR . '/log/dialog_cleanup.log');
        $dao = new isys_cmdb_dao_dialog_admin(isys_application::instance()->container->get('database'));
        $tables = (new isys_dialog_admin_dao(isys_application::instance()->container->get('database')))->get_dialog_tables();
        $tables[] = 'isys_dialog_plus_custom';

        foreach ($tables as $table) {
            $res = $dao->get_data($table, null, $table . '__status = ' . $dao->convert_sql_int($type));
            $count += $res->count();
            while ($row = $res->get_row()) {
                try {
                    $log->warning('Deleting dialog entry #' . $row[$table . '__id'] . ' in "' . $table . '" ...');
                    $dao->delete($table, $row[$table . '__id']);
                } catch (Exception $e) {
                    $count--;
                    $log->error($e->getMessage());
                }
            }
        }

        return $count;
    }

    /**
     * Sets all dialog entries to normal.
     *
     * @throws Exception
     */
    public function recycle_dialog(): int
    {
        $count = 0;
        $log = Logger::factory('dialog_recycle', BASE_DIR . '/log/dialog_recycle.log');
        $dao = new isys_cmdb_dao_dialog_admin(isys_application::instance()->container->get('database'));
        $tables = (new isys_dialog_admin_dao(isys_application::instance()->container->get('database')))->get_dialog_tables();
        $tables[] = 'isys_dialog_plus_custom';

        foreach ($tables as $table) {
            $res = $dao->get_data($table, null, $table . '__status != ' . $dao->convert_sql_int(C__RECORD_STATUS__NORMAL));
            $count += $res->count();
            while ($row = $res->get_row()) {
                try {
                    $log->warning('Setting dialog entry #' . $row[$table . '__id'] . ' in "' . $table . '" to normal ...');
                    $dao->save($row[$table . '__id'], $table, $row[$table . '__title'], $row[$table . '__sort'], null, C__RECORD_STATUS__NORMAL);
                } catch (Exception $e) {
                    $count--;
                    $log->error($e->getMessage());
                }
            }
        }

        return $count;
    }

    /**
     * Lists all objects with the given status (for system module -> cache).
     *
     * @global  array   $g_dirs
     *
     * @param   integer $p_type
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function list_objects($p_type = C__RECORD_STATUS__BIRTH)
    {
        global $g_dirs;

        $l_return = [];
        $l_dao = isys_application::instance()->container->get('cmdb_dao');
        $l_quickinfo = new isys_ajax_handler_quick_info();
        $empty = isys_tenantsettings::get('gui.empty_value', '-');

        $l_sql = 'SELECT isys_obj__id, isys_obj__title, isys_obj__sysid, isys_obj_type__title
			FROM isys_obj
			LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__status = ' . $l_dao->convert_sql_int($p_type) . ' AND isys_obj__undeletable = 0;';

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_return[] = $l_quickinfo->get_quick_info(
                    $l_row['isys_obj__id'],
                    '<img src="' . $g_dirs['images'] . 'axialis/basic/button-info.svg" class="vam" /> <span class="vam">' .
                    $this->language
                        ->get($l_row['isys_obj_type__title']) . ' > ' . ($l_row['isys_obj__title'] ?: $empty) . '</span>',
                    C__LINK__OBJECT
                );
            }
        }

        return $l_return;
    }

    /**
     * @param string $type
     */
    public function cleanup_other($type)
    {
        $mapping = [
            'DeleteExportedCheckMkTags' => DeleteExportedCheckMkTags::class,
            'MigrateDbObjectsToCategory' => MigrateDbObjectsToCategory::class,
            'RemoveOrphanedCustomCategoryData' => RemoveOrphanedCustomCategoryData::class,
            'TenantDatabaseEncryption' => TenantDatabaseEncryption::class,
            'MigrateOldObjectTypeIcons' => MigrateOldObjectTypeIcons::class
        ];

        if (isset($mapping[$type])) {
            (new $mapping[$type])->process();
        }
    }

    /**
     * Deletes all categorie entries with the given status.
     *
     * @param   integer $p_type
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_categories($p_type = C__RECORD_STATUS__BIRTH)
    {
        if ($p_type == C__RECORD_STATUS__NORMAL) {
            die('i-doit will not remove all category data with status "normal"!');
        }

        $log = Logger::factory('category_cleanup', BASE_DIR . '/log/category_cleanup.log');

        $l_count_all = 0;
        $l_dao = new isys_cmdb_dao(isys_application::instance()->database);
        $l_catg_row = [];
        $ignoreCategoryTypes = [isys_cmdb_dao_category::TYPE_VIEW, isys_cmdb_dao_category::TYPE_FOLDER];

        $l_arr_skip = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => filter_defined_constants([
                'C__CATG__LOGICAL_UNIT',
                'C__CATG__ASSIGNED_LOGICAL_UNIT',
                'C__CATG__OBJECT',
                'C__CATG__VIRTUAL_AUTH',
                'C__CATG__VIRTUAL_SUPERNET',
                'C__CATG__NET_CONNECTIONS_FOLDER',
                'C__CATG__WORKFLOW',
                'C__CATG__NAGIOS_SERVICE_REFS_TPL_BACKWARDS',
                'C__CATG__NAGIOS_HOST_TPL_ASSIGNED_OBJECTS',
                'C__CATG__NAGIOS_REFS_SERVICES_BACKWARDS',
                'C__CATG__NAGIOS_REFS_SERVICES',
                'C__CATG__NAGIOS_APPLICATION_FOLDER',
                'C__CATG__NAGIOS_APPLICATION_REFS_NAGIOS_SERVICE',
            ]),
            C__CMDB__CATEGORY__TYPE_SPECIFIC => filter_defined_constants([
                'C__CATS__CONTRACT_ALLOCATION',
                'C__CMDB__SUBCAT__WS_ASSIGNMENT', // @todo  Remove in i-doit 1.12
                'C__CATS__WS_ASSIGNMENT',
                'C__CMDB__SUBCAT__FILE_VERSIONS', // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_VERSIONS',
                'C__CMDB__SUBCAT__FILE_OBJECTS',  // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_OBJECTS',
                'C__CMDB__SUBCAT__FILE_ACTUAL',   // @todo  Remove in i-doit 1.12
                'C__CATS__FILE_ACTUAL'
            ])
        ];

        // Remove Custom category entries
        $l_res = $l_dao->get_all_catg_custom(null, ' AND isysgui_catg_custom__list_multi_value > 0');
        while ($l_row = $l_res->get_row()) {
            // Remove custom fields
            $l_res2 = $l_dao->retrieve('SELECT * FROM isys_catg_custom_fields_list WHERE
              isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $l_dao->convert_sql_id($l_row['isysgui_catg_custom__id']) . '
              AND (isys_catg_custom_fields_list__status = ' . $l_dao->convert_sql_int($p_type) . ' OR isys_catg_custom_fields_list__status IS NULL)');
            $l_data_ids = [];
            while ($l_row2 = $l_res2->get_row()) {
                if (!isset($l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']])) {
                    $log->warning('Deleting custom fields data entry #' . $l_row2['isys_catg_custom_fields_list__data__id'] . ' in "isys_catg_custom_fields_list" ...');
                    $l_data_ids[$l_row2['isys_catg_custom_fields_list__data__id']] = true;
                }

                $l_dao->delete_entry($l_row2['isys_catg_custom_fields_list__id'], 'isys_catg_custom_fields_list');
            }
            $l_count_all += count($l_data_ids);
            unset($l_data_ids);
        }

        $l_res = $l_dao->get_all_catg(
            null,
            ' AND isysgui_catg__list_multi_value > 0 AND isysgui_catg__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_GLOBAL]) . ')'
        );

        while ($l_row = $l_res->get_row()) {
            $l_class = $l_row['isysgui_catg__class_name'];
            $l_table = (substr($l_row['isysgui_catg__source_table'], -5) == '_list') ? $l_row['isysgui_catg__source_table'] : $l_row['isysgui_catg__source_table'] . '_list';
            $categoryType = $l_row['isysgui_catg__type'];

            if (class_exists($l_class) && strpos($l_table, '_2_') == false && !in_array($categoryType, $ignoreCategoryTypes)) {
                $l_catg_res = call_user_func([
                    $l_class,
                    'instance'
                ], isys_application::instance()->database)->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_catg_res->num_rows();
                if ($l_count > 0) {
                    while ($l_catg_row = $l_catg_res->get_row()) {
                        $log->warning('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_catg_row[$l_table . '__id'], $l_table);
                    }

                    $l_count_all += $l_count;
                }
            }
        }

        $l_res = $l_dao->get_all_cats(
            null,
            ' AND isysgui_cats__list_multi_value > 0 AND isysgui_cats__id NOT IN (' . implode(',', $l_arr_skip[C__CMDB__CATEGORY__TYPE_SPECIFIC]) . ')'
        );

        while ($l_row = $l_res->get_row()) {
            $l_table = $l_row['isysgui_cats__source_table'];
            $l_class = $l_row['isysgui_cats__class_name'];
            $categoryType = $l_row['isysgui_cats__type'];

            if (class_exists($l_class) && strpos($l_table, '_2_') == false && !in_array($categoryType, $ignoreCategoryTypes)) {
                $l_cats_res = call_user_func([
                    $l_class,
                    'instance'
                ], isys_application::instance()->database)->get_data(
                    null,
                    null,
                    ' AND (' . $l_table . '.' . $l_table . '__status = ' . $l_dao->convert_sql_int($p_type) . ' OR ' . $l_table . '.' . $l_table . '__status IS NULL)',
                    null,
                    $p_type
                );

                $l_count = $l_cats_res->num_rows();

                if ($l_count > 0) {
                    while ($l_cats_row = $l_cats_res->get_row()) {
                        $log->warning('Deleting entry #' . $l_catg_row[$l_table . '__id'] . ' in "' . $l_class . '" ...');
                        $l_dao->delete_entry($l_cats_row[$l_table . '__id'], $l_table);
                    }

                    $l_count_all += $l_count;
                }
            }
        }

        return $l_count_all;
    }

    /**
     * Method which removes duplicate "obj-type to category" assignments. This sometimes happens by accident.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function cleanup_category_assignments()
    {
        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        echo 'Removing duplicate category assignments...<br />';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->get_object_types();

        while ($l_row = $l_res->get_row()) {
            $l_sql = 'SELECT isys_obj_type_2_isysgui_catg__id, isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id, count(isys_obj_type_2_isysgui_catg__isysgui_catg__id) AS cnt
				FROM isys_obj_type_2_isysgui_catg
				WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_row['isys_obj_type__id']) . '
				GROUP BY isys_obj_type_2_isysgui_catg__isysgui_catg__id
				HAVING cnt > 1
				ORDER BY cnt;';

            $l_duplicate_res = $l_dao->retrieve($l_sql);

            if ($l_duplicate_res->num_rows() > 0) {
                echo 'Removing duplicates from obj-type "' . $this->language
                        ->get($l_row['isys_obj_type__title']) . '" (' . $l_row['isys_obj_type__id'] . ')<br />';

                while ($l_duplicate_row = $l_duplicate_res->get_row()) {
                    // With this SQL we remove all duplicates but one.
                    $l_remove_sql = 'DELETE FROM isys_obj_type_2_isysgui_catg
						WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isysgui_catg__id']) . '
						AND isys_obj_type_2_isysgui_catg__isys_obj_type__id = ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__isys_obj_type__id']) . '
						AND isys_obj_type_2_isysgui_catg__id != ' . $l_dao->convert_sql_id($l_duplicate_row['isys_obj_type_2_isysgui_catg__id']) . ';';

                    $l_dao->update($l_remove_sql);
                }

                $l_dao->apply_update();
            }
        }

        echo 'Done!';
    }

    /**
     * Helper function which checks if a table really exists.
     *
     * @param string $p_table
     *
     * @return boolean
     * @throws Exception
     * @deprecated Please use 'isys_cmdb_dao->table_exists()' instead
     */
    public function does_table_exists($p_table)
    {
        $database = isys_application::instance()->container->get('database');

        return $database->num_rows($database->query('SHOW TABLES LIKE "' . $database->escape_string($p_table) . '";')) > 0;
    }

    /**
     * This function deletes all unassigned relation entries and objects
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function cleanup_unassigned_relations()
    {
        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $l_dao_rel = isys_cmdb_dao_relation::instance(isys_application::instance()->container->get('database'));
        $l_stats = $l_dao_rel->delete_dead_relations();
        $l_dead_relation_objects = $l_stats[isys_cmdb_dao_relation::C__DEAD_RELATION_OBJECTS];

        if ($l_dead_relation_objects > 0) {
            //echo '(' . $l_amount . ') unassigned relation objects deleted.<br>';
            echo $this->language->get('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__OBJECTS_DELETED', [$l_dead_relation_objects]) . '<br />';
        } else {
            echo $this->language->get('LC__SYSTEM__CLEANUP_UNASSIGNED_RELATION_OBJECTS__NO_OBJECTS_DELETED');
        }
    }

    /**
     * This function renews the relation titles of all relation objects.
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function renew_relation_titles()
    {
        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $l_sql = 'SELECT * FROM isys_catg_relation_list INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id';
        $l_dao = isys_factory::get_instance('isys_cmdb_dao_category_g_relation', isys_application::instance()->database);
        $l_changes = false;

        $l_res = $l_dao->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_obj = $l_row['isys_catg_relation_list__isys_obj__id'];
            $l_master = $l_row['isys_catg_relation_list__isys_obj__id__master'];
            $l_slave = $l_row['isys_catg_relation_list__isys_obj__id__slave'];
            $l_relation_type = $l_row['isys_catg_relation_list__isys_relation_type__id'];
            $l_old_name = $l_row['isys_obj__title'];

            $l_dao->update_relation_object($l_obj, $l_master, $l_slave, $l_relation_type);

            $l_new_name = $l_dao->get_obj_name_by_id_as_string($l_row['isys_obj__id']);

            if ($l_old_name != $l_new_name) {
                $l_changes = true;
                echo "Relation title <strong>'" . $l_old_name . "'</strong> changed to <strong>'" . $l_new_name . "'</strong> (" . $l_obj . ").<br />";
            }
        }

        if (!$l_changes) {
            echo "No broken relation title found.";
        }
    }

    /**
     * Method for refilling empty SYS-IDs
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function refill_empty_sysids()
    {
        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $l_sql = 'SELECT * FROM isys_obj
		    LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
			WHERE isys_obj__sysid = ""
			OR isys_obj__sysid IS NULL;';

        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_res = $l_dao->retrieve($l_sql);

        if (count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                $l_new_sysid = $l_dao->generate_sysid($l_row['isys_obj_type__id'], $l_row['isys_obj__id']);

                echo 'The ' . $this->language
                        ->get($l_row['isys_obj_type__title']) . ' "' . $l_row['isys_obj__title'] . '" (#' . $l_row['isys_obj__id'] . ') has no SYS-ID. Filling it with: ' .
                    $l_new_sysid . '<br />';

                $l_sql = 'UPDATE isys_obj SET isys_obj__sysid = ' . $l_dao->convert_sql_text($l_new_sysid) . ' WHERE isys_obj__id = ' .
                    $l_dao->convert_sql_id($l_row['isys_obj__id']) . ';';

                if (!$l_dao->update($l_sql)) {
                    throw new isys_exception_cmdb("Updating the object with a new SYS-ID failed!");
                }
            }
        } else {
            echo 'No empty SYS-IDs found.';
        }
    }

    /**
     * Temporary solution in cleaning the auth table
     *
     * @return mixed
     */
    public function cleanup_auth()
    {
        $l_modules = isys_module_manager::instance()
            ->get_modules();
        $l_dao = isys_cmdb_dao::factory(isys_application::instance()->database);

        $l_system_module = false;

        while ($l_row = $l_modules->get_row()) {
            $l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_row['isys_module__id']);

            if ($l_auth_instance) {
                if (get_class($l_auth_instance) == 'isys_auth_system') {
                    if (!$l_system_module) {
                        $l_system_module = true;
                        $l_row['isys_module__id'] = defined_or_default('C__MODULE__SYSTEM');
                    } else {
                        continue;
                    }
                }

                $l_auth_module_obj = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);
            } else {
                continue;
            }

            // Get auth methods
            $l_auth_module_methods = $l_auth_module_obj->get_auth_methods();

            foreach ($l_auth_module_methods as $l_method => $l_content) {
                // Check if cleanup exists
                if (!array_key_exists('cleanup', $l_content)) {
                    continue;
                }
                $l_found = false;
                foreach ($l_content['cleanup'] as $l_table => $l_search_field) {
                    // Prepare search query
                    $l_query = 'SELECT * FROM ' . $l_table . ' WHERE ' . $l_search_field . ' = ';
                    // Prepare delete query
                    $l_delete_query = 'DELETE FROM isys_auth WHERE isys_auth__id = ';
                    // Get paths
                    $l_auth_query = 'SELECT isys_auth__id, isys_auth__path FROM isys_auth ' . 'WHERE isys_auth__isys_module__id = ' .
                        $l_dao->convert_sql_id($l_row['isys_module__id']) . ' ' . 'AND isys_auth__path LIKE ' . $l_dao->convert_sql_text(strtoupper($l_method) . '/%');

                    $l_res = $l_dao->retrieve($l_auth_query);
                    if (!$l_found && $l_res->num_rows() > 0) {
                        while ($l_row2 = $l_res->get_row()) {
                            $l_search_query = $l_query;
                            $l_delete_query2 = $l_delete_query;
                            $l_path_arr = explode('/', $l_row2['isys_auth__path']);
                            $l_field_value = $l_path_arr[1];

                            if ($l_field_value == isys_auth::WILDCHAR) {
                                continue;
                            }

                            $l_search_query .= (is_numeric($l_field_value)) ? $l_dao->convert_sql_id($l_field_value) : $l_dao->convert_sql_text($l_field_value);
                            $l_search_res = $l_dao->retrieve($l_search_query);
                            if ($l_search_res->num_rows() == 0) {
                                // Field value does not exist complete the delete query
                                $l_delete_query2 .= $l_dao->convert_sql_id($l_row2['isys_auth__id']);
                            } else {
                                // Field value found
                                unset($l_delete_query2);
                                $l_found = true;
                            }
                        }
                    }
                }
                if (isset($l_delete_query2)) {
                    $l_dao->update($l_delete_query2);
                }
            }
        }

        return $l_dao->apply_update();
    }

    /**
     * Handles relationship types
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function handle_relation_types()
    {
        isys_module_system::getAuth()->check(isys_auth::VIEW, 'GLOBALSETTINGS/RELATIONSHIPTYPES');
        $isAllowedToSupervise = isys_module_system::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/RELATIONSHIPTYPES');

        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');

        $relationTypeModel = RelationType::instance($database);
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($database);
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        switch ($l_navmode) {
            case C__NAVMODE__EDIT:
                isys_module_system::getAuth()->check(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/RELATIONSHIPTYPES');
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                try {
                    isys_module_system::getAuth()->check(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/RELATIONSHIPTYPES');
                    // At first we update all the "weighting" data.
                    $l_res = $l_dao_relation->retrieve('SELECT isys_relation_type__id FROM isys_relation_type;');

                    while ($l_row = $l_res->get_row()) {
                        $l_dao_relation->update_relation_type_weighting($l_row['isys_relation_type__id'], $l_posts['relation_weighting'][$l_row['isys_relation_type__id']]);
                    }

                    if ($l_posts['delRelTypes'] != '') {
                        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance($database);
                        $l_del_rel_types = explode(',', $l_posts['delRelTypes']);
                        $l_contacts_res = $l_dao_contact->get_contact_objects_by_tag(
                            null,
                            null,
                            'AND isys_contact_tag__isys_relation_type__id IN (' . $l_posts['delRelTypes'] . ')'
                        );
                        $l_contacts_role_already_updated = [];

                        while ($l_row = $l_contacts_res->get_row()) {
                            $l_relation_type = defined_or_default('C__RELATION_TYPE__USER');
                            $l_master = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave = $l_row['isys_obj__id'];
                            $l_catg_contact_id = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_contact_role = $l_row['isys_contact_tag__id'];

                            if (!in_array($l_contact_role, $l_contacts_role_already_updated)) {
                                // Set contact role with relation type contact user
                                $l_dao_contact->update_contact_tag($l_contact_role, null, $l_relation_type);
                                $l_contacts_role_already_updated[] = $l_contact_role;
                            }

                            // Update all contacts with the default relation type for contacts
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        }

                        $l_dao_relation->remove_relation_type($l_del_rel_types);
                    }

                    if (isset($l_posts['relation_title']) && is_array($l_posts['relation_title'])) {
                        foreach ($l_posts['relation_title'] as $relationTypeId => $title) {
                            $relationTypeModel->save($relationTypeId, [
                                'title'       => $title,
                                'titleMaster' => $l_posts['relation_title_master'][$relationTypeId],
                                'titleSlave'  => $l_posts['relation_title_slave'][$relationTypeId],
                                'type'        => $l_posts['relation_type'][$relationTypeId],
                                'default'     => $l_posts['relation_direction'][$relationTypeId]
                            ]);
                        }
                    }

                    if (isset($l_posts['new_relation_title'])) {
                        foreach ($l_posts['new_relation_title'] as $index => $title) {
                            $titleMaster = $l_posts['new_relation_title_master'][$index];
                            $titleSlave = $l_posts['new_relation_title_slave'][$index];

                            // Relation types need a title, master title and slave title.
                            if (empty($title) || empty($titleMaster) || empty($titleSlave)) {
                                continue;
                            }

                            $type = $l_posts['new_relation_type'][$index];
                            $direction = $l_posts['new_relation_direction'][$index];
                            $weighting = $l_posts['new_relation_weighting'][$index];

                            /*
                             * LF: Changed these lines since "$l_posts['new_relation_title']" can only contain NEW relation types.
                             * Otherwise it would have been possible to change core relation types.
                             */
                            $relationTypeModel->save(null, [
                                'title'       => $title,
                                'titleMaster' => $titleMaster,
                                'titleSlave'  => $titleSlave,
                                'type'        => $type,
                                'default'     => $direction,
                                'category'    => 'C__CATG__CUSTOM_FIELDS',
                                'editable'    => true,
                                'status'      => C__RECORD_STATUS__NORMAL,
                                'weightingId' => $weighting
                            ]);
                        }
                    }

                    isys_notify::success($this->language
                        ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                } catch (isys_exception_general $e) {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active($isAllowedToSupervise, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        $l_weighting_dao = isys_factory_cmdb_dialog_dao::get_instance('isys_weighting', $database);
        $l_weighting_dialog = isys_factory::get_instance('isys_smarty_plugin_f_dialog');
        $l_relation_types = $l_dao_relation->get_relation_types_as_array();
        $l_relation_type = [];

        isys_glob_sort_array_by_column($l_relation_types, 'title_lang');

        foreach ($l_relation_types as &$l_relation_type) {
            $l_weighting = $l_weighting_dao->get_data($l_relation_type['weighting']);

            $l_dialog_params = [
                'name'            => 'relation_weighting[' . $l_relation_type['id'] . ']',
                'p_strTable'      => 'isys_weighting',
                'p_strClass'      => 'input-mini',
                'p_strSelectedID' => $l_relation_type['weighting'],
                'order'           => 'isys_weighting__id',
                'p_bSort'         => false,
                'p_bDbFieldNN'    => true
            ];

            $l_relation_type['weighting'] = $l_weighting_dialog->navigation_edit($template, $l_dialog_params);
            $l_relation_type['weighting_text'] = $this->language
                ->get($l_weighting['isys_weighting__title']);
            $l_relation_type['type'] = $l_relation_type["type"];
        }

        $l_dialog_params = [
            'name'            => 'new_relation_weighting[]',
            'p_strTable'      => 'isys_weighting',
            'p_strClass'      => 'input-mini',
            'p_strSelectedID' => $l_relation_type['weighting'],
            'order'           => 'isys_weighting__id',
            'p_bSort'         => false,
            'p_bDbFieldNN'    => true
        ];

        $template
            ->assign('weighting_tpl', $l_weighting_dialog->navigation_edit($template, $l_dialog_params))
            ->assign('relation_types', $l_relation_types)
            ->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__RELATIONSHIP_TYPES'))
            ->include_template('contentbottomcontent', 'modules/system/relation_types.tpl');
    }

    /**
     *
     * @throws  isys_exception_cmdb
     */
    public function handle_roles_administration()
    {
        self::getAuth()->check(isys_auth::VIEW, 'GLOBALSETTINGS/ROLESADMINISTRATION');

        $isAllowedToSupervise = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/ROLESADMINISTRATION');

        $database = isys_application::instance()->container->get('database');
        $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($database);
        $l_dao_contact = isys_cmdb_dao_category_g_contact::instance($database);
        $l_navbar = isys_component_template_navbar::getInstance();

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];

        $l_condition = " AND isys_relation_type__const IS NULL OR isys_relation_type__category = " . $l_dao_relation->convert_sql_text('C__CATG__CONTACT');
        $l_relation_types = $l_dao_relation->get_relation_types_as_array(null, C__RELATION__IMPLICIT, $l_condition);

        switch ($l_navmode) {
            case C__NAVMODE__EDIT:
                self::getAuth()->check(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/ROLESADMINISTRATION');
                $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                self::getAuth()->check(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/ROLESADMINISTRATION');
                $l_new_contact_role_titles = $l_posts['new_role_title'];
                $l_new_contact_role_relations = $l_posts['new_role_relation_type'];
                $l_delete_contact_roles = $l_posts['delRoles'];
                $l_update_contact_roles = $l_posts['updRoles'];
                try {
                    $l_contact_tag_res = $l_dao_contact->get_contact_tag_data();
                    $l_contact_tag_arr = [];
                    while ($l_contact_tag_row = $l_contact_tag_res->get_row()) {
                        $l_contact_tag_arr[$l_contact_tag_row['isys_contact_tag__id']] = $l_contact_tag_row['isys_contact_tag__title'];
                    }

                    if (is_array($l_new_contact_role_titles) && count($l_new_contact_role_titles)) {
                        foreach ($l_new_contact_role_titles as $l_key => $l_role_title) {
                            if (!in_array($l_role_title, $l_contact_tag_arr)) {
                                $l_dao_contact->add_contact_tag($l_role_title, $l_new_contact_role_relations[$l_key]);
                            } elseif (($l_contact_tag_id = array_search($l_role_title, $l_contact_tag_arr))) {
                                $l_update_contact_roles .= ',' . $l_contact_tag_id;
                                $l_posts['role_relation_type'][$l_contact_tag_id] = $l_new_contact_role_relations[$l_key];
                                $l_posts['role_title'][$l_contact_tag_id] = $l_role_title;
                            }
                        }
                    }

                    if ($l_delete_contact_roles != '') {
                        $l_delete_contact_roles_as_array = explode(',', $l_delete_contact_roles);
                        $l_contacts_res = $l_dao_contact->get_contact_objects_by_tag(null, $l_delete_contact_roles_as_array);
                        while ($l_row = $l_contacts_res->get_row()) {
                            $l_relation_type = defined_or_default('C__RELATION_TYPE__USER');
                            $l_master = $l_row['isys_catg_contact_list__isys_obj__id'];
                            $l_slave = $l_row['isys_obj__id'];
                            $l_catg_contact_id = $l_row['isys_catg_contact_list__id'];
                            $l_catg_contact_rel_id = $l_row['isys_catg_contact_list__isys_catg_relation_list__id'];
                            $l_dao_relation->handle_relation($l_catg_contact_id, "isys_catg_contact_list", $l_relation_type, $l_catg_contact_rel_id, $l_master, $l_slave);
                        }
                        $l_dao_contact->delete_contact_tag($l_delete_contact_roles_as_array);
                    }

                    if ($l_update_contact_roles != '') {
                        $l_update_contact_roles = explode(',', $l_update_contact_roles);
                        $l_contact_update_relation_types = $l_posts['role_relation_type'];
                        $l_contact_update_title = $l_posts['role_title'];
                        foreach ($l_update_contact_roles as $l_contact_tag_id) {
                            $l_contact_tag_relation_type_id = $l_contact_update_relation_types[$l_contact_tag_id];
                            $l_contact_tag_title = (isset($l_contact_update_title[$l_contact_tag_id])) ? $l_contact_update_title[$l_contact_tag_id] : null;

                            $l_dao_contact->update_contact_tag($l_contact_tag_id, $l_contact_tag_title, $l_contact_tag_relation_type_id);
                        }
                    }
                    isys_notify::success($this->language
                        ->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                } catch (isys_exception_general $e) {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
            default:
                $l_navbar->set_active($isAllowedToSupervise, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        isys_module_request::get_instance()
            ->get_template()
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__ROLES_ADMINISTRATION'))
            ->assign('contact_roles', $l_dao_contact->get_contact_tag_data())
            ->assign('relation_types', $l_relation_types)
            ->include_template('contentbottomcontent', 'modules/system/roles_administration.tpl');
    }

    /**
     * Method for setting the relation weightings of all relations to the default setting.
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected function set_default_relation_priorities()
    {
        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $database = isys_application::instance()->container->get('database');
        $l_dao = isys_cmdb_dao_category_g_relation::instance($database);

        $l_relation_types = $l_dao->get_relation_types_as_array();

        foreach ($l_relation_types as $l_relation_type_id => $l_relation_type) {
            echo 'Setting the priority of all relations (of type "' . $this->language
                    ->get($l_relation_type['title']) . '") to ' . $l_relation_type['weighting'] . '<br />';

            $l_sql = 'UPDATE isys_catg_relation_list
				SET isys_catg_relation_list__isys_weighting__id = ' . $l_dao->convert_sql_id($l_relation_type['weighting']) . '
				WHERE isys_catg_relation_list__isys_relation_type__id = ' . $l_dao->convert_sql_id($l_relation_type_id) . ';';

            if (!($l_dao->update($l_sql) && $l_dao->apply_update())) {
                isys_notify::error($database->get_last_error_as_string(), ['sticky' => true]);
            }
        }
    }

    /**
     * Method for handline the cache actions.
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function processCacheDatabase()
    {
        isys_auth_system_tools::instance()->cache(isys_auth::VIEW);

        $database = isys_application::instance()->container->get('database');
        $session = isys_application::instance()->container->get('session');
        $dao = isys_component_dao::instance($database);

        if ($_GET['ajax']) {
            try {
                switch ($_GET['do']) {
                    case 'db_optimize':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        echo '<table class="listing"><thead><tr><th>Table</th><th>Operation</th><th>Status</th></tr></thead><tbody>';

                        $result = $dao->retrieve('SHOW TABLES;');

                        while ($row = $result->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW)) {
                            $table = $row[0];

                            $optimizeResult = $dao->retrieve("OPTIMIZE TABLE {$table};")->get_row();

                            echo "<tr><td>{$optimizeResult['Table']}</td><td>{$optimizeResult['Op']}</td><td>{$optimizeResult['Msg_text']}</td></tr>";
                        }

                        echo '</tbody></table>';
                        break;

                    case 'db_defrag':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        echo '<table class="listing"><thead><tr><th>Table</th><th>Operation</th><th>Status</th></tr></thead><tbody>';

                        $result = $dao->retrieve('SHOW FULL TABLES;');

                        while ($row = $result->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW)) {
                            [$tableName, $tableType] = $row;

                            if ($tableType === 'VIEW') {
                                continue;
                            }

                            if ($dao->update("ALTER TABLE {$tableName} ENGINE = INNODB;") && $dao->apply_update()) {
                                $defragResult = 'OK';
                            } else {
                                $defragResult = 'DEFRAG NOT POSSIBLE';
                            }

                            echo "<tr><td>{$tableName}</td><td>defrag</td><td>{$defragResult}</td></tr>";
                        }

                        echo '</tbody></table>';
                        break;

                    case 'db_location':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $daoLocation = isys_cmdb_dao_location::instance($database);
                        $daoLocation->_location_fix();
                        $stats = $daoLocation->getLocationFixStats();

                        echo $this->language->get('LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS_DONE') . '<br />';

                        foreach ($stats as $key => $counter) {
                            if ($counter) {
                                echo $key . ': ' . $counter . '<br />';
                            }
                        }

                        break;

                    case 'db_relation':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $l_dao = isys_cmdb_dao_relation::instance($database);

                        try {
                            // Delete dead relation objects
                            $l_dao->delete_dead_relations();

                            // Regenerate relation objects
                            $l_dao->regenerate_relations();
                            echo $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_SUCCESS');
                        } catch (Exception $e) {
                            echo $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_ERROR');
                        }

                        break;

                    case 'db_set_lists_to_wildcardfilter':
                    case 'db_set_lists_to_rowclick':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $l_sql = 'SELECT isys_obj_type_list__id, isys_obj_type_list__table_config, isys_obj__title, isys_obj_type__title
                            FROM isys_obj_type_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_obj_type_list__isys_obj__id
                            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj_type_list__isys_obj_type__id;';
                        $l_dao = isys_cmdb_dao::instance($database);
                        $l_res = $l_dao->retrieve($l_sql);
                        $counter = count($l_res);

                        if ($_GET["do"] === 'db_set_lists_to_wildcardfilter') {
                            $l_method = 'setFilterWildcard';
                            $l_lc_response = 'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER_RESPONSE';
                        } else {
                            $l_method = 'setRowClickable';
                            $l_lc_response = 'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_RESPONSE';
                        }

                        if ($counter) {
                            while ($l_row = $l_res->get_row()) {
                                if (is_string($l_row['isys_obj_type_list__table_config']) && isys_format_json::is_json_array($l_row['isys_obj_type_list__table_config'])) {
                                    $l_config = Config::fromJson($l_row['isys_obj_type_list__table_config']);

                                    $l_config->$l_method(true);

                                    $l_sql = 'UPDATE isys_obj_type_list
                                    SET isys_obj_type_list__table_config = ' . $l_dao->convert_sql_text($l_config->toJson()) . '
                                    WHERE isys_obj_type_list__id = ' . $l_dao->convert_sql_id($l_row['isys_obj_type_list__id']) . ';';

                                    $l_dao->update($l_sql);
                                } else {
                                    $counter--;
                                    echo $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_ERROR', [
                                            $this->language->get($l_row['isys_obj_type__title']),
                                            $l_row['isys_obj__title']
                                        ]) . '<br />';
                                }
                            }
                        }

                        echo $this->language->get($l_lc_response, $counter);

                        break;

                    case "db_properties":
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $l_dao = new isys_cmdb_dao_category_property($database);
                        $propertyResults = $l_dao->rebuild_properties();

                        foreach ($propertyResults as $key => $values) {
                            if ($key === 'missing_classes') {
                                continue;
                            }

                            echo '<strong>' . ucfirst($key) . '</strong><br /><br />';

                            $i = 0;
                            $results = [];

                            if (!is_array($values)) {
                                continue;
                            }

                            foreach ($values as $daoClassName => $propertyValues) {
                                /** @var isys_cmdb_dao_category $daoClassName */
                                try {
                                    $title = $this->language->get($daoClassName::instance($database)->getCategoryTitle());
                                } catch (Exception $e) {
                                    $title = '';
                                }

                                if ($daoClassName === 'isys_cmdb_dao_category_g_custom_fields') {
                                    $title = $this->language->get('LC__CMDB__CATG__CUSTOM_CATEGORY');
                                }

                                $propretyCount = count($propertyValues);
                                $suffix = "{$key} {$propretyCount} properties";

                                if (empty($title)) {
                                    echo "<strong><code>{$daoClassName}</code></strong> {$suffix}<br />";
                                } else {
                                    $results[mb_strtolower($title) . '-' . $i] = "<strong>{$title}</strong> (<code>{$daoClassName}</code>) {$suffix}";
                                }

                                $i++;
                            }

                            ksort($results);

                            echo implode('<br />', $results) . '<br /><br />';
                        }

                        if (is_array($propertyResults['missing_classes']) && count($propertyResults['missing_classes'])) {
                            echo '<strong>Missing classes</strong><br /><br />' . implode('<br />', $propertyResults['missing_classes']);
                        }

                        break;

                    case 'db_cleanup_objects':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $l_count = 0;

                        try {
                            switch ($_GET['param']) {
                                default:
                                case C__RECORD_STATUS__BIRTH:
                                case C__RECORD_STATUS__ARCHIVED:
                                case C__RECORD_STATUS__DELETED:
                                    // Method for cleaning up the objects.
                                    $l_count = $this->cleanup_objects($_GET['param']);
                                    break;

                                case 'all':
                                    $l_count = $this->cleanup_objects(C__RECORD_STATUS__BIRTH) +
                                        $this->cleanup_objects(C__RECORD_STATUS__ARCHIVED) +
                                        $this->cleanup_objects(C__RECORD_STATUS__DELETED);
                            }
                        } catch (Exception $e) {
                            echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                        }

                        echo sprintf($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DONE'), $l_count);

                        echo '<hr class="mb5 mt5" />';
                        break;

                    case 'db_list_objects':
                        $l_objects = [];

                        try {
                            switch ($_GET['param']) {
                                default:
                                case C__RECORD_STATUS__BIRTH:
                                case C__RECORD_STATUS__ARCHIVED:
                                case C__RECORD_STATUS__DELETED:
                                    // Method for cleaning up the objects.
                                    $l_objects = $this->list_objects($_GET['param']);
                                    break;

                                case 'all':
                                    $l_objects = array_merge(
                                        $this->list_objects(C__RECORD_STATUS__BIRTH),
                                        $this->list_objects(C__RECORD_STATUS__ARCHIVED),
                                        $this->list_objects(C__RECORD_STATUS__DELETED)
                                    );
                            }
                        } catch (Exception $e) {
                            echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                        }

                        if (count($l_objects) > 0) {
                            echo '<ul><li>' . implode('</li><li>', $l_objects) . '</li></ul>';
                        } else {
                            echo isys_tenantsettings::get('gui.empty_value', '-');
                        }

                        break;

                    case 'db_cleanup_categories':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $l_count = 0;

                        try {
                            switch ($_GET['param']) {
                                default:
                                case C__RECORD_STATUS__BIRTH:
                                case C__RECORD_STATUS__ARCHIVED:
                                case C__RECORD_STATUS__DELETED:
                                    // Method for cleaning up the category content.
                                    $l_count = $this->cleanup_categories($_GET['param']);
                                    break;

                                case 'all':
                                    $l_count = $this->cleanup_categories(C__RECORD_STATUS__BIRTH) +
                                        $this->cleanup_categories(C__RECORD_STATUS__ARCHIVED) +
                                        $this->cleanup_categories(C__RECORD_STATUS__DELETED);
                            }
                        } catch (Exception $e) {
                            echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                        }

                        echo sprintf($this->language->get("LC__SYSTEM__CLEANUP_OBJECTS_DONE_CATEGORIES"), $l_count);

                        break;

                    case 'db_cleanup_dialog':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $count = 0;

                        try {
                            switch ($_GET['param']) {
                                default:
                                case C__RECORD_STATUS__BIRTH:
                                case C__RECORD_STATUS__ARCHIVED:
                                case C__RECORD_STATUS__DELETED:
                                    // Method for cleaning up the category content.
                                    $count = $this->cleanup_dialog($_GET['param']);
                                    echo sprintf($this->language->get("LC__SYSTEM__CLEANUP_DIALOG_DONE"), $count);
                                    break;
                                case 'all':
                                    $count = $this->cleanup_dialog(C__RECORD_STATUS__BIRTH) +
                                        $this->cleanup_dialog(C__RECORD_STATUS__ARCHIVED) +
                                        $this->cleanup_dialog(C__RECORD_STATUS__DELETED);
                                    echo sprintf($this->language->get("LC__SYSTEM__CLEANUP_DIALOG_DONE"), $count);
                                    break;
                                case C__RECORD_STATUS__NORMAL:
                                    throw new Exception('Cannot delete normal dialog entries.');
                            }
                        } catch (Exception $e) {
                            echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                        }
                        break;

                    case 'db_recycle_dialog':
                        self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
                        $count = 0;

                        try {
                            $count = $this->recycle_dialog();
                            echo sprintf($this->language->get("LC__SYSTEM__RECYCLE_DIALOG_DONE"), $count);
                        } catch (Exception $e) {
                            echo '<div class="error p5 m5">' . $e->getMessage() . '</div>';
                        }
                        break;

                    case 'cleanup_other':
                        $this->cleanup_other($_GET['param']);
                        break;

                    case 'db_cleanup_cat_assignments':
                        $this->cleanup_category_assignments();
                        break;

                    case 'db_cleanup_duplicate_sv_entries':
                        (new DeleteDuplicateSingleValueEntries())->process();
                        break;

                    case 'db_cleanup_unassigned_relations':
                        $this->cleanup_unassigned_relations();
                        break;

                    case 'db_renew_relation_titles':
                        $this->renew_relation_titles();
                        break;

                    case 'db_refill_empty_sysids':
                        $this->refill_empty_sysids();
                        break;

                    case 'db_set_default_relation_priorities':
                        $this->set_default_relation_priorities();
                        break;

                    case 'db_cleanup_colors':
                        // @see ID-10820
                        (new ReformatColorValues())->process();
                        break;

                    case 'cache_system':
                        global $g_dirs;
                        self::getAuth()->check(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE');

                        // Removing isys_cache values
                        isys_cache::keyvalue()->flush();

                        $l_deleted = $l_undeleted = 0;

                        // Deleting contents of the temp directory.
                        isys_glob_delete_recursive($g_dirs["temp"], $l_deleted, $l_undeleted, (ENVIRONMENT === 'development'));
                        echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', $l_deleted . ' System');

                        isys_component_signalcollection::get_instance()->emit('system.afterFlushSystemCache');

                        break;
                    case 'cache_auth':
                        self::getAuth()->check(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE');
                        // Invalidate auth cache
                        $affectedCacheFiles = isys_auth::invalidateCache();

                        echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_CACHE_MESSAGE', count($affectedCacheFiles));
                        break;

                    case 'cache_quick_info':
                        try {
                            self::getAuth()->check(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE');

                            if (!QuickInfo::instance($database)->truncate()) {
                                throw new Exception();
                            }

                            echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE_MESSAGE');
                        } catch (Exception $e) {
                            echo $this->language->get('LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE_MESSAGE_FAILURE') . ' ' . $e->getMessage();
                        }
                        break;

                    case 'search_index':
                        try {
                            self::getAuth()->check(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

                            $searchEngine = new Mysql($database);

                            $collector = new CategoryCollector($database, [], []);

                            $dispatcher = isys_application::instance()->container->get('event_dispatcher');

                            // @see  ID-6535  Don't output anything because it will take double the time.
                            $manager = new Manager($searchEngine, $dispatcher);
                            $manager->addCollector($collector, 'categoryCollector');
                            $manager->clearIndex();
                            $manager->generateIndex();

                            echo 'Search index was created!';
                        } catch (Exception $e) {
                            echo 'An error occured: ' . $e->getMessage();
                        }
                        break;
                    case 'refreshReports':
                        try {
                            self::getAuth()->check(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE');

                            $refresher = new ReportsRefresher();
                            $refresher->refresh();
                            $statistics = $refresher->getStatistics();
                            $amountReports = $statistics->getCountRefreshedReports();
                            $totalTime = $statistics->getTotalTime();

                            echo "It took {$totalTime} seconds to refresh {$amountReports} reports.";
                        } catch (Throwable $e) {
                            echo 'An error occured: ' . $e->getMessage();
                        }
                        break;
                }
            } catch (Throwable $e) {
                echo 'An error occured: ' . $e->getMessage();
            }

            die;
        }

        $rightsSupervisor = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');
        $rightsExecute = self::getAuth()->is_allowed_to(isys_auth::EXECUTE, 'SYSTEMTOOLS/CACHE');

        // Cache buttons.
        $l_cache_buttons = [
            'LC__SETTINGS__SYSTEM__FLUSH_ALL_CACHE'        => [
                'onclick'  => 'window.flush_cache(true);',
                'style'    => 'background-color:#eee;',
                'css'      => 'mb15',
                'hasRight' => $rightsExecute
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_SYS_CACHE'        => [
                'onclick'  => "window.flush_database('cache_system', '', this);",
                'css'      => 'cache-button',
                'hasRight' => $rightsExecute
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_TPL_CACHE'        => [
                'onclick'  => "window.flush_cache('IDOIT_DELETE_TEMPLATES_C', this);",
                'css'      => 'cache-button',
                'hasRight' => $rightsExecute
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_AUTH_CACHE'       => [
                'onclick'  => "window.flush_database('cache_auth', '', this);",
                'css'      => 'cache-button',
                'hasRight' => $rightsExecute
            ],
            'LC__SETTINGS__SYSTEM__FLUSH_QUICK_INFO_CACHE' => [
                'onclick'  => "window.flush_database('cache_quick_info', '', this);",
                'css'      => 'cache-button',
                'hasRight' => $rightsExecute
            ]
        ];

        // Database buttons.
        $l_database_buttons = [
            'LC__SYSTEM__CACHE_DB__TABLE_OPTIMIZATION'                     => [
                'onclick' => "window.flush_database('db_optimize', '" . $this->language->get('LC__SYSTEM__CACHE_DB__TABLE_OPTIMIZATION_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__TABLE_DEFRAG'                           => [
                'onclick' => "window.flush_database('db_defrag', '" . $this->language->get('LC__SYSTEM__CACHE_DB__TABLE_DEFRAG_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS'                    => [
                'onclick' => "window.flush_database('db_location', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CALCULATE_LOCATIONS_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_CATEGORY_ASSIGNMENTS'           => [
                'onclick' => "window.flush_database('db_cleanup_cat_assignments', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_CATEGORY_ASSIGNMENTSCONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__RENEW_PROPERTIES'                       => [
                'onclick' => "window.flush_database('db_properties', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RENEW_PROPERTIES_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_DUPLICATE_SINGLE_VALUE_ENTRIES' => [
                'onclick' => "window.flush_database('db_cleanup_duplicate_sv_entries', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_DUPLICATE_SINGLE_VALUE_ENTRIES_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_UNASSIGNED_RELATIONS'           => [
                'onclick' => "window.flush_database('db_cleanup_unassigned_relations', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_UNASSIGNED_RELATIONS_CONFIRMATION') . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__RENEW_RELATION_TITLES'                  => [
                'onclick' => "window.flush_database('db_renew_relation_titles', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RENEW_RELATION_TITLES_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REFILL_EMPTY_SYSIDS'                    => [
                'onclick' => "window.flush_database('db_refill_empty_sysids', '" . $this->language->get('LC__SYSTEM__CACHE_DB__REFILL_EMPTY_SYSIDS_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__RESET_RELATION_PRIORITIES'              => [
                'onclick' => "window.flush_database('db_set_default_relation_priorities', '" . $this->language->get('LC__SYSTEM__CACHE_DB__RESET_RELATION_PRIORITIES_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS'                   => [
                'onclick' => "window.flush_database('db_relation', '" . $this->language->get('LC__SYSTEM__CACHE_DB__REGENERATE_RELATIONS_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK'             => [
                'onclick' => "window.flush_database('db_set_lists_to_rowclick', '" . $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_ROW_CLICK_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__CLEANUP_COLORS'                         => [
                'onclick' => "window.flush_database('db_cleanup_colors', '" . $this->language->get('LC__SYSTEM__CACHE_DB__CLEANUP_COLORS_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
        ];

        $l_dao = isys_cmdb_dao::factory($database);

        $l_born = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ' AND isys_obj__undeletable = 0;')
            ->get_row();
        $l_archived = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ' AND isys_obj__undeletable = 0;')
            ->get_row();
        $l_deleted = $l_dao
            ->retrieve('SELECT COUNT(*) AS count FROM isys_obj WHERE isys_obj__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ' AND isys_obj__undeletable = 0;')
            ->get_row();

        // The aliases are used to display translated headings.
        $l_query = 'SELECT isys_obj__id AS \'ID\', isys_obj_type__title AS \'LC__REPORT__FORM__OBJECT_TYPE###1\', isys_obj__title AS \'LC__UNIVERSAL__TITLE###1\', ' .
            'isys_obj__created AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_created::isys_obj__created::LC__TASK__DETAIL__WORKORDER__CREATION_DATE\', ' .
            'isys_obj__updated AS \'isys_cmdb_dao_category_g_global::dynamic_property_callback_changed::isys_obj__updated::LC__CMDB__LAST_CHANGE\' ' . 'FROM isys_obj ' .
            'INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id ' . 'WHERE isys_obj__undeletable = 0 AND isys_obj__status = ';

        $objectButtons = [
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH'    => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH_CONFIRMATION', $l_born['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__BIRTH) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:85%;',
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED' => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED_CONFIRMATION', $l_archived['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:85%;',
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED'  => [
                'onclick' => "window.flush_objects(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED_CONFIRMATION', $l_deleted['count'])) . "', this);",
                'query'   => $l_query . $l_dao->convert_sql_int(C__RECORD_STATUS__DELETED) . ';',
                'css'     => 'fl mr5',
                'style'   => 'width:85%;',
                'hasRight' => $rightsSupervisor
            ]
        ];

        if (!$this->isPro) {
            unset(
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['query'],
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['style'],
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['query'],
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['style'],
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['query'],
                $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['style']
            );

            $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_BIRTH']['css'] = 'btn-block';
            $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_ARCHIVED']['css'] = 'btn-block';
            $objectButtons['LC__SYSTEM__CACHE_DB__REMOVE_OBJECTS_DELETED']['css'] = 'btn-block';
        }

        $categoryButtons = [
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_BIRTH'    => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_BIRTH_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_ARCHIVED' => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_ARCHIVED_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_DELETED'  => [
                'onclick' => "window.flush_categories(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_CATEGORIES_DELETED_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ]
        ];

        $dialogButtons = [
            'LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_BIRTH'    => [
                'onclick' => "window.flush_dialog(" . C__RECORD_STATUS__BIRTH . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_BIRTH_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_ARCHIVED' => [
                'onclick' => "window.flush_dialog(" . C__RECORD_STATUS__ARCHIVED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_ARCHIVED_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_DELETED'  => [
                'onclick' => "window.flush_dialog(" . C__RECORD_STATUS__DELETED . ", '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__REMOVE_DIALOG_DELETED_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__SET_ALL_DIALOG_ENTRIES_NORMAL'  => [
                'onclick' => "window.normal_dialog('" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_DIALOG_ENTRIES_NORMAL_CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ]
        ];

        $otherButtons = [
            'LC__MODULE__SEARCH__START_INDEXING'                         => [
                'onclick' => 'window.search_index(this);',
                'hasRight' => $rightsSupervisor
            ],
            'LC__MODULE__REPORT__REFRESH_REPORTS' => [
                'onclick' => 'window.refreshReports(this);',
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__MIGRATE_DATABASE_OBJECTS_TO_CATEGORY' => [
                'onclick' => "window.flush_other('MigrateDbObjectsToCategory', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__MIGRATE_DATABASE_OBJECTS_TO_CATEGORY_CONFIRM')) . "', this);",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER'      => [
                'onclick' => "window.flush_database('db_set_lists_to_wildcardfilter', '" . $this->language->get('LC__SYSTEM__CACHE_DB__SET_ALL_LISTS_TO_WILDCARDFILTER_CONFIRMATION') . "', this)",
                'hasRight' => $rightsSupervisor
            ],
            'LC__SYSTEM__CACHE_DB__MIGRATE_OLD_OBJECT_TYPE_ICONS'        => [
                'onclick' => "window.flush_other('MigrateOldObjectTypeIcons', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__MIGRATE_OLD_OBJECT_TYPE_ICONS_CONFIRM')) . "', this);",
                'hasRight' => $rightsSupervisor
            ]
        ];

        // Create a DAO instance with the system database.
        $dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database_system'));
        $tenantId = $dao->convert_sql_id($session->get_mandator_id());

        $encryptedPassword = $dao
            ->retrieve("SELECT * FROM isys_mandator WHERE isys_mandator__id = {$tenantId} LIMIT 1;")
            ->get_row_value('isys_mandator__db_password');

        // Only display this button if the password has not been encrypted yet.
        if (empty($encryptedPassword)) {
            $otherButtons['LC__SYSTEM__CACHE_DB__TENANT_DATABASE_ENCRYPTION'] = [
                'onclick' => "window.processSecureAction('TenantDatabaseEncryption', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TENANT_DATABASE_ENCRYPTION__CONFIRMATION')) . "', this);",
                'hasRight' => $rightsSupervisor
            ];
        }

        if (isys_module_manager::instance()->is_active('check_mk')) {
            $otherButtons['LC__SYSTEM__CACHE_DB__TRUNCATE_EXPORTED_CHECK_MK_TAGS'] = [
                'onclick' => "window.flush_other('DeleteExportedCheckMkTags', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TRUNCATE_EXPORTED_CHECK_MK_TAGS_CONFIRM')) . "', this);",
                'hasRight' => $rightsSupervisor
            ];
        }

        if (isys_module_manager::instance()->is_active('custom_fields')) {
            $otherButtons['LC__SYSTEM__CACHE_DB__TRUNCATE_ORPHANED_CUSTOM_CATEGORY_DATA'] = [
                'onclick' => "window.flush_other('RemoveOrphanedCustomCategoryData', '" . isys_glob_htmlentities($this->language->get('LC__SYSTEM__CACHE_DB__TRUNCATE_ORPHANED_CUSTOM_CATEGORY_DATA_CONFIRM')) . "', this);",
                'hasRight' => $rightsSupervisor
            ];
        }

        $this->m_userrequest->get_template()
            ->activate_editmode()
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__REPAIR_AND_CLEANUP'))
            ->assign('report_sql_path', isys_application::instance()->app_path . '/src/classes/modules/report/templates/report.js')
            ->assign('cache_buttons', $l_cache_buttons)
            ->assign('database_buttons', $l_database_buttons)
            ->assign('object_buttons', $objectButtons)
            ->assign('category_buttons', $categoryButtons)
            ->assign('dialog_buttons', $dialogButtons)
            ->assign('other_buttons', $otherButtons)
            ->include_template('contentbottomheader', '')
            ->include_template('contentbottomcontent', 'content/sys_cache.tpl');
    }

    /**
     * Handle object match profiles
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_object_matching()
    {
        $l_dbID = isys_glob_get_param("profileID");
        $database = isys_application::instance()->container->get('database');

        try {
            switch ($_POST[C__GET__NAVMODE]) {
                case C__NAVMODE__NEW:
                    self::getAuth()->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');
                    $this->object_matching_profile();
                    $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                    break;

                case C__NAVMODE__SAVE:
                    self::getAuth()->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');
                    $this->object_matching_profile($l_dbID);
                    $this->object_matching_profile_list();
                    break;

                case C__NAVMODE__PURGE:
                    self::getAuth()->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');
                    idoit\Module\Cmdb\Model\Matcher\MatchDao::instance($database)->deleteMatchProfile($_POST["id"]);
                    $this->object_matching_profile_list();
                    break;

                default:
                    if ($l_dbID != null) {
                        $this->object_matching_profile($l_dbID);
                    } else {
                        if ($_POST["id"] != null) {
                            $this->object_matching_profile($_POST["id"][0]);
                        } else {
                            $this->object_matching_profile_list();
                        }
                    }
                    break;
            }
        } catch (isys_exception_general $e) {
            isys_notify::warning($e->getMessage());
            $this->object_matching_profile_list();
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
            $this->object_matching_profile_list();
        }
    }

    /**
     * Show object match profile list
     *
     * @throws Exception
     * @throws isys_exception_database
     * @throws isys_exception_general
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function object_matching_profile_list()
    {
        self::getAuth()->check(isys_auth::VIEW, 'OBJECT_MATCHING');
        $isAllowedToSupervise = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');

        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');

        $l_navbar = isys_component_template_navbar::getInstance();

        $l_list = new isys_component_list();

        $l_list_headers = [
            "isys_obj_match__title"      => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__TITLE"),
            "isys_obj_match__bits"       => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS"),
            "isys_obj_match__min_match"  => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH"),
            "isys_obj_match__filter"     => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER"),
            "isys_obj_match__filter_min" => $this->language->get("LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM"),
        ];

        $l_list_data = [];

        $l_profiles = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance($database)
            ->getMatchProfiles();
        $l_count = $l_profiles->num_rows();

        if ($l_count > 0) {
            while ($l_profile = $l_profiles->get_row()) {
                $l_profile['isys_obj_match__bits'] = implode(', ', $this->get_object_matching_bits($l_profile['isys_obj_match__bits'], true));
                $l_profile['isys_obj_match__filter'] = implode(', ', $this->get_object_matching_bits($l_profile['isys_obj_match__filter'], true));
                $l_list_data[] = $l_profile;
            }

            $l_list->set_data($l_list_data);
            $l_list->config($l_list_headers, '?' . C__GET__MODULE_ID . '=' . defined_or_default('C__MODULE__SYSTEM') . '&' . C__GET__TREE_NODE . '=' . $_GET[C__GET__TREE_NODE] .
                "&" . C__GET__SETTINGS_PAGE . "=object_matching&profileID=[{isys_obj_match__id}]", "[{isys_obj_match__id}]");

            if ($l_list->createTempTable()) {
                $template->assign("objectTableList", $l_list->getTempTableHtml());
            }
        } else {
            $template->assign("objectTableList", '<div class="p10">' . $this->language->get('LC__CMDB__FILTER__NOTHING_FOUND_STD') . '</div>');
        }

        $l_navbar
            ->set_active($isAllowedToSupervise, C__NAVBAR_BUTTON__NEW)
            ->set_active($isAllowedToSupervise && $l_count > 0, C__NAVBAR_BUTTON__PURGE)
            ->set_active($isAllowedToSupervise && $l_count > 0, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

        $template
            ->assign("content_title", $this->language->get('LC__IMPORT_MATCHING_PROFILES'))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');
    }

    /**
     * Show object match profile
     *
     * @param null $p_profileID
     *
     * @return bool
     * @throws isys_exception_auth
     * @throws isys_exception_dao
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function object_matching_profile($p_profileID = null)
    {
        self::getAuth()->check(isys_auth::VIEW, 'OBJECT_MATCHING');

        $isAllowedToSupervise = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');

        if ($p_profileID == 1) {
            isys_notify::warning($this->language->get('LC__MODULE__SYSTEM__OBJECT_MATCHING__DEFAULT_PROFILE_IS_NOT_EDITABLE', $p_profileID));
        }

        $l_navbar = isys_component_template_navbar::getInstance();
        $database = isys_application::instance()->container->get('database');

        $l_posts = isys_module_request::get_instance()
            ->get_posts();
        $l_navmode = $l_posts[C__GET__NAVMODE];
        $l_rules = $l_identifier_data = [];
        $l_selected_bit = null;

        // Navmode-Handling
        switch ($l_navmode) {
            case C__NAVMODE__SAVE:
                self::getAuth()->check(isys_auth::SUPERVISOR, 'OBJECT_MATCHING');
                $l_matchDAO = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance($database);
                if ($p_profileID) {
                    $l_matchDAO->saveMatchProfile(
                        $p_profileID,
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS__selected_box'] ?: []),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER__selected_box'] ?: []),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM']
                    );
                    $l_id = $p_profileID;
                } else {
                    $l_id = $l_matchDAO->createMatchProfile(
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS__selected_box'] ?: []),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH'],
                        array_sum($_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER__selected_box'] ?: []),
                        $_POST['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM']
                    );
                }

                if ($l_id) {
                    isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));

                    return true;
                } else {
                    isys_notify::error($this->language->get('LC__INFOBOX__DATA_WAS_NOT_SAVED'), ['sticky' => true]);

                    return false;
                }
                break;
            case C__NAVMODE__NEW:
            case C__NAVMODE__EDIT:
                if ($p_profileID == 1) {
                    $l_navbar->set_active(false, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(false, C__NAVBAR_BUTTON__CANCEL);
                } else {
                    $l_navbar->set_active($isAllowedToSupervise, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                }
                $l_navbar->set_visible($isAllowedToSupervise, C__NAVBAR_BUTTON__SAVE)
                    ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);

                break;

            default:
                if ($p_profileID == 1) {
                    $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT);
                } else {
                    $l_navbar->set_active($isAllowedToSupervise, C__NAVBAR_BUTTON__EDIT);
                }

                $l_navbar->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                break;
        }

        $l_selected_filter = 0;

        if ($p_profileID && $l_data = idoit\Module\Cmdb\Model\Matcher\MatchDao::instance($database)->getMatchProfileById($p_profileID)) {
            $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE']['p_strValue'] = $l_data['isys_obj_match__title'];
            $l_selected_bit = $l_data['isys_obj_match__bits'];
            $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']['p_strSelectedID'] = $l_data['isys_obj_match__min_match'];
            $l_selected_filter = $l_data['isys_obj_match__filter'];
            $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM']['p_strSelectedID'] = $l_data['isys_obj_match__filter_min'];
        }

        $l_objectMatchingTypes = $this->get_object_matching_bits($l_selected_bit);
        $l_minMatch = [];
        $l_counter = 1;
        foreach ($l_objectMatchingTypes as $l_objectMatchType) {
            if ($l_objectMatchType['sel'] === true) {
                $l_minMatch[$l_counter] = $l_counter;
                $l_counter++;
            }
        }

        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH']['p_arData'] = $l_minMatch;
        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS']['p_arData'] = $l_objectMatchingTypes;

        $newObjectFilter = $this->get_object_matching_bits(
            $l_selected_filter,
            false,
            ['objectTitle', 'modelSerial', 'mac', 'ipAddress', 'hostname']
        );
        $filterMinimum = [];
        $counter = 1;
        foreach ($newObjectFilter as $item) {
            if ($item['sel'] === true) {
                $filterMinimum[$counter] = $counter;
                $counter++;
            }
        }

        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM']['p_arData'] = $filterMinimum;
        $l_rules['C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER']['p_arData'] = $newObjectFilter;

        $filterSelected = false;
        if ($l_selected_filter && $l_navmode == C__NAVMODE__EDIT) {
            $filterSelected = true;
        }

        isys_application::instance()->container->get('template')
            ->assign('profileID', $p_profileID)
            ->assign('content_title', $this->language->get('LC__IMPORT_MATCHING_PROFILE'))
            ->assign('filterSelected', $filterSelected)
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'modules/system/object_matching.tpl');
    }

    /**
     * Helper Method which gets the Object matching bits for a dialog list
     *
     * @param int   $p_selected_bit
     * @param bool  $p_only_selection
     * @param array $p_filter
     *
     * @return array
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function get_object_matching_bits($p_selected_bit = null, $p_only_selection = false, array $p_filter = [])
    {
        $l_ciIdentifiers = new \idoit\Module\Cmdb\Model\Matcher\Ci\CiIdentifiers(null);
        $l_identifiers = $l_ciIdentifiers->getIdentifiers();
        $l_identifier_data = [];
        if (count($l_identifiers)) {
            foreach ($l_identifiers as $l_identifier) {
                // skip identifiers which are not in the filter
                if ($p_filter && !in_array($l_identifier::KEY, $p_filter)) {
                    continue;
                }

                $l_sel = false;
                $l_identifier_bit = $l_identifier::getBit();
                $l_identifier_title = $this->language->get($l_identifier->getTitle());

                if ($p_selected_bit & $l_identifier_bit) {
                    $l_sel = true;
                }

                if ($p_only_selection) {
                    if ($l_sel) {
                        $l_identifier_data[$l_identifier_bit] = $l_identifier_title;
                    }
                } else {
                    $l_identifier_data[] = [
                        'id'  => $l_identifier_bit,
                        'val' => $l_identifier_title . ($p_filter ? '' : ' (' . implode(', ', $l_identifier->getUsableIn()) . ')'),
                        'sel' => $l_sel
                    ];
                }
            }
        }

        return $l_identifier_data;
    }

    /**
     * Handles h-inventory
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function handle_hinventory()
    {
        try {
            $this->handle_hinventory_config();
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }
    }

    /**
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function handle_customizeObjectBrowser()
    {
        $setting = new CustomizeObjectBrowser(
            isys_application::instance()->container->get('template'),
            isys_application::instance()->container->get('database'),
            $this->language
        );

        // Initiate the "render page" action with the current navmode.
        $setting->renderPage((int) ($_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE]));
    }

    private function handle_customCounter()
    {
        $setting = new CustomCounter(
            isys_application::instance()->container->get('template'),
            isys_application::instance()->container->get('database'),
            $this->language
        );

        $setting->renderPage((int) ($_POST[C__GET__NAVMODE] ?: $_GET[C__GET__NAVMODE]));
    }

    /**
     * Handles h-inventory config page
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     * @throws Exception
     */
    private function handle_hinventory_config()
    {
        $l_navbar = isys_component_template_navbar::getInstance();

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__EDIT:
                self::getAuth()->check(isys_auth::SUPERVISOR, 'HINVENTORY/CONFIG');
                $l_navbar
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;

            case C__NAVMODE__SAVE:
                // Save config page
                self::getAuth()->check(isys_auth::SUPERVISOR, 'HINVENTORY/CONFIG');
                isys_tenantsettings::set('import.hinventory.default_status', (int) $_POST['C__SETTING__STATUS__IMPORT']);
                isys_tenantsettings::set('import.hinventory.object-matching', $_POST['obj_match']);
                isys_tenantsettings::force_save();
                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                // no break

            default:
                self::getAuth()->check(isys_auth::VIEW, 'HINVENTORY/CONFIG');
                $l_navbar->set_active(self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'HINVENTORY/CONFIG'), C__NAVBAR_BUTTON__EDIT);
                break;
        }

        $rules['C__SETTING__STATUS__IMPORT']['p_arData']        = $this->getStatus();
        $rules['C__SETTING__STATUS__IMPORT']['p_strSelectedID'] = isys_tenantsettings::get(
            'import.hinventory.default_status',
            defined_or_default('C__CMDB_STATUS__IN_OPERATION')
        );

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__HINVENTORY'))
            ->assign('object_match_id', isys_tenantsettings::get('import.hinventory.object-matching', 1))
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
            ->include_template('contentbottomcontent', 'modules/system/hinventory_config.tpl');
    }

    /**
     * @return isys_auth_system
     */
    public static function getAuth()
    {
        return isys_auth_system::instance();
    }

    public function getStatus(): array
    {
        $status    = [];
        $statusDao = (
            new isys_cmdb_dao_status(
                isys_application::instance()->container->get('database')
            )
        )->get_cmdb_status();
        while ($row = $statusDao->get_row()) {
            $status[$row['isys_cmdb_status__id']] = $this->language->get($row['isys_cmdb_status__title']);
        }
        return $status;
    }

    /**
     * @return void
     */
    public static function registerRouting(): void
    {
        isys_application::instance()->container->get('routes')
            ->addCollection((new PhpFileLoader(new FileLocator(__DIR__)))->load('config/routes.php'));
    }
}
