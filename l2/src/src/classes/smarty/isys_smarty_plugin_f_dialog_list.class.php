<?php

use idoit\Component\Helper\Unserialize;

/**
 * i-doit
 *
 * Smarty plugin for Selection lists.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Andre Woesten <awoesten@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @author      Selcuk Kekec <skekec@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_dialog_list extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * Returns the map for the Smarty Meta Map (SM²).
     *
     * @return array
     */
    public static function get_meta_map()
    {
        return [
            "p_strSelectedID",
            "p_arData",
        ];
    }

    /**
     * @param isys_component_template $template
     * @param                         $parameters
     * @return string
     * @throws Exception
     */
    public function navigation_view(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $l_arParams = null;
        $l_strInfoIcon = $this->getInfoIcon($parameters);

        if (is_array($parameters["p_arData"])) {
            $l_arParams = $parameters["p_arData"];
        } elseif (is_string($parameters["p_arData"]) && strpos($parameters["p_arData"], 'a:') === 0) {
            // @see ID-9642 Be sure to only pass serialized content.
            $l_arParams = Unserialize::toArray($parameters["p_arData"]);
        }

        if (isset($parameters['p_strSelectedBit'])) {
            $l_bitSelection = $parameters['p_strSelectedBit'];
        }

        if (is_array($l_arParams)) {
            $l_strOut = $l_strInfoIcon . '<div class="chosen-container chosen-container-multi"><ul class="chosen-choices" style="border:none;background:transparent">';

            // Divide selected and not selected into 2 arrays.
            foreach ($l_arParams as $l_val_ar) {
                $l_bURL = false;

                $l_value = $l_val_ar["val"];

                if (isset($l_bitSelection)) {
                    $l_sel = $l_val_ar['id'] & $l_bitSelection;
                } else {
                    $l_sel = $l_val_ar["sel"];
                }

                $l_url = $l_val_ar["url"];

                if ($l_sel) {
                    $l_strOut .= "<li class='search-choice' style='float:none; margin: 3px 0 3px 0'>";

                    if (strlen($l_url ?? '') >= 1) {
                        $l_bURL = true;
                        $l_strOut .= "<a href=\"" . $l_url . "\">";
                    }

                    $l_strOut .= "<span>" . $l_value . "</span>";

                    if ($l_bURL) {
                        $l_strOut .= "</a>";
                    }

                    $l_strOut .= "</li>";
                }
            }

            $l_strOut .= "</ul></div>";
        } else {
            $l_strOut = '<span class="ml20">-</span>';
        }

        return $l_strOut;
    }

    /**
     * @param isys_component_template $template
     * @param null                    $parameters
     * @return string
     * @throws Exception
     */
    public function navigation_edit(isys_component_template $template, $parameters = null)
    {
        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $language = isys_application::instance()->container->get('language');
        $this->m_strPluginClass = "f_button";
        $this->m_strPluginName = $parameters["name"];

        $l_arrSelectedValues = [];

        // If disabled is set the value will not be send with the formdata.
        $l_extra = (($parameters["p_bDisabled"] == "1") ? "disabled=\"disabled\" " : "") . (($parameters["p_bReadonly"] == "1") ? "readonly=\"readonly\" " : "");

        // CallBack Preparation.
        if (isset($parameters["add_callback"])) {
            $l_add_callback = $parameters["add_callback"];
        } else {
            $l_add_callback = '';
        }

        if (isset($parameters["remove_callback"])) {
            $l_remove_callback = $parameters["remove_callback"];
        } else {
            $l_remove_callback = '';
        }

        // Name-Handling.
        if ($parameters["name"] != "") {
            $l_strOptionsName = $parameters["name"] . "__selected_box";
            $l_strSelectedValues = $parameters["name"] . "__selected_values";
        } else {
            $l_strOptionsName = "SelectBox__selected_box";
            $l_strSelectedValues = "SelectBox__selected_values";
        }

        // Unserialize data if needed.
        if (is_string($parameters["p_arData"])) {
            $parameters["p_arData"] = Unserialize::toArray($parameters["p_arData"]);
        }

        if (isset($parameters['p_strSelectedBit'])) {
            $l_selectedBit = intval($parameters['p_strSelectedBit']);
        }

        // Extract data to selected / unselected.
        if (is_array($parameters["p_arData"])) {
            if (count($parameters["p_arData"]) > 0) {
                $l_options = [];

                foreach ($parameters["p_arData"] as $l_val_ar) {
                    if (is_array($l_val_ar)) {
                        $l_id = isset($l_val_ar['id']) ? $l_val_ar['id'] : '';
                        $l_value = isset($l_val_ar['val']) ? $l_val_ar['val'] : '';

                        if (isset($l_selectedBit)) {
                            $l_selected = ($l_id & $l_selectedBit) ? ' selected="selected"' : '';
                        } else {
                            $l_selected = isset($l_val_ar['sel']) && $l_val_ar['sel'] ? ' selected="selected"' : '';
                        }

                        $l_sticky = isset($l_val_ar['sticky']) ? $l_val_ar['sticky'] : '';

                        if ($l_sticky) {
                            $l_arSticky[$l_id] = $l_sticky;
                        }

                        if ($l_selected) {
                            $l_arrSelectedValues[$l_id] = $l_value;
                        }

                        $l_options[$l_id] = '<option value="' . $l_id . '" ' . $l_selected . '>' . $l_value . '</option>';
                    } else {
                        return '<div class="error p5 ml20">Error: dialog_list structure incompatible: ' . nl2br(var_export($parameters["p_arData"], true)) .
                            '<br />Use: array(array(id => int, val = string, sel = 1/0, sticky = 1/0))</div>';
                    }
                }

                if (!isset($parameters['p_bSort']) || $parameters['p_bSort']) {
                    asort($l_options);
                }

                $parameters['chosen-btn-all'] = $language->get('LC__UNIVERSAL__CHOOSE_ALL_SHORT');
                $parameters['chosen-btn-inverted'] = $language->get('LC__UNIVERSAL__CHOOSE_INVERTED_SHORT');
                $parameters['chosen-btn-none'] = $language->get('LC__UNIVERSAL__CHOOSE_NONE_SHORT');
                $parameters['additional_value_field'] = $l_strSelectedValues;

                $l_out = $this->getInfoIcon($parameters) . '<input type="hidden" name="' . $l_strSelectedValues . '" id="' . $l_strSelectedValues . '" value="' .
                    implode(',', array_keys($l_arrSelectedValues)) . '" />' . "<select name='{$l_strOptionsName}[]' id='{$l_strOptionsName}' multiple class=\"input " .
                    $parameters['p_strClass'] . "\"
						data-placeholder=\"" . $language->get(isset($parameters['placeholder']) ? $parameters['placeholder'] : 'LC__SMARTY__PLUGIN__DIALOGLIST__CHOSEN') . "\"
	                    onChange=\"updateDialogList(this.id, '{$l_strSelectedValues}');{$l_add_callback};{$l_remove_callback}\" {$l_extra}>" . implode('', $l_options) .
                    "</select>";

                $l_js = '<script type="text/javascript">(function() {
				    var $field = $("' . $l_strOptionsName . '");
				    new Chosen($field, {"no_results_text": "' . $language->get('LC__SMARTY__PLUGIN__DIALOGLIST__NO_RESULTS') . '", search_contains: true});
				    new ChosenExtension($field, ' . isys_format_json::encode($parameters) . ');
				    })();</script>';

                return $l_out . $l_js;
            } elseif (isset($parameters['emptyMessage'])) {
                return $this->getInfoIcon($parameters) . '<span class="emptyMessage">' . $language->get($parameters['emptyMessage']) . '</span>';
            }
        } elseif (isset($parameters['emptyMessage'])) {
            return $this->getInfoIcon($parameters) . '<span class="emptyMessage">' . $language->get($parameters['emptyMessage']) . '</span>';
        }

        return '';
    }
}
