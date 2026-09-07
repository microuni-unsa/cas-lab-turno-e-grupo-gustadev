<?php

use idoit\Component\Breadcrumb\Breadcrumb;
use idoit\Component\Breadcrumb\Page;
use idoit\Component\Helper\Purify;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * i-doit
 *
 * Breadcrumb Navigation: Hierarchical View of Links in the Banner
 *
 * @internal
 * @package     i-doit
 * @subpackage  Components_Template
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_breadcrumb
{
    private $includeHome = false;

    /**
     * Sets the option to include the "home" segment to the breadcrumb.
     *
     * @param bool $include
     *
     * @return $this
     */
    public function includeHome(bool $include): self
    {
        $this->includeHome = $include;

        return $this;
    }

    /**
     * @param bool     $plain
     * @param int|null $moduleId
     * @return string
     * @throws Exception
     */
    public function process(bool $plain, ?int $moduleId = null)
    {
        global $g_dirs;

        $home = '';
        $output = [];

        if ($this->includeHome) {
            $home = '<li><a href="' . isys_application::instance()->www_path . '" class="home btn btn-secondary" data-tooltip="1" title="Home">' .
                '<img src="' . $g_dirs['images'] . 'axialis/construction/house-4.svg" alt="i-doit" />' .
                '</a></li>';
        }

        // Determine module manager as "first level".
        $moduleManager = isys_application::instance()->container->get('moduleManager', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        // Retrieve the current module.
        if (!empty($moduleId)) {
            $activeModule = $moduleId;
        } elseif (!$moduleManager) {
            $activeModule = defined_or_default('C__MODULE__CMDB');
        } else {
            $activeModule = $moduleManager->get_active_module();
        }

        // Prioritize the service.
        $breadcrumb = $this->getServiceBreadcrumb();

        if (empty($breadcrumb) && is_numeric($activeModule)) {
            $breadcrumb = $this->getLegacyBreadcrumb((int)$activeModule, $moduleManager);
        }

        $breadcrumbPages = count($breadcrumb);

        // Iterating through breadcrumb entries.
        foreach ($breadcrumb as $index => $page) {
            /** @var Page $page */
            if ($plain) {
                $output[] = $page->getName();
                continue;
            }

            $isLast = $index === ($breadcrumbPages - 1);

            if ($isLast) {
                $output[] = '<li><strong>' . $page->getName() . '</strong></li>';
            } else {
                $output[] = '<li>' . $this->buildLink($page->getUrl(), $page->getName()) . '</li>';
            }
        }

        return $home . implode($plain ? ' > ' : '', $output);
    }

    /**
     * @param int                 $activeModule
     * @param isys_module_manager $moduleManager
     * @return array
     * @throws isys_exception_general
     */
    private function getLegacyBreadcrumb(int $activeModule, isys_module_manager $moduleManager): array
    {
        // Return active module register entry.
        $moduleRegister = $moduleManager->get_by_id($activeModule);

        if (!is_object($moduleRegister) || !$moduleRegister->is_initialized()) {
            return [];
        }

        // Asking module for its breadcrumb navigation.
        $moduleInstance = $moduleRegister->get_object();

        // The module data includes the module title
        $moduleData = $moduleRegister->get_data();
        $title = $moduleData["isys_module__title"];

        // Build first entry of breadcrumb.
        $getParameter = isys_module_request::get_instance()->get_gets();
        $getParameter = Purify::castIntValues($getParameter);

        // This is for 'Templates' and 'Mass changes', because both functions are using the same module class.
        if (isset($getParameter[C__GET__MODULE]) && $moduleInstance instanceof isys_module_templates) {
            if ($getParameter[C__GET__MODULE] == defined_or_default('C__MODULE__CMDB')) {
                $title = 'LC__MASS_CHANGE';
            } else {
                $title = 'LC__MODULE__TEMPLATES';
            }
        }

        // Build URL for GET-Parameters of module.
        $url = isys_glob_build_url(isys_glob_http_build_query([C__GET__MODULE_ID => $activeModule]));

        // @see ID-8897 Fix for add-ons with 'rewritten' URLs.
        if (defined(get_class($moduleInstance) . '::MAIN_MENU_REWRITE_LINK') && constant(get_class($moduleInstance) . '::MAIN_MENU_REWRITE_LINK')) {
            $url = isys_application::instance()->www_path . $moduleInstance->getIdentifier();
        }

        $modulePage = new Page($title, $url);

        if ($moduleInstance instanceof isys_module) {
            /**
             * 'breadcrumb_get' returns the following data structure:
             *
             * [
             *    [
             *       "MeinObjekt" => [
             *          "param" => "value",
             *          "param2"    => "value2",
             *          ...
             *       ]
             *    ],
             *    ...
             * ];
             */

            $pages = array_map(
                fn ($page) => new Page(key($page), isys_helper_link::create_url(current($page) ?? [])),
                $moduleInstance->breadcrumb_get($_GET) ?? []
            );

            // Return all pages with the module as first page.
            return [$modulePage, ...$pages];
        }

        return [];
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getServiceBreadcrumb(): array
    {
        /** @var Breadcrumb $breadcrumb */
        $breadcrumb = isys_application::instance()->container->get('breadcrumb', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if ($breadcrumb === null) {
            return [];
        }

        return $breadcrumb->getPages();
    }

    /**
     * @param string $url
     * @param string $title
     *
     * @return string
     * @throws Exception
     */
    private function buildLink(string $url, string $title): string
    {
        if (empty($title)) {
            $title = isys_application::instance()->container->get('language')->get('LC__NAVIGATION__BREADCRUMB__NO_TITLE');
        }

        if (str_contains($title, '<') || str_contains($title, '>')) {
            $title = htmlentities($title, ENT_QUOTES, BASE_ENCODING);
        }

        if (empty($url)) {
            return $title;
        }

        if (!str_starts_with($url, '/')) {
            $url = isys_application::instance()->www_path . $url;
        }

        return "<a href=\"{$url}\">{$title}</a>";
    }
}
