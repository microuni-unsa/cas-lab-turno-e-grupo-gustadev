<?php

/**
 * i-doit
 *
 * Calendar class
 *
 *
 * @package     i-doit
 * @subpackage  popups
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_calendar extends isys_component_popup
{
    /**
     * @param  isys_component_template &$p_tplclass
     * @param  array                   $p_params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template $p_tplclass, $p_params)
    {
        $loadedLanguage = $this->language->get_loaded_language();
        $locales = isys_application::instance()->container->get('locales');

        if (!$loadedLanguage) {
            $loadedLanguage = 'en';
        }

        $l_name = str_replace(['[', ']', '-'], '_', $p_params['name']);
        $l_time = '';
        $l_hidden_date = '';
        $styleAddition = '';

        if (strpos($p_params['name'], '[') !== false && strpos($p_params['name'], ']') !== false) {
            $l_tmp = explode('[', $p_params['name']);

            $viewId = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $hiddenId = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $viewId = $p_params['name'] . '__VIEW';
            $hiddenId = $p_params['name'] . '__HIDDEN';
        }

        $l_readonly = isset($p_params['p_bReadonly']) && $p_params['p_bReadonly'];

        $p_params['p_strValue'] = trim($p_params['p_strValue'] ?? '');
        $withTime = isset($p_params['p_bTime']) && $p_params['p_bTime'];

        // @see ID-9574 Check if any data was provided.
        $isEmpty = $p_params['p_strValue'] === '';
        $date = $this->getDate($p_params['p_strValue']);

        if ($withTime) {
            $l_time_value = '';

            if (!$isEmpty) {
                $l_time_value = $date->format('H:i:s');
            }

            $l_time = ' - ' . $l_time_value;

            if ($this->template->editmode() || $p_params['p_bEditMode']) {
                $readOnly = $l_readonly ? ' readonly="readonly"' : '';
                $onChange = $p_params['timeOnChange'] ? ' onchange="' . $p_params['timeOnChange'] . '"' : '';
                $styleAddition = 'width:50%;';

                $l_time = '<input type="text" class="input" style="width:50%;" id="' . $l_name . '__TIME" name="' . $l_name . '__TIME" value="' .
                    $l_time_value . '" ' . $readOnly . ' ' . $onChange . '/>';
            }
        }

        if (!$isEmpty) {
            if ($withTime) {
                $l_hidden_date = $date->format('Y-m-d H:i:s');
            } else {
                $l_hidden_date = $date->format('Y-m-d');
            }

            $p_params['p_strValue'] = $locales->fmt_date($date->format('Y-m-d'));
        }

        $disableFutureDate = (isset($p_params['disableFutureDate']) && $p_params['disableFutureDate']) ? 'true' : 'false';
        $disablePastDate = (isset($p_params['disablePastDate']) && $p_params['disablePastDate']) ? 'true' : 'false';

        $l_cellCallback = isset($p_params['cellCallback']) ? ', cellCallback: ' . $p_params['cellCallback'] : '';
        $l_clickCallback = (isset($p_params['clickCallback'])) ? ', clickCallback: ' . $p_params['clickCallback'] : '';

        $p_params['p_strID'] = $viewId;

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if ($this->template->editmode() || $p_params['p_bEditMode'] == true) {
            if (isset($p_params['p_onChange'])) {
                $p_params['p_onChange'] = rtrim($p_params['p_onChange'], ';') . ';';
            }

            // @see ID-12258 React to 'german' or 'not german' date format. The following code needs to be cleaned up in the future.
            $rawDateFormat = $locales->resolve_language_by_constant($locales->get_setting(LC_TIME)) === 'de' ? 'd.m.Y' : 'Y-m-d';

            $formatSeparator = (strpos($rawDateFormat, '.') ? '.' : '-');
            $format = explode($formatSeparator, str_replace(['d', 'm', 'Y'], ['dd', 'mm', 'yyyy'], $rawDateFormat));

            if ($l_readonly === false) {
                // @see ID-1904  Changed the DatePickerFormatter according to lines below.
                $p_params['p_onChange'] .= "var val = ''; if(! this.value.blank()) { " .
                    "var df = new DatePickerFormatter(['{$format[0]}', '{$format[1]}', '{$format[2]}'], '{$formatSeparator}').match(this.value); ".
                    "val = df[0] + '-' + df[1] + '-' + df[2];" .
                    "} " .
                    "$('{$hiddenId}').setValue(val);";
            }

            $l_strHiddenField = '<input name="' . $hiddenId . '" id="' . $hiddenId . '" type="hidden" value="' . $l_hidden_date . '" />';

            $p_params['disableInputGroup'] = true;
            $p_params['p_strStyle'] = $styleAddition . $p_params['p_strStyle'];

            $l_strOut = $l_objPlugin->navigation_edit($this->template, $p_params) . $l_time .
                '<span class="input-group-addon" title="' . $this->language->get('LC__POPUP__BROWSER__CALENDAR_TITLE') . '" data-tooltip="1">' .
                '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/calendar-1.svg" alt="" />' .
                '</span>' . $l_strHiddenField;

            // @see ID-9733 Only set the language, if it is actually available (see 'src/tools/js/datepicker.js').
            $availableLanguages = ['fr', 'en', 'sp', 'it', 'de', 'pt', 'hu', 'lt', 'nl', 'dk', 'no', 'lv', 'ja', 'fi', 'ro', 'zh', 'sv', 'pl', 'ru', 'el'];

            if (!in_array($loadedLanguage, $availableLanguages, true)) {
                $loadedLanguage = 'en';
            }

            if ($l_readonly === false) {
                $l_strOut .= "<script type=\"text/javascript\">
                var dpck_" . $l_name . "	= new DatePicker({
                    relative: '{$viewId}',
                    hidden: '{$hiddenId}',
                    time: '{$l_name}__TIME',
                    language: '{$loadedLanguage}',
                    observeScrollingParent: '" . $p_params['observeScrollingParent'] . "',
                    closeEffect: 'fade',
                    showEffect: 'appear',
                    keepFieldEmpty : true,
                    disableFutureDate: {$disableFutureDate},
                    disablePastDate: {$disablePastDate},
                    topOffset: $('{$viewId}').getHeight() + 2,
                    leftOffset: 1,
                    wrongFormatMessage: '" . $this->language->get('LC_CALENDAR_POPUP__WRONGDATE') . "',
                    zindex: 99999,
                    dateFormat: [['{$format[0]}', '{$format[1]}', '{$format[2]}'], '{$formatSeparator}'],
                    hiddenDateFormat: [['yyyy', 'mm', 'dd'], '-']
                    {$l_cellCallback}
                    {$l_clickCallback}
                });
                </script>";
            }

            return $l_strOut;
        }

        $p_params['p_bHtmlDecode'] = true;

        return $l_objPlugin->navigation_view($this->template, $p_params) . $l_time;
    }

    /**
     * @param isys_module_request $p_modreq
     * @return void
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
    }

    /**
     * @param string $date
     *
     * @return DateTime
     */
    private function getDate(string $date): DateTime
    {
        try {
            return new DateTime($date);
        } catch (Throwable $e) {
            // Do nothing, this should only catch 'Failed to parse time string ...' exception,
        }

        // Try to 'parse' the date via strtotime.
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            // Fallback to '0' (= 1970-01-01).
            $timestamp = 0;
        }

        return new DateTime("@{$timestamp}");
    }
}
