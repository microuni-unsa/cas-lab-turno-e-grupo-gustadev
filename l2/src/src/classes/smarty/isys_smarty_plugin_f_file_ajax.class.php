<?php

use idoit\Component\Upload\UploadType;

/**
 * i-doit
 *
 * Smarty plugin for ajax file upload
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_smarty_plugin_f_file_ajax extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * @param isys_component_template $smarty
     * @param array                   $parameters
     *
     * @return string
     */
    public function navigation_view(isys_component_template $smarty, $parameters = null)
    {
        return '';
    }

    /**
     * @param isys_component_template $smarty
     * @param array                   $parameters
     *
     * @return string
     * @throws Exception
     */
    public function navigation_edit(isys_component_template $smarty, $parameters = null)
    {
        global $g_dirs;

        $this->m_strPluginClass = 'f_file_ajax';
        $this->m_strPluginName = $parameters['name'];

        if (!isset($parameters['id'])) {
            $parameters['id'] = $parameters['name'];
        }

        if ($parameters === null) {
            $parameters = $this->m_parameter;
        }

        $uploadService = isys_application::instance()->container->get('upload');

        if (empty($parameters['uploadType'])) {
            return '<p class="p5 box-red">' . 'You have to select a upload type' . '</p>';
        }

        if (!$uploadService->hasUploadType($parameters['uploadType'])) {
            return '<p class="p5 box-red">' . 'The selected upload type "' . $parameters['uploadType'] . '" does not exit.' . '</p>';
        }

        $type = $uploadService->getUploadType($parameters['uploadType']);

        $uploadUrl = isys_application::instance()->container->get('route_generator')->generate('system.ajax-upload', ['type' => $parameters['uploadType']]);

        $parameters['options'] = array_merge(
            [
                'ajaxURL' => $uploadUrl,
                'sizeLimit' => $type->getSizeLimit(),
                'validExtensions' => $type->getValidExtensions()
            ],
            (array)($parameters['options'] ?: [])
        );

        $parameters["p_strClass"] = "input-size-normal input-file-ajax " . $parameters["p_strClass"];

        $this->getStandardAttributes($parameters);
        $this->getJavascriptAttributes($parameters);

        $lang = isys_application::instance()->container->get('language');

        return '<div id="' . $parameters['id'] . '" ' . $parameters["p_strClass"] . '>' .
            '<div class="container" ' . $parameters["p_strStyle"] . ' ' . $parameters["p_strTitle"] . '></div>' .
            '<button type="button" class="btn text-normal hide">' .
            '<img src="' . $g_dirs["images"] . 'axialis/basic/symbol-upload.svg" alt="" />' .
            '<span>' . $lang->get('LC__UNIVERSAL__FILEUPLOAD') . '</span>' .
            '</button>' .
            '</div>' .
            '<script type="text/javascript">"use strict";
idoit.Require.require(["fileUploader", "smartyFilesUpload"], function () {
    smartyFilesUpload("' . $parameters['id'] . '", "' . $parameters['uploadType'] . '", ' . isys_format_json::encode($parameters['options']) . ');
});</script>';
    }
}
