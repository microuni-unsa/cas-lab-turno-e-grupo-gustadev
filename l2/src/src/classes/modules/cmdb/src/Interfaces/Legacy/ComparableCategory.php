<?php

namespace idoit\Module\Cmdb\Interfaces\Legacy;

/**
 * i-doit interface for categories to implement the "compare_category_data" method.
 *
 * @package    i-doit
 * @subpackage Core
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ComparableCategory
{
    /**
     * Compares category data which will be used in the import module
     *
     * @param $p_category_data_values
     * @param $p_object_category_dataset
     * @param $p_used_properties
     * @param $p_comparison
     * @param $p_badness
     * @param $p_mode
     * @param $p_category_id
     * @param $p_unit_key
     * @param $p_category_data_ids
     * @param $p_local_export
     * @param $p_dataset_id_changed
     * @param $p_dataset_id
     * @param $p_logger
     * @param $p_category_name
     * @param $p_table
     * @param $p_cat_multi
     * @param $p_category_type_id
     * @param $p_category_ids
     * @param $p_object_ids
     * @param $p_already_used_data_ids
     *
     * @return mixed
     */
    public function compare_category_data(
        &$p_category_data_values,
        &$p_object_category_dataset,
        &$p_used_properties,
        &$p_comparison,
        &$p_badness,
        &$p_mode,
        &$p_category_id,
        &$p_unit_key,
        &$p_category_data_ids,
        &$p_local_export,
        &$p_dataset_id_changed,
        &$p_dataset_id,
        &$p_logger,
        &$p_category_name = null,
        &$p_table = null,
        &$p_cat_multi = null,
        &$p_category_type_id = null,
        &$p_category_ids = null,
        &$p_object_ids = null,
        &$p_already_used_data_ids = null
    );
}
