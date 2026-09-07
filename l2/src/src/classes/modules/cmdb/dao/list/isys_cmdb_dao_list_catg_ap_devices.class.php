<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for remote management controller backward
 *
 * @package    i-doit
 * @subpackage CMDB_Category_lists
 * @author     Paul Kolbovich <pkolbovich@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_ap_devices extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__AP_DEVICES');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $str
     * @param   integer $objID
     * @param   integer $cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($str = null, $objID = null, $cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_ap_devices::instance($this->m_db)
            ->get_data(null, $objID, "", null, (empty($cRecStatus) ? $this->get_rec_status() : $cRecStatus));
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        if ($row["isys_obj__id"] != null) {
            $row["isys_obj_type__title"] = $this->m_cat_dao->get_objtype_name_by_id_as_string($this->m_cat_dao->get_objtypeID($row["isys_obj__id"]));
            $row["isys_obj__title"] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                $row["isys_obj__id"],
                $this->m_cat_dao->get_obj_name_by_id_as_string($row["isys_obj__id"])
            );
        }
    }

    /**
     * Gets flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     *
     * @return array
     */
    public function get_fields()
    {
        return [
            "isys_obj__title"      => "LC_UNIVERSAL__OBJECT",
            "isys_obj_type__title" => "LC__CMDB__OBJTYPE"
        ];
    }
}
