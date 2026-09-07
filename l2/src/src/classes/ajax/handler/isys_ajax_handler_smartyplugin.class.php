<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.3
 */
class isys_ajax_handler_smartyplugin extends isys_ajax_handler
{
    private $tpl = null;

    /**
     * Init method, which gets called from the framework.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            $this->tpl = isys_application::instance()->container->get('template');
            $pluginType = (string)$_POST['plugin_name'];
            $parameters = (array)isys_format_json::decode($_POST['parameters']);

            switch ($_GET['mode']) {
                case 'view':
                    $l_return['data'] = $this->view_mode($pluginType, $parameters);
                    break;

                case 'edit':
                    $l_return['data'] = $this->edit_mode($pluginType, $parameters);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Method for loading an smarty plugin in VIEW mode.
     *
     * @param   string $pluginType
     * @param   array  $parameters
     *
     * @throws  isys_exception_template
     * @return  string
     */
    protected function view_mode(string $pluginType, array $parameters = []): string
    {
        $className = 'isys_smarty_plugin_' . $pluginType;

        if (!class_exists($className)) {
            throw new isys_exception_template('The requested plugin "' . $pluginType . '" seems to miss its PHP-class "' . $className . '".');
        }

        $instance = new $className;

        if (!$instance instanceof isys_smarty_plugin_f) {
            throw new isys_exception_template('The requested class "' . $instance . '" is not extending "isys_smarty_plugin_f".');
        }

        return $instance->navigation_view($this->tpl, $parameters);
    }

    /**
     * Method for loading an smarty plugin in EDIT mode.
     *
     * @param   string $pluginType
     * @param   array  $parameters
     *
     * @throws  isys_exception_template
     * @return  string
     */
    protected function edit_mode(string $pluginType, array $parameters = []): string
    {
        $this->tpl->activate_editmode();

        $className = 'isys_smarty_plugin_' . $pluginType;

        if (!class_exists($className)) {
            throw new isys_exception_template('The requested plugin "' . $pluginType . '" seems to miss its PHP-class "' . $className . '".');
        }

        $instance = new $className;

        if (!$instance instanceof isys_smarty_plugin_f) {
            throw new isys_exception_template('The requested class "' . $instance . '" is not extending "isys_smarty_plugin_f".');
        }

        return $instance->navigation_edit($this->tpl, $parameters);
    }
}
