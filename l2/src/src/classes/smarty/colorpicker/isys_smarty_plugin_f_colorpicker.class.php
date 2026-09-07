<?php

/**
 * i-doit
 *
 * Smarty plugin for colorpicker input field
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_colorpicker extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    public const SIZE_MINI   = 'mini';
    public const SIZE_SMALL  = 'small';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_BLOCK  = 'block';

    /**
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return string
     * @throws Exception
     */
    public function navigation_view(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        if (isset($parameters['invisible']) && $parameters['invisible']) {
            return '';
        }

        if (isset($parameters['editmode']) && $parameters['editmode']) {
            return $this->navigation_edit($template, $parameters);
        }

        $colorValue = isys_helper_color::unifyHexColor((string) $parameters['p_strValue']);

        return $this->getInfoIcon($parameters) . '<span class="colorpicker-pill" style="background-color: ' . $colorValue . '">' . $colorValue . '</span>';
    }

    /**
     * @param isys_component_template $template
     * @param array                   $parameters
     *
     * @return string
     * @throws SmartyException
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $this->m_strPluginClass = 'f_suggestion';
        $this->m_strPluginName = $parameters['name'];

        $sizes = [
            self::SIZE_MINI,
            self::SIZE_SMALL,
            self::SIZE_MEDIUM,
            self::SIZE_BLOCK
        ];

        return (string)$template
            ->assign('id', $parameters['id'] ?? $parameters['name'])
            ->assign('name', $parameters['name'])
            ->assign('containerClass', $parameters['containerClass'] ?? 'ml20')
            ->assign('placeholder', $parameters['placeholder'])
            ->assign('disabled', isset($parameters['disabled']) && $parameters['disabled'])
            ->assign('size', in_array($parameters['size'], $sizes, true) ? 'input-size-' . $parameters['size'] : 'input-size-normal')
            ->assign('data', (array)($parameters['data'] ?? []))
            ->assign('value', isys_helper_color::unifyHexColor((string)$parameters['p_strValue']))
            ->assign('parent', $parameters['parent'] ?? '#contentWrapper')
            ->fetch(__DIR__ . '/colorpicker.tpl');
    }
}
