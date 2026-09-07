<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_tree extends isys_ajax_handler
{
    /**
     * Initialization for this AJAX request.
     */
    public function init()
    {
        $userSettings = isys_application::instance()->container->get('settingsUser');

        // @see  ID-5888  Save the "last-clicked" object type group to the session, so we can re-set it in later responses.
        if (isset($_GET[C__CMDB__GET__OBJECTGROUP]) && is_numeric($_GET[C__CMDB__GET__OBJECTGROUP])) {
            // Append a zero, because of the definition in `isys_component_menu->set_objtype_group_menu`.
            $_SESSION['last-clicked-object-type-group'] = (int) ($_GET[C__CMDB__GET__OBJECTGROUP] . 0);
        }

        if (!defined('C__WF__VIEW__TREE') || $_GET[C__CMDB__GET__TREEMODE] != C__WF__VIEW__TREE) {
            if ($_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_LOCATION) {
                isys_auth_cmdb::instance()->check(isys_auth::VIEW, 'LOCATION_VIEW');
            }

            if ($_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_OBJECTTYPE || $_GET[C__CMDB__GET__TREEMODE] == C__CMDB__VIEW__TREE_LOCATION) {
                $userSettings->set('gui.default-tree-view', $_GET[C__CMDB__GET__TREEMODE]);
            }
        }

        // At this point we need to select the previously saved option to assign it to the template.
        isys_application::instance()->container->get('template')
            ->assign('treeType', $userSettings->get('gui.default-tree-type', C__CMDB__VIEW__TREE_LOCATION__LOCATION))
            ->display('file:' . $this->m_smarty_dir . 'templates/content/leftContent.tpl');

        $this->_die();
    }

    /**
     * Method which defines, if the hypergate needs to be run.
     *
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }
}
