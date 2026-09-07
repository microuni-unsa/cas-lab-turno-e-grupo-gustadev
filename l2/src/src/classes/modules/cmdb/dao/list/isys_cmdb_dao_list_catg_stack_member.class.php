<?php

/**
 * i-doit
 *
 * CMDB Global list DAO for stacking.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_stack_member extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__STACK_MEMBER');
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
     * Modifies row.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $wwwDir = isys_application::instance()->www_path;
        $language = isys_application::instance()->container->get('language');

        $p_row['obj_title'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (isset($p_row['isys_catg_stack_member_list__stack_member']) && $p_row['isys_catg_stack_member_list__stack_member']) {
            $stackMemberObject = isys_application::instance()->container->get('cmdb_dao')
                ->get_object((int) $p_row['isys_catg_stack_member_list__stack_member'])
                ->get_row();

            $p_row['obj_title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                $stackMemberObject['isys_obj__id'],
                $language->get_in_text("{$stackMemberObject['isys_obj_type__title']} &raquo; {$stackMemberObject['isys_obj__title']}")
            );
        }

        switch ($p_row['isys_catg_stack_member_list__mode']) {
            default:
                $p_row['isys_catg_stack_member_list__mode'] = isys_tenantsettings::get('gui.empty_value', '-');
                break;

            case 1:
                $p_row['isys_catg_stack_member_list__mode'] = '<div class="display-flex align-items-center">' .
                    '<img src="' . $wwwDir . 'images/axialis/basic/symbol-ok.svg" alt="" class="mr5" />' .
                    '<span class="text-green">' . $language->get('LC__UNIVERSAL__ACTIVE') . '</span>' .
                    '</div>';
                break;

            case 0:
                $p_row['isys_catg_stack_member_list__mode'] = '<div class="display-flex align-items-center">' .
                    '<img src="' . $wwwDir . 'images/axialis/basic/symbol-cancel.svg" alt="" class="mr5" />' .
                    '<span class="text-red">' . $language->get('LC__UNIVERSAL__PASSIVE') . '</span>' .
                    '</div>';
                break;
        }
    }

    /**
     * Method for returning the fields to display.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "obj_title"                                => "LC__CATG__STACK_MEMBER__STACK_MEMBER",
            "isys_catg_stack_member_list__mode"        => "LC__CATG__STACK_MEMBER__MODE",
            "isys_catg_stack_member_list__description" => "LC__CMDB__LOGBOOK__DESCRIPTION"
        ];
    }

    /**
     * @param array $getParams
     *
     * @return string
     */
    public function make_row_link($getParams = [])
    {
        $objectField = '[{' . $this->get_dao_category()->get_connected_object_id_field() . '}]';

        return isys_helper_link::create_url([
            C__CMDB__GET__OBJECT   => $objectField,
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG     => defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__TREEMODE => $getParams[C__CMDB__GET__TREEMODE]
        ]);
    }
}
