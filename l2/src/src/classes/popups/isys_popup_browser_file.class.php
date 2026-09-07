<?php

/**
 * i-doit
 *
 * File browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_file extends isys_component_popup
{
    /**
     * @var bool
     */
    private bool $useAuth;

    /**
     * @var isys_auth_cmdb
     */
    private isys_auth_cmdb $auth;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->useAuth = (bool) isys_tenantsettings::get('auth.use-in-file-browser', false);
        $this->auth = isys_module_cmdb::getAuth();
    }

    /**
     * @param isys_component_template $template
     * @param                         $parameters
     *
     * @return string
     * @throws SmartyException
     */
    public function handle_smarty_include(isys_component_template $template, $parameters)
    {
        $parameters = $this->fromOldParameters($parameters);

        $id = $parameters['value'];
        $name = $parameters['name'];

        if (str_contains($parameters['name'], '[') && str_contains($parameters['name'], ']')) {
            $tmp = explode('[', $parameters['name']);
            $viewFieldName = $tmp[0] . '__VIEW[' . implode('[', array_slice($tmp, 1));
            $hiddenFieldName = $tmp[0] . '__HIDDEN[' . implode('[', array_slice($tmp, 1));
            unset($tmp);
        } else {
            $viewFieldName = $parameters['name'] . '__VIEW';
            $hiddenFieldName = $parameters['name'] . '__HIDDEN';
        }

        $suggestionPlugin = new isys_smarty_plugin_f_suggestion();
        $hiddenField = '<input id="' . $hiddenFieldName . '" name="' . $hiddenFieldName . '" type="hidden" value="' . $id . '" />';

        $parameters['value'] = $this->formatSelection($id);

        if (isys_glob_is_edit_mode() || $parameters[isys_popup_browser_object_ng::C__EDIT_MODE]) {
            $routeGenerator = isys_application::instance()->container->get('route_generator');

            $customJs = <<<JS
                const \$fileBrowserHiddenField = $('{$hiddenFieldName}');
                if (\$fileBrowserHiddenField && ev.memo.selectedChoice) {
                    \$fileBrowserHiddenField.setValue(ev.memo.selectedChoice.readAttribute('data-key'));
                }
                JS;

            $suggestionParameter = [
                'name'                 => $viewFieldName,
                'placeholder'          => $parameters['value'],
                'value'                => $parameters['value'],
                'size'                 => $parameters['size'],
                'cssClass'             => $parameters['cssClass'],
                'callbackOnSelect'     => $customJs . $parameters['callbackAccept'],
                'url'                  => $routeGenerator->generate('cmdb.browse-files.suggestion'),
                'additionalAttributes' => [
                    'data-hidden-field' => $hiddenFieldName,
                    'data-last-value'   => $id
                ]
            ];

            $suggestion = $suggestionPlugin->navigation_edit($template, $suggestionParameter);

            $modalParams = [
                'returnView'        => $viewFieldName,
                'returnHidden'      => $hiddenFieldName,
                'selectedFile'      => $id,
                'callbackAccept'    => $parameters['callbackAccept'],
                'callbackDetach'    => $parameters['callbackDetach'],
                'allowedExtensions' => (array)$parameters['allowed_extensions']
            ];

            $actionButtons = '<button type="button" title="' . $this->language->get('LC__UNIVERSAL__CHOOSE') . '" class="btn" data-tooltip="1" onClick="' . $this->getModalJs('', 1200, 700, $modalParams, 600, 400) . ';">' .
                    '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/link.svg" alt="" />' .
                '</button>' .
                '<button type="button" title="' . $this->language->get("LC__UNIVERSAL__DETACH") . '" class="btn" data-tooltip="1" onClick="$(\'' . $viewFieldName . '\').setValue(\'' . $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . '\');$(\'' . $hiddenFieldName . '\').setValue(0);' . $parameters['callbackDetach'] . '">' .
                    '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/link-broken.svg" alt="" />' .
                '</button>';

            return $suggestion . $actionButtons . $hiddenField;
        }

        return $suggestionPlugin->navigation_view($template, $parameters) . $hiddenField;
    }

    /**
     * @param array $parameters
     *
     * @return array
     * @see ID-10914 Modify 'old' parameters to fit the new ones.
     */
    private function fromOldParameters(array $parameters): array
    {
        $parameters['value'] = (int)($parameters['value'] ?? $parameters['p_strSelectedID'] ?? 0);
        $parameters['cssClass'] = $parameters['cssClass'] ?? $parameters['p_strClass'] ?? '';
        $parameters['callbackAccept'] = $parameters['callbackAccept'] ?? $parameters['callback_accept'] ?? '';
        $parameters['callbackDetach'] = $parameters['callbackDetach'] ?? $parameters['callback_detach'] ?? '';

        return $parameters;
    }

    /**
     * @param int $fileObjectId
     *
     * @return string
     * @throws Exception
     */
    private function formatSelection(int $fileObjectId): string
    {
        if ($fileObjectId === 0) {
            return $this->language->get('LC__CMDB__BROWSER_OBJECT__NONE_SELECTED');
        }

        $objectData = isys_application::instance()->container->get('cmdb_dao')->get_object_by_id($fileObjectId)->get_row();

        if ($this->useAuth && !$this->auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileObjectId)) {
            return '[' . $this->language->get('LC__UNIVERSAL__HIDDEN') . ']';
        }

        if (isys_glob_is_edit_mode()) {
            return $this->language->get($objectData['isys_obj_type__title']) . ' » ' . $objectData['isys_obj__title'];
        }

        $quickInfo = new isys_ajax_handler_quick_info();

        return $this->language->get($objectData['isys_obj_type__title']) . ' » ' . $quickInfo->get_quick_info($fileObjectId, $objectData['isys_obj__title'], C__LINK__OBJECT);
    }

    /**
     * @param isys_module_request $moduleRequest
     *
     * @return void
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $moduleRequest)
    {
        $this->template->activate_editmode()
            ->assign('parameters', isys_format_json::decode(base64_decode($_POST['params'])))
            ->assign('allowedToUpload', isys_auth_cmdb::instance()->is_allowed_to(isys_auth::EDIT, 'OBJ_IN_TYPE/C__OBJTYPE__FILE'))
            ->display('popup/filebrowser.tpl');
        die;
    }
}
