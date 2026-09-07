<?php

use idoit\AddOn\AuthableInterface;
use idoit\AddOn\ExtensionProviderInterface;
use idoit\AddOn\RoutingAwareInterface;
use idoit\Module\Report\ReportExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Routing\Loader\PhpFileLoader;

/**
 * i-doit
 *
 * i-doit Report Manager.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_report extends isys_module implements AuthableInterface, ExtensionProviderInterface, RoutingAwareInterface
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    private static ?isys_module_report_pro $m_instance = null;

    /**
     * @var isys_report_dao
     */
    protected $m_dao;

    /**
     * Template location
     *
     * @var null
     */
    protected $m_tpl = null;

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
     * Static method for retrieving the path, to the modules templates.
     *
     * @static
     * @return string
     */
    public static function get_tpl_dir(): string
    {
        return __DIR__ . '/templates/';
    }

    /**
     * Static get instance method.
     *
     * @return isys_module_report_pro
     */
    public static function get_instance(): isys_module_report_pro
    {
        if (!is_object(self::$m_instance)) {
            self::$m_instance = new isys_module_report_pro();
        }

        return self::$m_instance;
    }

    /**
     * @param   isys_module_request & $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        return true;
    }

    /**
     * Enhances the breadcrumb navigation.
     *
     * @param array $p_gets
     *
     * @return array|null
     * @throws isys_exception_database
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_report = new isys_module_report_pro();

        if (method_exists($l_report, 'breadcrumb_get')) {
            return $l_report->breadcrumb_get($_GET);
        }

        return null;
    }

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree & $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        $l_report = new isys_module_report_pro();

        $l_report->build_tree($p_tree, $p_system_module, $p_parent);
    }

    /**
     * Method for retrieving a bookmark string (mydoit).
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @version Van Quyen Hoang <qhoang@i-doit.org>
     * @since   0.9.9-9
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $l_link_options = [
            C__GET__MODULE_ID        => defined_or_default('C__MODULE__REPORT'),
            C__GET__REPORT_PAGE      => $_GET[C__GET__REPORT_PAGE],
            C__GET__REPORT_REPORT_ID => $_GET[C__GET__REPORT_REPORT_ID],
            C__GET__TREE_NODE        => $_GET[C__GET__TREE_NODE],
            'report_category'        => $_GET['report_category']
        ];

        $p_text[] = 'Report Manager';
        switch ($_GET[C__GET__REPORT_PAGE]) {
            case C__REPORT_PAGE__REPORT_BROWSER:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__QUERY_BROWSER');
                break;
            case C__REPORT_PAGE__CUSTOM_REPORTS:
                if ($_GET['report_category'] > 0) {
                    $p_text[] = $this->m_dao->get_report_categories($_GET['report_category'], false)
                        ->get_row_value('isys_report_category__title');
                } else {
                    $p_text[] = isys_application::instance()->container->get('language')
                        ->get('LC__REPORT__MAINNAV__CUSTOM_QUERIES');
                }
                break;
            case C__REPORT_PAGE__QUERY_BUILDER:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__QUERY_BUILDER');
                break;
            case C__REPORT_PAGE__VIEWS:
                $p_text[] = 'Views';
                break;
            case C__REPORT_PAGE__STANDARD_REPORTS:
            default:
                $p_text[] = isys_application::instance()->container->get('language')
                    ->get('LC__REPORT__MAINNAV__STANDARD_QUERIES');
                break;
        }

        if (isset($_GET[C__GET__REPORT_REPORT_ID])) {
            $l_row = $this->m_dao->get_report($_GET[C__GET__REPORT_REPORT_ID]);
            $p_text[] = $l_row['isys_report__title'];
        }

        // Define the favorite-link.
        $p_link = isys_glob_http_build_query($l_link_options);

        return true;
    }

    /**
     * Start-method.
     *
     * @return isys_module_report_pro
     * @throws Exception
     */
    public function start()
    {
        $l_report = new isys_module_report_pro();

        $this->m_dao = isys_report_dao::instance();

        $l_report->start();

        return $l_report;
    }

    /**
     * @return isys_auth_report
     */
    public static function getAuth()
    {
        return isys_auth_report::instance();
    }

    /**
     * Returns the module's container extension.
     *
     * @return ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new ReportExtension();
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
