<?php

/**
 * i-doit
 *
 * Popup class for location browser.
 *
 * @package     i-doit
 * @subpackage  Popups
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_popup_browser_location extends isys_component_popup
{
    use \idoit\Component\Provider\Singleton;

    /**
     * Return text instead of a href links in format_selection
     *
     * @var bool
     */
    public $m_format_as_text = false;

    /**
     * Exclude current object in format_selection
     *
     * @var bool
     */
    public $m_format_exclude_self = false;

    /**
     * Cut object name at 100 characters in format_selection
     *
     * @var int
     */
    public $m_format_object_name_cut = 100;

    /**
     * Cut the complete string in format_selection. 0 for disabling
     *
     * @var int
     */
    public $m_format_str_cut = 0;

    /**
     * Prefix for format_selection
     *
     * @var string
     */
    public $m_format_prefix = '';

    /**
     * Cut the string in format_selection by object depth count
     *
     * @var int
     */
    public $m_format_count = 0;

    /**
     * @param int $length
     *
     * @return $this
     * @inherit
     */
    public function set_format_count($length = 0)
    {
        $this->m_format_count = $length;

        return $this;
    }

    /**
     * @param int $length
     *
     * @return $this
     * @inherit
     */
    public function set_format_str_cut($length = 0)
    {
        $this->m_format_str_cut = $length;

        return $this;
    }

    /**
     * @param bool|false $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_as_text($bool = false)
    {
        $this->m_format_as_text = $bool;

        return $this;
    }

    /**
     * @param int $cut
     *
     * @inherit
     * @return $this
     */
    public function set_format_object_name_cut($cut = 100)
    {
        $this->m_format_object_name_cut = $cut;

        return $this;
    }

    /**
     * @param bool|false $bool
     *
     * @inherit
     * @return $this
     */
    public function set_format_exclude_self($bool = false)
    {
        $this->m_format_exclude_self = $bool;

        return $this;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function set_format_prefix($prefix)
    {
        $this->m_format_prefix = $prefix;

        return $this;
    }

    /**
     * Handles SMARTY request for location browser.
     *
     * @param   isys_component_template  & $p_tplclass
     * @param                            $p_params
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function handle_smarty_include(isys_component_template $p_tplclass, $p_params)
    {
        if (empty($p_params['name'])) {
            return '';
        }

        // If no origin object is selected, select the root node.
        if (empty($p_params['p_intOriginObjID'])) {
            $p_params['p_intOriginObjID'] = isys_cmdb_dao_location::instance($this->database)->get_root_location_as_integer();
        }

        $l_objPlugin = new isys_smarty_plugin_f_text();

        if (strstr($p_params['name'], '[') && strstr($p_params['name'], ']')) {
            $l_tmp = explode('[', $p_params['name']);
            $l_view = $l_tmp[0] . '__VIEW[' . implode('[', array_slice($l_tmp, 1));
            $l_hidden = $l_tmp[0] . '__HIDDEN[' . implode('[', array_slice($l_tmp, 1));
            unset($l_tmp);
        } else {
            $l_view = $p_params['name'] . '__VIEW';
            $l_hidden = $p_params['name'] . '__HIDDEN';
        }

        // Extract object id from either p_strValue or p_strSelectedID.
        if ($p_params['p_strValue']) {
            $l_object_id = (int)$p_params['p_strValue'];
        } elseif ($p_params['p_strSelectedID']) {
            $l_object_id = (int)$p_params['p_strSelectedID'];
        } else {
            $l_object_id = 0;
        }

        $l_editmode = (isys_glob_is_edit_mode() || isset($p_params['edit'])) && !isset($p_params['plain']);

        // We got a preselection.
        if ($l_object_id > 0) {
            // We are in edit mode, don't display any tags inside the input.
            if ($l_editmode) {
                $this->set_format_as_text(true)
                    ->set_format_exclude_self(false)
                    ->set_format_object_name_cut(0)
                    ->set_format_str_cut(0);

                $p_params['p_strValue'] = $this->format_selection($l_object_id, true);
            } else {
                $p_params['p_strValue'] = $this->format_selection($l_object_id);
            }

            $p_params['p_strSelectedID'] = $l_object_id;
        }

        // Prepare a few parameters.
        $p_params['mod'] = 'cmdb';
        $p_params['popup'] = 'browser_location';
        $p_params['currentObjID'] = $_GET[C__CMDB__GET__OBJECT];
        $p_params['resultField'] = $p_params['name'];
        $p_params['rootObjectId'] = $p_params['p_intOriginObjID'];
        $p_params['p_additional'] .= ' data-hidden-field="' . str_replace('"', "'", $l_hidden) . '"';

        // Hidden field, in which the selected value is put.
        $l_strHiddenField = '<input name="' . $l_hidden . '" id="' . $l_hidden . '" type="hidden" value="' . $l_object_id . '" />';

        // Set parameters for the f_text plug-in.
        $p_params['p_strID'] = $l_view;

        // Check if we are in edit-mode before displaying the input fields.
        if ($l_editmode) {
            // Auto Suggesstion.
            $p_params['p_onClick'] = "if (this.value == '" . isys_glob_htmlentities($p_params['p_strValue']) . "') this.value = '';";
            $p_params['p_strSuggest'] = 'location';
            $p_params['p_strSuggestView'] = $l_view;
            $p_params['p_strSuggestHidden'] = $l_hidden;

            if (isset($p_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT])) {
                $p_params['p_strSuggestParameters'] = 'parameters: {}, selectCallback: function() {' . $p_params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] . '}';
            }

            $p_params['disableInputGroup'] = true;

            // OnClick Handler for detaching the object.
            $l_onclick_detach = 'var $view = $(\'' . $l_view . '\'), $hidden = $(\'' . $l_hidden . '\');' . 'if($view && $hidden) {' . '$view.setValue(\'' .
                $this->language->get('LC__UNIVERSAL__CONNECTION_DETACHED') . '!\');' . '$hidden.setValue(0);}' .
                ($p_params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] ?: '');

            return $l_objPlugin->navigation_edit($this->template, $p_params) .
                '<button type="button" class="btn attach" onClick="' . $this->process_overlay('', '70%', '60%', $p_params, null, 640, 240, 1200, 800) . ';" title="' . $this->language->get('LC__UNIVERSAL__SELECT') . '" data-tooltip="1">' .
                '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/link.svg"  alt="" />' .
                '</button>' .
                '<button type="button" class="btn detach" onClick="' . $l_onclick_detach . ';" title="' . $this->language->get('LC__UNIVERSAL__DESELECT') . '" data-tooltip="1">' .
                '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/link-broken.svg" alt="" />' .
                '</button>' . $l_strHiddenField;
        }

        $p_params['p_bHtmlDecode'] = true;

        return $l_objPlugin->navigation_view($this->template, $p_params) . $l_strHiddenField;
    }

    /**
     * Formats a location string according to the specified enclosure ID.
     *
     * @param int  $objectID
     * @param bool $plain
     *
     * @return string
     * @throws Exception
     */
    public function format_selection($objectID, $plain = false)
    {
        global $g_dirs;
        $cut = null;
        $output = [];
        $quickInfoHandler = new isys_ajax_handler_quick_info();
        $dao = new isys_cmdb_dao_category_g_location($this->database);

        $separator = isys_tenantsettings::get('gui.separator.location', ' > ');

        $rootLocationTitle = isys_application::instance()->container->get('language')->get('LC__OBJ__ROOT_LOCATION');
        $root = $plain ? $rootLocationTitle : '<img src="' . $g_dirs['images'] . 'axialis/construction/house-4.svg" class="vam" title="' . $rootLocationTitle . '" />';

        if ($objectID == defined_or_default('C__OBJ__ROOT_LOCATION')) {
            return $root;
        }

        // Get location tree.
        try {
            $locationPath = $dao->get_location_path($objectID);
            $locationPath[] = null;
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }

        $locationPath = array_reverse(array_slice($locationPath, 0, max((int) $this->m_format_count-1, 0)));
        $i = 0;
        $length = 0;

        // Parse location tree.
        foreach ($locationPath as $locationID) {
            // @see ID-6330 Removed a check "$l_object_id > C__OBJ__ROOT_LOCATION" because this can happen, when the root location had to be re-created.
            if ($locationID != $objectID) {
                if (is_null($cut)) {
                    $i++;
                }
                if (is_null($locationID)) {
                    $output[] = $root;
                    continue;
                }

                $objectTitle = $dao->get_cached_locations($locationID)['title'];

                if (!$this->m_format_as_text) {
                    $output[] = $quickInfoHandler->get_quick_info($locationID, $objectTitle, C__LINK__OBJECT, $this->m_format_object_name_cut);
                } else {
                    $output[] = $objectTitle;
                }

                $length += strlen($objectTitle);

                if ($length >= $this->m_format_str_cut && is_null($cut)) {
                    $cut = $i;
                }
            }
        }

        // @see ID-6330 Removed a check "$l_object_id > C__OBJ__ROOT_LOCATION" because this can happen, when the root location had to be re-created.
        if (!$this->m_format_exclude_self) {
            if (!$this->m_format_as_text) {
                $output[] = $quickInfoHandler->get_quick_info($objectID, $dao->get_obj_name_by_id_as_string($objectID), C__LINK__OBJECT, $this->m_format_object_name_cut);
            } else {
                $output[] = $dao->get_obj_name_by_id_as_string($objectID);
            }
        }

        $l_tmp = $output;

        $output = implode($separator, $output);

        if ($this->m_format_str_cut && null !== $cut && count($l_tmp) >= $cut && strlen(strip_tags($output)) >= $this->m_format_str_cut) {
            $l_out_stripped = rtrim(strip_tags(preg_replace('(<script[^>]*>([\\S\\s]*?)<\/script>)', '', $output)), $separator);

            $output = '<abbr title="' . $l_out_stripped . '">..</abbr> ' . $separator;

            $tmpCount = count($l_tmp);

            for ($i = (int)($cut / 2);$i < $tmpCount;$i++) {
                if (isset($l_tmp[$i]) && !empty($l_tmp[$i])) {
                    $output .= $l_tmp[$i];

                    if (isset($l_tmp[$i + 1])) {
                        $output .= $separator;
                    }
                }
            }
        }

        if ($output && $this->m_format_prefix) {
            return $this->m_format_prefix . $output;
        }

        return $output;
    }

    /**
     * Handle the popup window and its content.
     *
     * @param isys_module_request $p_modreq
     *
     * @return isys_component_template|void
     * @throws \idoit\Exception\JsonException
     */
    public function &handle_module_request(isys_module_request $p_modreq)
    {
        // Get our parameters.
        $parameters = isys_format_json::decode(base64_decode($_POST['params']), true);

        $objectId = (int)$parameters['p_strSelectedID'];
        $locationPath = [];
        $selectionView = $this->language->get('LC__UNIVERSAL__NOT_SELECTED');
        $selectionHidden = null;

        if ($objectId) {
            // The get_node_hierarchy returns a comma-separated list including the object itself (for example a server).
            $locationPath = array_map('intval', explode(',', isys_cmdb_dao_location::instance($this->database)->get_node_hierarchy($objectId)));

            // Remove the selected object itself.
            array_shift($locationPath);

            $selectionHidden = $objectId;
            $selectionView = $this->set_format_as_text(true)
                ->set_format_exclude_self(false)
                ->set_format_object_name_cut(0)
                ->set_format_str_cut(0)
                ->format_selection($objectId, true);
        }

        // @see ID-6330 Notify the user, if the "rootObjectId" is not there.
        if (!$parameters['rootObjectId']) {
            $administrationUrl = isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__SETTINGS_PAGE => 'cache',
            ], true);

            isys_notify::warning(
                'It seems as if your "Root location" is missing. Please go to <a href="' . $administrationUrl . '" target="_blank">the administration</a> and run the "Correction of locations".',
                ['sticky' => true]
            );
        }

        // Assign everything.
        $this->template
            ->assign('callback_accept', $parameters['callback_accept'] . ';')
            ->assign('return_hidden', $parameters['p_strSuggestHidden'])
            ->assign('return_view', $parameters['p_strSuggestView'])
            ->assign('rootObjectId', $parameters['rootObjectId'])
            ->assign('selectionView', $selectionView)
            ->assign('selectionHidden', $selectionHidden)
            ->assign('onlyContainer', (bool)$parameters['containers_only'])
            ->assign('openNodes', array_filter($locationPath))
            ->assign('selectedNodes', array_filter([$selectionHidden]))
            ->assign('considerRights', isys_tenantsettings::get('auth.use-in-location-tree', false))
            ->display('popup/location_ajax.tpl');
        die();
    }

    /**
     * isys_popup_browser_location constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->set_format_str_cut(isys_tenantsettings::get('maxlength.location.path', 100));
        $this->set_format_object_name_cut(isys_tenantsettings::get('maxlength.location.objects', 16));
        $this->set_format_count(isys_tenantsettings::get('cmdb.limits.location-path', 5));
    }
}
