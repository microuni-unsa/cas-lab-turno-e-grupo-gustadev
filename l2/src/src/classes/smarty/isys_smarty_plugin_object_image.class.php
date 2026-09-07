<?php

/**
 * i-doit
 *
 * Smarty plugin for object images. Returns a string with the source of the image.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_object_image extends isys_smarty_plugin_f implements isys_smarty_plugin
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
        global $g_config;

        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $this->m_strPluginClass = "object_image";
        $this->m_strPluginName = $parameters["name"];

        $imageUrl = isys_application::instance()->container->get('route_generator')->generate(
            'cmdb.object.image',
            ['objectId' => (int)($parameters[C__CMDB__GET__OBJECT] ?: $_GET[C__CMDB__GET__OBJECT])]
        );

        $attributes = [
            'src="' . $imageUrl . '"',
        ];

        if (!empty($parameters["width"]) && is_numeric($parameters["width"])) {
            $attributes[] = 'width="' . ((int)$parameters["width"]) . '"';
        }

        if (!empty($parameters["height"]) && is_numeric($parameters["height"])) {
            $attributes[] = 'height="' . ((int)$parameters["height"]) . '"';
        }

        return '<img id="object_image_header" ' . implode(' ', $attributes) . ' />';
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
