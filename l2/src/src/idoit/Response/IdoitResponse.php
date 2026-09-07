<?php

namespace idoit\Response;

use Exception;
use idoit\Tree\Node;
use isys_application;
use isys_component_dao_user;
use isys_component_signalcollection;
use isys_component_template_infobox;
use isys_component_template_navbar;
use isys_component_tree;
use isys_register;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class IdoitResponse extends Response
{
    /**
     * @var array
     */
    private array $templates;

    /**
     * @var bool
     */
    private bool $showNavbar;

    /**
     * @var bool
     */
    private bool $showBreadcrumb;

    /**
     * @var Node|null
     */
    private ?Node $navigationTreeNode;

    /**
     * @var bool
     */
    private bool $sortNavigationTree;

    /**
     * @param string $templatePath
     * @param int    $status
     * @param array  $headers
     */
    public function __construct(string $templatePath, int $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->showNavbar = true;
        $this->showBreadcrumb = true;
        $this->navigationTreeNode = null;
        $this->sortNavigationTree = true;
        $this->templates = [
            'leftcontent'          => 'content/leftContent.tpl',
            'contentbottomcontent' => $templatePath
        ];
    }

    /**
     * @param string $area
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate(string $area, string $template): self
    {
        $this->templates[$area] = $template;

        return $this;
    }

    /**
     * @param bool $show
     *
     * @return $this
     */
    public function showNavbar(bool $show): self
    {
        $this->showNavbar = $show;

        return $this;
    }

    /**
     * @param bool $show
     *
     * @return $this
     */
    public function showBreadcrumb(bool $show): self
    {
        $this->showBreadcrumb = $show;

        return $this;
    }

    /**
     * @param Node $node
     * @param bool $sort
     *
     * @return $this
     * @throws Exception
     */
    public function setNavigationTree(Node $node, bool $sort = true): self
    {
        $this->navigationTreeNode = $node;
        $this->sortNavigationTree = $sort;

        return $this;
    }

    /**
     * @return IdoitResponse
     * @throws Exception
     */
    public function sendHeaders(): static
    {
        // @see  ID-8647  Deny usage of admin center in frames.
        if (isys_application::instance()->container->get('settingsTenant')
            ->get('system.deny-frame-options', 1)) {
            $this->headers->set('X-Frame-Options', 'deny');
        }

        return parent::sendHeaders();
    }

    /**
     * @return IdoitResponse
     * @throws Exception
     * @throws \isys_exception_general
     */
    public function sendContent(): static
    {
        global $g_absdir;

        $diContainer = isys_application::instance()->container;

        $mainTemplate = $g_absdir . '/src/themes/default/smarty/templates/main.tpl';

        if (!file_exists($mainTemplate)) {
            throw new Exception("Template {$mainTemplate} does not exist.");
        }

        if ($this->showNavbar) {
            $this->templates['navbar'] = 'content/navbar/main.tpl';

            isys_component_template_navbar::getInstance()
                ->show_navbar();
        }

        $menuWidth = 0;
        $defaultMenuWidth = (int)$diContainer->get('settingsUser')
            ->get('gui.leftcontent.width', isys_component_dao_user::C__CMDB__TREE_MENU_WIDTH);

        if ($this->navigationTreeNode !== null) {
            $menuWidth = $defaultMenuWidth;
            $this->renderNavigationTree();
        }

        // @see ID-11741 Introduce routes to IdoitResponse.
        $frontendRoutes = [];

        try {
            foreach (isys_application::instance()->container->get('routes')->all() as $name => $route) {
                $frontendRoutes[$name] = $route->getPath();
            }
        } catch (\Throwable $e) {
            \isys_notify::warning('Frontend routes could not be loaded, please reload.');
        }

        $this->content = $diContainer->get('template')
            ->assign('g_mandant_name', $diContainer->get('session')->get_mandator_name())
            ->assign('infobox', isys_component_template_infobox::instance())
            ->assign('defaultWidth', $defaultMenuWidth)
            ->assign('menu_width', $menuWidth)
            ->assign('showBreadcrumb', $this->showBreadcrumb)
            ->assign('index_includes', $this->templates)
            ->assign('routes', $frontendRoutes)
            ->fetch($mainTemplate);

        // Emit signal afterRender.
        isys_component_signalcollection::get_instance()
            ->emit('system.gui.afterRender');

        return parent::sendContent();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function renderNavigationTree(): void
    {
        $scriptName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $relativePath = $_SERVER['REQUEST_URI'];

        if ($scriptName !== '/') {
            $relativePath = substr($_SERVER['REQUEST_URI'], mb_strlen($scriptName));
        }

        $request = isys_register::factory('request')
            ->set('REQUEST', '/' . ltrim(rawurldecode($relativePath), '/'))
            ->set('BASE', isys_application::instance()->www_path);

        $tree = isys_component_tree::factory('nav_tree');
        $tree->set_tree_sort($this->sortNavigationTree);
        $tree->payload($this->navigationTreeNode, $request);

        isys_application::instance()->container->get('template')
            ->assign('menu_tree', $tree->process());
    }
}
