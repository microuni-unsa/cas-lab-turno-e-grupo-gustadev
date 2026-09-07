<?php

use idoit\AddOn\RoutingAwareInterface;
use idoit\Module\UserSettings\TfaService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\PhpFileLoader;

/**
 * i-doit
 *
 * User settings.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_user_settings extends isys_module implements isys_module_interface, RoutingAwareInterface
{
    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

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
     * @param   isys_module_request $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    }

    /**
     * Method for retrieving the breadcrumb part.
     *
     * @param array $p_gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        $breadcrumbMapping = [
            'login'             => 'LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOGIN_CREDENTIALS',
            'locale-formatting' => 'LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOCALE_FORMATTING',
            'user-language'     => 'LC__MODULE__SYSTEM__TREE__USER_SETTINGS__USER_LANGUAGE',
            'views'             => 'LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS'
        ];

        if (isset($breadcrumbMapping[$p_gets[C__GET__SETTINGS_PAGE]])) {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS') => [
                        C__GET__MODULE_ID => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS
                    ]
                ],
                [
                    $this->language->get($breadcrumbMapping[$p_gets[C__GET__SETTINGS_PAGE]]) => null
                ]
            ];
        }

        return null;
    }

    /**
     * User specific settings.
     *
     * @return isys_module_user_settings|void
     * @throws Exception
     */
    public function start()
    {
        $l_tplclass = isys_application::instance()->container->get('template');
        $l_navbar = isys_component_template_navbar::getInstance();

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM') || empty($_GET[C__GET__SETTINGS_PAGE])) {
            $redirectUrl = isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__USER_SETTINGS,
                C__GET__SETTINGS_PAGE => 'login'
            ], true);

            header('Location: ' . $redirectUrl);
            die;
        }

        // Navbar stuff.
        $l_navbar->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param("navMode") == C__NAVMODE__EDIT) {
            $l_navbar
                ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        switch ($_GET[C__GET__SETTINGS_PAGE]) {
            default:
            case 'locale-formatting':
                $this->processLocaleFormatting();
                break;

            case 'user-language':
                $this->processUserLanguage();
                break;

            case 'login':
                $this->processUserLoginCredentials();
                break;

            case 'views':
                $this->processViewSettings();
                break;
        }
    }

    /**
     * Method for displaying the user-settings.
     *
     * @return void
     * @throws isys_exception_database
     * @throws isys_exception_locale
     */
    private function processLocaleFormatting(): void
    {
        if (isset($_POST["navMode"]) && $_POST["navMode"] == C__NAVMODE__SAVE) {
            try {
                $l_objUser = isys_component_dao_user::instance(isys_application::instance()->container->get('database'));
                $l_objUser->save_settings($_POST);

                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                isys_application::instance()->container->get('locales')->reset_cache(true);
            } catch (isys_exception_general $e) {
                $l_error = $e->getMessage();
                isys_notify::error($l_error, ['sticky' => true]);
            }
        }

        $locales = isys_application::instance()->container->get('locales');
        $languages = isys_glob_get_language_constants();

        // We can only use DE / EN for date and numeric formats.
        $systemLanguages = array_filter($languages, function ($key) {
            return in_array($key, [ISYS_LANGUAGE_GERMAN, ISYS_LANGUAGE_ENGLISH]);
        }, ARRAY_FILTER_USE_KEY);

        $userSettingsArray = isys_component_dao_user::instance(isys_application::instance()->container->get('database'))->get_user_settings();

        $rules = [
            'C__CATG__OVERVIEW__DATE_FORMAT'      => [
                'p_arData'        => $systemLanguages,
                'p_strSelectedID' => $locales->get_setting(LC_TIME),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ],
            'C__CATG__OVERVIEW__NUMERIC_FORMAT'   => [
                'p_arData'        => $systemLanguages,
                'p_strSelectedID' => $locales->get_setting(LC_NUMERIC),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ],
            'C__CATG__OVERVIEW__MONETARY_FORMAT'  => [
                'p_strTable'      => 'isys_currency',
                'p_strSelectedID' => $userSettingsArray['isys_user_locale__isys_currency__id'],
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ]
        ];

        isys_application::instance()->container->get('template')
            ->assign("content_title", $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOCALE_FORMATTING'))
            ->assign("useBrowserLanguage", $locales->get_setting('browser_language'))
            ->smarty_tom_add_rules("tom.content.bottom", $rules)
            ->include_template('contentbottom', __DIR__ . '/templates/locale-formatting.tpl');
    }

    /**
     * Method for displaying the user-settings.
     *
     * @return void
     * @throws isys_exception_database
     * @throws isys_exception_locale
     */
    private function processUserLanguage(): void
    {
        $locales = isys_application::instance()->container->get('locales');

        if (isset($_POST["navMode"]) && $_POST["navMode"] == C__NAVMODE__SAVE) {
            try {
                $l_objUser = isys_component_dao_user::instance(isys_application::instance()->container->get('database'));
                $l_objUser->save_settings($_POST);

                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                $locales->reset_cache(true);
            } catch (isys_exception_general $e) {
                $l_error = $e->getMessage();
                isys_notify::error($l_error, ['sticky' => true]);
            }
        }

        $languages = isys_glob_get_language_constants();
        $yesNoValues = get_smarty_arr_YES_NO();

        // We can only use DE / EN for date and numeric formats.
        $systemLanguages = array_filter($languages, function ($key) {
            return in_array($key, [ISYS_LANGUAGE_GERMAN, ISYS_LANGUAGE_ENGLISH]);
        }, ARRAY_FILTER_USE_KEY);

        $userSettingsArray = isys_component_dao_user::instance(isys_application::instance()->container->get('database'))->get_user_settings();

        $rules = [
            'C__CATG__OVERVIEW__LANGUAGE'         => [
                'p_arData'        => $languages,
                'p_strSelectedID' => $locales->get_setting(LC_LANG),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true
            ],
            'C__CATG__OVERVIEW__BROWSER_LANGUAGE' => [
                'p_arData'        => $yesNoValues,
                'p_strSelectedID' => $locales->get_setting('browser_language'),
                'p_strClass'      => 'input-small',
                'p_bDbFieldNN'    => true,
                'p_onChange'      => "if ($('language') && this.value == 1) { $('language').addClassName('hide'); } else if ($('language')) { $('language').removeClassName('hide'); }"
            ],
        ];

        isys_application::instance()->container->get('template')
            ->assign("content_title", $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__USER_LANGUAGE'))
            ->assign("useBrowserLanguage", $locales->get_setting('browser_language'))
            ->smarty_tom_add_rules("tom.content.bottom", $rules)
            ->include_template('contentbottom', __DIR__ . '/templates/user-language.tpl');
    }

    /**
     * Method for displaying the "change password" page.
     *
     * @return void
     * @throws Exception
     */
    private function processUserLoginCredentials(): void
    {
        $database = isys_application::instance()->container->get('database');
        $template = isys_application::instance()->container->get('template');

        $l_user_dao = isys_component_dao_user::instance($database);
        $l_user_id = $l_user_dao->get_current_user_id();

        $l_row = $l_user_dao->get_user($l_user_id)
            ->get_row();

        $l_rules = [
            'C__CONTACT__PERSON_PASSWORD'       => [
                'p_strValue'       => '',
                'p_bReadonly'      => true,
                'hide-set-empty'   => true,
                'p_strPlaceholder' => ''
            ],
            'C__CONTACT__CHANGE_PASSWORD_POPUP' => [
                'target-field'            => 'C__CONTACT__PERSON_PASSWORD',
                'data-field'              => 'C__CONTACT__PERSON_PASSWORD__action',
                'p_strClass'              => 'input',
                'remove-set-empty-button' => true
            ],
            'C__CONTACT__PERSON_PASSWORD_RESET_EMAIL' => [
                'p_strValue' => $l_row['isys_cats_person_list__password_reset_email']
            ]
        ];

        if (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $_POST['C__CONTACT__PERSON_PASSWORD'] = trim($_POST['C__CONTACT__PERSON_PASSWORD']);

            $l_password_minlength = (int)isys_tenantsettings::get('minlength.login.password', 4);

            try {
                $password = trim($_POST['C__CONTACT__PERSON_PASSWORD']);
                // @see ID-11314 Save 'password reset' email.
                $emailAddress = trim($_POST['C__CONTACT__PERSON_PASSWORD_RESET_EMAIL']);
                $personEntryId = (int)$l_row['isys_cats_person_list__id'];

                $l_rules["C__CONTACT__PERSON_PASSWORD"]["p_strValue"] = $password;
                $l_rules["C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"]["p_strValue"] = $emailAddress;

                $personLoginDao = isys_cmdb_dao_category_s_person_login::instance($database);
                // @see ID-10373 set object id in dao
                $personLoginDao->set_object_id($l_user_id);
                $validationResult = $personLoginDao->validate_user_data();

                if (!$validationResult) {
                    $validationRules = $personLoginDao->get_additional_rules();
                    $l_rules = isys_glob_array_merge($l_rules, $validationRules);

                    $template->assign('passwordValidationError', isset($validationRules['C__CONTACT__PERSON_PASSWORD']));
                    $template->assign('emailValidationError', isset($validationRules['C__CONTACT__PERSON_PASSWORD_RESET_EMAIL']));

                    throw new Exception(current($validationRules)['message']);
                }

                if ($_POST['C__CONTACT__PERSON_PASSWORD__action'] !== isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                    if ($password !== '' && strlen($password) >= $l_password_minlength) {
                        $personLoginDao->change_password($personEntryId, $password);
                    } else {
                        $template->assign('passwordValidationError', true);
                        throw new Exception($this->language->get('LC__LOGIN__SAVE_ERROR', $l_password_minlength));
                    }
                }

                if ($personLoginDao->updatePasswordResetEmail($personEntryId, $emailAddress) === false) {
                    $template->assign('emailValidationError', true);
                    throw new Exception($this->language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_EMAIL'));
                }

                isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            } catch (Throwable $e) {
                $_GET[C__GET__NAVMODE] = C__NAVMODE__EDIT;
                $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;

                isys_notify::error($e->getMessage(), ['sticky' => true]);
            }
        }

        // navbar stuff.
        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__EDIT);

        if (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__EDIT) {
            $l_navbar->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        }

        $tfaExists = isys_application::isPro() && class_exists(TfaService::class);

        if ($tfaExists) {
            $tfa = isys_application::instance()->container->get('tfa');

            $template
                ->assign('tfa_secret', $tfa->getTfaSecretForUser())
                ->assign('tfa_qrcode', $tfa->getQrCode());
        }

        $template
            ->assign('tfa_exists', $tfaExists)
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__LOGIN_CREDENTIALS'))
            ->assign('title', $this->language->get('LC__LOGIN__SETTINGS_CHANGE_FOR_USER', [$l_row['isys_cats_person_list__title']]))
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', __DIR__ . '/templates/user_login.tpl');

    }

    /**
     * Process generic settings (config.inc.php).
     *
     * @return void
     * @throws Exception
     */
    private function processViewSettings()
    {
        if (isset($_POST['settings']['user']) && is_array($_POST['settings']['user'])) {
            foreach ($_POST['settings']['user'] as $l_key => $l_value) {
                isys_usersettings::set($l_key, $l_value);
            }

            // @see ID-8965 Prepare a 'per user' CSS cache.
            $session = isys_application::instance()->container->get('session');

            try {
                $compiledCssPath = isys_application::instance()->app_path . 'temp/compiled-style_' . $session->get_mandator_id() . '_' . $session->get_user_id() . '.css';

                if (file_exists($compiledCssPath)) {
                    unlink($compiledCssPath);
                }
            } catch (Exception $e) {
                // Do nothing.
            }

            isys_notify::success($this->language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
        }

        isys_component_template_navbar::getInstance()
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
            ->set_save_mode('quick');

        $definition = [];

        foreach (isys_usersettings::get_definition() as $key => $values) {
            $definition[$this->language->get($key)] = $values;
        }

        ksort($definition);

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

        isys_application::instance()->container->get('template')
            ->activate_editmode()
            ->assign("bShowCommentary", false)
            ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS'))
            ->assign('definition', $definition)
            ->assign('systemWideKey', 'user')
            ->assign('settings', isys_usersettings::get())
            ->include_template('contentbottomcontent', 'modules/system_settings/index.tpl');
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
