<?php

/**
 * i-doit
 *
 * Smarty plugin for images.
 *
 * @deprecated  Will be removed in i-doit 40!
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_image extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * @return bool
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return string
     */
    public function navigation_view(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $this->m_strPluginClass = 'image';
        $this->m_strPluginName = $parameters['name'];

        $this->getStandardAttributes($parameters);
        $this->getJavascriptAttributes($parameters);

        $l_strRet = '<a href="javascript:">';

        if (!empty($parameters["p_strLink"])) {
            $l_strRet = '<a href="' . $parameters['p_strLink'] . '">';
        }

        if (empty($parameters['p_bInvisible'])) {
            $l_strRet .= '<img src="' . $parameters["p_strSrc"] . '" ' . $parameters["p_strID"] . '>';
        }

        return $this->getInfoIcon($parameters) . $l_strRet . '</a>';
    }

    /**
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return string
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        return $this->navigation_view($template, $parameters);
    }
}
