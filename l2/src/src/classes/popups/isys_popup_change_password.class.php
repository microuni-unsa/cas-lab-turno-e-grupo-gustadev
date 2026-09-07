<?php

/**
 * i-doit
 *
 * Change password popup.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_change_password extends isys_component_popup
{
    /**
     * Handles SMARTY request for location browser.
     *
     * @param isys_component_template $template
     * @param                         $params
     *
     * @return string
     * @throws Exception
     */
    public function handle_smarty_include(isys_component_template $template, $params)
    {
        if (isys_glob_is_edit_mode()) {
            $popupTrigger = $params['name'] . '__POPUP_TRIGGER';
            $clearTrigger = $params['name'] . '__CLEAR_TRIGGER';

            $targetField = $params['target-field'];
            $dataField = $params['data-field'];
            $onClick = $this->process_overlay('', 480, 240, $params);

            $hideSetEmpty = (bool)($params['hide-set-empty-button'] ?? false);
            $removeSetEmpty = (bool)($params['remove-set-empty-button'] ?? false);

            return '<button type="button" id="' . $popupTrigger . '" class="btn">' .
                '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/key-filled.svg" alt="" />' .
                '<span>' . $this->language->get('LC__LOGIN__PASSWORD_CHANGE') . '</span>' .
                '</button>' .
                '<button type="button" id="' . $clearTrigger . '" class="btn ' . ($removeSetEmpty || $hideSetEmpty ? 'hide' : '') . '">' .
                '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/symbol-cancel.svg" alt="" />' .
                '<span>' . $this->language->get('LC__UNIVERSAL__SET_EMPTY') . '</span>' .
                '</button>' .
                '<script>' .
                '$(\'' . $popupTrigger . '\').on(\'click\', function () { ' . $onClick . ' });' .
                '$(\'' . $clearTrigger . '\').on(\'click\', function () { ' .
                    'idoit.Notify.info(\'' . $this->language->get('LC__LOGIN__PASSWORD_CLEARED') . '\', { sticky: true }); ' .
                    '$(\'' . $clearTrigger . '\').addClassName(\'hide\'); ' .
                    '$(\'' . $targetField . '\').setValue(\'\'); ' .
                    '$(\'' . $dataField . '\').setValue(\'clear\'); ' .
                '});' .
                '</script>';
        }

        return '';
    }

    /**
     * @param isys_module_request $modreq
     *
     * @return isys_component_template
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $modreq)
    {
        $params = (array)isys_format_json::decode(base64_decode($_POST['params']));

        $rules = [
            'NEW_PASSWORD'        => [
                'p_strClass' => 'input-small',
                'hide-set-empty' => true
            ],
            'NEW_PASSWORD_CONFIRM' => [
                'p_strClass' => 'input-small',
                'hide-set-empty' => true
            ]
        ];

        $this->template->activate_editmode()
            ->assign('passwordMinimumLength', (int)isys_tenantsettings::get('minlength.login.password', 4))
            ->assign('targetField', $params['target-field'])
            ->assign('dataField', $params['data-field'])
            ->assign('popupTriggerButton', $params['name'] . '__POPUP_TRIGGER')
            ->assign('clearTriggerButton', $params['name'] . '__CLEAR_TRIGGER')
            ->assign('showClearTriggerButton', !($params['remove-set-empty-button'] ?? false))
            ->smarty_tom_add_rules('tom.popup.change-password', $rules)
            ->display('popup/change-password.tpl');
        die;
    }
}
