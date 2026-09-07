<?php

use idoit\AddOn\AuthableInterface;
use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * New authorization module.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_module_auth extends isys_module implements isys_module_interface, AuthableInterface
{
    // Defines whether this module will be displayed in the extras-menu.
    const DISPLAY_IN_MAIN_MENU = false;

    // Defines, if this module shall be displayed in the systme-menu.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * Settings page for resetting the right system
     *
     * @var  string
     */
    const RESET_RIGHT_SYSTEM = 'reset_right_system';

    /**
     * Variable which defines, if this module is licenced.
     *
     * @var  boolean
     */
    protected static $m_licenced = true;

    /** @var isys_component_template_language_manager */
    protected $language;

    /**
     * Instance of module DAO.
     *
     * @var  isys_auth_dao
     */
    protected $m_dao;

    /**
     * User request.
     *
     * @var  isys_module_request
     */
    protected $m_userrequest;

    protected $m_module_id;

    protected $m_db;

    /**
     * Static factory method for instant method chaining.
     *
     * @static
     * @return  isys_module_auth
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public static function factory()
    {
        return new self;
    }

    /**
     * @param $p_gets
     *
     * @return array|null[][]
     * @throws isys_exception_database
     */
    public function breadcrumb_get(&$p_gets)
    {
        $moduleConstant = $p_gets[C__GET__SETTINGS_PAGE] ?? '';

        if (defined($moduleConstant)) {
            $moduleTitle = isys_module_manager::instance()
                ->get_modules(null, $moduleConstant)
                ->get_row_value('isys_module__title');

            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__AUTH') => [
                        'moduleID'    => C__MODULE__SYSTEM,
                        'moduleSubID' => C__MODULE__AUTH,
                    ]
                ],
                [
                    $this->language->get($moduleTitle) => null
                ]
            ];
        }

        if ($p_gets[C__GET__SETTINGS_PAGE] === 'reset_right_system') {
            return [
                [
                    $this->language->get('LC__MODULE__SYSTEM__TREE__AUTH') => [
                        'moduleID'    => C__MODULE__SYSTEM,
                        'moduleSubID' => C__MODULE__AUTH,
                    ]
                ],
                [
                    $this->language->get('LC__MODULE__AUTH__TREE__RESET_RIGHT_SYSTEM') => null
                ]
            ];
        }

        return [
            [
                $this->language->get('LC__MODULE__SYSTEM__TREE__AUTH') => null
            ]
        ];
    }

    /**
     * Initiates module.
     *
     * @param   isys_module_request $p_req
     *
     * @return  isys_module_auth
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = $p_req;

        return $this;
    }

    /**
     * Builds menu tree.
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
        $nextId = $tree->count();

        $l_get = $this->m_userrequest->get_gets();
        $l_get = Purify::castIntValues($l_get);

        $imageDir = isys_application::instance()->www_path . 'images/axialis/';
        $isAuth = $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__AUTH;

        $l_auth_root = $tree->add_node(
            ++$nextId,
            $parentNodeId,
            $this->language->get('LC__MODULE__SYSTEM__TREE__AUTH'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__AUTH
            ]),
            '',
            "{$imageDir}user-management/key-user-group.svg",
            $isAuth,
            '',
            '',
            isys_auth_auth::instance()->is_allowed_to(isys_auth::VIEW, 'OVERVIEW')
        );

        // Get only active modules
        $moduleResult = $this->m_userrequest->get_module_manager()->get_modules(null, null, true);

        if (is_countable($moduleResult) && count($moduleResult) > 0) {
            $nodes = [];

            while ($l_row = $moduleResult->get_row()) {
                $l_auth_instance = isys_module_manager::instance()->get_module_auth($l_row['isys_module__id']);

                if (!$l_auth_instance || $l_row['isys_module__status'] != C__RECORD_STATUS__NORMAL) {
                    continue;
                }

                // If auth class name is isys_auth_system but the class itself is not the system module then skip it in the tree
                if (get_class($l_auth_instance) === 'isys_auth_system' && $l_row['isys_module__const'] !== 'C__MODULE__SYSTEM') {
                    continue;
                }

                if (!isys_auth_auth::instance()->is_allowed_to(isys_auth::VIEW, 'MODULE/' . $l_row['isys_module__const'])) {
                    continue;
                }

                $l_module_title = $this->language->get($l_row['isys_module__title']);

                if ($l_row['isys_module__id'] == defined_or_default('C__MODULE__TEMPLATES')) {
                    $l_module_title = $this->language->get_in_text('LC__AUTH_GUI__TEMPLATES_CONDITION / LC__AUTH_GUI__MASS_CHANGES_CONDITION');
                }

                // Special logic to keep 'CMDB' on the top :)
                if ($l_row['isys_module__const'] === 'C__MODULE__CMDB') {
                    $tree->add_node(
                        ++$nextId,
                        $l_auth_root,
                        $l_module_title,
                        isys_helper_link::create_url([
                            C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                            C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                            C__GET__SETTINGS_PAGE => 'C__MODULE__CMDB',
                        ]),
                        '',
                        '',
                        $isAuth && $l_get[C__GET__SETTINGS_PAGE] === 'C__MODULE__CMDB'
                    );
                    continue;
                }

                $nodes[$l_module_title] = $l_row['isys_module__const'];
            }

            ksort($nodes);

            foreach ($nodes as $label => $constant) {
                $tree->add_node(
                    ++$nextId,
                    $l_auth_root,
                    $label,
                    isys_helper_link::create_url([
                        C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                        C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                        C__GET__SETTINGS_PAGE => $constant,
                    ]),
                    '',
                    '',
                    $isAuth && $l_get[C__GET__SETTINGS_PAGE] === $constant
                );
            }
        }

        $tree->add_node(
            ++$nextId,
            $l_auth_root,
            $this->language->get('LC__MODULE__AUTH__TREE__RESET_RIGHT_SYSTEM'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__AUTH,
                C__GET__SETTINGS_PAGE => self::RESET_RIGHT_SYSTEM
            ]),
            '',
            "{$imageDir}basic/cleanup.svg",
            $isAuth && $l_get[C__GET__SETTINGS_PAGE] === self::RESET_RIGHT_SYSTEM
        );
    }

    /**
     * Start method.
     *
     * @throws  isys_exception_auth
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function start()
    {
        $l_save = null;
        $l_get = $this->m_userrequest->get_gets();
        $l_get = Purify::castIntValues($l_get);
        $language = isys_application::instance()->container->get('language');

        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote');

        if (array_key_exists(C__GET__AJAX, $l_get)) {
            if (array_key_exists('func', $l_get)) {
                // Call the internal "ajax" function, with the given method as parameter.
                $this->ajax($l_get['func']);
            } elseif (array_key_exists('navMode', $_POST) && $_POST['navMode'] == C__NAVMODE__SAVE) {
                // Save action.
                try {
                    $l_module_constant = $l_get[C__GET__SETTINGS_PAGE];
                    $l_auth = isys_auth_auth::instance();

                    // Check if the user is allowed to see this page.
                    $l_auth->check(isys_auth::EDIT, 'MODULE/' . $l_module_constant);

                    if ($this->save($_POST['C__AUTH__PERSON_SELECTION__HIDDEN'], constant($l_module_constant))) {
                        isys_notify::success($language->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
                    }
                } catch (Exception $e) {
                    isys_notify::error($e->getMessage());
                }
            }
        }

        if (array_key_exists(C__GET__SETTINGS_PAGE, $l_get) && $l_get[C__GET__SETTINGS_PAGE] != self::RESET_RIGHT_SYSTEM) {
            $l_module_constant = $l_get[C__GET__SETTINGS_PAGE];
            $l_auth = isys_auth_auth::instance();

            // Check if the user is allowed to see this page.
            $l_auth->check(isys_auth::VIEW, 'MODULE/' . $l_module_constant);

            isys_component_template_navbar::getInstance()
                ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_auth->is_allowed_to(isys_auth::EDIT, 'MODULE/' . $l_module_constant), C__NAVBAR_BUTTON__SAVE);

            // Retrieve auth-instance of the given module.
            $methods = [];
            $rightsMapping = self::getDefaultRightsMapping();

            $l_auth_instance = isys_module_manager::instance()->get_module_auth($l_module_constant);

            if ($l_auth_instance instanceof isys_auth_interface) {
                $methods = $l_auth_instance->get_auth_methods();
            }

            if ($l_auth_instance instanceof isys_auth_rights_map_interface) {
                $rightsMapping = $l_auth_instance->getRightsMap();
            }

            $methods = array_map(function ($method) use ($language) {
                $method['title'] = $language->get($method['title']);

                return $method;
            }, $methods);

            $l_module_data = isys_module_manager::instance()
                ->get_modules(null, $l_module_constant)
                ->get_row();

            // Retrieve the rights and make sure, the titles are UTF8.
            $rights = isys_auth::get_rights();

            $rights = array_map(function ($right) use ($language) {
                $right['title'] = $language->get($right['title']);

                return $right;
            }, $rights);

            // Remove the "edit mode" parameter and add "ajax".
            $l_url = isys_helper_link::remove_params_from_url(isys_glob_add_to_query(C__GET__AJAX, '1'), C__CMDB__GET__EDITMODE);

            $this->m_userrequest->get_template()
                ->activate_editmode()
                ->assign('module_id', constant($l_module_constant))
                ->assign('ajax_url', $l_url)
                ->assign('auth_rights', isys_format_json::encode($rights))
                ->assign('rights_mapping', isys_format_json::encode($rightsMapping))
                ->assign('auth_methods', isys_format_json::encode($methods))
                ->assign('auth_wildchar', isys_auth::WILDCHAR)
                ->assign('auth_empty_id', isys_auth::EMPTY_ID_PARAM)
                ->assign('content_title', $language->get($l_module_data['isys_module__title']))
                ->include_template('contentbottomcontent', 'modules/auth/configuration.tpl');
        } elseif ($l_get[C__GET__SETTINGS_PAGE] == self::RESET_RIGHT_SYSTEM) {
            global $g_admin_auth;

            $l_admin_auth = $g_admin_auth;
            $l_admin_key = array_pop(array_keys($l_admin_auth));
            $l_admin_value = array_pop(array_values($l_admin_auth));

            if (empty($l_admin_key) || empty($l_admin_value)) {
                throw new isys_exception_auth('Your admin center credentials are not set. These are necessary to reset the authorization system (see config.ing.php).');
            } else {
                $l_rules = [
                    'C__AUTH__RESET_RIGHT_SYSTEM__USERNAME' => [
                        'p_strClass' => 'input-small'
                    ],
                    'C__AUTH__RESET_RIGHT_SYSTEM__PASSWORD' => [
                        'p_bPassword' => 1,
                        'p_strClass'  => 'input-small'
                    ]
                ];

                $l_gets = [
                    C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
                    C__GET__MODULE_SUB_ID => defined_or_default('C__MODULE__AUTH'),
                    C__GET__AJAX          => 1
                ];

                $this->m_userrequest->get_template()
                    ->activate_editmode()
                    ->assign('ajax_handler_url', '?call=auth&ajax=1')
                    ->assign('ajax_url', isys_helper_link::create_url($l_gets))
                    ->assign('content_title', $language->get('LC__MODULE__AUTH__TREE__RESET_RIGHT_SYSTEM'))
                    ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
                    ->include_template('contentbottomcontent', 'modules/auth/reset_right_system.tpl');
            }
        } else {
            $l_modules = [];

            $l_module_res = $this->m_userrequest->get_module_manager()
                ->get_modules();

            if (is_countable($l_module_res) && count($l_module_res) > 0) {
                while ($l_row = $l_module_res->get_row()) {
                    $l_auth_instance = isys_module_manager::instance()
                        ->get_module_auth($l_row['isys_module__id']);

                    if ($l_auth_instance && $l_row['isys_module__status'] == C__RECORD_STATUS__NORMAL) {
                        // If auth class name is isys_auth_system but the class itself is not the system module then skip it in the tree
                        if (get_class($l_auth_instance) == 'isys_auth_system' && constant($l_row['isys_module__const']) != defined_or_default('C__MODULE__SYSTEM')) {
                            continue;
                        }

                        $l_modules[$l_row['isys_module__id']] = $language->get($l_row['isys_module__title']);
                    }
                }
            }

            $l_rules = [
                'condition_filter_object' => [
                    'p_bInfoIconSpacer'     => 0,
                    'p_strPopupType'        => 'browser_object_ng',
                    'secondSelection'       => false,
                    'catFilter'             => 'C__CATS__PERSON;C__CATS__PERSON_GROUP',
                    'callback_accept'       => "$$('.condition_filter')[1].simulate('click');",
                    'disableInputGroup'     => true,
                    'inputGroupMarginClass' => ''
                ],
                'condition_filter_module' => [
                    'p_arData'              => $l_modules,
                    'p_bInfoIconSpacer'     => 0,
                    'p_bDbFieldNN'          => true,
                    'p_strSelectedID'       => defined_or_default('C__MODULE__CMDB'),
                    'disableInputGroup'     => true,
                    'inputGroupMarginClass' => ''
                ]
            ];

            $this->m_userrequest->get_template()
                ->activate_editmode()
                ->assign('ajax_handler_url', '?call=auth&ajax=1')
                ->assign('content_title', $this->language->get('LC__MODULE__SYSTEM__TREE__AUTH'))
                ->assign('ajax_url', isys_glob_add_to_query(C__GET__AJAX, '1'))
                ->assign('auth_wildchar', isys_auth::WILDCHAR)
                ->assign('auth_empty_id', isys_auth::EMPTY_ID_PARAM)
                ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
                ->include_template('contentbottomcontent', 'modules/auth/indexpage.tpl');
        }

        return $this;
    }

    /**
     * Method for adding links to the "sticky" category bar.
     *
     * @param  isys_component_template $template
     * @param  string                  $templateVariable
     * @param  int                     $objectId
     * @param  int                     $objectTypeId
     */
    public static function process_menu_tree_links(isys_component_template $template, string $templateVariable, int $objectId, int $objectTypeId)
    {
        global $g_config;

        if (!isys_application::isPro()) {
            return;
        }

        // @see ID-8833 Skip 'auth' category for templates.
        $status = isys_application::instance()->container->get('cmdb_dao')
            ->get_object($objectId)
            ->get_row_value('isys_obj__status');

        if (in_array($status, [C__RECORD_STATUS__TEMPLATE, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE], false)) {
            return;
        }

        // Check if the user is allowed to see the "auth"-category.
        if ($g_config['use_auth'] && isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $objectId, 'C__CATG__VIRTUAL_AUTH')) {
            $l_link_data = [
                'title' => isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__AUTH'),
                'link'  => "javascript:get_content_by_object('" . $objectId . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . defined_or_default('C__CATG__VIRTUAL_AUTH') .
                    "', '" . C__CMDB__GET__CATG . "');",
                'icon'  => isys_application::instance()->www_path . 'images/axialis/basic/padlock-filled.svg'
            ];

            $template->append($templateVariable, ['auth' => $l_link_data], true);
        }
    }

    /**
     * Ajax dispatcher for this module.
     *
     * @param   string $p_method
     *
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax($p_method)
    {
        try {
            $p_method = 'ajax_' . $p_method;
            $l_data = null;

            if (!method_exists($this, $p_method)) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_METHOD', [
                        $p_method,
                        get_class($this)
                    ]));
            }

            switch ($p_method) {
                case 'ajax_retrieve_paths':
                    $l_data = $this->ajax_retrieve_paths((int)$_POST['obj_id'], (int)$_POST['module_id']);
                    break;

                case 'ajax_reset_right_system':
                    $l_data = $this->ajax_reset_right_system($_POST['username'], $_POST['password']);
                    break;

                case 'ajax_retrieve_parameter':
                    // First we check if the auth class brings a "retrieve_parameter" method of its own.
                    if (defined($_GET[C__GET__SETTINGS_PAGE]) || is_numeric($_GET[C__GET__SETTINGS_PAGE])) {
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($_GET[C__GET__SETTINGS_PAGE]);

                        if ($l_auth_instance && method_exists($l_auth_instance, 'retrieve_parameter')) {
                            $l_data = $l_auth_instance->retrieve_parameter($_POST['method'], $_POST['param'], $_POST['counter'], (bool)$_POST['edit_mode']);

                            if ($l_data && is_array($l_data)) {
                                break;
                            }
                        }
                    }

                    $l_data = $this->ajax_retrieve_parameter($_POST['method'], $_POST['param'], $_POST['counter'], (bool)$_POST['edit_mode']);
                    break;

                default:
                    $l_data = call_user_func([
                        $this,
                        $p_method
                    ], $_POST['method'], $_POST['param'], $_POST['counter'], (bool)$_POST['edit_mode']);
                    break;
            }

            $l_return = [
                'success' => true,
                'message' => null,
                'data'    => $l_data
            ];
        } catch (Exception $e) {
            $l_return = [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        }

        header('Content-Type: application/json');
        echo isys_format_json::encode($l_return);
        die;
    }

    /**
     * Flush cache system after save
     */
    public static function invalidateAuthSystemCache(): void
    {
        isys_cache::keyvalue()->ns('auth')->flush();
    }

    /**
     * Method for saving the configuration.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     *
     * @throws  isys_exception_general
     * @return  string
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function save($p_obj_id, $p_module_id)
    {
        if (!($p_obj_id > 0 && $p_module_id > 0)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__AUTH_GUI__EXCEPTION__MISSING_PARAM'));
        }

        /**
         * Invalidate person's auth cache after updating rights
         */
        isys_component_signalcollection::get_instance()
            ->connect('mod.auth.afterRemoveAllRights', [
                'isys_auth_cmdb_objects',
                'invalidate_cache'
            ])
            ->connect('mod.auth.afterRemoveAllRights', [
                'isys_module_auth',
                'invalidateAuthSystemCache'
            ]);

        if (!$this->m_dao->remove_all_paths($p_obj_id, $p_module_id)) {
            // This should not happen... But you'll never know.
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__AUTH_GUI__EXCEPTION__REMOVING_OLD_PATHS'));
        }

        $l_path_data = [];

        // This is necessary for finding all paths and bring them in the right syntax... Maybe we can clean this up.
        foreach ($_POST as $l_key => $l_value) {
            if (strpos($l_key, 'method_') === 0) {
                $i = (int)substr($l_key, 7);

                // Skip new paths with no rights.
                if (!isset($_POST['right_' . $i])) {
                    continue;
                }

                $l_param = $this->get_gui_param($i);
                $l_right = $_POST['right_' . $i];

                // Because of the current "syntax" every path and every right needs an own row in the DB. So lets begin!
                if (is_array($l_param)) {
                    foreach ($l_param as $l_param_item) {
                        if (is_array($l_right)) {
                            foreach ($l_right as $l_right_item) {
                                $l_path_data[$l_value][$l_param_item][] = $l_right_item;
                            }
                        } else {
                            $l_path_data[$l_value][$l_param_item][] = $l_right;
                        }
                    }
                } else {
                    if (is_array($l_right)) {
                        foreach ($l_right as $l_right_item) {
                            $l_path_data[$l_value][$l_param][] = $l_right_item;
                        }
                    } else {
                        $l_path_data[$l_value][$l_param][] = $l_right;
                    }
                }
            }
        }

        return $this->m_dao->create_paths($p_obj_id, $p_module_id, $l_path_data);
    }

    /**
     * Retrieve the "param" content from the GUI's POST-data.
     *
     * @param   integer $p_count
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function get_gui_param($p_count)
    {
        $l_plus = null;

        // Checks for the "All"-Button and sets the wildchar.
        if (array_key_exists('auth_param_button_val_' . $p_count, $_POST) && $_POST['auth_param_button_val_' . $p_count] == '1') {
            // This is a special route, like for example "All categories in object type server" > "*+C__OBJTYPE__SERVER"
            if (in_array($_POST['method_' . $p_count], [
                'category_in_obj_type',
                'category_in_object',
                'category_in_location',
                'category_in_contact_role',
            ])) {
                return isys_auth::WILDCHAR . '+' . ($_POST['auth_param_form_' . $p_count . 'plus__HIDDEN'] ?: $_POST['auth_param_form_' . $p_count . 'plus']);
            }

            return isys_auth::WILDCHAR;
        }

        // Will occur for object, location and some other browsers.
        if (isset($_POST['auth_param_form_' . $p_count . '__HIDDEN']) && !empty($_POST['auth_param_form_' . $p_count . '__HIDDEN'])) {
            return isys_format_json::decode($_POST['auth_param_form_' . $p_count . '__HIDDEN'], true);
        }

        // We check for additional parameters.
        if (!empty($_POST['auth_param_form_' . $p_count . 'plus'])) {
            $l_plus = '+' . $_POST['auth_param_form_' . $p_count . 'plus'];
        }

        // We check for additional object- / location-browser.
        if (!empty($_POST['auth_param_form_' . $p_count . 'plus__HIDDEN'])) {
            $l_plus = '+' . $_POST['auth_param_form_' . $p_count . 'plus__HIDDEN'];
        }

        // It may happen, that the parameter is an array, when selecting multiple values.
        if (is_array($_POST['auth_param_form_' . $p_count])) {
            if ($l_plus) {
                foreach ($_POST['auth_param_form_' . $p_count] as &$l_param) {
                    $l_param .= $l_plus;
                }
            }

            return $_POST['auth_param_form_' . $p_count];
        }

        return $_POST['auth_param_form_' . $p_count] . $l_plus;
    }

    /**
     * Method for retrieving the "parameter" in the configuration GUI. Gets called generically by "ajax()" method.
     *
     * @see     $this->ajax();
     *
     * @param   string  $p_method
     * @param   string  $p_param
     * @param   integer $p_counter
     * @param   bool    $editmode
     * @param   bool    $combinedParameter This parameter is used, when more than one box is displayed at once (category in object, ...).
     *
     * @return  array
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax_retrieve_parameter($p_method, $p_param, $p_counter, $editmode = null, $combinedParameter = null)
    {
        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        $editmode = (bool)$editmode;
        $spacerClass = $editmode ? 'fl p5' : 'pl5 pr5';
        $combinedParameter = (bool)$combinedParameter;

        $database = isys_application::instance()->container->get('database');
        $template = isys_application::instance()->container->get('template');

        // The "empty-id" parameter will only show up
        if ($p_param !== isys_auth::EMPTY_ID_PARAM) {
            switch ($p_method) {
                case 'object':
                    $l_return['html'] = (new isys_smarty_plugin_f_popup())->navigation_edit($template, [
                        'name'                                          => 'auth_param_form_' . $p_counter . ($combinedParameter ? 'plus' : ''),
                        'p_strPopupType'                                => 'browser_object_ng',
                        isys_popup_browser_object_ng::C__EDIT_MODE      => $editmode,
                        isys_popup_browser_object_ng::C__MULTISELECTION => true,
                        'p_bInfoIconSpacer'                             => 0,
                        'p_strClass'                                    => 'input-' . ($combinedParameter ? 'small' : 'normal'),
                        'p_strSelectedID'                               => $p_param,
                        'inputGroupMarginClass'                         => '',
                        'viewAsCommaList'                               => true
                    ]);
                    break;

                case 'location':
                    $l_params = [
                        'name'                  => 'auth_param_form_' . $p_counter . ($combinedParameter ? 'plus' : ''),
                        'p_strPopupType'        => 'browser_location',
                        'edit'                  => $editmode,
                        'p_bInfoIconSpacer'     => 0,
                        'p_strClass'            => 'input-' . ($combinedParameter ? 'small' : 'normal'),
                        'p_strSelectedID'       => $p_param,
                        'containers_only'       => true,
                        'inputGroupMarginClass' => ''
                    ];

                    if ($editmode === false) {
                        $l_params['plain'] = 1;
                    }

                    $l_return['html'] = (new isys_smarty_plugin_f_popup())->navigation_edit($template, $l_params);
                    break;

                case 'object_type':
                    // Convert the parameter (a constant) back to upper-case.
                    $p_param = strtoupper($p_param);

                    $l_object_types = [];
                    $l_data = isys_cmdb_dao::instance($this->m_db)
                        ->get_object_type();

                    foreach ($l_data as $l_object_type) {
                        if ($l_object_type['isys_obj_type__const'] && $l_object_type['LC_isys_obj_type__title'] && $l_object_type['isys_obj_type__status'] == C__RECORD_STATUS__NORMAL) {
                            $l_object_types[$l_object_type['isys_obj_type__const']] = $l_object_type['LC_isys_obj_type__title'];
                        }
                    }

                    if (strpos($p_param, ',') !== false && !$combinedParameter) {
                        // Remove all selections, that do not (or "no longer") exist.
                        $p_param = implode(',', array_filter(explode(',', $p_param), 'defined'));
                    } elseif (!defined($p_param)) {
                        $p_param = null;
                    }

                    $l_return['html'] = (new isys_smarty_plugin_f_dialog())->navigation_edit($template, [
                        'name'                  => 'auth_param_form_' . $p_counter . ($combinedParameter ? 'plus' : '[]'),
                        'p_arData'              => $l_object_types,
                        'p_multiple'            => !$combinedParameter,
                        'chosen'                => !$combinedParameter,
                        'p_editMode'            => $editmode,
                        'p_bDbFieldNN'          => 1,
                        'p_bInfoIconSpacer'     => 0,
                        'p_strClass'            => 'input-' . ($combinedParameter ? 'small' : 'normal'),
                        'p_strSelectedID'       => $p_param,
                        'inputGroupMarginClass' => '',
                        'viewAsCommaList'       => true,
                        'placeholder'           => 'LC__AUTH_GUI__OBJ_TYPE_CONDITION__PLACEHOLDER'
                    ]);
                    break;

                case 'category':
                    $l_cmdb_dao = new isys_cmdb_dao($database);
                    $l_cat_data = $l_cmdb_dao->get_all_categories();
                    $l_cat_custom = $l_cmdb_dao->get_all_catg_custom();

                    // Category type strings
                    $l_global = $this->language->get('LC__UNIVERSAL__GLOBAL');
                    $l_specific = $this->language->get('LC__UNIVERSAL__SPECIFIC');
                    $l_custom = $this->language->get('LC__CMDB__CUSTOM_CATEGORIES');
                    $l_categories = [];

                    /*
                     * Global categories
                     */
                    if (isset($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL]) && is_countable($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL]) && count($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL]) > 0) {
                        foreach ($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL] as $l_catg) {
                            if ($l_catg['id'] == defined_or_default('C__CATG__CUSTOM_FIELDS')) {
                                continue;
                            }

                            if ($l_catg['parent'] !== null && isset($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_catg['parent']])) {
                                $l_title = $this->language->get($l_cat_data[C__CMDB__CATEGORY__TYPE_GLOBAL][$l_catg['parent']]['title']) . ' > ' .
                                    $this->language->get($l_catg['title']);
                            } else {
                                $l_title = $this->language->get($l_catg['title']);
                            }

                            $l_categories[$l_global][$l_catg['const']] = $l_title;
                        }
                        asort($l_categories[$l_global]);
                    }

                    /*
                     * Specific categories
                     */
                    if (isset($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) && is_countable($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) && count($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC]) > 0) {
                        foreach ($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC] as $l_cats) {
                            if ($l_cats['parent'] !== null && isset($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_cats['parent']])) {
                                $l_title = $this->language->get($l_cat_data[C__CMDB__CATEGORY__TYPE_SPECIFIC][$l_cats['parent']]['title']) . ' > ' .
                                    $this->language->get($l_cats['title']);
                            } else {
                                $l_title = $this->language->get($l_cats['title']);
                            }

                            $l_categories[$l_specific][$l_cats['const']] = $l_title;
                        }
                        asort($l_categories[$l_specific]);
                    }

                    /*
                     * Custom categories
                     */
                    if ($l_cat_custom->num_rows() > 0) {
                        while ($l_category_data = $l_cat_custom->get_row()) {
                            $l_categories[$l_custom][$l_category_data['isysgui_catg_custom__const']] = $this->language->get($l_category_data['isysgui_catg_custom__title']);
                        }
                        asort($l_categories[$l_custom]);
                    }

                    // Initialize dialog
                    $l_return['html'] = (new isys_smarty_plugin_f_dialog())->navigation_edit($template, [
                        'name'              => 'auth_param_form_' . $p_counter . '[]',
                        'p_arData'          => $l_categories,
                        'p_multiple'        => true,
                        'chosen'            => true,
                        'p_editMode'        => $editmode,
                        'p_bDbFieldNN'      => 1,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-' . ($combinedParameter ? 'small' : 'normal'),
                        'p_strSelectedID'   => strtoupper($p_param),
                        'p_bSort'           => false,
                        'viewAsCommaList'   => true
                    ]);
                    break;

                case 'category_in_obj_type':
                    [$l_category, $l_obj_type] = explode('+', $p_param);

                    // Call the same method for "object types" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $editmode, true);
                    $l_obj_type = $this->ajax_retrieve_parameter('object_type', $l_obj_type, $p_counter, $editmode, true);

                    $l_return['html'] = $l_category['html'] . "<span class='{$spacerClass}'>in</span>" . $l_obj_type['html'];
                    break;

                case 'category_in_object':
                    [$l_category, $l_objects] = explode('+', $p_param);

                    // Call the same method for "objects" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $editmode, true);
                    $l_object = $this->ajax_retrieve_parameter('object', $l_objects, $p_counter, $editmode, true);

                    $l_return['html'] = $l_category['html'] . "<span class='{$spacerClass}'>in</span>" . $l_object['html'];
                    break;

                case 'category_in_location':
                    [$l_category, $l_objects] = explode('+', $p_param);

                    // Call the same method for "objects" and "categories".
                    $l_category = $this->ajax_retrieve_parameter('category', $l_category, $p_counter, $editmode, true);
                    $l_location = $this->ajax_retrieve_parameter('location', $l_objects, $p_counter, $editmode, true);

                    $l_return['html'] = $l_category['html'] . "<span class='{$spacerClass}'>in</span>" . $l_location['html'];
                    break;

                case 'category_in_contact_role':
                    [$categories, $role] = explode('+', $p_param);

                    // Call the same method for "objects" and "categories".
                    $categoryResult = $this->ajax_retrieve_parameter('category', $categories, $p_counter, $editmode, true);
                    $roleResult = $this->ajax_retrieve_parameter('contact_role', $role, $p_counter, $editmode, true);

                    $l_return['html'] = $categoryResult['html'] . "<span class='{$spacerClass}'>in</span>" . $roleResult['html'];
                    break;

                case 'contact_role':
                    $p_param = strtoupper($p_param);

                    $roles = [];

                    if ($combinedParameter) {
                        $roles[isys_auth::WILDCHAR] = 'LC__AUTH_GUI__ALL_ASSIGNMENTS';
                    }

                    $result = isys_cmdb_dao_category_g_contact::instance($database)->get_contact_tag_data();

                    while ($row = $result->get_row()) {
                        if ($row['isys_contact_tag__title'] && $row['isys_contact_tag__status'] == C__RECORD_STATUS__NORMAL) {
                            $roles[$row['isys_contact_tag__id']] = $row['isys_contact_tag__title'];
                        }
                    }

                    $l_return['html'] = (new isys_smarty_plugin_f_dialog())->navigation_edit($template, [
                        'name'                  => 'auth_param_form_' . $p_counter . ($combinedParameter ? 'plus' : '[]'),
                        'p_arData'              => $roles,
                        'p_multiple'            => !$combinedParameter,
                        'chosen'                => !$combinedParameter,
                        'p_editMode'            => $editmode,
                        'p_bDbFieldNN'          => 1,
                        'p_bInfoIconSpacer'     => 0,
                        'p_strClass'            => 'input-' . ($combinedParameter ? 'small' : 'normal'),
                        'p_strSelectedID'       => $p_param,
                        'inputGroupMarginClass' => '',
                        'viewAsCommaList'       => true,
                        'placeholder'           => 'LC__AUTH_GUI__BY_CONTACT_ROLE__PLACEHOLDER'
                    ]);

                    break;

                case 'modules':
                    // Init the dialog admin.
                    $l_data = [];
                    $l_modules = isys_module_manager::instance()->get_modules();

                    while ($l_row = $l_modules->get_row()) {
                        $l_auth_instance = isys_module_manager::instance()
                            ->get_module_auth($l_row['isys_module__id']);

                        // We only want to select modules, which have their own auth-classes.
                        if ($l_auth_instance) {
                            $l_data[$l_row['isys_module__const']] = $this->language->get($l_row['isys_module__title']);
                        }
                    }

                    $l_return['html'] = (new isys_smarty_plugin_f_dialog())->navigation_edit($template, [
                        'name'              => 'auth_param_form_' . $p_counter,
                        'p_arData'          => $l_data,
                        'p_editMode'        => $editmode,
                        'p_bDbFieldNN'      => 1,
                        'p_bInfoIconSpacer' => 0,
                        'p_strClass'        => 'input-normal',
                        'p_strSelectedID'   => strtoupper($p_param),
                        'viewAsCommaList'   => true
                    ]);
                    break;

                case 'boolean':
                    break;

                default:
                    throw new isys_exception_general('Please provide a function for auth-method "' . $p_method . '" with parameter "' . isys_format_json::encode($p_param) .
                        '".');
            }
        }

        return $l_return;
    }

    /**
     * Method for retrieving the paths, defined for a person and a module.
     *
     * @see     $this->ajax();
     *
     * @param   integer $p_obj_id
     * @param   integer $p_module_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    private function ajax_retrieve_paths($p_obj_id, $p_module_id)
    {
        $l_auth_dao = new isys_auth_dao($this->m_db);

        $l_paths = $l_auth_dao->get_paths($p_obj_id, $p_module_id);
        $l_group_paths = $l_auth_dao->get_group_paths_by_person($p_obj_id, $p_module_id);

        $l_return = [
            'paths'       => $l_paths ? $l_auth_dao->build_paths_by_result($l_paths) : [],
            'group_paths' => $l_group_paths ? $l_auth_dao->build_paths_by_result($l_group_paths) : []
        ];

        try {
            /* @var  isys_auth $l_module_auth */
            $l_module_auth = isys_module_manager::instance()
                ->get_module_auth($p_module_id);

            if ($l_module_auth) {
                $l_module_auth->combine_paths($l_return['paths'])
                    ->combine_paths($l_return['group_paths']);
            }
        } catch (Exception $e) {
            ; // Nothing to see here citizen, move along.
        }

        return $l_return;
    }

    /**
     * Method for resetting the right system for the current mandator
     *
     * @param $p_username
     * @param $p_password
     *
     * @return array
     */
    private function ajax_reset_right_system($p_username, $p_password)
    {
        global $g_admin_auth;

        if (isset($g_admin_auth[$p_username]) && (\idoit\Component\Security\Hash\PasswordVerify::instance()
                    ->verify($p_password, $g_admin_auth[$p_username]) || $p_password == $g_admin_auth[$p_username])) {
            if ($this->reset_right_system()) {
                return [
                    'success' => true,
                    'message' => 'Right system has been resetted'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Credentials are wrong or are not set.'
        ];
    }

    /**
     * Method where the actual reset of the right system happens
     *
     * @return bool
     */
    private function reset_right_system()
    {
        global $g_comp_session;

        $l_ignore_methods = [
            'category_in_obj_type',
            'category_in_object',
            'category_in_location'
        ];

        $l_modules = isys_module_manager::instance()
            ->get_modules();

        $l_current_user = $g_comp_session->get_user_id();

        // Remove all rights
        $this->m_dao->remove_all_paths($l_current_user);

        // Set right system for the current user
        $l_system_module = false;
        while ($l_row = $l_modules->get_row()) {
            $l_auth_instance = isys_module_manager::instance()
                ->get_module_auth($l_row['isys_module__id']);
            $l_auth_paths = [];

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

            $l_auth_methods = [];

            if ($l_auth_module_obj) {
                $l_auth_methods = $l_auth_module_obj->get_auth_methods();
            }

            $l_rights_supervisor = [isys_auth::SUPERVISOR];
            // Set path array
            foreach ($l_auth_methods as $l_method => $l_content) {
                if (in_array($l_method, $l_ignore_methods)) {
                    continue;
                }

                $l_content['title'] = isys_application::instance()->container->get('language')->get($l_content['title']);

                if (isset($l_content['rights'])) {
                    // get only the rights which are defined in $l_content['rights']
                    if (in_array(isys_auth::VIEW, $l_content['rights']) && is_countable($l_content['rights']) && count($l_content['rights']) > 1) {
                        $l_key = array_search(isys_auth::VIEW, $l_content['rights']);
                        unset($l_content['rights'][$l_key]);
                    }
                    $l_rights = $l_content['rights'];
                } else {
                    $l_rights = $l_rights_supervisor;
                }

                if ($l_content['type'] == 'boolean') {
                    $l_auth_paths[$l_method][null] = $l_rights;
                } else {
                    $l_auth_paths[$l_method][isys_auth::WILDCHAR] = $l_rights;
                }
            }
            $this->m_dao->create_paths($l_current_user, $l_row['isys_module__id'], $l_auth_paths);
        }
        isys_caching::factory('auth-' . $l_current_user)
            ->clear();

        return true;
    }

    /**
     * @param               $categoryId
     * @param int           $entryId
     * @param               $success
     * @param int           $objectId
     * @param isys_cmdb_dao $dao
     *
     * @return void
     * @see ID-9320
     */
    public static function cleanupRightsAfterCategoryCreate($categoryId, $entryId, $success, $objectId, isys_cmdb_dao $dao): void
    {
        if (!self::shouldClearAuthCache($dao)) {
            return;
        }

        $objects = self::findRelatedObjects(
            is_numeric($entryId) ? (int)$entryId : null,
            is_numeric($objectId) ? (int)$objectId : null,
            $dao
        );

        self::clearAuthCache($objects);
    }

    /**
     * @param isys_cmdb_dao $dao
     * @param int           $entryId
     * @param               $success
     * @param int           $objectId
     *
     * @return void
     * @see ID-9320
     */
    public static function cleanupRightsAfterCategorySave(isys_cmdb_dao $dao, $entryId, $success, $objectId): void
    {
        if (!self::shouldClearAuthCache($dao)) {
            return;
        }

        $objects = self::findRelatedObjects(
            is_numeric($entryId) ? (int)$entryId : null,
            is_numeric($objectId) ? (int)$objectId : null,
            $dao
        );

        self::clearAuthCache($objects);
    }

    /**
     * @param isys_cmdb_dao $dao
     * @param int           $objectId
     * @param int           $entryId
     *
     * @return void
     * @see ID-9320
     */
    public static function cleanupRightsAfterCategoryRank(isys_cmdb_dao $dao, $objectId, $entryId): void
    {
        if (!self::shouldClearAuthCache($dao)) {
            return;
        }

        $objects = self::findRelatedObjects(
            is_numeric($entryId) ? (int)$entryId : null,
            is_numeric($objectId) ? (int)$objectId : null,
            $dao
        );

        self::clearAuthCache($objects);
    }

    /**
     * @param array $objects
     *
     * @return void
     */
    private static function clearAuthCache(array $objects = []): void
    {
        array_map(fn (isys_caching $cache) => $cache->clear(), isys_caching::find('auth-*'));

        // Clear user-related caches (for lists and trees).
        isys_cache::keyvalue()->ns('auth')->flush();

        foreach ($objects as $object) {
            isys_cache::keyvalue()->ns($object . '-' . isys_auth::VIEW)->flush();
            isys_cache::keyvalue()->ns($object . '-' . isys_auth::CREATE)->flush();
        }
    }

    /**
     * Decide which objects caches should be cleared, based on category data.
     *
     * @param int|null               $entryId
     * @param int|null               $objectId
     * @param isys_cmdb_dao_category $dao
     *
     * @return array
     * @see ID-10485
     */
    private static function findRelatedObjects(?int $entryId, ?int $objectId, isys_cmdb_dao_category $dao): array
    {
        $relatedObjects = [$objectId];

        if ($entryId === null && $objectId === null) {
            return [];
        }

        // Fetch related category entries by entry ID and/or object ID.
        $result = $dao->get_data($entryId, $objectId);
        $objectReferenceField = null;

        // @todo Use 'match' once PHP8+ is useable.
        switch (get_class($dao)) {
            case isys_cmdb_dao_category_g_contact::class:
                $objectReferenceField = 'isys_connection__isys_obj__id';
                break;

            case isys_cmdb_dao_category_s_person_contact_assign::class:
                $objectReferenceField = 'isys_catg_contact_list__isys_obj__id';
                break;

            case isys_cmdb_dao_category_s_person_group_contact_assign::class:
                $objectReferenceField = 'isys_obj__id';
                break;

            case isys_cmdb_dao_category_s_person_group_members::class:
                $objectReferenceField = 'isys_cats_person_list__isys_obj__id';
                break;

            case isys_cmdb_dao_category_s_person_assigned_groups::class:
                $objectReferenceField = 'isys_cats_person_group_list__isys_obj__id';
                break;

            case isys_cmdb_dao_category_g_location::class:
                $objectReferenceField = 'isys_catg_location_list__parentid';
                break;

            case isys_cmdb_dao_category_g_logical_unit::class:
                // No need to check for 'isys_cmdb_dao_category_g_assigned_workstation' because it extends 'isys_cmdb_dao_category_g_logical_unit'.
                $objectReferenceField = 'isys_catg_logical_unit_list__isys_obj__id__parent';
                break;

            case isys_cmdb_dao_category_g_person_assigned_workstation::class:
            case isys_cmdb_dao_category_g_assigned_logical_unit::class:
                $objectReferenceField = 'isys_catg_logical_unit_list__isys_obj__id';
                break;
        }

        if ($objectReferenceField !== null) {
            while ($row = $result->get_row()) {
                $relatedObjects[] = (int)$row[$objectReferenceField];
            }
        }

        return array_filter($relatedObjects, fn ($objectId) => is_int($objectId) && $objectId > 0);
    }

    /**
     * Method for clearing caches, after certain category entries have been created, saved or ranked.
     *
     * @param isys_cmdb_dao $dao
     *
     * @return void
     * @see ID-9320 Delete auth cache after certain categories where saved
     */
    private static function shouldClearAuthCache(isys_cmdb_dao $dao): bool
    {
        $authRelatedDaos = [
            isys_cmdb_dao_category_g_contact::class,                     // contact assignment
            isys_cmdb_dao_category_s_person_contact_assign::class,       // contact assignment
            isys_cmdb_dao_category_s_person_group_contact_assign::class, // contact assignment
            isys_cmdb_dao_category_s_person_group_members::class,        // person group
            isys_cmdb_dao_category_s_person_assigned_groups::class,      // person group
            isys_cmdb_dao_category_g_location::class,                    // location
            isys_cmdb_dao_category_g_assigned_workstation::class,        // logical location
            isys_cmdb_dao_category_g_assigned_logical_unit::class,       // logical location
            isys_cmdb_dao_category_g_logical_unit::class,                // logical location
            isys_cmdb_dao_category_g_person_assigned_workstation::class, // logical location
        ];

        foreach ($authRelatedDaos as $authRelatedDao) {
            if (is_a($dao, $authRelatedDao)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_module_id = defined_or_default('C__MODULE__AUTH');
        $this->m_db = isys_application::instance()->container->get('database');
        $this->m_dao = new isys_auth_dao($this->m_db);
        $this->language = isys_application::instance()->container->get('language');
    }

    /**
     * @return isys_auth_auth
     */
    public static function getAuth()
    {
        return isys_auth_auth::instance();
    }

    /**
     * @return array
     */
    public static function getDefaultRightsMapping(): array
    {
        return [
            isys_auth::CREATE => [],
            isys_auth::VIEW => [],
            isys_auth::EDIT => [isys_auth::CREATE],
            isys_auth::ARCHIVE => [],
            isys_auth::DELETE => [],
            isys_auth::EXECUTE => [isys_auth::VIEW],
            isys_auth::SUPERVISOR => [isys_auth::VIEW, isys_auth::CREATE, isys_auth::EDIT, isys_auth::ARCHIVE, isys_auth::DELETE, isys_auth::EXECUTE],
        ];
    }
}
