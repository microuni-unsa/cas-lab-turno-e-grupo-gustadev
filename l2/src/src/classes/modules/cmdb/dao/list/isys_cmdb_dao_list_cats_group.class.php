<?php

/**
 * i-doit
 *
 * DAO: group List
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_group extends isys_component_dao_category_table_list
{
    /**
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__GROUP');
    }

    /**
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID = null, $p_cRecStatus = null)
    {
        $l_group_type_data['isys_cats_group_type_list__type'] = 0;
        if (class_exists('isys_cmdb_dao_category_s_group_type')) { // @see ID-4321
            $l_dao = isys_cmdb_dao_category_s_group_type::instance($this->get_database_component());
            $l_group_type_data = $l_dao->get_data(null, $p_objID)
                ->get_row();
        }

        if ($l_group_type_data['isys_cats_group_type_list__type'] == 1) {
            $l_report_id = $l_group_type_data['isys_cats_group_type_list__isys_report__id'];
            $l_sql = 'SELECT * FROM isys_obj WHERE isys_obj__id IS NULL;';

            $l_report_dao = isys_report_dao::instance(isys_application::instance()->database_system);
            $l_report_data = $l_report_dao->get_report($l_report_id);

            if (!empty($l_report_data['isys_report__query'])) {
                $l_sql = 'SELECT obj_main.isys_obj__id AS __id__, obj_main.isys_obj__title, objtype.isys_obj_type__title ';

                if (substr_count(strtolower($l_report_data['isys_report__query']), 'from') > 1) {
                    $l_sql .= ', ' . substr($l_report_data['isys_report__query'], strpos(strtolower($l_report_data['isys_report__query']), 'select') + 6, strlen($l_report_data['isys_report__query']));
                } else {
                    $l_sql .= substr($l_report_data['isys_report__query'], strpos($l_report_data['isys_report__query'], 'FROM'), strlen($l_report_data['isys_report__query']));
                }
                $l_sql = str_replace(
                    'isys_obj AS obj_main',
                    'isys_obj AS obj_main INNER JOIN isys_obj_type AS objtype ON objtype.isys_obj_type__id = obj_main.isys_obj__isys_obj_type__id ',
                    $l_sql
                );
            }
            try {
                return $this->retrieve($l_sql);
            } catch (Exception $e) {
                throw new Exception(isys_application::instance()->container->get('language')->get('LC__CMDB__CATS__GROUP__QUERY_IS_NOT_COMPATIBLE'));
            }
        } else {
            return isys_cmdb_dao_category_s_group::instance(isys_application::instance()->database)
                ->get_connected_objects($p_objID, $p_cRecStatus ?: $this->get_rec_status());
        }
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        global $g_dirs;

        $id = $row['__id__'] ?: $row['isys_obj__id'];

        if ($id != null) {
            $link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $id,
                C__CMDB__GET__OBJECTTYPE => $row['isys_obj__isys_obj_type__id'],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__GLOBAL'),
                C__CMDB__GET__TREEMODE   => $_GET['tvMode']
            ]);

            $row['isys_obj__title'] = isys_ajax_handler_quick_info::instance()->get_quick_info(
                $id,
                '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam" /> ' . $row['isys_obj__title'],
                $link
            );
        }
    }

    /**
     * Returns the link the browser shall follow if clicked on a row.
     *
     * @param array $getParams
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function make_row_link($getParams = [])
    {
        $type = $this->get_dao_category()->get_group_type($getParams[C__CMDB__GET__OBJECT]);
        $placeholder = $this->get_dao_category()->get_connected_object_id_field();

        if (class_exists('isys_cmdb_dao_category_s_group_type') && $type === isys_cmdb_dao_category_s_group_type::GROUP_TYPE_DYNAMIC) {
            $placeholder = '__id__';
        }

        $objectField = '[{' . $placeholder . '}]';

        return isys_helper_link::create_url([
            C__CMDB__GET__OBJECT   => $objectField,
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__LIST_CATEGORY,
            C__CMDB__GET__CATG     => defined_or_default('C__CATG__GLOBAL'),
            C__CMDB__GET__TREEMODE => $getParams[C__CMDB__GET__TREEMODE]
        ]);
    }

    /**
     * Retrieve the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"      => "LC__CMDB__CATG__ODEP_OBJ",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    }
}
