<?php

use Smarty\Smarty;
use Smarty\Template;

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  Components Template
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template extends Smarty
{
    /**
     * @var isys_component_template
     */
    private static $m_instance = null;

    /**
     * Static, instance-global array with SM2 formular elements.
     * Is also providing as a backup of the formular data, which are registered as the global variable $g_SM2_FORM.
     *
     * @var array
     */
    private static $m_sm2_submitted;

    /**
     * TOM-ruleset.
     *
     * @var array
     */
    private array $m_ruleset;

    /**
     * This array represents the current nesting state (check usages of 'isys_group').
     * @var array
     */
    private array $stack;

    /**
     * Singleton instance function
     *
     * @return isys_component_template
     */
    public static function instance()
    {
        if (!self::$m_instance) {
            self::$m_instance = new self();
        }

        return self::$m_instance;
    }

    /**
     * SM2 - sm2_process_request_data
     *
     * Processes the request data and extracts an array into the global
     * symbol list with all SM2-parameters
     */
    private function sm2_process_request_data()
    {
        global $g_SM2_FORM; // Register $g_SM2_FORM

        // Prefix for SM2 variables.
        define('SM2_PREFIX', 'SM2__');

        // Create global formular storage.
        $g_SM2_FORM = [];

        foreach ($_POST as $key => $value) {
            // Does the $_POST-Field start with SM2__ and does it include an array with the data.
            if (str_starts_with($key, SM2_PREFIX) && is_array($value)) {
                $l_field = substr($key, strlen(SM2_PREFIX));

                // If yes, append entry with real TOM name to formular storage.
                self::$m_sm2_submitted[$l_field] = $value;
                unset($_POST[$key]);
            }
        }

        // Transport formular storage into global variable, $g_SM2_FORM, which YOU can use :-).
        $g_SM2_FORM = self::$m_sm2_submitted;
    }

    /**
     * Returns SM2 array
     *
     * @return array
     */
    public static function sm2_get_processed_form()
    {
        return self::$m_sm2_submitted;
    }

    /**
     * Checks a formular field and returns a boolean result.
     *
     * @param   string  $p_field
     * @param   integer $p_method
     *
     * @return  boolean
     */
    public static function sm2_check($p_field, $p_method)
    {
        if (isset(self::$m_sm2_submitted["$p_field"])) {
            $l_field = self::$m_sm2_submitted["$p_field"];
            $l_result = false;

            switch ($p_method) {
                case 'SM2_METHOD_IS_EMPTY':
                    $l_result = empty($l_field);
                    break;

                case 'SM2_METHOD_IS_NUMERIC':
                    $l_result = is_numeric($l_field);
                    break;

                case 'SM2_METHOD_IS_VALID_SQL_DATE':
                    $l_result = preg_match("/^\d\d\d\d-\d\d-\d\d$/", $l_field);
                    break;

                case 'SM2_METHOD_IS_VALID_SQL_DATE_TIME':
                    $l_result = preg_match("/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/", trim($l_field));
                    break;
            }

            return $l_result;
        }

        return false;
    }

    /**
     * Check, if the edit-mode is set.
     *
     * @return  bool
     */
    public static function editmode(): bool
    {
        global $g_navmode;
        $l_editmode = isys_glob_get_param(C__CMDB__GET__EDITMODE);

        // @see  ID-8018  Check if we are in 'cancel' mode.
        if (isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__CANCEL) {
            return false;
        }
        if ($_POST[C__GET__NAVMODE] != C__NAVMODE__SAVE || (isset($g_navmode) && $g_navmode == C__NAVMODE__EDIT)) {
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT && $l_editmode == C__EDITMODE__OFF) {
                return (bool)C__EDITMODE__ON;
            }
        } else {
            return (bool)C__EDITMODE__OFF;
        }

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            return (bool)C__EDITMODE__ON;
        }

        return (bool)$l_editmode;
    }

    /**
     * Append Javascript file to main <head>
     *
     * @param string $p_www_path_to_file Physical path to Javascript File
     *
     * @return isys_component_template
     */
    public function appendJavascript($p_www_path_to_file)
    {
        $this->append('jsFiles', $p_www_path_to_file);

        return $this;
    }

    /**
     * Append Javascript file to main <head>
     *
     * @param string $p_inlineJS Javascript Code
     *
     * @return isys_component_template
     */
    public function appendInlineJavascript($p_inlineJS)
    {
        $this->append('additionalInlineJS', $p_inlineJS);

        return $this;
    }

    /**
     * Sets an include for $p_key to $p_path in global variable $index_includes
     *
     * @param $p_key
     * @param $p_path
     *
     * @return $this
     */
    public function include_template($p_key, $p_path)
    {
        global $index_includes;
        $index_includes[$p_key] = $p_path;

        return $this;
    }

    /**
     * Returns css class for table row.
     *
     * @param int $index
     *
     * @return string
     * @deprecated
     */
    public function row_background_color(int $index): string
    {
        return ($index % 2 == 1) ? "CMDBListElementsEven" : "CMDBListElementsOdd";
    }

    /**
     * Globally activate the template editmode.
     *
     * @return  isys_component_template
     */
    public function activate_editmode()
    {
        global $g_navmode;

        $g_navmode = C__NAVMODE__EDIT;
        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        $_GET[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;

        return $this;
    }

    /**
     * Global i-doit plugin callback for Smarty.
     *
     * @param array $parameters
     *
     * @return string
     */
    public function smarty_function_isys(array $parameters): string
    {
        $navMode = isys_glob_get_param('navMode');

        if (!isset($navMode)) {
            return '';
        }

        $pluginClassName = 'isys_smarty_plugin_' . $parameters['type'];

        if (!class_exists($pluginClassName)) {
            return '';
        }

        /** @var $plugin isys_smarty_plugin */
        $plugin = new $pluginClassName;

        // When a name is passed, try to apply TOM rules.
        if (isset($parameters['name'])) {
            $currentStack = implode('.', $this->stack) . '.' . $parameters['name'];

            if (isset($this->m_ruleset[$currentStack])) {
                // Merge TOM rules and directly passed parameters.
                $parameters = array_replace_recursive($parameters, $this->m_ruleset[$currentStack]);
            }
        }

        // Call smarty plugin dependant from the selected edit mode.
        if (self::editmode()) {
            $output = $plugin->navigation_edit($this, $parameters);
        } else {
            $output = $plugin->navigation_view($this, $parameters);
        }

        // SM2 : Processing.
        if (method_exists($plugin, 'get_meta_map') && $plugin->enable_meta_map()) {
            $l_plugin_map = $plugin->get_meta_map();
            $l_plugin_map[] = "type";

            if (is_array($l_plugin_map) && !empty($parameters["name"]) && !$parameters["p_plain"]) {
                foreach ($l_plugin_map as $l_plugin_field) {
                    $value = isys_glob_unescape($parameters[$l_plugin_field]);

                    if (is_array($parameters[$l_plugin_field])) {
                        $value = serialize($value);
                    }

                    $name = "SM2__{$parameters["name"]}[{$l_plugin_field}]";

                    $output .= '<input type="hidden" name="' . $name . '" value="' . isys_glob_htmlentities($value) . '" />' . PHP_EOL;
                }
            }
        }

        return (string) $output;
    }

    /**
     * Callback for handling TOM groups, this is mandatory for Smarty >= 5.0!
     *
     * @param array       $parameters
     * @param string|null $content
     * @param Template    $template
     * @param bool        $repeat
     *
     * @return string
     */
    public function smarty_tom_grouping(array $parameters, ?string $content, Template $template, bool &$repeat): string
    {
        if ($repeat) {
            $this->stack[] = $parameters["name"];
        } else {
            array_pop($this->stack);
        }

        if (!empty($parameters["name"])) {
            return $content ?? '';
        }

        return "Invalid 'isys_group' instance, mandatory 'name' parameter was not set!";
    }

    /**
     * Add a TOM rule to the ruleset - these rules will be applied, while plugin callbacks are executed.
     *
     * @param   string $p_command
     *
     * @return  isys_component_template
     */
    public function smarty_tom_add_rule($p_command)
    {
        if (preg_match("/^([a-zA-Z0-9*\[\]\?._-]+)\\.(.*?)=(.+)$/is", $p_command, $l_regex)) {
            if (isset($l_regex[2]) && $l_regex[2] != "") {
                $this->m_ruleset[$l_regex[1]][$l_regex[2]] = $l_regex[3];
            }
        }

        return $this;
    }

    /**
     * Applies rules defined in $rules to fields relative to $tom.
     *
     * Example:
     *    smarty_tom_add_rules("tom.content", array(
     *       "FIELDNAME" => array(
     *          "ATTRIBUTE1" => "CONTENT",
     *          "ATTRIBUTE2" => "CONTENT"
     *       ),
     *       "FIELD2" => array(
     *          "ATTR1" => "CONTENT")
     *       )
     *    );
     *
     * @param string     $tom
     * @param array|null $rules
     *
     * @return $this
     */
    public function smarty_tom_add_rules(string $tom, ?array $rules = null): self
    {
        if (empty($rules) || empty($tom)) {
            return $this;
        }

        foreach ($rules as $field => $data) {
            if (!is_array($data) || empty($data)) {
                continue;
            }

            foreach ($data as $key => $value) {
                $this->m_ruleset["{$tom}.{$field}"][$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns an array containing template variables
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function get_template_vars($name = null)
    {
        return $this->getTemplateVars($name);
    }

    /**
     * This is a wrapper method for allowing a little fluent-interface.
     *
     * @param   string $template
     * @param   string $cache_id
     * @param   string $compile_id
     * @param   string $parent
     *
     * @return $this
     * @throws SmartyException
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        /* Emit signal beforeRender */
        isys_component_signalcollection::get_instance()
            ->emit('system.gui.beforeRender');

        parent::display($template, $cache_id, $compile_id, $parent);

        return $this;
    }

    /**
     * Returns the current ruleset as array.
     *
     * @return array
     * @deprecated Use '$template->getRuleset()'
     */
    public function &smarty_tom_get_current_ruleset()
    {
        return $this->getRuleset();
    }

    /**
     * Returns the current ruleset as array.
     *
     * @return array
     */
    public function getRuleset(): array
    {
        return $this->m_ruleset;
    }

    /**
     * Initialize and assign some default variables used by smarty
     */
    private function initialize_defaults()
    {
        global $g_config, $g_dirs;
        // Assign current query string for ajax-submits to the right URL.
        $l_gets = $_GET;
        unset($l_gets[C__GET__AJAX_CALL], $l_gets[C__GET__AJAX]);

        // Assign the default theme and the product info and some directories:
        $this->assign('theme', $g_config['theme'])
            ->assign('dir_tools', $g_config['www_dir'] . 'src/tools/')
            ->assign('dir_images', $g_dirs['images'])
            ->assign('dir_theme', $g_dirs['theme'])
            ->assign('dirs', $g_dirs)
            ->assign('query_string', isys_glob_build_url((isys_glob_http_build_query($l_gets))))
            ->assign('html_encoding', BASE_ENCODING)
            ->assign('C__NAVMODE__SAVE', C__NAVMODE__SAVE)
            ->assign('C__NAVMODE__CANCEL', C__NAVMODE__CANCEL)
            ->assign('treemode', C__CMDB__GET__TREEMODE)
            ->assign('viewmode', C__CMDB__GET__VIEWMODE)
            ->assign('object', C__CMDB__GET__OBJECT)
            ->assign('objtype', C__CMDB__GET__OBJECTTYPE)
            ->assign('objgroup', C__CMDB__GET__OBJECTGROUP)
            ->assign('config', $g_config);

        if (class_exists('isys_application')) {
            $this->assign('gProductInfo', isys_application::instance()->info);
        }

        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
            $this->assign('formAdditionalAction', 'action="?' . htmlentities($_SERVER['QUERY_STRING'], ENT_COMPAT, BASE_ENCODING) . '"');
        }

        unset($l_gets);
    }

    /**
     * Constructs the template component.
     *
     * @throws SmartyException
     */
    public function __construct()
    {
        global $g_dirs, $g_comp_session;

        // Construct Smarty library
        parent::__construct();

        // Set directories.
        $this->setTemplateDir($g_dirs['smarty'] . 'templates/')
            ->setConfigDir($g_dirs['smarty'] . 'configs/')
            ->setCompileDir($g_dirs['temp'] . 'smarty/compiled/')
            ->setCacheDir($g_dirs['temp'] . 'smarty/cache/');
        // We DO NOT use caching, because smarty caches via variable name... But we re-use variable names everywhere :(

        $this->stack = [];
        $this->m_ruleset = [];
        $this->default_template_handler_func = [$this, 'templateHandler'];
        $this->debugging = false;
        $this->compile_check = true;
        $this->force_compile = false;

        if (is_object($g_comp_session)) {
            $this->compile_id = $g_comp_session->get_language();
        } else {
            $this->compile_id = 'en';
        }

        $this->setLeftDelimiter('[{');
        $this->setRightDelimiter('}]');

        $this
            // Register 'isys' plugin.
            ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'isys', [$this, 'smarty_function_isys'])
            // Register 'isys_group' plugin.
            ->registerPlugin(Smarty::PLUGIN_BLOCK, 'isys_group', [$this, 'smarty_tom_grouping'])
            // @see ID-9210 Register 'common' modifier functions.
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'count', 'count')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'implode', 'implode')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'json_encode', 'json_encode')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'round', 'round')
            // @see ID-10010 Register more modifier functions.
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'array_keys', 'array_keys')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'array_values', 'array_values')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'extension_loaded', 'extension_loaded')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'file_exists', 'file_exists')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'get_class', 'get_class')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'intval', 'intval')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'is_object', 'is_object')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'is_scalar', 'is_scalar')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'is_string', 'is_string')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'is_numeric', 'is_numeric')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'isys_glob_get_pagelimit', 'isys_glob_get_pagelimit')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'isys_glob_is_edit_mode', 'isys_glob_is_edit_mode')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'get_smarty_arr_YES_NO', 'get_smarty_arr_YES_NO')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'method_exists', 'method_exists')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'strpos', 'strpos')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'strstr', 'strstr')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'time', 'time')
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'version_compare', 'version_compare')
        ;

        // We just need to initialize this one time, since this is static.
        if (!is_array(self::$m_sm2_submitted)) {
            $this->sm2_process_request_data();
        }

        // Initialize defaults
        $this->initialize_defaults();
    }

    /**
     * Moved here from 'functions.inc.php'.
     *
     * @param string                  $type
     * @param string                  $name
     * @param string|null             $content
     * @param int|null                $modified
     * @param isys_component_template $template
     *
     * @return bool
     */
    public function templateHandler($type, $name, &$content, &$modified, isys_component_template $template)
    {
        $appPath = isys_application::instance()->app_path;

        if ($type === 'file' && !is_readable($name)) {
            $l_default_file = "{$appPath}/src/themes/default/smarty/templates/{$name}";

            if (file_exists($l_default_file)) {
                $modified = time();
                $content = file_get_contents($l_default_file);

                return true;
            }

            $name = basename($name);
            $l_default_file = "{$appPath}/src/themes/default/smarty/templates/{$name}";

            if (file_exists($l_default_file)) {
                $modified = time();
                $content = file_get_contents($l_default_file);

                return true;
            }
        }

        return false;
    }
}
