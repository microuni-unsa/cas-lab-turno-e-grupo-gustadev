<?php

/**
 * i-doit
 *
 * Smarty plugin for suggestion input field
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_suggestion extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    public const SIZE_MINI   = 'mini';
    public const SIZE_SMALL  = 'small';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_BLOCK  = 'block';

    /**
     * @return array
     */
    public static function get_meta_map()
    {
        return [];
    }

    /**
     * @param isys_component_template $template
     * @param                         $parameters
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

        return $this->getInfoIcon($parameters) . '<span>' . $parameters['value'] . '</span>';
    }

    /**
     * @param isys_component_template $template
     * @param                         $parameters
     *
     * @return string
     * @throws SmartyException
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        /*
         * In order to react to different events, please observe the custom events on the suggestion input.
         * The 'ev.memo' contains additional infos.
         *
         * $suggestionField.on('suggestion:afterUpdateElement', (ev) => { ... });
         * $suggestionField.on('suggestion:afterShow', (ev) => { ... });
         * $suggestionField.on('suggestion:afterHide', (ev) => { ... });
         */

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

        $additionalAttributes = [];

        foreach ($parameters['additionalAttributes'] ?? [] as $attribute => $value) {
            $additionalAttributes[] = $attribute . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return (string)$template->assign('name', $parameters['name'])
            ->assign('placeholder', $parameters['placeholder'])
            ->assign('value', $parameters['value'])
            ->assign('size', in_array($parameters['size'], $sizes, true) ? 'input-size-' . $parameters['size'] : 'input-size-normal')
            ->assign('url', $parameters['url'])
            ->assign('urlParameters', (array)$parameters['urlParameters'] ?? [])
            ->assign('additionalCssClass', $parameters['cssClass'] ?? '')
            ->assign('callbackOnSelect', $parameters['callbackOnSelect'] ?? '')
            ->assign('additionalAttributes', implode(' ', $additionalAttributes))
            ->fetch(__DIR__ . '/suggestion.tpl');
    }
}
