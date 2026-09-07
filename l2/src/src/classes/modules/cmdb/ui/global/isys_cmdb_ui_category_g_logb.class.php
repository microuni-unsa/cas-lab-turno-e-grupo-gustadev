<?php

/**
 * i-doit
 *
 * CMDB Logbook
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_logb extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return array|void
     * @throws isys_exception_database
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_rules = [];

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            $lang = isys_application::instance()->container->get('language');
            $l_rules["C__CATG__LOGBOOK__ALERTLEVEL"]["p_arData"] = filter_array_by_keys_of_defined_constants([
                'C__LOGBOOK__ALERT_LEVEL__0' => $lang->get("LC__CMDB__LOGBOOK__ALERT_LEVEL_0"),
                'C__LOGBOOK__ALERT_LEVEL__1' => $lang->get("LC__CMDB__LOGBOOK__ALERT_LEVEL_1"),
                'C__LOGBOOK__ALERT_LEVEL__2' => $lang->get("LC__CMDB__LOGBOOK__ALERT_LEVEL_2"),
                'C__LOGBOOK__ALERT_LEVEL__3' => $lang->get("LC__CMDB__LOGBOOK__ALERT_LEVEL_3")
            ]);

            $this->get_template_component()
                ->assign("bShowCommentary", false)
                ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
                ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__list.tpl');

            return;
        }

        $l_catdata = $p_cat->get_result()->get_row();

        if (!$l_catdata) {
            $l_catdata = $p_cat->get_result()
                ->__to_array();
        }

        $l_listdao = isys_component_dao_logbook::instance($this->get_database_component());
        $l_daores = $l_listdao->get_result_by_logbook_id($l_catdata["isys_catg_logb_list__isys_logbook__id"]);
        $l_catdata = $l_daores->get_row();

        $l_lbTitle = $l_mod_event_manager->translateEvent(
            $l_catdata["isys_logbook__event_static"],
            $l_catdata["isys_logbook__obj_name_static"],
            $l_catdata["isys_logbook__category_static"],
            $l_catdata["isys_logbook__obj_type_static"],
            $l_catdata["isys_logbook__entry_identifier_static"],
            $l_catdata["isys_logbook__changecount"]
        );

        // Make rules
        $l_rules["C__CMDB__LOGBOOK__TITLE"]["p_strValue"] = $l_lbTitle;

        // Unescape the logbook sql statement.
        // @see ID-2217 Removed the "isys_glob_unescape" functions
        $l_desc = $l_catdata["isys_logbook__description"];
        $l_desc = $l_listdao->match_description($l_desc);
        $l_rules["C__CMDB__LOGBOOK__DESCRIPTION"]["p_strValue"] = $l_desc;
        $l_rules["C__CMDB__LOGBOOK__COMMENT"]["p_strValue"] = $l_catdata["isys_logbook__comment"];
        $l_rules["C__CMDB__LOGBOOK__DATE"]["p_strValue"] = isys_application::instance()->container->get('locales')->fmt_datetime($l_catdata["isys_logbook__date"]);
        $l_rules["C__CMDB__LOGBOOK__LEVEL"]["p_strValue"] = isys_application::instance()->container->get('language')->get($l_catdata["isys_logbook_level__title"]);

        //is there a name?
        $l_dao_user = new isys_cmdb_dao_category_s_person_master($this->get_database_component());
        $l_userdata = $l_dao_user->get_person_by_id($l_catdata["isys_logbook__isys_obj__id"]);

        if ($l_userdata->num_rows() > 0) {
            $l_userdata = $l_userdata->get_row();

            $l_strUsertitle = "<a href=\"?" . C__CMDB__GET__OBJECT . "=" . $l_userdata["isys_cats_person_list__isys_obj__id"] . "\">" .
                $l_userdata["isys_cats_person_list__title"] . "</a>" . " (" . $l_userdata["isys_cats_person_list__first_name"] .
                $l_userdata["isys_cats_person_list__last_name"];

            if ($l_userdata["isys_cats_person_list__mail_address"]) {
                $l_strUsertitle .= '; <a href="' . isys_helper_link::create_mailto($l_userdata["isys_cats_person_list__mail_address"]) . '" target="_blank">' .
                    $l_userdata["isys_cats_person_list__mail_address"] . '</a>';
            }

            $l_strUsertitle .= ")";
        } else {
            $l_strUsertitle = $l_catdata["isys_logbook__user_name_static"];
        }

        $l_rules["C__CMDB__LOGBOOK__USER"]["p_strValue"] = $l_strUsertitle;

        // Assign and retrieve changes.
        $l_changes_ar = $this->get_changes_as_array($l_catdata["isys_logbook__changes"]);

        $l_rules["C__CMDB__LOGBOOK__CHANGED_FIELDS"]["p_strValue"] = count($l_changes_ar);

        if (($l_changes = $this->get_changes_as_html_table($l_changes_ar))) {
            $this->get_template_component()->assign("changes", $l_changes);
        }

        //switch navbar buttons
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT);

        // Apply rules
        $this->get_template_component()
            ->include_template('contentbottomcontent', 'content/bottom/content/catg__logbook.tpl')
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Genrate html list for logbook entries.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param array                  $p_get_param_override
     * @param string                 $p_strVarName
     * @param string                 $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param string                 $p_db_field_name
     *
     * @return NULL
     * @throws  isys_exception_general
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        $language = isys_application::instance()->container->get('language');

        /* @var  isys_component_dao_logbook $l_listdao */
        $l_listdao = isys_component_dao_logbook::instance($this->get_database_component());

        $l_arTableHeader = [
            "+"                              => "",
            "isys_logbook__title"            => $language->get("LC__CMDB__LOGBOOK__TITLE"),
            "isys_logbook__user_name_static" => $language->get('LC__CMDB__LOGBOOK__SOURCE__USER'),
            "isys_logbook__changes"          => $language->get("LC__CMDB__LOGBOOK__CHANGED_FIELDS"),
            "isys_logbook__date"             => $language->get("LC__CMDB__LOGBOOK__DATE"),
            "isys_logbook_level__title"      => $language->get("LC__CMDB__LOGBOOK__LEVEL")
        ];

        $l_objList = new isys_component_list_logbook(null, null, $l_listdao);

        $l_strRowLink = "document.location.href='?moduleID=" . defined_or_default('C__MODULE__LOGBOOK') . "&id=[{isys_logbook__id}]';";

        $l_objList->config($l_arTableHeader, $l_strRowLink);
        $l_objList->setTableClass('mainTable w100');

        $_POST['object_id'] = $_GET[C__CMDB__GET__OBJECT];

        $this->setupFilter($l_listdao);

        $this->get_template_component()
            ->activate_editmode()
            ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__list.tpl')
            ->include_template('navbar', 'content/navbar/logbook.tpl')
            ->assign("LogbookList", $l_objList->getTempTableHtml($_POST))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rule("tom.content.top.filter.p_strValue=" . isys_glob_get_param("filter"));

        return null;
    }

    /**
     * Return compressed changes as "from"/"to" array.
     *
     * @param   string $p_changes_binary
     *
     * @return  array
     */
    public function get_changes_as_array($p_changes_binary)
    {
        return isys_component_dao_logbook::get_changes_as_array($p_changes_binary);
    }

    /**
     * Return changes as HTML Table.
     *
     * @param   array  $l_changes_ar
     * @param   string $tableClassName
     *
     * @return  string|bool
     */
    public function get_changes_as_html_table($l_changes_ar, string $tableClassName = "listing")
    {
        $language = isys_application::instance()->container->get('language');

        if (is_array($l_changes_ar) && count($l_changes_ar) > 0) {
            $l_changes = "<table class=\"" . $tableClassName . " w100\" cellspacing=\"0\">" .
                "<colgroup><col width=\"30%\" /><col width=\"30%\" /><col width=\"30%\" /></colgroup>" .
                "<thead>" . "<tr>" .
                "<th>" . $language->get("LC__REGEDIT__VALUE") . "</th>" .
                "<th>" . $language->get("LC_UNIVERSAL__FROM") . "</th>" .
                "<th>" . $language->get("LC__TO") . "</th>" .
                "</tr>" . "</thead>" . "<tbody>";

            $i = 0;

            foreach ($l_changes_ar as $l_field => $l_change) {
                $l_lang_field = '';

                if (strpos($l_field, '::') > 0) {
                    $l_data = explode('::', $l_field);

                    if (class_exists($l_data[0])) {
                        $l_dao = new $l_data[0]($this->get_database_component());

                        if (isset($l_data[2])) {
                            // Custom category.
                            if (method_exists($l_dao, 'set_catg_custom_id')) {
                                $l_dao->set_catg_custom_id($l_data[2]);
                            }
                        }

                        if (method_exists($l_dao, 'get_properties_ng')) {
                            $l_data_information = $l_dao->get_properties();
                            $l_lang_field = $language->get($l_data_information[$l_data[1]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
                        }
                    }
                } else {
                    if (is_numeric($l_field) && is_array($l_change)) {
                        // If there are more than one dataset inside the logbook entry than it has to be split up in more entries.
                        $l_changes .= '<tr><td colspan="3" class="bold">' . $language->get('LC__LOGBOOK__ENTRY') . ': ' . $l_field . '</td></tr>';

                        // Collected entries from one category.
                        foreach ($l_change as $l_property_key => $l_property_changes) {
                            $l_data = explode('::', $l_property_key);
                            $l_lang_field = '';

                            if (class_exists($l_data[0])) {
                                $l_dao = new $l_data[0]($this->get_database_component());
                                if (isset($l_data[2])) {
                                    // Custom category.
                                    if (method_exists($l_dao, 'set_catg_custom_id')) {
                                        $l_dao->set_catg_custom_id($l_data[2]);
                                    }
                                }

                                if (method_exists($l_dao, 'get_properties_ng')) {
                                    $l_data_information = $l_dao->get_properties();
                                    $l_lang_field = $language->get($l_data_information[$l_data[1]][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
                                }
                            }

                            // @see ID-2217 Removed the "isys_glob_unescape" functions
                            $l_changes .= '<tr>' .
                                '<td>' . $l_lang_field . '</td>' .
                                '<td>' . self::encodeValue($l_property_changes["from"]) . '</td>' .
                                '<td>' . self::encodeValue($l_property_changes["to"]) . '</td>' .
                                '</tr>';
                        }

                        $l_changes .= '<tr><td colspan="3"></td></tr>';
                        continue;
                    } else {
                        if (stripos($l_field, 'C__') === 0) {
                            $l_lang_field = $language->get("L" . str_replace('C__', 'C__CMDB__', $l_field));
                        } else {
                            $l_lang_field = $l_field;
                        }

                        if (strpos($l_lang_field, "LC__") === 0) {
                            $l_lang_field = $l_field;
                        }

                        if (strstr($l_lang_field, "COMMENTARY")) {
                            $l_lang_field = $language->get('LC__CMDB__CATG__DESCRIPTION');
                        } else {
                            if ($l_lang_field == 'C__OBJ__CMDB_STATUS') {
                                $l_lang_field = "CMDB-Status";
                            } else {
                                if (strpos($l_lang_field, "C__") === 0) {
                                    $l_tmp = explode("__", $l_lang_field);
                                    $l_lang_field = $l_tmp[count($l_tmp) - 1];

                                    if ($l_lang_field == "HIDDEN") {
                                        $l_lang_field = $l_tmp[count($l_tmp) - 2];
                                    }

                                    unset($l_tmp);

                                    $l_lang_field = ucfirst(strtolower(str_replace("_", " ", $l_lang_field)));
                                }
                            }
                        }
                    }
                }

                /* ---------------------------------------------------------------------------------------- */

                // @see ID-9631 Skip changes that have the wrong format, due to failed unserialize calls.
                if (!is_array($l_change)) {
                    continue;
                }

                // Set data, if not set.
                if (!isset($l_change['from'])) {
                    $l_change['from'] = '';
                }

                // Set data, if not set.
                if (!isset($l_change['to'])) {
                    $l_change['to'] = '';
                }

                // @see ID-2217 Removed the "isys_glob_unescape" functions
                $l_changes .= '<tr>' .
                    '<td>' . $l_lang_field . '</td>' .
                    '<td>' . self::encodeValue($l_change["from"]) . '</td>' .
                    '<td>' . self::encodeValue($l_change["to"]) . '</td>' .
                    '</tr>';
            }

            return $l_changes . "</tbody></table>";
        }

        return false;
    }

    private static function encodeValue($value, $seperator = '<br/>'): string
    {
        if (!is_array($value)) {
            return htmlentities($value);
        }

        $encodedValues = array_map(function ($a) {
            return htmlentities($a);
        }, $value);

        return implode($seperator, $encodedValues);
    }

    /**
     * @param $p_daoLogbook
     */
    private function setupFilter($p_daoLogbook)
    {
        $l_sourceFilter = $p_daoLogbook->getSources();
        $l_alertFilter = $p_daoLogbook->getAlertlevels();
        $l_categoryFilter = [];
        $objectId = $this->m_catdao->get_object_id();

        if ($objectId) {
            try {
                // Get object type for this specific object
                $objectTypeId = $this->m_catdao->get_object_by_id($objectId)->get_row_value('isys_obj__isys_obj_type__id');

                // Use the same logic as the menu tree
                $categoryComponent = new \idoit\Module\Cmdb\Component\Category\Category($objectTypeId, $objectId);
                $categories = $categoryComponent->getAssignedCategories();
            } catch (\Exception) {
                $categories = [];
            }

            foreach ($categories as $category) {
                if (!($category instanceof \idoit\Module\Cmdb\Component\Category\Item\FolderItem)) {
                    $l_categoryFilter[$category->getName()] = $category->getTranslatedName();
                }
            }
        }

        $l_typeFilter = [
            '0' => 'LC__CMDB__CATG__SYSTEM',
            '1' => 'LC__NAVIGATION__MENUTREE__BUTTON_OBJECT_VIEW'
        ];

        $l_rules = [
            'filter_source' => [
                'p_arData'        => $l_sourceFilter,
                'p_strSelectedID' => (isset($_POST['filter_source'])) ? $_POST['filter_source'] : '-1'
            ],
            'filter_alert'  => [
                'p_arData'        => $l_alertFilter,
                'p_strSelectedID' => (isset($_POST['filter_alert'])) ? $_POST['filter_alert'] : '-1'
            ],
            'filter_type'   => [
                'p_arData'        => $l_typeFilter,
                'p_strSelectedID' => (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '-1'
            ],
            'filter_category' => [
                'p_arData'        => $l_categoryFilter,
                'p_strSelectedID' => $_POST['filter_category'] ?? '-1',
                'p_strClass' => 'input-small'
            ]
        ];

        if (isset($_POST['filter_from__HIDDEN'])) {
            $l_rules['filter_from']['p_strValue'] = $_POST['filter_from__HIDDEN'];
        }

        if (isset($_POST['filter_to__HIDDEN'])) {
            $l_rules['filter_to']['p_strValue'] = $_POST['filter_to__HIDDEN'];
        }

        if (isset($_POST['filter_user__HIDDEN'])) {
            $l_rules['filter_user']['p_strSelectedID'] = $_POST['filter_user__HIDDEN'];
        }

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }

    /**
     * @param isys_component_template $p_template
     *
     * @throws isys_exception_ui
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template('module__logbook__list.tpl');

        parent::__construct($p_template);
    }
}
