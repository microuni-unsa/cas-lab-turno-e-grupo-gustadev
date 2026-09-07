<?php

use idoit\Component\ConstantManager;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Helper\Unserialize;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;

/**
 * i-doit
 *
 * System settings.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_system_settings extends isys_module implements isys_module_interface
{
    public const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    public const DISPLAY_IN_SYSTEM_MENU = true;
    public const TENANT_WIDE            = 'Tenant-wide';
    public const SYSTEM_WIDE            = 'System-wide';
    public const USER                   = 'User';

    // @see ID-11929 Define a list of allowed settings.
    private const CLOUD_ALLOWED_EXPERT_SETTINGS = [
        'auth.logging',
        'auth.use-in-cmdb-explorer',
        'auth.use-in-cmdb-explorer-service-browser',
        'auth.use-in-object-browser',
        'auth.use-in-file-browser',
        'auth.use-in-location-tree',
        'cache.default-expiration-time',
        'ckeditor.font_names',
        'cmdb.limits.order-threshhold',
        'cmdb.limits.obj-browser.objects-in-viewmode',
        'cmdb.limits.object-table-columns',
        'cmdb.limits.port-lists-layer2',
        'cmdb.limits.port-lists-vlans',
        'cmdb.limits.port-overview-default-vlan-only',
        'cmdb.multiedit.text-size-in-px',
        'cmdb.objtype.OBJECT\_TYPE\_ID.auto-inventory-no',
        'cmdb.only-show-ranked-entries-as-such',
        'cmdb.quickpurge',
        'cmdb.skip-unidirectional-connection-ranking',
        'cmdb.unique.hostname',
        'cmdb.unique.ip-address',
        'cmdb.unique.layer-2-net',
        'cmdb.unique.object-title',
        'gui.empty_value',
        'gui.nat-sort.port-list',
        'jdisc.import-unidentified-devices',
        'maxlength.dialog_plus',
        'maxlength.list.placeholder',
        'maxlength.location.objects',
        'maxlength.location.path',
        'maxlength.object.lists',
        'search.global.autostart-deep-search',
        'security.passwort.minlength',
        'qrcode.config',
        // User settings:
        'gui.leftcontent.width',
        'workflows.max-checklist-entries',
        'gui.login.display',
    ];

    private static $internalConfigurationKeys = [
        'plain' => [
            'system.start-of-end',
            'cmdb.objtype-',
            'cmdb.object-browser.C__',
            'cmdb.base-object-list.config.C__',
            'cmdb.base-object-table.config.C__',
            'cmdb.default-object-table.sql.C__',
            'cmdb.multilist-table.config.',
            'cmdb.multilist-table.default-config.',
            'cmdb.default-object-list.config.',
        ],
        'regexp' => [
            '~cmdb\.obj-(\d+)-(\d+)\.table-columns~',
            '~cmdb\.obj-(\d+)-(\d+)-(\d+)\.table-columns~',
            '~cmdb\.obj-([a-zA-Z0-9_]+)\.table-columns~',
            '~cmdb\.objtype\.([a-zA-Z0-9_]+)\.specific-cat-position~',
        ]
    ];

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /** @var isys_component_template_language_manager */
    protected $language;

    /**
     * Module constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = isys_application::instance()->container->get('language');
    }

    /**
     * Callback function for construction of breadcrumb navigation.
     *
     * @param $p_gets
     *
     * @return array|null
     * @throws isys_exception_database
     */
    public function breadcrumb_get(&$p_gets)
    {
        $page = $p_gets[C__GET__SETTINGS_PAGE];

        $tenantSettingsMapping = [
            'tenant-settings' => $this->language->get('LC__MODULE__SYSTEM__OVERVIEW', [isys_application::instance()->tenant->name]),
            'expert-settings' => $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__EXPERT_SETTINGS'),
        ];

        if (isset($tenantSettingsMapping[$page])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS', [isys_application::instance()->tenant->name]) => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__SETTINGS_PAGE => 'system-overview'
                    ]
                ],
                [
                    $tenantSettingsMapping[$page] => null
                ]
            ];
        }

        if ($page === 'cmdb-status') {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__PREDEFINED_CONTENT') => [
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__SYSTEM_SETTINGS,
                        C__GET__SETTINGS_PAGE => 'cmdb-status'
                    ]
                ],
                [
                    $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS') => null
                ]
            ];
        }

        return null;
    }

    /**
     * @return $this
     * @throws isys_exception_auth
     * @throws isys_exception_database
     */
    public function start()
    {
        $l_navbar = isys_component_template_navbar::getInstance();
        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_posts = isys_module_request::get_instance()->get_posts();
        $database = isys_application::instance()->container->get('database');

        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param('navMode') == C__NAVMODE__EDIT) {
            $l_navbar->set_selected(true, C__NAVBAR_BUTTON__EDIT);
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE);
            $l_navbar->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        /**
         * @desc handle navmode actions
         */
        if (isset($l_posts['navMode'])) {
            switch ($l_posts['navMode']) {
                case C__NAVMODE__SAVE:
                    switch ($_GET[C__GET__SETTINGS_PAGE]) {
                        case 'cmdb-status':
                            isys_tenantsettings::set('system.mydoit.show_filter', $l_posts['C__SETTING__STATUS__SHOW_FILTER']);

                            // Save status.
                            $l_status_dao = new isys_cmdb_dao_status($database);

                            if (!empty($_POST['delStatus'])) {
                                foreach (explode(',', $_POST['delStatus']) as $l_delStatus) {
                                    $l_status_dao->delete_status($l_delStatus);
                                }
                            }

                            if (is_array($_POST['status_title'])) {
                                foreach ($_POST['status_title'] as $l_id => $l_title) {
                                    $l_const = $_POST['status_const'][$l_id];
                                    $l_color = $_POST['status_color'][$l_id];

                                    $l_status_dao->save($l_id, $l_const, $l_title, $l_color);
                                }
                            }

                            if (is_array($_POST['new_status_title'])) {
                                foreach ($_POST['new_status_title'] as $l_id => $l_title) {
                                    $l_const = $_POST['new_status_const'][$l_id];
                                    $l_color = $_POST['new_status_color'][$l_id];

                                    $l_status_dao->create($l_const, $l_title, $l_color);
                                }
                            }

                            // @see API-436 Re-create DCM cache after deleting / saving / creating CMDB-Status.
                            /** @var ConstantManager $cm */
                            $cm = isys_application::instance()->container->get('constant_manager');
                            $cm->createTenantCacheFile();

                            isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                            break;

                        case 'expert-settings':
                        case 'tenant-settings':
                            $allowList = FeatureManager::isCloud() ? self::CLOUD_ALLOWED_EXPERT_SETTINGS : [];
                            $skippedNotAllwedSettings = [];

                            if (isset($_POST['settings'][self::TENANT_WIDE]) && is_array($_POST['settings'][self::TENANT_WIDE])) {
                                $tenantDefinitions = isys_tenantsettings::getRawDefinition();
                                foreach ($_POST['settings'][self::TENANT_WIDE] as $l_key => $l_value) {
                                    // @see ID-11929 Skip modification for not allowed settings.
                                    if (count($allowList) && !in_array($l_key, $allowList, true)) {
                                        $skippedNotAllwedSettings[] = $l_key;
                                        continue;
                                    }

                                    if ($_POST['remove_settings'][self::TENANT_WIDE][$l_key] === '1') {
                                        isys_tenantsettings::remove($l_key);
                                    } else {
                                        // This is used for passwords.
                                        if (isset($_POST['settings__action'][self::TENANT_WIDE][$l_key]) && $_POST['settings__action'][self::TENANT_WIDE][$l_key] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                                            continue;
                                        }
                                        $settingBeforeSet = isys_tenantsettings::get($l_key);
                                        isys_tenantsettings::set($l_key, html_entity_decode(is_scalar($l_value) ? (string)$l_value : ''));
                                        $changed = $settingBeforeSet !== isys_tenantsettings::get($l_key);

                                        if ($changed && isset($tenantDefinitions[$l_key]['callback']) && is_callable($tenantDefinitions[$l_key]['callback'])) {
                                            call_user_func($tenantDefinitions[$l_key]['callback'], $l_value);
                                        }
                                    }
                                }
                            }

                            if (isset($_POST['settings'][self::USER]) && is_array($_POST['settings'][self::USER])) {
                                foreach ($_POST['settings'][self::USER] as $l_key => $l_value) {
                                    // @see ID-11929 Skip modification for not allowed settings.
                                    if (count($allowList) && !in_array($l_key, $allowList, true)) {
                                        $skippedNotAllwedSettings[] = $l_key;
                                        continue;
                                    }

                                    if ($_POST['remove_settings'][self::USER][$l_key] === '1') {
                                        isys_usersettings::remove($l_key);
                                    } else {
                                        // This is used for passwords.
                                        if (isset($_POST['settings__action'][self::USER][$l_key]) && $_POST['settings__action'][self::USER][$l_key] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                                            continue;
                                        }

                                        isys_usersettings::set($l_key, html_entity_decode(is_scalar($l_value) ? (string)$l_value : ''));
                                    }
                                }
                            }

                            // Expert Settings
                            if (isset($_POST['custom_settings']) && FeatureManager::isFeatureActive('expert-settings')) {
                                $l_callmap = [
                                    self::TENANT_WIDE => 'isys_tenantsettings',
                                    self::USER        => 'isys_usersettings'
                                ];

                                // Custom
                                foreach ($_POST['custom_settings']['key'] as $l_index => $l_key) {
                                    // @see ID-11929 Skip modification for not allowed settings.
                                    if (count($allowList) && !in_array($l_key, $allowList, true)) {
                                        $skippedNotAllwedSettings[] = $l_key;
                                        continue;
                                    }

                                    if ($l_key && $l_key != '') {
                                        $l_value = $_POST['custom_settings']['value'][$l_index] ?? null;
                                        $l_type = $_POST['custom_settings']['type'][$l_index] ?: null;

                                        if ($l_type !== null && $l_value !== null && isset($l_callmap[$l_type])) {
                                            call_user_func([$l_callmap[$l_type], 'set'], $l_key, html_entity_decode(is_scalar($l_value) ? (string)$l_value : ''));
                                        }
                                    }
                                }
                            }

                            $signales = isys_application::instance()->container->get('signals');

                            // @See ID-4990
                            isys_cache::keyvalue()->flush();

                            // @See ID-4990 flush system cache
                            $signales->emit('system.afterFlushSystemCache');

                            if (count($skippedNotAllwedSettings)) {
                                isys_notify::warning(
                                    $this->language->get('LC__SETTINGS__SKIPPED_SAVING_EXPERT_SETTINGS') . implode(', ', $skippedNotAllwedSettings),
                                    ['sticky' => true]
                                );
                            } else {
                                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                            }
                            break;
                    }
                    break;

                case C__NAVMODE__DELETE:
                    break;

                case C__NAVMODE__NEW:
                    break;
            }
        }

        if ($l_gets[C__GET__SETTINGS_PAGE] === 'cmdb-status') {
            $this->processCmdbStatusSettings();
        } elseif ($l_gets[C__GET__SETTINGS_PAGE] === 'expert-settings') {
            $this->processExpertSettings();
        } elseif ($l_gets[C__GET__SETTINGS_PAGE] === 'tenant-settings') {
            $this->processTenantSettings();
        }

        return $this;
    }

    /**
     * @param isys_module_request $p_req
     *
     * @return Boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    }

    /**
     * CMDB Status
     */
    private function processCmdbStatusSettings()
    {
        $database = isys_application::instance()->container->get('database');

        $l_status_complete = [];

        // Check rights
        isys_module_system::getAuth()->check(isys_auth::VIEW, 'GLOBALSETTINGS/CMDBSTATUS');

        $l_dao_cmdb = new isys_cmdb_dao_status($database);
        $l_status_dao = $l_dao_cmdb->get_cmdb_status();

        while ($l_row = $l_status_dao->get_row()) {
            if ($l_row['isys_cmdb_status__editable'] && !in_array($l_row['isys_cmdb_status__const'], ['C__CMDB_STATUS__IN_OPERATION', 'C__CMDB_STATUS__INOPERATIVE'], true)) {
                $l_row['isys_cmdb_status__color'] = isys_helper_color::unifyHexColor((string)$l_row['isys_cmdb_status__color']);

                $l_status_complete[] = $l_row;
            }
        }

        $l_rules['C__SETTING__STATUS__SHOW_FILTER']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['C__SETTING__STATUS__SHOW_FILTER']['p_strSelectedID'] = isys_tenantsettings::get('system.mydoit.show_filter', 1);

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE);

        if (isys_glob_get_param(C__GET__NAVMODE) != C__NAVMODE__EDIT) {
            $l_navbar->set_active(isys_auth_system::instance()
                ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/CMDBSTATUS'), C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
        } else {
            $l_navbar->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
        }

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__CMDB_STATUS'))
            ->assign('cmdb_status', $l_status_complete)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', 'content/bottom/content/module__settings__status.tpl');
    }

    /**
     * @return void
     * @throws isys_exception_auth
     * @throws isys_exception_database
     */
    private function processExpertSettings(): void
    {
        if (!FeatureManager::isFeatureActive('expert-settings')) {
            return;
        }

        $allowList = FeatureManager::isCloud() ? self::CLOUD_ALLOWED_EXPERT_SETTINGS : [];

        // Check rights
        isys_auth_system::instance()->check(isys_auth::VIEW, 'SYSTEM');
        $isSupervisor = isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEM');

        isys_component_template_navbar::getInstance()
            ->set_active($isSupervisor, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_save_mode('quick');

        $settings = [];
        $passwordSettings = [];

        // @see ID-12249 Collect all 'password' fields to skip them later.
        foreach (isys_settings::get_definition() as $settings) {
            foreach ($settings as $key => $definition) {
                if ($definition['type'] === 'password') {
                    $passwordSettings[] = $key;
                }
            }
        }

        foreach (isys_tenantsettings::get_definition() as $settings) {
            foreach ($settings as $key => $definition) {
                if ($definition['type'] === 'password') {
                    $passwordSettings[] = $key;
                }
            }
        }

        foreach (isys_usersettings::get_definition() as $settings) {
            foreach ($settings as $key => $definition) {
                if ($definition['type'] === 'password') {
                    $passwordSettings[] = $key;
                }
            }
        }

        $tenantSettings = isys_tenantsettings::get();
        ksort($tenantSettings);

        $userSettings = isys_usersettings::get();
        ksort($userSettings);

        foreach ($tenantSettings as $key => $value) {
            // @see ID-11929 Skip not allowed settings.
            if (count($allowList) && !in_array($key, $allowList, true)) {
                continue;
            }

            // @see ID-12249 Skip password fields so they do not get shown in plaintext.
            if (in_array($key, $passwordSettings, true)) {
                continue;
            }

            if (!$this->isInternalSetting($key, $value)) {
                $settings[self::TENANT_WIDE][$key] = htmlentities(is_scalar($value) ? (string)$value : '');
            }
        }

        foreach ($userSettings as $key => $value) {
            // @see ID-11929 Skip not allowed settings.
            if (count($allowList) && !in_array($key, $allowList, true)) {
                continue;
            }

            if (!$this->isInternalSetting($key, $value)) {
                $settings[self::USER][$key] = htmlentities(is_scalar($value) ? (string)$value : '');
            }
        }

        $template = isys_application::instance()->container->get('template')
            ->assign('bShowCommentary', false)
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__TENANT_SETTINGS__EXPERT_SETTINGS'))
            ->assign('settings', $settings)
            ->include_template('contentbottomcontent', 'modules/system_settings/expert.tpl');

        if ($isSupervisor) {
            $template
                ->assign('isEditing', $isSupervisor)
                ->activate_editmode();
        }
    }

    /**
     * @return void
     * @throws isys_exception_auth
     * @throws isys_exception_database
     */
    private function processTenantSettings(): void
    {
        $systemSettings = isys_application::instance()->container->get('settingsSystem');

        // Check rights
        isys_auth_system::instance()->check(isys_auth::VIEW, 'SYSTEM');
        $isSupervisor = isys_auth_system::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'SYSTEM');

        isys_component_template_navbar::getInstance()
            ->set_active($isSupervisor, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_save_mode('quick');

        $l_tenant_settings = isys_tenantsettings::get();
        $l_tenant_definition = isys_tenantsettings::get_definition();
        ksort($l_tenant_settings);

        // @see  ID-6829  This code will encode the array to JSON.
        if (is_array($l_tenant_settings['ldap.config'])) {
            $l_tenant_settings['ldap.config'] = json_encode($l_tenant_settings['ldap.config'], JSON_PRETTY_PRINT);
        }

        // @see ID-10957 Fetch system setting, if tenant setting is not (yet) set.
        foreach ($l_tenant_definition as $tenantSettings) {
            foreach ($tenantSettings as $key => $definition) {
                if (!isset($l_tenant_settings[$key]) || $l_tenant_settings[$key] === '') {
                    $l_tenant_settings[$key] = isys_settings::get($key);
                }
            }
        }

        // @see ID-11319 Hide sections that only contain hidden settings.
        $l_tenant_definition = array_filter(
            $l_tenant_definition,
            fn ($settings) => count(array_filter($settings, fn ($setting) => !isset($setting['hidden']) || !$setting['hidden']))
        );

        // @see ID-9557 Set the proper default, if not set... Might this make sense for all settings?
        if (!isset($l_tenant_settings['cmdb.registry.object_type_sorting'])) {
            $l_tenant_settings['cmdb.registry.object_type_sorting'] = $systemSettings->get('cmdb.registry.object_type_sorting', C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC);
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

        $template = isys_application::instance()->container->get('template')
            ->assign('bShowCommentary', false)
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__OVERVIEW', [isys_application::instance()->tenant->name]))
            ->assign('systemWideKey', self::TENANT_WIDE)
            ->assign('definition', $l_tenant_definition)
            ->assign('settings', $l_tenant_settings)
            ->assign('showPasswordReset', true)
            ->assign('smtpConfigured', trim(isys_settings::get('system.email.smtp-host', '')) !== '')
            ->assign('smtpConfigurationUrl', './admin/?req=settings')
            ->include_template('contentbottomcontent', 'modules/system_settings/index.tpl');

        if ($isSupervisor) {
            $template
                ->assign('isEditing', $isSupervisor)
                ->activate_editmode();
        }
    }

    /**
     * @param string $settingKey
     * @param mixed  $settingValue
     *
     * @return bool
     */
    private function isInternalSetting($settingKey, $settingValue)
    {
        try {
            foreach (self::$internalConfigurationKeys['plain'] as $key) {
                if (strpos($settingKey, $key) !== false) {
                    // If we find a "internal" configuration jump out immediately!
                    return true;
                }
            }

            // @see ID-00000 Skip additional internal settings, based on REGEXP.
            foreach (self::$internalConfigurationKeys['regexp'] as $key) {
                if (preg_match($key, $settingKey)) {
                    return true;
                }
            }

            if (is_scalar($settingValue)) {
                if (!$settingValue) {
                    return false;
                }

                // @see ID-9433 Only allow specific classes to be unserialized.
                // @see ID-10724 Check if the string starts with 'O:' before trying to unserialize it.
                if (str_starts_with($settingValue, 'O:') && !Unserialize::toObject($settingValue, [Config::class, Property::class]) instanceof stdClass) {
                    return true;
                }

                if (isys_format_json::is_json($settingValue) && !is_scalar(isys_format_json::decode($settingValue))) {
                    return true;
                }
            }
        } catch (Exception $e) {
        }

        return false;
    }
}
