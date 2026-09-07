<?php

use idoit\AddOn\AuthableInterface;
use idoit\Component\ConstantManager;
use idoit\Component\Helper\Purify;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * i-doit
 *
 * Administration for Dialog and Dialog+ boxes.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_dialog_admin extends isys_module implements AuthableInterface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var boolean
     */
    protected static $m_licenced = true;

    /**
     * Use this array, if a dialog+ table shall be able to receive descriptions!
     *
     * @var  array
     */
    private $m_description_whitelist = [
        'isys_sla_service_level'
    ];

    /**
     * @var  isys_module_request
     */
    private $m_userrequest;

    /** @var isys_component_template_language_manager|null */
    protected $language;

    /**
     * Module constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = isys_application::instance()->container->get('language', ContainerInterface::NULL_ON_INVALID_REFERENCE);
    }

    /**
     * Initializes the module.
     *
     * @param isys_module_request $p_req
     *
     * @return $this
     */
    public function init(isys_module_request $p_req)
    {
        $this->m_userrequest = &$p_req;

        return $this;
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
     */
    public function build_tree(isys_component_tree $tree, $isSystemModule = true, $parentNodeId = null)
    {
        $nextId = $tree->count();
        $l_auth = self::getAuth();

        // Get dialog+ and dialogs of the custom categories.
        $dao = isys_dialog_admin_dao::instance(isys_application::instance()->container->get('database'));
        $tables = [];
        $dialogTables = $dao->get_dialog_tables(true);
        $customDialogs = $dao->get_custom_dialogs();
        $categoryFolders = [];

        foreach ($dialogTables as $table) {
            $tables[$table] = $this->language->get($table);
        }

        natcasesort($tables);

        if (!$isSystemModule) {
            $tree->set_tree_sort(false);
            $tree->set_tree_search(true);
            $parentNodeId = $tree->add_node(++$nextId, $parentNodeId, $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN'));
        }

        if (is_countable($customDialogs) && count($customDialogs)) {
            // Create rootfolder in tree first.
            $customFieldNode = $tree->add_node(++$nextId, $parentNodeId, $this->language->get('LC__UNIVERSAL__CUSTOM_DIALOG_PLUS'));
        }

        foreach ($tables as $table => $tableName) {
            $nodeParent = $parentNodeId;

            // @see ID-8941 Implement logic to put category related dialog+ fields in sub-directories.
            if (strpos($tableName, ':') !== false) {
                [$category, $tableName] = explode(':', $tableName, 2);

                $categoryIdentifier = Purify::formatFilename($category);

                if (!isset($categoryFolders[$categoryIdentifier])) {
                    $categoryFolders[$categoryIdentifier] = $tree->add_node(
                        ++$nextId,
                        $parentNodeId,
                        trim($category)
                    );
                }

                $nodeParent = $categoryFolders[$categoryIdentifier];
            }

            $l_strRowLink = isys_helper_link::create_url([
                C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
                C__GET__MODULE_SUB_ID => C__MODULE__DIALOG_ADMIN,
                'table'               => $table
            ]);

            $tree->add_node(
                ++$nextId,
                $nodeParent,
                trim($tableName),
                $l_strRowLink,
                null,
                null,
                ($table === $_GET["table"]),
                '',
                '',
                $l_auth->is_allowed_to(isys_auth::VIEW, 'TABLE/' . strtoupper($table))
            );
        }

        if (is_countable($customDialogs) && count($customDialogs)) {
            $preparedDialogs = [];

            // @see ID-5094 Append all category names at the end
            foreach ($customDialogs as $customDialog) {
                $key = $customDialog['fieldTitle'] . "|§§|" . $customDialog['identifier'];

                if (!isset($preparedDialogs[$key])) {
                    $preparedDialogs[$key] = [];
                }

                $preparedDialogs[$key][] = $customDialog['categoryTitle'];
            }

            foreach ($preparedDialogs as $key => $preparedDialog) {
                [$fieldTitle, $identifier] = explode('|§§|', $key);

                $l_link = isys_helper_link::create_url([
                    C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
                    C__GET__MODULE_SUB_ID => C__MODULE__DIALOG_ADMIN,
                    'table'               => 'isys_dialog_plus_custom',
                    'identifier'          => $identifier
                ]);

                $tree->add_node(
                    ++$nextId,
                    $customFieldNode,
                    $fieldTitle . '<small class="text-neutral-400 ml10" title="Identifier: ' . $identifier . '">(' . isys_helper_textformat::this_this_and_that($preparedDialog) .
                    ')</small>',
                    $l_link,
                    null,
                    null,
                    ("isys_dialog_plus_custom" === $_GET['table'] && $_GET['identifier'] === $identifier),
                    '',
                    '',
                    $l_auth->is_allowed_to(isys_auth::VIEW, 'CUSTOM/' . strtoupper($identifier))
                );
            }
        }
    }

    /**
     * @return $this|isys_module_dialog_admin
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function start()
    {
        $l_navbar = isys_component_template_navbar::getInstance();

        // Unpack request package.
        $auth = self::getAuth();
        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');
        $session = isys_application::instance()->container->get('session');

        $l_tree = $this->m_userrequest->get_menutree();
        $l_gets = Purify::purifyParams($this->m_userrequest->get_gets());
        $l_posts = $this->m_userrequest->get_posts();
        $l_addons = [];

        if ($_GET[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $this->build_tree($l_tree, false, -1);

            $template->assign('menu_tree', $l_tree->process($_GET[C__GET__TREE_NODE]));
        }

        $l_dao = new isys_cmdb_dao_dialog_admin($database);

        // Custom-Normal Dialog+ Handling for the check method.
        $l_auth_identifier = strtoupper($l_gets["identifier"]);
        $l_auth_path = 'CUSTOM';

        if ($l_gets['table'] !== 'isys_dialog_plus_custom') {
            $l_auth_identifier = strtoupper($l_gets['table']);
            $l_auth_path = 'TABLE';
        }

        $l_edit_right = $auth->is_allowed_to(isys_auth::EDIT, "{$l_auth_path}/{$l_auth_identifier}");
        $l_delete_right = $auth->is_allowed_to(isys_auth::DELETE, "{$l_auth_path}/{$l_auth_identifier}");

        // Switch back to list on cancel.
        if (in_array($_POST[C__GET__NAVMODE], [C__NAVMODE__CANCEL, C__NAVMODE__NEW, C__NAVMODE__SAVE, C__NAVMODE__PURGE])) {
            unset($l_gets["id"]);
        }

        if (isset($l_gets['id'])) {
            // @see  ID-6539  Hard redirect instead AJAX, so we get a fresh URL.
            $getParameters = $_GET;

            unset($getParameters[C__GET__ID]);

            $l_navbar
                ->set_save_mode('formsubmit')
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
                ->set_visible(true, C__NAVBAR_BUTTON__SAVE)
                ->set_js_onclick("document.location.href='" . isys_helper_link::create_url($getParameters) . "'", C__NAVBAR_BUTTON__CANCEL)
                ->set_visible(true, C__NAVBAR_BUTTON__CANCEL);
        } else {
            $l_navbar
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                ->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                ->set_visible(true, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(true, C__NAVBAR_BUTTON__PURGE);
        }

        if ($session->get('dialog-admin-saved', false)) {
            isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
            $session->unset('dialog-admin-saved');
        }

        switch ($l_posts[C__GET__NAVMODE]) {
            case C__NAVMODE__PURGE:
                // @see ID-10967 Check rights before actually processing further...
                if (!$l_delete_right) {
                    $tableName = $this->language->get($_GET['table']);

                    throw new isys_exception_auth(str_replace(':table', $tableName, $this->language->get('LC__DIALOG_ADMIN__AUTH__NOT_ALLOWED_TO_DELETE')));
                }

                if (is_array($l_posts['id']) && count($l_posts['id']) > 0) {
                    try {
                        foreach ($l_posts['id'] as $dialogId) {
                            $l_dao->delete($l_gets['table'], $dialogId);
                        }
                    } catch (Exception $e) {
                        isys_notify::warning($e->getMessage());
                    }
                } elseif ($l_posts["dialog_id"] > 0) {
                    try {
                        $l_dao->delete($l_gets['table'], $l_posts["dialog_id"]);
                    } catch (Exception $e) {
                        isys_notify::warning($e->getMessage());
                    }
                }

                // Clear constant cache because of the constant

                /** @var ConstantManager $cm */
                $cm = isys_application::instance()->container->get('constant_manager');
                $cm->deleteTenantCacheFile();

                $l_gets["id"] = 0;
                break;
            case C__NAVMODE__SAVE:
                try {
                    // @see ID-10967 Check rights before actually processing further...
                    if (!$l_edit_right) {
                        $tableName = $this->language->get($_GET['table']);

                        throw new isys_exception_auth(str_replace(':table', $tableName, $this->language->get('LC__DIALOG_ADMIN__AUTH__NOT_ALLOWED_TO_EDIT')));
                    }

                    $dialogTitle = trim($l_posts['title']);
                    $dialogConstant = trim($l_posts['const']);
                    $dialogIdentifier = (isset($l_gets['identifier']) ? trim($l_gets['identifier']) : null);

                    // @see  ID-5418  Do not check for "empty" in case a user inserts a zero.
                    if (mb_strlen($dialogTitle)) {
                        $id = null;
                        $daoDialog = new isys_cmdb_dao_dialog($database, $l_gets['table']);

                        // Validate the constant string (if given).
                        if (mb_strlen($dialogConstant)) {
                            if (preg_match('~^[a-zA-Z_][a-zA-Z0-9_]*$~', $dialogConstant, $match) !== 1) {
                                isys_notify::warning($this->language->get('LC__DIALOG_ADMIN__INVALID_CONSTANT'), ['sticky' => true]);
                                $dialogConstant = '';
                            }
                        }

                        if (isset($l_posts['dialog_id']) && $l_posts['dialog_id'] > 0) {
                            $id = $l_posts['dialog_id'];
                        }

                        if ($dialogIdentifier) {
                            if ($daoDialog->entryExists($dialogTitle, $dialogIdentifier, [$id])) {
                                throw new isys_exception_general($this->language->get('LC__POPUP__DIALOG_PLUS__MESSAGE__DUPLICATE_ENTRY', $dialogTitle));
                            }
                        } elseif (!empty($l_posts['C__DIALOG__PARENTS'])) {
                            if ($daoDialog->get_data_by_parent($dialogTitle, $l_posts['C__DIALOG__PARENTS'], [$id])) {
                                throw new isys_exception_general($this->language->get('LC__POPUP__DIALOG_PLUS__MESSAGE__DUPLICATE_ENTRY', $dialogTitle));
                            }
                        } elseif ($daoData = $daoDialog->get_data(null, $dialogTitle)) {
                            if ($daoData[$l_gets['table'] . '__id'] != $id) {
                                throw new isys_exception_general($this->language->get('LC__POPUP__DIALOG_PLUS__MESSAGE__DUPLICATE_ENTRY', $dialogTitle));
                            }
                        }

                        if ($id > 0) {
                            $l_dao->save(
                                $id,
                                $l_gets['table'],
                                $dialogTitle,
                                $l_posts['sort'],
                                $dialogConstant,
                                $l_posts['status'],
                                (!empty($l_posts['C__DIALOG__PARENTS']) ? $l_posts['C__DIALOG__PARENTS'] : null),
                                $l_posts['description']
                            );

                            unset($l_gets['id']);
                        } else {
                            // @see ID-9885 Set the default relation type when a contact role gets created.
                            if ($l_gets['table'] === 'isys_contact_tag') {
                                $l_posts['C__DIALOG__PARENTS'] = defined_or_default('C__RELATION_TYPE__USER');
                            }

                            $id = $l_dao->create(
                                $l_gets['table'],
                                $dialogTitle,
                                $l_posts['sort'],
                                $dialogConstant,
                                $l_posts['status'],
                                (!empty($l_posts['C__DIALOG__PARENTS']) ? $l_posts['C__DIALOG__PARENTS'] : null),
                                $dialogIdentifier,
                                $l_posts['description']
                            );
                        }

                        $l_navbar->set_visible(false, C__NAVBAR_BUTTON__SAVE)
                            ->set_visible(false, C__NAVBAR_BUTTON__CANCEL)
                            ->set_active($l_edit_right, C__NAVBAR_BUTTON__NEW)
                            ->set_active($l_delete_right, C__NAVBAR_BUTTON__PURGE)
                            ->set_visible(true, C__NAVBAR_BUTTON__NEW)
                            ->set_visible(true, C__NAVBAR_BUTTON__PURGE);

                        // Clear constant cache because of the constant
                        /** @var ConstantManager $cm */
                        $cm = isys_application::instance()->container->get('constant_manager');
                        $cm->deleteTenantCacheFile();

                        // @see  ID-6539  Hard redirect after saving, so we get a fresh URL.
                        $getParameters = $_GET;

                        $session->set('dialog-admin-saved', true);

                        unset($getParameters[C__GET__ID]);

                        isys_core::send_header('Location', isys_helper_link::create_url($getParameters));
                    }
                } catch (isys_exception_general $e) {
                    isys_notify::error($e->getMessage(), ['sticky' => true]);
                }
                break;

            case C__NAVMODE__EDIT:
                // only edit first element, no list editing here
                $l_gets['id'] = $_POST['id'][0];
                // no break

            case C__NAVMODE__NEW:
                // @see ID-10967 Check rights before actually processing further...
                if (!$l_edit_right) {
                    $tableName = $this->language->get($_GET['table']);

                    throw new isys_exception_auth(str_replace(':table', $tableName, $this->language->get('LC__DIALOG_ADMIN__AUTH__NOT_ALLOWED_TO_EDIT')));
                }

                $l_navmode = $l_posts[C__GET__NAVMODE];
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                    ->set_active($l_edit_right, C__NAVBAR_BUTTON__CANCEL)
                    ->set_visible(false, C__NAVBAR_BUTTON__NEW)
                    ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(false, C__NAVBAR_BUTTON__PURGE);
                break;
        }

        if ($l_gets['table']) {
            // Addons.
            switch ($l_gets['table']) {
                case 'isys_relation_type':
                    $l_addons['relation'] = true;

                    if ($l_posts[C__GET__NAVMODE] && empty($l_posts['C__UNIVERSAL__BUTTON_CANCEL']) && isset($id) && $id > 0) {
                        $l_dao->mod_relation_type($id, $l_posts['relation_master'], $l_posts['relation_slave']);
                    }
                    break;
            }

            $l_parent_table = $l_dao->get_parent_table($l_gets['table']);

            // @see ID-9885 Don't refer to the relation types.
            if ($l_parent_table === 'isys_relation_type') {
                $l_parent_table = false;
            }

            $l_data = [
                'status' => C__RECORD_STATUS__NORMAL,
                'sort'   => 99
            ];

            if ($l_gets['id'] > 0) {
                // Am i allowed to create a new dialog+ entry.
                if ($auth->check(isys_auth::EDIT, $l_auth_path . '/' . $l_auth_identifier)) {
                    $l_daodata = $l_dao->get_data($l_gets['table'], $l_gets['id']);

                    if (is_countable($l_daodata) && count($l_daodata) > 0) {
                        $l_row = $l_daodata->get_row();

                        // @see  ID-8492  Add some special handling for custom dialog data.
                        if ($l_parent_table === 'isys_dialog_plus_custom') {
                            $l_data['parent__id'] = $l_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'];
                            unset($l_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id']);
                        }

                        foreach ($l_row as $l_key => $l_value) {
                            if ($l_parent_table !== 'isys_dialog_plus_custom' && strpos($l_key, $l_parent_table) === 0) {
                                $l_data[str_replace($l_parent_table, 'parent', str_replace($l_gets['table'] . '__', '', $l_key))] = $l_value;
                            } else {
                                $l_data[str_replace($l_gets['table'] . '__', '', $l_key)] = $l_value;
                            }
                        }
                    }
                }
            } elseif ($l_posts[C__GET__NAVMODE] != C__NAVMODE__NEW) {
                // Am i allowed to view dialogs content.
                if ($auth->check(isys_auth::VIEW, $l_auth_path . '/' . $l_auth_identifier)) {
                    try {
                        if (($l_content = $this->get_content($l_gets['table']))) {
                            $template->assign('g_list', $l_content);
                        } else {
                            $template->assign('g_message', "Table {$l_gets['table']} can not be accessed.");
                        }
                    } catch (isys_exception_database $e) {
                        $template->assign('g_message', "Table {$l_gets['table']} does not exist.");
                    }
                }
            }

            if ($l_parent_table) {
                $l_ar_data = [];
                $l_arr_res = $l_dao->get_dialog($l_parent_table);
                while ($l_row = $l_arr_res->get_row()) {
                    // @see  ID-8492  Skip potential parent, if are a child themselves.
                    if ($l_parent_table === 'isys_dialog_plus_custom' && $l_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'] > 0) {
                        continue;
                    }

                    $l_ar_data[$l_row[$l_parent_table . '__id']] = $l_row[$l_parent_table . '__title'];
                }

                $l_data['has_parent'] = 1;
                $l_rules['C__DIALOG__PARENTS']['p_arData'] = $l_ar_data;
                $l_rules['C__DIALOG__PARENTS']['p_strSelectedID'] = $l_data['parent__id'];
                $l_rules['C__DIALOG__PARENTS']['p_bEditMode'] = 1;
                $l_rules['C__DIALOG__PARENTS']['p_bDbFieldNN'] = $l_parent_table !== 'isys_dialog_plus_custom';

                $template->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
            }

            $template->assign('recordStatus', [
                C__RECORD_STATUS__NORMAL   => $this->language->get('LC__CMDB__RECORD_STATUS__NORMAL'),
                C__RECORD_STATUS__ARCHIVED => $this->language->get('LC__CMDB__RECORD_STATUS__ARCHIVED'),
                C__RECORD_STATUS__DELETED  => $this->language->get('LC__CMDB__RECORD_STATUS__DELETED')
            ]);

            $template->assign(
                'display_wysiwyg',
                (in_array($l_gets['table'], $this->m_description_whitelist) && in_array($l_gets['table'] . '__description', $l_dao->get_table_fields($l_gets['table'])))
            )
                ->assign('g_data', $l_data);
        }

        $contentTitle = $this->language->get('LC__CMDB__TREE__SYSTEM__TOOLS__DIALOG_ADMIN');

        if (isset($_GET['table']) && $_GET['table'] !== 'isys_dialog_plus_custom') {
            $contentTitle = $this->language->get($_GET['table']);
        }

        $template->activate_editmode()
            ->assign('content_title', $contentTitle)
            ->assign('addons', $l_addons)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1");

        // This is necessary, because "$template->activate_editmode()" will set the navmode to EDIT.
        if (isset($l_navmode) && $l_navmode == C__NAVMODE__NEW) {
            global $g_navmode;

            $g_navmode = C__NAVMODE__NEW;
            $_POST[C__GET__NAVMODE] = C__NAVMODE__NEW;
        }

        $template->include_template('contentbottomcontent', 'content/bottom/content/module_dialog_admin.tpl');

        return $this;
    }

    /**
     * Fill the list from the specified 'Dialog' table.
     *
     * @param   string $p_table
     *
     * @return string|bool
     * @throws Exception
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function get_content($p_table)
    {
        $database = isys_application::instance()->container->get('database');

        $p_table = $database->escape_string($p_table);

        $l_dao = isys_cmdb_dao_dialog_admin::instance($database);
        $l_sql = 'SHOW TABLES LIKE \'' . $p_table . '\';';
        $l_query = $database->query($l_sql);

        if ($database->num_rows($l_query) ===  0) {
            throw new isys_exception_database("Table {$p_table} does not exists.");
        }

        if ($p_table !== 'isys_dialog_plus_custom') {
            if (!in_array(
                $p_table,
                isys_dialog_admin_dao::instance(isys_application::instance()->container->get('database'))
                    ->get_dialog_tables()
            )) {
                return false;
            }
        }

        $l_listres = (!empty($_GET['identifier'])) ? $l_dao->get_custom_dialog_data($_GET['identifier']) : $l_dao->get_data($p_table);

        $rowLinkParameters = [
            C__GET__MODULE_ID     => defined_or_default('C__MODULE__SYSTEM'),
            'table'               => $p_table,
            'identifier'          => $_GET['identifier'],
            C__GET__MODULE_SUB_ID => defined_or_default('C__MODULE__DIALOG_ADMIN'),
            C__GET__TREE_NODE     => $_GET[C__GET__TREE_NODE],
            C__GET__ID            => '[{' . $p_table . '__id}]'
        ];

        if (isset($_GET['orderBy']) && isset($_GET['orderByDir'])) {
            $rowLinkParameters['orderBy'] = $_GET['orderBy'];
            $rowLinkParameters['orderByDir'] = $_GET['orderByDir'];
        }

        $l_strRowLink = isys_helper_link::create_url($rowLinkParameters);

        // Array with table header titles.
        $l_arTableHeader = [
            $p_table . '__id'     => 'LC__UNIVERSAL__ID',
            $p_table . '__title'  => 'LC__CMDB__CATP__TITLE',
            $p_table . '__const'  => 'LC__CMDB__CUSTOM_CATEGORIES__CONSTANT',
            $p_table . '__status' => 'LC__UNIVERSAL__STATUS'
        ];

        // ID-2372 Deletable shouldn't be shown in custom Dialog+
        if ($p_table !== 'isys_dialog_plus_custom') {
            $l_arTableHeader['deleteable'] = 'LC__REGEDIT__DELETEABLE';
        }

        $l_parent_table = $l_dao->get_parent_table($p_table);

        // @see ID-9885 Don't refer to the relation types.
        if ($l_parent_table !== 'isys_relation_type') {
            if ($l_parent_table === 'isys_dialog_plus_custom') {
                $l_arTableHeader['isys_dialog_plus_custom__isys_dialog_plus_custom__id'] = 'LC__DIALOG_ADMIN__PARENT';
            } else {
                $l_arTableHeader[$l_parent_table . '__title'] = $l_parent_table;
            }
        }

        $l_objList = new isys_component_list(null, $l_listres);

        $l_objList
            ->config($l_arTableHeader, $l_strRowLink, '[{' . $p_table . '__id}]', true)
            ->set_table_config($l_objList->get_table_config()
            ->setFilterProperty($p_table . '__title'))
            ->createTempTable();

        $l_objList->set_row_modifier($this, 'modify_dialog_row');

        return $l_objList->getTempTableHtml();
    }

    /**
     * Modify row method for dialog+ admin entries.
     *
     * @param array &$p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_dialog_row(&$p_row)
    {
        $mapping = [
            0                          => isys_tenantsettings::get('gui.empty_value', '-'),
            C__RECORD_STATUS__BIRTH    => 'LC__CMDB__RECORD_STATUS__BIRTH',
            C__RECORD_STATUS__NORMAL   => 'LC__CMDB__RECORD_STATUS__NORMAL',
            C__RECORD_STATUS__ARCHIVED => 'LC__CMDB__RECORD_STATUS__ARCHIVED',
            C__RECORD_STATUS__DELETED  => 'LC__CMDB__RECORD_STATUS__DELETED',
            C__RECORD_STATUS__PURGE    => 'LC__NAVIGATION__NAVBAR__PURGE'
        ];

        foreach ($p_row as $key => &$value) {
            if (substr($key, -8) == '__status' && is_numeric($value)) {
                $value = $mapping[$value];
            }
        }

        if (isset($p_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'])) {
            if (empty($p_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'])) {
                $p_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'] = isys_tenantsettings::get('gui.empty_value', '-');
            } else {
                $database = isys_application::instance()->container->get('database');

                $p_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'] = isys_cmdb_dao_dialog_admin::instance($database)
                    ->get_data('isys_dialog_plus_custom', $p_row['isys_dialog_plus_custom__isys_dialog_plus_custom__id'])
                    ->get_row_value('isys_dialog_plus_custom__title');
            }
        }
    }

    /**
     * @return isys_auth_dialog_admin
     */
    public static function getAuth()
    {
        return isys_auth_dialog_admin::instance();
    }
}
