<?php

/**
 * Menu AJAX handler for several things: Dragbar, Visibility, Breadcrumb, ...
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_menu extends isys_ajax_handler
{
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
            switch ($_GET['func']) {
                case 'save_menu_width':
                    $l_return['data'] = $this->save_menu_width($_POST['menu_width']);
                    break;

                case 'save_tree_visibility':
                    $l_return['data'] = $this->save_tree_visibility($_POST['objtype'], $_POST['categories']);
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
     * Method for saving the menu width.
     *
     * @param   integer $p_width
     *
     * @return  boolean
     */
    protected function save_menu_width($p_width = 235)
    {
        // Initialize, set and regenerate the cache
        isys_usersettings::set('gui.leftcontent.width', $p_width);

        return true;
    }

    /**
     * Method for saving the menu visibility (hide empty items).
     *
     * @param mixed $hideObjectType
     * @param mixed $hideCategory
     *
     * @return bool
     * @throws Exception
     */
    protected function save_tree_visibility($hideObjectType = null, $hideCategory = null): bool
    {
        $userSettings = isys_application::instance()->container->get('settingsUser');

        if ($hideObjectType !== null) {
            $userSettings->set('gui.tree.hide-empty-object-types', (bool)$hideObjectType);
        }

        if ($hideCategory !== null) {
            $userSettings->set('gui.tree.hide-empty-categories', (bool)$hideCategory);
        }

        return true;
    }
}
