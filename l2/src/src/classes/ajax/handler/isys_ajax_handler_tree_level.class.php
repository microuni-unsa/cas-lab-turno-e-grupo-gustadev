<?php

/**
 * AJAX handler for tree levels.
 *
 * @package     i-doit
 * @subpackage  General
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_tree_level extends isys_ajax_handler
{
    /**
     * @var array
     */
    private $assignedCategoryCache = [];

    /**
     * Initialize tree level handler
     *
     * @return void
     * @throws Exception
     */
    public function init()
    {
        // @see ID-11253 Unblock the session, so that 'follow-up requests' don't have to wait.
        isys_application::instance()->container->get('session')->write_close();

        // Retrieve id parameter. Convert -1 request to null (root node).
        if ($this->m_get['id'] == -1) {
            $l_id = null;
        } else {
            $l_id = $this->m_get['id'];
        }

        if ($this->m_get['get_obj_name']) {
            $l_location_popup = new isys_popup_browser_location();
            echo $l_location_popup->format_selection($l_id);

            $this->_die();
        }

        header('Content-Type: application/json');

        // ID-2898 - Only append the auth-condition, if this feature is enabled.
        $l_consider_rights = isys_tenantsettings::get('auth.use-in-location-tree', false);

        // Check for "$l_id != C__OBJ__ROOT_LOCATION" because we can't authorize the root location itself ;)
        if ($l_consider_rights && $l_id > 0 && $l_id != defined_or_default('C__OBJ__ROOT_LOCATION') && !isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $l_id)) {
            echo '[]';

            $this->_die();
        }

        $l_return = $this->location($l_id, true, $l_consider_rights);

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * Filters logical devices from the physical tree in the combined view
     *
     * @param  $p_tree_array
     * @throws isys_exception_database
     */
    public function filter_logical_devices_from_physical(&$p_tree_array)
    {
        $l_dao = isys_cmdb_dao::instance($this->m_database_component);

        if (empty($this->assignedCategoryCache) || !is_array($this->assignedCategoryCache)) {
            $result = $l_dao->get_obj_type_by_catg(filter_defined_constants(['C__CATG__ASSIGNED_LOGICAL_UNIT', 'C__CATG__PERSON_ASSIGNED_WORKSTATION']));
            while ($row = $result->get_row()) {
                $this->assignedCategoryCache[$row['isys_obj_type_2_isysgui_catg__isysgui_catg__id']][$row['isys_obj_type_2_isysgui_catg__isys_obj_type__id']] = true;
            }
        }

        foreach ($p_tree_array as $l_key => $l_value) {
            $objTypeIdCurrentObject = $l_dao->get_objTypeID($l_value['id']);
            $objTypeIdParentObject = $l_dao->get_objTypeID($l_value['parentId']);
            if (!defined('C__CATG__ASSIGNED_LOGICAL_UNIT') || !is_array($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')])) {
                continue;
            }

            if (!$this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdCurrentObject] &&
                !$this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdParentObject]) {
                $l_logical_data = $l_dao->retrieve('SELECT workstation.isys_catg_logical_unit_list__isys_obj__id__parent AS workstationObject, isys_obj__isys_obj_type__id AS objType
                            FROM isys_catg_logical_unit_list workstation
                            INNER JOIN isys_obj ON isys_obj__id = workstation.isys_catg_logical_unit_list__isys_obj__id__parent
						WHERE workstation.isys_catg_logical_unit_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_value['id']))
                    ->get_row();

                $objTypeId = $l_logical_data['objType'];

                if ($objTypeId) {
                    if ($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeId] === true) {
                        unset($p_tree_array[$l_key]);
                    }
                }
            } elseif ($this->assignedCategoryCache[constant('C__CATG__ASSIGNED_LOGICAL_UNIT')][$objTypeIdCurrentObject] && $l_value['is_physically_assigned']) {
                $query = 'SELECT isys_catg_location_list__id FROM isys_catg_logical_unit_list
                    INNER JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent
                    WHERE isys_catg_logical_unit_list__isys_obj__id = ' . $l_dao->convert_sql_id($l_value['id']);
                $res = $l_dao->retrieve($query);
                if (is_countable($res) && count($res)) {
                    unset($p_tree_array[$l_key]);
                }
            }
        }
    }

    /**
     * Return logical locations by its parent.
     *
     * @param  integer $p_id
     * @param  boolean $p_leaf_checking
     * @param  boolean $p_consider_rights
     *
     * @return array
     */
    private function location($p_id = -1, $p_leaf_checking = true, $p_consider_rights = false)
    {
        // Determine, whether to show the root location.
        $l_hide_root = isset($this->m_get['hide_root']);
        $routeGenerator = isys_application::instance()->container->get('route_generator');

        $l_result = [];

        $l_dao = new isys_cmdb_dao_location($this->m_database_component);

        // ID-3236 - Instead of simply displaying children of the root-location, display objects that we are allowed to see.
        if ($p_id === null && $p_consider_rights) {
            $l_res = isys_auth_cmdb_objects::instance()->get_allowed_locations((int)defined_or_default('C__OBJ__ROOT_LOCATION', 1), $l_hide_root);
        } else {
            $l_res = $l_dao->get_child_locations($p_id, $l_hide_root, $this->m_get['containersOnly'], $p_consider_rights);
        }

        if ($l_res instanceof isys_component_dao_result && count($l_res)) {
            while ($l_row = $l_res->get_row()) {
                // Decide whether to show node.
                // 1. Condition: If the object ID is equal to the current, the node is not added. So we avoid loopbacks in the location tree.
                if ($l_row['isys_catg_location_list__isys_obj__id'] != $this->m_get['currentObjID']) {
                    $l_icon = $routeGenerator->generate('cmdb.object-type.icon', ['objectTypeId' => $l_row['isys_obj_type__id']]);

                    if ($l_hide_root && $l_row['isys_catg_location_list__parentid'] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                        $l_node_root = -1;
                    } else {
                        $l_node_root = $l_row['isys_catg_location_list__parentid'];
                    }

                    // Set the default callback action.
                    $l_selectCallback = 'ObjectSelected';

                    if ($this->m_get['selectCallback']) {
                        $l_selectCallback = $this->m_get['selectCallback'];
                    }

                    $l_hyperlinks = !(isset($this->m_get['no-hyperlinks']) && $this->m_get['no-hyperlinks'] > 0);
                    $l_url = $l_hyperlinks ? 'javascript:' . $l_selectCallback . '(' . $l_row['isys_catg_location_list__isys_obj__id'] . ', ' .
                        $l_row['isys_obj__isys_obj_type__id'] . ', \'' . addslashes($l_row['isys_obj__title']) . '\', \'' .
                        isys_application::instance()->container->get('language')
                            ->get($l_row['isys_obj_type__title']) . '\', \'g_browser_Link_' . $l_row['isys_catg_location_list__isys_obj__id'] .
                        '\', this);' : 'javascript:Prototype.emptyFunction;';

                    $l_result[] = [
                        'id'                     => $l_row ['isys_catg_location_list__isys_obj__id'],
                        'text'                   => $l_row['isys_obj__title'],
                        'icon'                   => $l_icon,
                        'url'                    => $l_url,
                        'parentId'               => $l_node_root,
                        'is_leaf'                => ($p_leaf_checking ? ($l_row['ChildrenCount'] == 0) : false),
                        'is_logically_assigned'  => false,
                        'is_physically_assigned' => true
                    ];
                }
            }
        }

        usort($l_result, function ($a, $b) {
            return strnatcasecmp($a['text'], $b['text']);
        });

        return $l_result;
    }
}
