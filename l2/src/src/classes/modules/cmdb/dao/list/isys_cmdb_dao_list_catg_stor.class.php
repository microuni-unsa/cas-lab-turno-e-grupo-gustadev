<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for storage devices
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_stor extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__STORAGE_DEVICE');
    }

    /**
     * Return constant of category type-
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $p_row["isys_catg_stor_list__capacity"] = isys_convert::memory(
            $p_row["isys_catg_stor_list__capacity"],
            $p_row["isys_memory_unit__const"],
            C__CONVERT_DIRECTION__BACKWARD
        );
        $p_row["isys_catg_stor_list__capacity"] = isys_convert::formatNumber($p_row["isys_catg_stor_list__capacity"]) . " " . $p_row["isys_memory_unit__title"];
        $p_row['isys_catg_stor_list__hotspare'] = $p_row['isys_catg_stor_list__hotspare'] ? 'LC__UNIVERSAL__YES' : 'LC__UNIVERSAL__NO';
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_stor_list__slot'        => 'LC__CATG__STORAGE_SLOT',
            'isys_catg_stor_list__title'       => 'LC__CATG__STORAGE_TITLE',
            'isys_stor_type__title'            => 'LC__CATG__STORAGE_TYPE',
            'isys_catg_stor_list__capacity'    => 'LC__CATG__STORAGE_CAPACITY',
            'isys_catg_controller_list__title' => 'LC__CATG__STORAGE_CONTROLLER',
            'isys_catg_stor_list__firmware'    => 'LC__CATG__STORAGE_FIRMWARE',
            'isys_catg_stor_list__description' => 'LC__CMDB__CAT__COMMENTARY',
            'isys_stor_manufacturer__title'    => 'LC__CMDB__CATG__MANUFACTURE',
            'isys_stor_model__title'           => 'LC__CATG__STORAGE_MODEL',
            'isys_catg_stor_list__capacity'    => 'LC__CMDB_CATG__MEMORY_CAPACITY',
            'isys_catg_stor_list__hotspare'    => 'LC__CATG__STORAGE_HOTSPARE',
            'isys_stor_con_type__title'        => 'LC__CATG__STORAGE_CONNECTION_TYPE',
            'isys_catg_controller_list__title' => 'LC__STORAGE_CONTROLLER',
            'isys_catg_raid_list__title'       => 'LC__CATG__RAIDGROUP',
            'isys_catg_stor_list__serial'      => 'LC__CATG__STORAGE_SERIAL',
            'isys_stor_lto_type__title'        => 'LC__CATG__STORAGE_LTO_TYPE',
            'isys_catg_stor_list__fc_address'  => 'LC__CATG__STORAGE_FC_ADDRESS',
            'isys_catg_stor_list__slot'        => 'LC__CATG__STORAGE_SLOT',
        ];
    }
}
