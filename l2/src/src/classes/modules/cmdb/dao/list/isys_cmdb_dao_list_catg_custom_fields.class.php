<?php

/**
 * i-doit
 * DAO: Custom category list
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_custom_fields extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * @var array
     */
    private $m_config = [];

    /**
     * @var array
     */
    private $m_properties = [];

    /**
     * @var array
     */
    private $m_rows = [];

    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CUSTOM_FIELDS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Overwrite this for special count Handling.
     *
     * @return  array  Counts of several Status
     */
    public function get_rec_counts()
    {
        if ($this->m_rec_counts) {
            return $this->m_rec_counts;
        } else {
            $l_normal = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );
            $l_archived = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__ARCHIVED,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );
            $l_deleted = $this->get_result(
                null,
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__DELETED,
                $_GET[C__CMDB__GET__CATG_CUSTOM],
                null,
                'GROUP BY isys_catg_custom_fields_list__data__id'
            );

            $this->m_rec_counts = [
                C__RECORD_STATUS__NORMAL   => ($l_normal) ? $l_normal->num_rows() : 0,
                C__RECORD_STATUS__ARCHIVED => ($l_archived) ? $l_archived->num_rows() : 0,
                C__RECORD_STATUS__DELETED  => ($l_deleted) ? $l_deleted->num_rows() : 0,
            ];

            if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
                $l_template = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__TEMPLATE);
                $this->m_rec_counts[C__RECORD_STATUS__TEMPLATE] = ($l_template) ? $l_template->num_rows() : 0;
            }

            return $this->m_rec_counts;
        }
    }

    /**
     * Get result for list.
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   integer $p_recStatus
     * @param   integer $p_config_id
     * @param   string  $p_condition
     * @param   string  $p_additional
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id = null, $p_recStatus = null, $p_config_id = null, $p_condition = null, $p_additional = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_custom_fields_list
			WHERE isys_catg_custom_fields_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			AND isys_catg_custom_fields_list__status = ' . $this->convert_sql_int($p_recStatus) . ' ';

        if ($p_condition !== null) {
            $l_sql .= $p_condition . ' ';
        }

        if ($p_config_id !== null) {
            $l_sql .= 'AND isys_catg_custom_fields_list__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_config_id) . ' ';
        }

        if ($p_additional !== null) {
            $l_sql .= $p_additional;
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method which build the row link
     *
     * @param array $getParams
     *
     * @return  string
     * @throws isys_exception_cmdb
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function make_row_link($getParams = [])
    {
        $getParams[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;
        $getParams['cateID'] = '[{id}]';

        return urldecode(isys_helper_link::create_catg_url($getParams));
    }

    /**
     * Method for retrieving the displayable fields.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function get_fields()
    {
        $l_arr = [];
        $l_arr['id'] = 'ID';

        foreach ($this->m_properties as $l_prop_key => $l_property) {
            // Continue if field should not be visible in list.
            if (isset($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['show_in_list']) && !$l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['show_in_list']) {
                continue;
            }

            if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'hr' && $l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] != 'html') {
                $l_arr[$l_prop_key] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
            }
        }

        return $l_arr;
    }

    /**
     * Sets properties.
     *
     * @param   array $p_properties
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_properties($p_properties)
    {
        $this->m_properties = $p_properties;

        return $this;
    }

    /**
     * Sets row.
     *
     * @param   array $p_row
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_rows($p_row)
    {
        $this->m_rows = $p_row;

        return $this;
    }

    /**
     * Gets row.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_rows()
    {
        return $this->m_rows;
    }

    /**
     * Sets custom category config.
     *
     * @param   array $p_config
     *
     * @return  $this
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_config($p_config)
    {
        $this->m_config = $p_config;

        return $this;
    }

    /**
     * @param int    $objectId
     * @param string $type
     *
     * @return array|null
     * @throws isys_exception_database
     */
    public function reformatRows(int $objectId, string $type): ?array
    {
        $l_data_id = $l_arr = null;
        $l_req_obj = isys_request::factory()->set_object_id($objectId);

        $locales = isys_application::instance()->container->get('locales');
        $quicklink = isys_ajax_handler_quick_info::instance();
        $l_counter = 0;

        if (is_array($this->m_rows) && count($this->m_rows) > 0) {
            $l_arr = [];

            $l_dao_custom_fields = isys_cmdb_dao_category_g_custom_fields::instance(isys_application::instance()->container->get('database'));
            $l_already_used_keys = [];

            $groupAsCommaList = $this->getConfiguration()->getGroupingType() === isys_cmdb_dao_category_property_ng::C__GROUPING__COMMA || $type === 'csv';

            foreach ($this->m_rows as $l_row) {
                $l_dao_custom_fields->set_catg_custom_id($l_row[0]['isys_catg_custom_fields_list__isysgui_catg_custom__id']);
                foreach ($this->m_properties as $l_prop_key => $l_property) {
                    $l_type = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION];
                    $l_identifier = substr($l_prop_key, strlen($l_type) + 1);

                    $l_arr[$l_counter][$l_prop_key] = null;

                    if ($l_prop_key === 'description') {
                        $l_identifier = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_CUSTOM . $l_dao_custom_fields->get_catg_custom_id();
                        $l_type = 'commentary';
                    }

                    foreach ($l_row as $l_row_key => $l_val) {
                        $l_data_id = (int)$l_val['isys_catg_custom_fields_list__data__id'];
                        if ($l_val['isys_catg_custom_fields_list__field_key'] == $l_identifier && $l_val['isys_catg_custom_fields_list__field_type'] == $l_type) {
                            switch ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                                case C__TYPE__DATE:
                                    // @see  ID-4984  Prepend a hidden date in format "yyyy-mm-dd".
                                    $l_arr[$l_counter][$l_prop_key] = '<span data-sort="' . htmlentities($l_val['isys_catg_custom_fields_list__field_content'], ENT_COMPAT, BASE_ENCODING) . '">' .
                                        $locales->fmt_date($l_val['isys_catg_custom_fields_list__field_content']) . '</span>';
                                    break;

                                case C__TYPE__DATE_TIME:
                                    // @see  ID-7507  New "date + time" property
                                    $l_arr[$l_counter][$l_prop_key] = '<span data-sort="' . htmlentities($l_val['isys_catg_custom_fields_list__field_content'], ENT_COMPAT, BASE_ENCODING) . '">' .
                                        $locales->fmt_datetime($l_val['isys_catg_custom_fields_list__field_content']) .
                                        '</span>';
                                    break;

                                case C__TYPE__INT:
                                    switch ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['popup']) {
                                        case 'browser_object':
                                        case 'file':
                                            if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] > 0) {
                                                if (!isset($l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                    $l_val['isys_catg_custom_fields_list__data__id']])) {
                                                    $l_objects = $l_dao_custom_fields->get_assigned_entries(
                                                        $l_val['isys_catg_custom_fields_list__field_key'],
                                                        $l_val['isys_catg_custom_fields_list__data__id']
                                                    );

                                                    $values = [];

                                                    foreach ($l_objects as $l_obj_id) {
                                                        $values[] = $quicklink->getQuickInfoReplacement(
                                                            $l_obj_id,
                                                            $l_dao_custom_fields->get_obj_name_by_id_as_string($l_obj_id)
                                                        );
                                                    }

                                                    $l_arr[$l_counter][$l_prop_key] = $this->getGroupedValue($values, $groupAsCommaList);
                                                    $l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                    $l_val['isys_catg_custom_fields_list__data__id']] = true;
                                                }
                                            } else {
                                                $l_arr[$l_counter][$l_prop_key] = $quicklink->getQuickInfoReplacement(
                                                    $l_val['isys_catg_custom_fields_list__field_content'],
                                                    $l_dao_custom_fields->get_obj_name_by_id_as_string($l_val['isys_catg_custom_fields_list__field_content'])
                                                );
                                            }
                                            break;
                                        case 'calendar':
                                            $l_arr[$l_counter][$l_prop_key] = $locales
                                                ->fmt_date($l_val['isys_catg_custom_fields_list__field_content']);
                                            break;
                                        default:
                                            // dialog plus
                                            $l_callback_obj = $l_property[C__PROPERTY__UI]['params']['p_arData'];

                                            if ($l_callback_obj instanceof isys_callback) {
                                                if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['multiselection'] > 0) {
                                                    if (!isset($l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                        $l_val['isys_catg_custom_fields_list__data__id']])) {
                                                        $l_dialog_ids = $l_dao_custom_fields->get_assigned_entries(
                                                            $l_val['isys_catg_custom_fields_list__field_key'],
                                                            $l_val['isys_catg_custom_fields_list__data__id']
                                                        );

                                                        // Set assigned dialog ids to limit the result
                                                        $l_req_obj->set_data('selectedDialogIds', $l_dialog_ids);

                                                        // Execute data callback
                                                        $l_data = $l_callback_obj->execute($l_req_obj);

                                                        $values = [];

                                                        foreach ($l_dialog_ids as $l_id) {
                                                            if (isset($l_data[$l_id])) {
                                                                $values[] = $l_data[$l_id];
                                                            }
                                                        }

                                                        $l_arr[$l_counter][$l_prop_key] = $this->getGroupedValue($values, $groupAsCommaList);
                                                        $l_already_used_keys[$l_val['isys_catg_custom_fields_list__field_key'] . '+' .
                                                        $l_val['isys_catg_custom_fields_list__data__id']] = true;
                                                    }
                                                } elseif ($l_val['isys_catg_custom_fields_list__field_content']) {
                                                    // Set selected dialog ids
                                                    $l_req_obj->set_data('selectedDialogIds', [$l_val['isys_catg_custom_fields_list__field_content']]);
                                                    $l_req_obj->set_category_data_id($l_val['isys_catg_custom_fields_list__data__id']);

                                                    // Execute data callback
                                                    $l_data = $l_callback_obj->execute($l_req_obj);

                                                    if (isset($l_data[$l_val['isys_catg_custom_fields_list__field_content']])) {
                                                        $l_arr[$l_counter][$l_prop_key] = $l_data[$l_val['isys_catg_custom_fields_list__field_content']];
                                                    }
                                                }
                                            }
                                            break;
                                    }
                                    break;
                                case C__TYPE__TEXT:
                                case C__TYPE__TIME:
                                case C__TYPE__TEXT_AREA:
                                    switch ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) {
                                        case 'hr':
                                            continue 3;
                                        case 'link':
                                            if (!empty($l_val['isys_catg_custom_fields_list__field_content'])) {
                                                $l_param = [
                                                    'p_strValue'        => $l_val['isys_catg_custom_fields_list__field_content'],
                                                    'p_strTarget'       => '_blank',
                                                    'p_bInfoIconSpacer' => '0'
                                                ];
                                                unset($_GET[C__CMDB__GET__EDITMODE]);

                                                $l_arr[$l_counter][$l_prop_key] = isys_factory::get_instance('isys_smarty_plugin_f_link', $this->m_db)
                                                    ->navigation_view(isys_application::instance()->template, $l_param);
                                            }
                                            break;
                                        case 'password':
                                            $l_arr[$l_counter][$l_prop_key] = '*****';
                                            break;
                                        default:
                                            // @see ID-7871 removing 1 from unset yes-no fields
                                            if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['extra'] === 'yes-no' &&
                                                $l_val['isys_catg_custom_fields_list__field_content'] == -1) {
                                                $l_arr[$l_counter][$l_prop_key] = isys_tenantsettings::get('gui.empty_value', '-');
                                            } else {
                                                $l_arr[$l_counter][$l_prop_key] = nl2br($l_val['isys_catg_custom_fields_list__field_content']);
                                            }
                                            break;
                                    }
                                    break;
                            }

                            if (!isset($l_arr[$l_counter][$l_prop_key])) {
                                $l_arr[$l_counter][$l_prop_key] = null;
                            }

                            unset($l_row[$l_row_key]);
                            continue 2;
                        }
                    }
                }

                $l_arr[$l_counter]['id'] = $l_data_id;
                $l_counter++;
            }
        }

        return $l_arr;
    }

    /**
     * @param array $values
     * @param bool  $asCommaList
     *
     * @return string
     * @see ID-11453
     */
    private function getGroupedValue(array $values, bool $asCommaList): string
    {
        if ($asCommaList) {
            return implode(', ', $values);
        }

        return '<ul><li>' . implode('</li><li>', $values) . '</li></ul>';
    }
}
