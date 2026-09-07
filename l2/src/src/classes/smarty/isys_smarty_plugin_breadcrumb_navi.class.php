<?php

/**
 * i-doit
 *
 * Smarty plugin for breadcrumb navigation.
 *
 * @internal
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_breadcrumb_navi extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Defines if the SM2 meta map is enabled or not.
     *
     * @return  bool
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Shows the hierarchical breadcrumb navigation.
     *
     * @param isys_component_template $template
     * @param array|null              $parameters
     * @return string
     * @throws Exception
     */
    public function navigation_view(isys_component_template $template, $parameters = null)
    {
        global $g_comp_session;

        if (!$g_comp_session->is_logged_in()) {
            return 'Login';
        }

        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $this->m_strPluginClass = 'breadcrumb_navi';
        $this->m_strPluginName = $parameters['name'];

        $breadcrumbHtml = (new isys_component_template_breadcrumb)
            ->includeHome(isset($parameters['home']) && $parameters['home'])
            ->process((bool)$parameters['plain']);

        return stripslashes($breadcrumbHtml);
    }

    /**
     * Wrapper for the navigation_view.
     *
     * @param isys_component_template $template
     * @param array|null              $parameters
     * @return string
     * @throws Exception
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        return $this->navigation_view($template, $parameters);
    }
}
