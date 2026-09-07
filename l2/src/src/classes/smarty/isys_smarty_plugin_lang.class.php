<?php

/**
 * i-doit
 *
 * Smarty plugin for language constants
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_lang extends isys_smarty_plugin_f
{
    /**
     * @return bool
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Method for translating language constants.
     *
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return mixed|object|null
     * @throws Exception
     */
    public function navigation_view(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $this->m_strPluginClass = 'lang';
        $this->m_strPluginName = $parameters['name'] ?? '';

        $return = '';

        if (array_key_exists('ident', $parameters)) {
            $return = isys_application::instance()->container->get('language')->get($parameters['ident'], $parameters['values'] ?? null);
        }

        if (isset($parameters['toUpper']) && $parameters['toUpper']) {
            $return = strtoupper($return);
        }

        if (!isset($parameters['p_bHtmlEncode']) || $parameters['p_bHtmlEncode']) {
            $return = isys_glob_htmlentities($return);
        }

        return $return;
    }

    /**
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return string
     * @throws Exception
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        return $this->navigation_view($template, $parameters);
    }
}
