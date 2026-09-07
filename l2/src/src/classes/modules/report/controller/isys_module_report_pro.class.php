<?php

use idoit\Module\Report\Configuration\Export;
use idoit\Module\Report\Configuration\Import;
use idoit\Module\Report\Configuration\ImportException;

/**
 * i-doit
 *
 * i-doit Report Manager PRO Version.
 *
 * @package       i-doit
 * @subpackage    Modules
 * @copyright     synetics GmbH
 * @license       http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report_pro extends isys_module_report
{
    // Define, if this module shall be displayed in the named menus.
    /**
     *
     */
    const DISPLAY_IN_MAIN_MENU = true;
    /**
     *
     */
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * URL of the report-repository.
     *
     * @var         string
     * @deprecated  ??
     */
    private $m_report_browser_www = "https://reports-ng.i-doit.org/";

    /**
     * Header of the current report
     *
     * @var array
     */
    private $m_report_headers = [];

    /** @var isys_component_template_language_manager */
    protected $language;

    private isys_report_dao $dao;

    /**
     * @param $p_report_class_path
     */
    public static function add_external_view($p_report_class_path)
    {
        isys_register::factory('additional-report-views')
            ->set($p_report_class_path);
    }

    /**
     * @return string[]
     */
    public static function getReportListHeaders()
    {
        return [
            'isys_report__id'              => 'ID',
            'isys_report__title'           => 'LC__UNIVERSAL__TITLE',
            'category_title'               => 'LC_UNIVERSAL__CATEGORY',
            'with_qb'                      => 'LC__REPORT__LIST__VIA_QUERY_BUILDER_CREATED',
            'isys_report__category_report' => 'LC__REPORT__FORM__CATEGORY_REPORT',
            'isys_report__description'     => 'LC__UNIVERSAL__DESCRIPTION'
        ];
    }

    /**
     * Method for assigning the object types to the dropdown.
     *
     * @throws Exception
     * @global isys_component_database $g_comp_database
     */
    public function build_object_types()
    {
        global $g_comp_database;

        $l_result = $g_comp_database->query('SELECT isys_obj_type__id AS id, isys_obj_type__title AS type
            FROM isys_obj_type
            WHERE isys_obj_type__show_in_tree = 1
            OR isys_obj_type__const = "C__OBJTYPE__RELATION";');

        while ($l_row = $g_comp_database->fetch_array($l_result)) {
            $l_objects[$l_row['id']] = $this->language->get($l_row['type']);
        }

        asort($l_objects);

        isys_application::instance()->container->get('template')->assign('object_types', $l_objects);
    }

    /**
     * Method for preparing the report-data to view it properly with the TabOrder.
     * This is used by the ajax handler for the online reports and the normal reports.
     *
     * @param string $l_query
     * @param null   $deprecated
     * @param bool   $p_ajax_request
     * @param bool   $p_limit
     * @param bool   $p_raw_data
     * @param bool   $p_title_chaining
     * @param bool   $compressedMultivalueResults
     * @param bool   $return_data
     * @return mixed  If this method is called by an ajax request, it returns an array. If not, null.
     * @throws Exception
     */
    public function process_show_report(
        $l_query,
        $deprecated = null,
        $p_ajax_request = false,
        $p_limit = false,
        $p_raw_data = false,
        $p_title_chaining = true,
        $compressedMultivalueResults = false,
        $showHtml = false,
        $return_data = false
    ) {
        try {
            $l_result = isys_report_dao::instance()->query($l_query, null, $p_raw_data, $p_title_chaining);
            $l_json = $l_return = [];
            $l_counter = 0;

            if (isset($l_result['headers'])) {
                // @see  ID-8021 / ID-7963  In some situations (empty querybuilder data) no headers are set.
                if (empty($l_result['headers'])) {
                    // We set a 'ID' header as default, so the report contains at least one field.
                    $l_result['headers'] = ['ID'];

                    foreach ($l_result['content'] as &$value) {
                        $value['ID'] = $value['__id__'];
                    }

                    // We'll also notify the user.
                    isys_notify::warning($this->language->get('LC__REPORT__EMPTY_HEADERS'), ['life' => 10]);
                }

                $this->set_report_headers($l_result['headers']);
            }

            $l_return = isys_report::reformatResult($l_result, $showHtml, $compressedMultivalueResults, $p_limit);

            if ($p_ajax_request) {
                return $l_return;
            }

            $l_json = isys_format_json::encode($l_return);
            if (!$return_data) {
                isys_application::instance()->container->get('template')
                    ->assign('rowcount', $l_result['num'])
                    ->assign("listing", $l_result)
                    ->assign("result", $l_json);
                return '';
            }

            return [
                'rowcount' => $l_result['num'],
                'listing' => $l_result,
                'result' => $l_json
            ];
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')
                ->error($e->getMessage());
        }
    }

    /**
     * @param bool $asDialog
     * @param bool $checkRight
     * @return array
     */
    public function getViews(bool $asDialog = false, bool $checkRight = false): array
    {
        $l_views = [];

        try {
            $auth = isys_module_report::getAuth();
            $reportViews = glob(isys_module_report::getPath() . 'views/*/*.class.php');

            foreach ($reportViews as $filePath) {
                $reportViewClassName = str_replace('.class.php', '', basename($filePath));

                if (in_array($reportViewClassName, ['isys_report_view', 'isys_report_view_sla'], true)) {
                    continue;
                }

                if ($checkRight && !$auth->is_allowed_to(isys_auth::VIEW, 'VIEWS/' . strtoupper($reportViewClassName))) {
                    continue;
                }

                if (!class_exists($reportViewClassName) || !is_a($reportViewClassName, isys_report_view::class, true)) {
                    continue;
                }

                /** @var isys_report_view $reportViewObject */
                $reportViewObject = new $reportViewClassName();

                if (!method_exists($reportViewObject, 'name')) {
                    continue;
                }

                if ($asDialog) {
                    $l_views[$reportViewClassName] = $this->language->get($reportViewObject->name());
                    continue;
                }

                $l_views[] = [
                    "filename"    => "{$reportViewClassName}.class.php",
                    "view"        => str_replace('isys_report_view_', '', $reportViewClassName),
                    "class"       => $reportViewClassName,
                    "name"        => $this->language->get($reportViewObject->name()),
                    "description" => $this->language->get($reportViewObject->description()),
                    "viewtype"    => $reportViewObject->viewtype()
                ];
            }

            $l_external_views = isys_register::factory('additional-report-views')
                ->get();

            foreach ($l_external_views as $l_external_view_class => $l_tmp) {
                if (!is_file($l_external_view_class)) {
                    continue;
                }

                include_once($l_external_view_class);

                $l_classname = strstr(basename($l_external_view_class), '.class.php', true);

                if ($checkRight && !self::getAuth()->is_allowed_to(isys_auth::VIEW, 'VIEWS/' . strtoupper($l_classname))) {
                    continue;
                }

                /** @var isys_report_view $l_class */
                $l_class = new $l_classname;

                if ($asDialog) {
                    $l_views[$l_classname] = $this->language->get($l_class->name());
                    continue;
                }

                $l_views[] = [
                    "filename"    => basename($l_external_view_class),
                    "view"        => $l_classname . '.tpl',
                    "class"       => $l_classname,
                    "name"        => $this->language->get($l_class->name()),
                    "description" => $this->language->get($l_class->description()),
                    "viewtype"    => $l_class->viewtype()
                ];
            }
        } catch (Exception $e) {
            isys_notify::error($e->getMessage());
        }

        // Adding some further report-views, which got added by modules.
        return $l_views;
    }

    /**
     * Checks if the report can only be edited via sql editor or not
     *
     * @param $p_id
     *
     * @return bool
     * @throws Exception
     */
    public function is_sql_only_report($p_id): bool
    {
        $l_report = $this->dao->getReportRaw((int)$p_id);

        return empty(trim($l_report['isys_report__querybuilder_data'] ?? ''));
    }

    /**
     * Enhances the breadcrumb navigation.
     *
     * @param $p_gets
     *
     * @return array
     * @throws isys_exception_database
     * @throws Exception
     */
    public function breadcrumb_get(&$p_gets)
    {
        if (!defined('C__MODULE__REPORT')) {
            return [];
        }
        $l_result = [];

        switch ($p_gets[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                $l_title = $this->language->get('LC__REPORT__MAINNAV__STANDARD_QUERIES');

                if (isset($p_gets['report_category'])) {
                    $l_report_category = current($this->dao->get_report_categories($p_gets['report_category']));
                    $l_report_category_title = $l_report_category['isys_report_category__title'];
                }

                if (isset($p_gets[C__GET__REPORT_REPORT_ID])) {
                    $l_report_title = $this->dao->get_report_title_by_id($p_gets[C__GET__REPORT_REPORT_ID]);
                }

                break;
            case C__REPORT_PAGE__REPORT_BROWSER:
                $l_title = $this->language->get('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $l_title = 'Views';

                if (isset($p_gets[C__GET__REPORT_REPORT_ID]) && class_exists($p_gets[C__GET__REPORT_REPORT_ID])) {
                    $l_report_title = $this->language->get($p_gets[C__GET__REPORT_REPORT_ID]::name());
                }
                break;
            default:
                return null;
                break;
        }

        if (isset($l_report_category_title)) {
            $l_result[] = [
                $l_report_category_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                    'report_category'   => $p_gets['report_category']
                ]
            ];
        } else {
            $l_result[] = [
                $l_title => [
                    C__GET__MODULE_ID   => C__MODULE__REPORT,
                    C__GET__TREE_NODE   => $p_gets[C__GET__TREE_NODE],
                    C__GET__REPORT_PAGE => $p_gets[C__GET__REPORT_PAGE],
                ]
            ];
        }

        if (isset($l_report_title)) {
            $l_result[] = [
                $l_report_title => []
            ];
        }

        return $l_result;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param isys_component_tree $p_tree
     * @param bool                $p_system_module
     * @param int                 $p_parent
     * @param int                 $expandReports
     * @throws isys_exception_database
     * @throws Exception
     * @see       isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null, $expandReports = 0)
    {
        if (!defined('C__MODULE__REPORT')) {
            return;
        }
        $l_parent = -1;
        $l_submodule = '';

        $p_tree->set_tree_sort(false);

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__REPORT;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__REPORT . '0', $l_parent, 'Report Manager');
        }

        $l_report_root = $p_tree->add_node(
            C__MODULE__REPORT . '2',
            $l_root,
            $this->language->get('LC__REPORT__MAINNAV__STANDARD_QUERIES'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            '',
            ((($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__CUSTOM_REPORTS || !isset($_GET[C__GET__REPORT_PAGE])) && !isset($_GET['report_category'])) ? 1 : 0),
            '',
            '',
            (isys_auth_report::instance()
                    ->has('custom_report') || isys_auth_report::instance()
                    ->has('reports_in_category')),
            '',
            $expandReports
        );

        $l_res = $this->dao->get_report_categories(null, false);
        while ($l_row = $l_res->get_row()) {
            $p_tree->add_node(
                C__MODULE__REPORT . '2' . $l_row['isys_report_category__id'],
                $l_report_root,
                $this->language->get($l_row['isys_report_category__title']),
                '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '2' .
                $l_row['isys_report_category__id'] . '&' . C__GET__REPORT_PAGE . '=' . C__REPORT_PAGE__CUSTOM_REPORTS . '&report_category=' .
                $l_row['isys_report_category__id'] . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
                '',
                '',
                ((isset($_GET['report_category']) && $_GET['report_category'] == $l_row['isys_report_category__id']) ? 1 : 0),
                '',
                '',
                (isys_auth_report::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'REPORTS_IN_CATEGORY/' . $l_row['isys_report_category__id']))
            );
        }

        $p_tree->add_node(
            C__MODULE__REPORT . '3',
            $l_root,
            $this->language->get('LC__REPORT__MAINNAV__QUERY_BROWSER'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '3' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__REPORT_BROWSER . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/axialis/web-email/internet-network-green.svg',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__REPORT_BROWSER) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("online_reports")
        );

        $p_tree->add_node(
            C__MODULE__REPORT . '5',
            $l_root,
            'Views',
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__REPORT . '5' . '&' . C__GET__REPORT_PAGE .
            '=' . C__REPORT_PAGE__VIEWS . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=' . $_GET['mNavID'],
            '',
            'images/axialis/database/data.svg',
            (($_GET[C__GET__REPORT_PAGE] == C__REPORT_PAGE__VIEWS) ? 1 : 0),
            '',
            '',
            isys_auth_report::instance()
                ->has("views")
        );
    }

    /**
     * Start module Report Manager.
     *
     * @throws Exception
     */
    public function start()
    {
        $l_id = 0;

        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call")) {
            $this->processAjaxRequest();
            die;
        }

        $request = isys_module_request::get_instance();
        $gets = $request->get_gets();
        $post = $request->get_posts();
        $files = $request->getFiles();
        $auth = isys_auth_report::instance();
        $template = isys_application::instance()->container->get('template');

        $template->assign('report_assets_dir', isys_module_report::getPath() . 'assets/');

        try {
            if (isset($gets["export"])) {
                $this->exportReport($gets["report_id"], $gets["type"], $gets[C__CMDB__GET__OBJECT]);
                die;
            }

            if (isset($gets["exportReportConfig"])) {
                $auth->check(isys_auth::EXECUTE, 'EXPORT_REPORT_CONFIG');
                $ids = isys_format_json::is_json_array($gets['reportIds']) ? isys_format_json::decode($gets['reportIds']) : [];
                $this->exportReportConfig($ids);
                die;
            }

            if (!empty($files) && $post[C__GET__NAVMODE] == C__NAVMODE__UPLOAD) {
                $auth->check(isys_auth::EXECUTE, 'IMPORT_REPORT_CONFIG');
                $this->importReportConfig($files);
            }

            isys_application::instance()->container->get('template')->assign('allowedObjectGroup', isys_auth_cmdb::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'OBJ_IN_TYPE/C__OBJECT_TYPE__GROUP'));

            switch ($gets[C__GET__REPORT_PAGE]) {
                case C__REPORT_PAGE__REPORT_BROWSER:
                    $auth->check(isys_auth::VIEW, 'ONLINE_REPORTS');
                    $this->processReportBrowser();
                    break;
                case C__REPORT_PAGE__VIEWS:
                    $auth->check(isys_auth::VIEW, 'VIEWS');
                    $this->processViews();
                    break;
                case C__REPORT_PAGE__CUSTOM_REPORTS:
                default:
                    switch ($post[C__GET__NAVMODE]) {
                        case C__NAVMODE__DUPLICATE:
                            try {
                                $this->duplicate_report($post['report_id']);
                                unset($post['savedCheckboxes']);
                                $this->processReportList();
                                isys_notify::success($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__SUCCESS'));
                            } catch (Exception $e) {
                                isys_notify::error($this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__ERROR'));
                            }
                            break;
                        case C__NAVMODE__EDIT:
                            if (is_array($post["id"])) {
                                $auth->check_report_right(isys_auth::EDIT, $post["id"][0]);
                                if (isset($post["querybuilder"]) && $post["querybuilder"] != '') {
                                    if ((bool)$post["querybuilder"]) {
                                        if ($this->is_sql_only_report($post["id"][0])) {
                                            $this->processReportList();
                                            isys_notify::error($this->language->get('LC__REPORT__LIST__EDIT__ERROR'));
                                        } else {
                                            $this->processQueryBuilder($post["id"][0], $gets['report_category']);
                                        }
                                    } else {
                                        $this->editReport($post["id"][0]);
                                    }
                                } else {
                                    if ($this->is_sql_only_report($post["id"][0])) {
                                        $this->editReport($post["id"][0]);
                                    } else {
                                        $this->processQueryBuilder($post["id"][0], $gets['report_category']);
                                    }
                                }
                            } else {
                                $this->processReportList();
                            }

                            break;

                        case C__NAVMODE__SAVE:
                            try {
                                if (!empty($post['report_mode'])) {
                                    $auth->check(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');
                                    switch ($post['report_mode']) {
                                        case 'category':
                                            if ($post['category_selection'] > 0) {
                                                // update
                                                $this->update_category(
                                                    $post['category_selection'],
                                                    $post['category_title'],
                                                    $post['category_description'],
                                                    $post['category_sort']
                                                );
                                            } else {
                                                // create
                                                $this->create_category($post['category_title'], $post['category_description'], $post['category_sort']);
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                    $this->processReportList();
                                } else {
                                    try {
                                        // isys_auth_report::instance()->check(isys_auth::EXECUTE, 'EDITOR');

                                        if (!empty($post['report_id'])) {
                                            // Update
                                            if (!empty($post["queryBuilder"])) {
                                                $this->saveReport($post['report_id'], true);
                                            } else {
                                                // SQL-Editor
                                                $this->saveReport($post['report_id']);
                                            }
                                        } else {
                                            // Create
                                            if (isset($post["queryBuilder"]) && $post["queryBuilder"] == 1) {
                                                // Query Builder
                                                $l_id = $this->createReport(true);
                                            } else {
                                                // SQL Editor
                                                $l_id = $this->createReport(false);
                                            }
                                        }
                                        isys_notify::success($this->language->get('LC__REPORT__FORM__SUCCESS'));

                                        if ($l_id > 0) {
                                            header('Content-Type: application/json');
                                            die(isys_format_json::encode([
                                                'success' => true,
                                                'id'      => $l_id
                                            ]));
                                        }
                                    } catch (Exception $e) {
                                        isys_notify::error($this->language->get('LC__REPORT__FORM__ERROR'));
                                    }
                                }
                            } catch (Exception $e) {
                                isys_notify::error($e->getMessage());
                            }
                            break;

                        case C__NAVMODE__NEW:
                            // @see  ID-4035  Replace the "editor" right with "create: reports in category".
                            if (is_numeric($gets['report_category'])) {
                                self::getAuth()->check(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/' . $gets['report_category']);
                            } else {
                                self::getAuth()->check(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/*');
                            }

                            if (isset($post['querybuilder']) && $post['querybuilder'] != '') {
                                switch ($post['querybuilder']) {
                                    case '0':
                                        $this->editReport();
                                        break;
                                    case '1':
                                    default:
                                        $this->processQueryBuilder(null, $gets['report_category']);
                                        break;
                                }
                            } else {
                                $this->processQueryBuilder(null, $gets['report_category']);
                            }
                            break;

                            // @see ID-10975 Move the 'purge' logic to the template and controller.

                        default:
                            if (isset($gets[C__GET__REPORT_REPORT_ID])) {
                                $auth->check_report_right(isys_auth::EXECUTE, $gets[C__GET__REPORT_REPORT_ID]);
                                $this->showReport((int)$gets[C__GET__REPORT_REPORT_ID]);
                            } else {
                                $this->processReportList();
                            }
                            break;
                    }
                    break;
            }
        } catch (isys_exception_auth $e) {
            isys_application::instance()->container->get('template')->assign("exception", $e->write_log())
                ->include_template('contentbottomcontent', 'exception-auth.tpl');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());
            $this->processReportList();
        }

        // Is the tree part of the system menu?
        if ($gets[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            // Handle the tree.
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();

            $this->build_tree($l_tree, false);

            isys_application::instance()->container->get('template')->assign("menu_tree", $l_tree->process($gets[C__GET__TREE_NODE]));
        }

        return $this;
    }

    /**
     * Set header of the current report
     *
     * @param $p_headers
     */
    public function set_report_headers($p_headers)
    {
        $this->m_report_headers = $p_headers;
    }

    /**
     * Gets the header of the current report
     *
     * @return array
     */
    public function get_report_headers()
    {
        return $this->m_report_headers;
    }

    /**
     * Method for building the report SQL.
     *
     * @return array
     * @throws Exception
     */
    protected function buildReport()
    {
        global $g_comp_database;

        $l_dao = new isys_cmdb_dao_category_property($g_comp_database);

        // Returning the SQL-query and the other dara (title, description, ...).
        $l_return = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'type'              => 'c',
            'userspecific'      => $_POST['chk_user_specific'],
            'query'             => $l_dao
                    ->prepareEnvironmentForReportByPost()
                    ->create_property_query_for_report(),
            'report_category'   => $_POST['report_category'],
            'empty_values'      => $_POST['empty_values'],
            'compressed_multivalue_results' => $_POST['compressed_multivalue_results'],
            'show_html' => $_POST['show_html'],
            'display_relations' => $_POST['display_relations'],
            'keep_description_format' => $_POST['keep_description_format'],
            'category_report'   => $_POST['category_report']
        ];
        if (!is_array($_POST['lvls_raw'])) {
            $l_lvls = isys_format_json::decode($_POST['lvls_raw']);
        } else {
            $l_lvls = $_POST['lvls_raw'];
        }

        if ($l_lvls !== null) {
            foreach ($l_lvls as $l_key => $l_lvl_content) {
                foreach ($l_lvl_content as $l_key2 => $l_content) {
                    $l_lvls[$l_key][$l_key2] = isys_format_json::decode($l_content);
                }
            }
        }

        if (!is_array($_POST['querycondition'])) {
            $l_condition = isys_format_json::decode($_POST['querycondition']);
        } else {
            $l_condition = $_POST['querycondition'];
        }

        if (isset($l_condition['#{queryConditionBlock}'])) {
            unset($l_condition['#{queryConditionBlock}']);
        }

        // @see ID-9023 reindex conditions
        array_walk($l_condition, function (&$value) {
            if (is_array($value)) {
                $value = array_values($value);
            }
        });

        // @see ID-10035 this is a workaround but needs to be refactored as it sets values for operators for IS NULL or IS NOT NULL
        $hiddenQueryConditions = is_array($_POST['querycondition__HIDDEN']) ? $_POST['querycondition__HIDDEN'] :  [];
        if (!empty($hiddenQueryConditions)) {
            $l_condition = array_replace_recursive($l_condition, $hiddenQueryConditions);
        }

        $l_arr = [
            'main_object'       => isys_format_json::decode($_POST['report__HIDDEN']),
            'lvls'              => $l_lvls,
            'conditions'        => $l_condition,
            'default_sorting'   => $_POST['default_sorting'],
            'sorting_direction' => $_POST['sorting_direction'],
            'statusFilter'      => $_POST['statusFilter'],
            'assignedObjectStatusFilter' => $_POST['assignedObjectStatusFilter'],
        ];

        $l_return['querybuilder_data'] = isys_format_json::encode($l_arr);

        if (!empty($_GET[C__GET__REPORT_REPORT_ID]) || !empty($_POST['report_id'])) {
            $l_return['report_id'] = (!empty($_POST['report_id']) ? $_POST['report_id'] : $_GET[C__GET__REPORT_REPORT_ID]);
        }

        return $l_return;
    }

    /**
     * Updates reports
     *
     * @param      $p_id
     * @param bool $p_querybuilder
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws Exception
     */
    private function saveReport($p_id, $p_querybuilder = false)
    {
        if ($p_querybuilder) {
            $l_report = new isys_report($this->buildReport());
            $l_report->update();
        } else {
            return $this->dao->saveReport(
                $p_id,
                $_POST["title"],
                $_POST["description"],
                $_POST["query"],
                null,
                (($_POST['chk_user_specific'] == 'on' ? true : false)),
                $_POST['report_category'],
                $_POST['compressed_multivalue_results'],
                $_POST['show_html'],
                $_POST['keep_description_format']
            );
        }

        return false;
    }

    /**
     * Duplicates Report
     *
     * @param $p_report_id
     * @throws Exception
     */
    private function duplicate_report($p_report_id)
    {
        $l_report = $this->dao->get_report($p_report_id);
        $l_param = [
            'title'             => $_POST['title'],
            'description'       => $_POST['description'],
            'query'             => $l_report['isys_report__query'],
            'userspecific'      => ($_POST['chk_user_specific'] == 'on') ? 1 : 0,
            'querybuilder_data' => $l_report['isys_report__querybuilder_data'],
            'report_category'   => $_POST['category_selection'],
            'type'              => $l_report['isys_report__type'],
            'empty_values'      => $l_report['isys_report__empty_values'],
            'category_report'   => $l_report['isys_report__category_report'],
            'compressed_multivalue_results' => $_POST['compressed_multivalue_results'] ?: $l_report['isys_report__compressed_multivalue_results'],
            'show_html' => $_POST['show_html'] ?: $l_report['isys_report__show_html'],
            'display_relations' => $l_report['isys_report__display_relations'],
            'keep_description_format' => $l_report['isys_report__keep_description_format'],
        ];
        $l_report_instance = new isys_report($l_param);
        $l_report_id = $l_report_instance->store();

        // Add right
        $this->add_right($l_report_id, 'custom_report');
    }

    /**
     * This Method is a helper method which adds the rights for custom_report or reports_in_category
     *
     * @param $p_id
     * @param $p_method
     */
    private function add_right($p_id, $p_method)
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session, $g_comp_database;

        // Check if user has wildcard rights for CUSTOM_REPORTS and REPORTS
        switch ($p_method) {
            case 'custom_report':
                if (isys_auth_report::instance()
                        ->get_allowed_reports() === true) {
                    return;
                }
                break;
            case 'reports_in_category':
                if (isys_auth_report::instance()
                        ->get_allowed_report_categories() === true) {
                    return;
                }
                break;
        }

        $l_user_id = (int)$g_comp_session->get_user_id();
        $l_path_data = [$p_method => [$p_id => [isys_auth::SUPERVISOR]]];
        // Add right for the report
        isys_auth_dao::instance($g_comp_database)
            ->create_paths($l_user_id, defined_or_default('C__MODULE__REPORT'), $l_path_data);
        isys_caching::factory('auth-' . $l_user_id)
            ->clear();
    }

    /**
     * @param $p_id
     *
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @throws Exception
     */
    private function editReport($p_id = null)
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session;

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__EDIT:
            case C__NAVMODE__NEW:
                $l_navbar = isys_component_template_navbar::getInstance();
                $l_navbar->set_save_mode('ajax')
                    ->set_ajax_return('ajaxReturnNote')
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                break;
        }

        $l_title = null;
        $l_selected_category = null;
        $l_report_description = null;
        $l_query = null;
        $l_user_specific = null;
        $l_my_report = false;
        $l_report = null;

        if ($p_id !== null) {
            $l_report = $this->dao->get_reports(null, [$p_id], null, true, false)[0];

            $l_title = $l_report["isys_report__title"];
            $l_selected_category = $l_report['isys_report__isys_report_category__id'];
            $l_report_description = $l_report["isys_report__description"];
            $l_query = isys_glob_htmlentities($l_report["isys_report__query"]);
            $l_user_specific = $l_report["isys_report__user_specific"];

            if ($g_comp_session->get_user_id() == $l_report['isys_report__user']) {
                $l_my_report = true;
            } else {
                isys_auth_report::instance()
                    ->check(isys_auth::EDIT, 'CUSTOM_REPORT/' . $p_id);
            }
        }

        $l_allowed_report_categories = isys_auth_report::instance()->get_allowed_report_categories();

        if ($l_allowed_report_categories === false) {
            $l_report_category_data = $this->dao->get_report_categories('Global', false)->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } else {
            $l_report_categories = $this->dao->get_report_categories($l_allowed_report_categories);
            $l_data = [];
            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    try {
                        // @see  ID-5548  Check if the user is allowed to see this category.
                        isys_auth_report::instance()->reports_in_category(isys_auth::CREATE, $l_category['isys_report_category__id']);

                        $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                    } catch (Exception $e) {
                        // Do nothing.
                    }
                }
            }
        }

        $l_rules = [
            'title'                         => [
                'p_strValue'    => html_entity_decode($l_title ?? ''),
            ],
            'report_category'               => [
                'p_strSelectedID' => $l_selected_category,
                'p_arData'        => $l_data
            ],
            'description'                   => [
                'p_strValue'    => html_entity_decode($l_report_description ?? ''),
            ],
            'query'                         => [
                'p_strValue' => $l_query
            ],
            'chk_user_specific'             => [
                'p_bChecked'    => $l_user_specific,
                'p_strDisabled' => (!$l_my_report)
            ],
            'compressed_multivalue_results' => [
                'p_strSelectedID' => ($l_report['isys_report__compressed_multivalue_results'] ?: 0)
            ],
            'show_html'                     => [
                'p_strSelectedID' => ($l_report['isys_report__show_html'] ?: 0)
            ]
        ];

        if (!empty($l_report["isys_report__querybuilder_data"])) {
            isys_application::instance()->container->get('template')
                ->assign("querybuilder_warning", $this->language->get('LC__REPORT__EDIT__WARNING_TEXT'));
        }

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__REPORT__LIST__SQL_EDITOR'))
            ->assign("report_id", $l_report["isys_report__id"])
            ->activate_editmode()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', isys_module_report::getPath() . 'templates/sql-editor.tpl');
    }

    /**
     * @param array $ids
     *
     * @throws Exception
     */
    private function exportReportConfig(array $ids)
    {
        $export = new Export($ids);
        $export->createExport();

        if ($export->getXml() instanceof SimpleXMLElement) {
            $export->output();
        }
    }

    /**
     * @param array $files
     *
     * @throws ImportException
     */
    private function importReportConfig(array $files)
    {
        if ($fileContent = file_get_contents($files['uploadFile']['tmp_name'])) {
            $import = new Import($fileContent);
            $import->importReport();
        }
    }

    /**
     * @param $p_id
     * @param $p_type
     * @param int|null $objectId
     *
     * @throws Exception
     */
    private function exportReport($p_id, $p_type, $objectId = null)
    {
        $l_row = $this->dao->get_report($p_id, $objectId);

        $l_report = [
            'report_id'   => $l_row['isys_report__id'],
            'type'        => $l_row['isys_report__type'],
            'title'       => $l_row['isys_report__title'],
            'description' => $l_row['isys_report__description'],
            'query'       => $l_row['isys_report__query'],
            'mandator'    => $l_row['isys_report__mandator'],
            'datetime'    => $l_row['isys_report__datetime'],
            'last_edited' => $l_row['isys_report__last_edited'],
            'show_html'   => $l_row['isys_report__show_html'],
            'compressed_multivalue_results' => $l_row['isys_report__compressed_multivalue_results'],
            'keep_description_format' => $l_row['isys_report__keep_description_format'],
        ];

        try {
            isys_application::instance()->container->get('session')->write_close();

            $report = (new \idoit\Module\Report\Report(
                new isys_component_dao(isys_application::instance()->container->get('database')),
                $l_row['isys_report__query'],
                $l_row['isys_report__title'],
                $l_row['isys_report__id'],
                $l_row['isys_report__type'],
                $l_row['isys_report__compressed_multivalue_results']
            ))->setCompressedMultivalueResults($l_row['isys_report__compressed_multivalue_results']);

            switch ($p_type) {
                case 'xml':
                    $l_report = new isys_report_xml($l_report);
                    break;

                case 'csv':
                    \idoit\Module\Report\Export\CsvExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;
                case 'txt':
                    \idoit\Module\Report\Export\TxtExport::factory($report)
                        ->export()
                        ->output();
                    die;

                    break;

                case 'pdf':
                    // @see ID-10860 Removed 'utf8_decode'.
                    $l_report['title'] = html_entity_decode($l_report['title']);
                    $l_report = new isys_report_pdf($l_report);
                    break;

                default:
                    throw new Exception('Missing or unknown export type');
            }

            $l_report->export();
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (\idoit\Exception\OutOfMemoryException $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());

            throw $e;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Method for processing ajax requests.
     *
     * @throws isys_exception_database
     * @throws isys_exception_dao
     * @throws Exception
     */
    private function processAjaxRequest()
    {
        /** @var isys_component_session $g_comp_session */
        global $g_comp_session;

        $g_comp_session->write_close();

        $request = isys_module_request::get_instance();
        $get = $request->get_gets();
        $post = $request->get_posts();

        switch ($get["request"]) {
            case isys_report_dao::AJAX_REPORT_DOWNLOAD:
                if (isys_auth_report::instance()->is_allowed_to(isys_auth::EXECUTE, 'ONLINE_REPORTS')) {
                    $l_dao = isys_report_dao::instance();

                    if ($l_dao->reportExists($post["title"], $post["query"])) {
                        isys_notify::warning($this->language->get('LC__REPORT__EXISTS'));
                    } else {
                        $l_global_id = $l_dao
                            ->get_report_categories('Global', false)
                            ->get_row_value('isys_report_category__id');

                        if ($l_dao->createReport($post["title"], $post["desc"], $post["query"], null, false, false, $l_global_id) !== false) {
                            isys_notify::success($this->language->get('LC__REPORT__DOWNLOAD_SUCCESSFUL'));
                        } else {
                            isys_notify::error($this->language->get('LC__REPORT__ERROR_SAVING'));
                        }
                    }
                } else {
                    isys_notify::error($this->language->get('LC__UNIVERSAL__NO_ACCESS_RIGHTS'));
                }
                break;

            default:
                if (isset($get["reportID"])) {
                    $l_class = $get["reportID"];

                    if (class_exists($l_class)) {
                        $l_obj = new $l_class();

                        // ID-6399 Only call "init" if available.
                        if (method_exists($l_obj, 'init')) {
                            $l_obj->init();
                        }

                        if (method_exists($l_obj, "ajax_request")) {
                            $l_obj->ajax_request();
                        }

                        unset($l_obj);
                    }
                }
                break;
        }
    }

    /**
     * Method for displaying a report.
     *
     * @param $p_id
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    private function showReport(int $p_id)
    {
        $l_report = isys_report_dao::instance()->get_report($p_id);

        if (!empty($l_report['isys_report__querybuilder_data'])) {
            $query = isys_cmdb_dao_category_property::instance(isys_application::instance()->container->get('database'))
                    ->prepareEnvironmentForReportById($p_id)
                    ->create_property_query_for_report() . '';
        } else {
            $query = $l_report['isys_report__query'];
        }

        try {
            $l_ajax_pager = false;

            // We use this DAO because here we defined how many pages we want to preload.
            $l_dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));

            $l_rowcount = [
                'count' => 0
            ];

            try {
                if (\idoit\Module\Report\Validate\Query::validate($query)) {
                    /**
                     * Count rows based on the complete statement (as a fallback)
                     *
                     * @param $l_q
                     *
                     * @return array
                     */
                    $l_checkfallback = function ($l_q) use ($l_dao) {
                        // If our first try fails because we broke the SQL, we use this here...
                        return [
                            'count' => $l_dao->retrieve($l_q)
                                ->num_rows()
                        ];
                    };

                    /**
                     * Check for inner selects
                     *
                     * @see https://i-doit.atlassian.net/browse/ID-3099
                     */
                    if (preg_match('/SELECT.*?^[SELECT].*?FROM/is', $query)) {
                        // First we modify the SQL to find out, with how many rows we are dealing...
                        $l_rowcount_sql = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) as count FROM', $query, 1);

                        try {
                            $l_num_rows = $l_dao->retrieve($l_rowcount_sql)
                                ->num_rows();

                            if ($l_num_rows == 1) {
                                $l_rowcount = $l_dao->retrieve($l_rowcount_sql)
                                    ->get_row();
                            } else {
                                $l_rowcount['count'] = $l_num_rows;
                            }
                        } catch (Exception $e) {
                            // If our first try fails because we broke the SQL, we use this here...
                            $l_rowcount = $l_checkfallback($query);
                        }
                    } else {
                        $l_rowcount = $l_checkfallback($query);
                    }
                }
            } catch (Exception $e) {
                // query validation failed.
            }

            $l_preloadable_rows = isys_glob_get_pagelimit() * ((int)isys_usersettings::get('gui.lists.preload-pages', 30));
            $template = isys_application::instance()->container->get('template');

            // If we get more rows than our defined preloading allowes, we need the ajax pager.
            if ($l_preloadable_rows < $l_rowcount['count'] && !strpos($query, 'LIMIT')) {
                // First we append an offset to the report-query.
                $query = rtrim(trim($query), ';') . ' LIMIT 0, ' . $l_preloadable_rows . ';';

                // Here we prepare the URL for the ajax pagination.
                $l_ajax_url = '?ajax=1&call=report&func=ajax_pager&report_id=' . $p_id;
                $l_ajax_pager = true;

                $template->assign('ajax_url', $l_ajax_url)
                    ->assign('preload_pages', ((int)isys_usersettings::get('gui.lists.preload-pages', 30)))
                    ->assign('max_pages', ceil($l_rowcount['count'] / isys_glob_get_pagelimit()));
            }

            $this->process_show_report($query, null, false, false, false, true, !!$l_report["isys_report__compressed_multivalue_results"], !!$l_report["isys_report__show_html"]);

            // Should we compress multivalue categorie results
            if ($l_report['isys_report__compressed_multivalue_results']) {
                // Reparse results -  actually it is unnecessary
                $results = isys_format_json::decode($template->get_template_vars('result'), true);
                $unsortedColumns = [];

                if (is_array($results) && !empty($results)) {
                    // Extract column names from first result row
                    $unsortedColumns = isys_format_json::encode(array_keys(reset($results)));
                }

                // Send it to template
                $template->assign('columnNames', $unsortedColumns);
            }

            // Should we show html
            if ($l_report['isys_report__show_html']) {
                // todo implement
            }

            // @see ID-10367 check if it has already been set from $this->process_show_report()
            $rowCount = $template->getTemplateVars('rowcount');

            if (!$rowCount) {
                $template->assign("rowcount", $l_rowcount['count']);
            }

            $template
                ->assign("ajax_pager", $l_ajax_pager)
                ->assign("report_id", $p_id)
                ->assign("reportTitle", $l_report["isys_report__title"])
                ->assign("reportDescription", $l_report["isys_report__description"])
                ->assign("compressedMultivalueCategories", $l_report["isys_report__compressed_multivalue_results"])
                ->assign("showHtml", $l_report["isys_report__show_html"])
                ->include_template('contentbottomcontent', isys_module_report::getPath() . 'templates/report_execute.tpl');
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());

            $this->processReportList();
        }
    }

    /**
     * Process views
     *
     * @throws isys_exception_auth
     * @throws Exception
     */
    private function processViews()
    {
        if (!isset($_GET["reportID"]) || !class_exists($_GET["reportID"])) {
            $l_header = [
                "viewtype"    => $this->language->get("LC__CMDB__CATG__TYPE"),
                "name"        => $this->language->get("LC__UNIVERSAL__TITLE"),
                "description" => $this->language->get("LC__LANGUAGEEDIT__TABLEHEADER_DESCRIPTION")
            ];

            $l_views = $this->getViews(false, true);

            $l_list = new isys_component_list($l_views, null, null, null);

            $l_rowLink = isys_glob_build_url(http_build_query($_GET) . "&" . C__GET__REPORT_REPORT_ID . "=[{class}]");
            $l_list->config($l_header, $l_rowLink);

            if ($l_list->createTempTable()) {
                isys_application::instance()->container->get('template')->assign("objectTableList", $l_list->getTempTableHtml());
            }

            isys_application::instance()->container->get('template')->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

            isys_application::instance()->container->get('template')->assign("content_title", "Report-Views")
                ->include_template('contentbottomcontent', 'content/bottom/content/object_table_list.tpl');
        } else {
            $l_class = strtoupper($_GET['reportID']);

            isys_auth_report::instance()
                ->check(isys_auth::VIEW, 'VIEWS/' . $l_class);

            if (class_exists($l_class)) {
                /** @var isys_report_view $l_view */
                $l_view = new $l_class();

                isys_application::instance()->container->get('template')
                    ->assign('content_title', $this->language->get($l_view::name()))
                    ->assign('viewTemplate', $l_view->template())
                    ->include_template('contentbottomcontent', isys_module_report::getPath() . 'views/view.tpl');

                // ID-6399 Only call "init" if available.
                if (method_exists($l_view, 'init')) {
                    $l_view->init();
                }

                $l_view->start();
            } else {
                throw new Exception('Error: Report does not exist.');
            }
        }
    }

    /**
     * @return void
     */
    private function handleCreateButton()
    {
        $newOverlay = [
            [
                "title"   => $this->language->get('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "axialis/basic/application-window.svg",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__NEW,
                "onclick" => "document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ],
            [
                "title"   => $this->language->get("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "axialis/database/document-database.svg",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__NEW,
                "onclick" => "$('querybuilder').value=0;document.isys_form.navMode.value='" . C__NAVMODE__NEW . "'; document.isys_form.submit();",
            ]
        ];

        $navbar = isys_component_template_navbar::getInstance()
            ->set_overlay($newOverlay, 'new');

        if (is_numeric($_GET['report_category'])) {
            $l_rights_create = self::getAuth()->is_allowed_to(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/' . $_GET['report_category']);
        } else {
            $l_rights_create = self::getAuth()->is_allowed_to(isys_auth::CREATE, 'REPORTS_IN_CATEGORY/*');
        }

        if ($l_rights_create) {
            $navbar->append_button($this->language->get('LC__NAVIGATION__NAVBAR__NEW'), 'new', [
                "icon"                => "axialis/documents-folders/document-type-new.svg",
                "navmode"             => C__NAVMODE__NEW,
                "add_onclick_prepend" => "$('querybuilder').value='';",
                "accesskey"           => "n"
            ]);
        }
    }

    /**
     * @return void
     */
    private function handleEditButton(array $reports)
    {
        $hasEditRight = $this->hasRightInReport(isys_auth::EDIT, $reports);

        if (!$hasEditRight) {
            $hasEditRight = isys_auth_report::instance()->is_allowed_to(isys_auth::EDIT, 'SELF_CREATED_REPORTS');
        }

        $editOverlay = [
            [
                "title"   => $this->language->get('LC__REPORT__MAINNAV__QUERY_BUILDER'),
                "icon"    => "axialis/basic/application-window.svg",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=1;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ],
            [
                "title"   => $this->language->get("LC__REPORT__LIST__SQL_EDITOR"),
                "icon"    => "axialis/database/document-database.svg",
                "href"    => "javascript:;",
                "navmode" => C__NAVMODE__EDIT,
                "onclick" => "$('querybuilder').value=0;document.isys_form.sort.value='';document.isys_form.navMode.value='" . C__NAVMODE__EDIT . "'; form_submit();"
            ]
        ];

        $navbar = isys_component_template_navbar::getInstance()
            ->set_overlay($editOverlay, 'edit');

        if ($hasEditRight) {
            $navbar->append_button($this->language->get('LC__AUTH__RIGHT_EDIT'), 'edit', [
                "icon"                => "axialis/documents-folders/document-edit.svg",
                "navmode"             => C__NAVMODE__EDIT,
                "add_onclick_prepend" => "$('querybuilder').value='';",
                "accesskey"           => "e"
            ]);
        }
    }

    /**
     * @param $right
     * @param $reports bool|array
     *
     * @return bool
     */
    private function hasRightInReport($right, $reports)
    {
        if ($reports === true) {
            return isys_auth_report::instance()->is_allowed_to($right, "CUSTOM_REPORT/*");
        } else {
            if (is_numeric($_GET['report_category'])) {
                $inCategoryRight = self::getAuth()->is_allowed_to($right, 'REPORTS_IN_CATEGORY/' . $_GET['report_category']);
            } else {
                $inCategoryRight = self::getAuth()->is_allowed_to($right, 'REPORTS_IN_CATEGORY/*');
            }

            if ($inCategoryRight === true) {
                return true;
            }
        }

        foreach ($reports as $reportId) {
            $allowed = isys_auth_report::instance()->is_allowed_to($right, "CUSTOM_REPORT/{$reportId}");
            if ($allowed === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    private function handlePurgeButton(array $reports)
    {
        $purgeRights = $this->hasRightInReport(isys_auth::DELETE, $reports);

        // @see ID-10363 check purge rights of self created reports
        if (!$purgeRights) {
            $purgeRights = self::getAuth()->is_allowed_to(isys_auth::DELETE, 'SELF_CREATED_REPORTS');
        }

        if ($purgeRights) {
            isys_component_template_navbar::getInstance()->append_button($this->language->get('LC__NAVIGATION__NAVBAR__PURGE'), 'purge', [
                "navmode"    => 'purge',
                "icon"       => "axialis/industry-manufacturing/waste-bin.svg",
                "accesskey"  => "d",
                'js_onclick' => "",
                'active'     => true,
                'visible'    => true
            ]);
        }
    }

    /**
     * @return void
     */
    private function handleDuplicateButton(array $reports)
    {
        $duplicateRight = $this->hasRightInReport(isys_auth::SUPERVISOR, $reports);

        // @see ID-10363 check purge rights of self created reports
        if (!$duplicateRight) {
            $duplicateRight = self::getAuth()->is_allowed_to(isys_auth::SUPERVISOR, 'SELF_CREATED_REPORTS');
        }

        if ($duplicateRight) {
            isys_component_template_navbar::getInstance()
                ->append_button($this->language->get('LC__NAVIGATION__NAVBAR__DUPLICATE'), 'duplicate_report', [
                    "icon"       => "axialis/documents-folders/pages.svg",
                    "navmode"    => C__NAVMODE__DUPLICATE,
                    "js_onclick" => " onclick=\"if ($$('.mainTableHover')[0].down('input:checked')) {get_popup('report', null, '480', '350', {'func':'show_duplicate'});} else {idoit.Notify.error('" .
                        $this->language->get('LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED') . "', {life:10}); }\""
                ]);
        }
    }

    /**
     * Shows the report list
     *
     * @throws Exception
     */
    private function processReportList()
    {
        $reportAuth = isys_auth_report::instance();
        $l_allowed_reports = $reportAuth
            ->get_allowed_reports();

        $l_dao = isys_report_dao::instance();
        $l_report_category = (isset($_GET['report_category'])) ? $_GET['report_category'] : null;
        $template = isys_application::instance()->container->get('template');

        if ($l_report_category !== null && !$l_dao->check_report_category($l_report_category)) {
            $l_report_category = null;
        }

        if ($l_report_category > 0) {
            $reportAuth->reports_in_category(isys_auth::VIEW, $l_report_category);
        }

        $l_reports = $l_dao->get_reports(null, $l_allowed_reports, $l_report_category, true, true);

        $l_header = self::getReportListHeaders();

        if (isset($_GET['call']) || isset($_GET['ajax'])) {
            $l_gets = $_GET;
            unset($l_gets['call']);
            unset($l_gets['ajax']);
        } else {
            $l_gets = $_GET;
        }

        $l_rowLink = isys_glob_build_url(http_build_query($l_gets) . "&" . C__GET__REPORT_REPORT_ID . "=[{isys_report__id}]");

        // @see ID-10394 Pass the 'resetFilter' parameter.
        $l_list = (new isys_component_list(null, $l_reports, null, null))
            ->config($l_header, $l_dao->buildRowLinkFunction($l_rowLink), "[{isys_report__id}]", true, true, null, false, ($_GET['resetFilter'] ?? false))
            ->set_row_modifier($l_dao, 'modify_row');

        $tableConfig = $l_list->get_table_config();
        $tableConfig->setFilterProperty((isys_tenantsettings::get('report.list.filter') ?: 'isys_report__id'));
        $l_list->set_table_config($tableConfig);

        if ($l_list->createTempTable()) {
            $template->assign("objectTableList", $l_list->getTempTableHtml());
        }

        $template->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__STANDARD_QUERIES"));

        $l_rights_report_category = $reportAuth->is_allowed_to(isys_auth::SUPERVISOR, 'REPORT_CATEGORY');
        $allowedToExport = $reportAuth->is_allowed_to(isys_auth::EXECUTE, 'EXPORT_REPORT_CONFIG');
        $allowedToImport = $reportAuth->is_allowed_to(isys_auth::EXECUTE, 'IMPORT_REPORT_CONFIG');

        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote');

        $this->handleCreateButton();

        $reports = isys_report_dao::instance()
            ->get_reports(null, $l_allowed_reports, $_GET['report_category'] ?? null, false, false) ?? [];

        if (!empty($reports)) {
            $reports = array_map(fn ($item) => (int)$item['isys_report__id'], $reports);
        }

        $this->handleEditButton($reports);

        if ($l_rights_report_category) {
            $l_navbar->append_button($this->language->get('LC_UNIVERSAL__CATEGORIES'), 'report_category', [
                    "icon"       => "axialis/documents-folders/folder.svg",
                    "navmode"    => 'report-categories',
                    "js_onclick" => "get_popup('report', null, '480', '360', {'func':'show_category'});"
                ]);
        }

        if ($allowedToExport) {
            $l_navbar->append_button($this->language->get('LC__REPORT__EXPORT_REPORT_CONFIG'), 'export_report_config', [
                "icon"       => "axialis/basic/symbol-download.svg",
                "js_onclick" => "",
                'navmode'    => 'export'
            ]);
        }

        if ($allowedToImport) {
            $l_navbar->append_button($this->language->get('LC__REPORT__IMPORT_REPORT_CONFIG'), 'import_report_config', [
                "icon"       => "axialis/basic/symbol-upload.svg",
                "navmode"    => C__NAVMODE__UPLOAD,
            ]);
            $template->assign("encType", "multipart/form-data");
        }

        $this->handleDuplicateButton($reports);
        $this->handlePurgeButton($reports);

        $template
            ->assign("querybuilder", 1)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
            ->include_template('contentbottomcontent', isys_module_report::getPath() . 'templates/report_list.tpl');
    }

    /**
     *
     */
    private function processReportBrowser()
    {
        isys_application::instance()->container->get('template')
            ->assign('content_title', isys_application::instance()->container->get('language')->get('LC__REPORT__MAINNAV__QUERY_BROWSER'))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->include_template('contentbottomcontent', isys_module_report::getPath() . 'templates/online-report-browser.tpl');
    }

    /**
     * Shows the querybuilder
     *
     * @param int $p_reportID
     * @param int $p_report_category_id
     *
     * @throws isys_exception_database
     * @throws Exception
     */
    private function processQueryBuilder($p_reportID = null, $p_report_category_id = null)
    {
        $template = isys_application::instance()->container->get('template');

        isys_component_template_navbar::getInstance()
            ->set_save_mode('ajax')
            ->set_ajax_return('ajaxReturnNote')
            ->set_active(true, C__NAVBAR_BUTTON__SAVE)
            ->set_active(true, C__NAVBAR_BUTTON__CANCEL);

        $l_rules = [];

        // Add the object types to the select-box.
        $this->build_object_types();

        // States array
        $statusFilter = [
            $this->language->get('LC__CMDB__RECORD_STATUS__ALL') . ' (0)',
            $this->language->get('LC__CMDB__RECORD_STATUS__BIRTH') . ' (' . C__RECORD_STATUS__BIRTH . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__NORMAL') . ' (' . C__RECORD_STATUS__NORMAL . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__ARCHIVED') . ' (' . C__RECORD_STATUS__ARCHIVED . ')',
            $this->language->get('LC__CMDB__RECORD_STATUS__DELETED') . ' (' . C__RECORD_STATUS__DELETED . ')'
        ];

        $l_report_category_data = $this->dao->get_report_categories();
        $l_allowed_report_categories = isys_auth_report::instance()
            ->get_allowed_report_categories();
        $l_global_report_category_id = null;

        if ($l_allowed_report_categories === false) {
            $l_report_category_data = $this->dao->get_report_categories('Global', false)->get_row();
            $l_data[$l_report_category_data['isys_report_category__id']] = $l_report_category_data['isys_report_category__title'];
        } else {
            $l_report_categories = $this->dao->get_report_categories($l_allowed_report_categories);
            $l_data = [];
            if (count($l_report_categories) > 0) {
                foreach ($l_report_categories as $l_category) {
                    try {
                        // @see  ID-5548  Check if the user is allowed to see this category.
                        isys_auth_report::instance()->reports_in_category(isys_auth::VIEW, $l_category['isys_report_category__id']);

                        $l_data[$l_category['isys_report_category__id']] = $l_category['isys_report_category__title'];
                    } catch (Exception $e) {
                        // Do nothing.
                    }
                }
            }
        }

        if ($p_report_category_id !== null) {
            $template->assign('category_selected', $p_report_category_id);
        } else {
            $template->assign('category_selected', $l_global_report_category_id);
        }

        $l_display_relation = 0;

        if ($p_reportID !== null) {
            $l_report = $this->dao->getReportRaw((int)$p_reportID);
            $l_querybuilder_data = isys_format_json::decode($l_report['isys_report__querybuilder_data']);
            $l_conditions = array_slice($l_querybuilder_data, 2, 1);

            // @see ID-8644 Due to an array that begins with index 1 the JS will receive an object (instead of an array) which causes problems.
            if (isset($l_conditions['conditions']) && is_array($l_conditions['conditions'])) {
                $l_conditions['conditions'] = array_values($l_conditions['conditions']);

                // @see ID-9265 Apply the same logic as in ID-9023 to fix the GUI.
                array_walk($l_conditions['conditions'], function (&$value) {
                    if (is_array($value)) {
                        $value = array_values($value);
                    }
                });
            }

            $l_display_relation = $l_report['isys_report__display_relations'] ?: 0;
            $l_keep_description_format = $l_report['isys_report__keep_description_format'] ?: 0;

            $template->assign('category_selected', $l_report['isys_report__isys_report_category__id'])
                ->assign('empty_values_selected', $l_report['isys_report__empty_values'])
                ->assign('report_id', $p_reportID)
                ->assign('chk_user_specific', $l_report["isys_report__user_specific"])
                ->assign('preselection_data', isys_format_json::encode($l_querybuilder_data['main_object']))
                ->assign('preselection_lvls', isys_format_json::encode($l_querybuilder_data['lvls']))
                ->assign('default_sorting', $l_querybuilder_data['default_sorting'])
                ->assign('sorting_direction', $l_querybuilder_data['sorting_direction'])
                ->assign('report_title', html_entity_decode($l_report['isys_report__title']))
                ->assign('report_description', $l_report['isys_report__description'])
                ->assign('querybuilder_conditions', ((count($l_conditions['conditions']) > 0) ? isys_format_json::encode($l_conditions) : null))
                ->assign('statusFilterValue', ($l_querybuilder_data['statusFilter'] ?: 0))
                ->assign('assignedObjectStatusFilterValue', ($l_querybuilder_data['assignedObjectStatusFilter'] ?: 0))
                ->assign("isCategoryReport", $l_report['isys_report__category_report']);

            $compressedMultivalueResults = $l_report['isys_report__compressed_multivalue_results'];
            $showHtml = $l_report['isys_report__show_html'];
        } else {
            $l_rules['report']['preselection'] = '[{"g":{"C__CATG__GLOBAL":["title"]}}]';
            $compressedMultivalueResults = 0;
            $showHtml = 0;
        }

        /**
         * @see ID-8402  Add a proper indicator for variable reports
         */
        $l_rules['category_report'] = [
            'p_bDbFieldNN'    => true,
            'p_arData'        => [
                0    => 'LC__REPORT__FORM__REPORT_TYPE__NORMAL',
                'on' => 'LC__REPORT__FORM__REPORT_TYPE__VARIABLE'
            ],
            'p_strSelectedID' => $l_report['isys_report__category_report'] ? 'on' : 0
        ];

        // Assign the title and make the save/cancel buttons invisible.
        $template->assign('category_data', $l_data)
            ->assign("content_title", $this->language->get("LC__REPORT__MAINNAV__QUERY_BUILDER"))
            ->assign("yes_or_no", get_smarty_arr_YES_NO())
            ->assign('keep_description_format', $l_keep_description_format)
            ->assign('display_relations_selected', $l_display_relation)
            ->assign('sorting_data', [
                'ASC'  => $this->language->get('LC__CMDB__SORTING__ASC'),
                'DESC' => $this->language->get('LC__CMDB__SORTING__DESC')
            ])
            ->assign("statusFilter", $statusFilter)
            ->assign('compressed_multivalue_results', $compressedMultivalueResults)
            ->assign('show_html', $showHtml)
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', isys_module_report::getPath() . 'templates/querybuilder.tpl');
    }

    /**
     * Method for inserting an report into the database.
     *
     * @param   boolean $p_querybuilder
     *
     * @return  mixed  boolean, null or integer
     */
    private function createReport($p_querybuilder)
    {
        $l_id = null;

        try {
            if ($p_querybuilder) {
                $l_report = new isys_report($this->buildReport());
                $l_id = $l_report->store();
            } else {
                $l_id = $this->dao->createReport(
                    $_POST["title"],
                    $_POST["description"],
                    $_POST["query"],
                    null,
                    false,
                    $_POST['chk_user_specific'],
                    $_POST['report_category'],
                    null,
                    $_POST['compressed_multivalue_results'],
                    $_POST['show_html'],
                    $_POST['keep_description_format']
                );
            }

            // Add right
            $allowedReports = isys_auth_report::instance()->get_allowed_reports();
            if (is_array($allowedReports)) {
                $this->add_right($l_id, 'custom_report');
            }
        } catch (Exception $e) {
            isys_application::instance()->container->get('notify')->error($e->getMessage());
        }

        return $l_id;
    }

    /**
     * Method to create a new report category
     *
     * @param string $p_title
     * @param string $p_description
     * @param int    $p_sorting
     * @return  bool
     * @throws isys_exception_dao
     */
    private function create_category($p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title))) {
            $l_last_id = $this->dao->create_category(trim($p_title), $p_description, $p_sorting);

            if ($l_last_id) {
                // Add auth right.
                $this->add_right($l_last_id, 'reports_in_category');
            }

            return $l_last_id;
        }

        return false;
    }

    /**
     * Method to update an existing report category
     *
     * @param   integer $p_id
     * @param   string  $p_title
     * @param   string  $p_description
     * @param   integer $p_sorting
     *
     * @return  boolean
     * @throws isys_exception_dao
     */
    private function update_category($p_id, $p_title, $p_description = null, $p_sorting = 99)
    {
        if (strlen(trim($p_title))) {
            return $this->dao->update_category($p_id, trim($p_title), $p_description, $p_sorting);
        }

        return false;
    }

    /**
     * Constructor method to be sure, there's a DAO instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = isys_application::instance()->container->get('language');
        $this->dao = isys_report_dao::instance();
    }
}
