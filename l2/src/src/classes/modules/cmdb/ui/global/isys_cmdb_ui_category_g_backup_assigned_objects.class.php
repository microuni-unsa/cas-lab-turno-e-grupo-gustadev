<?php

/**
 * i-doit
 *
 * CMDB UI: Global category "Backup assigned objects".
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_backup_assigned_objects extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $categoryDao
     *
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $categoryDao)
    {
        $rules = [];
        $catData = $categoryDao->get_general_data();

        $this->fill_formfields($categoryDao, $rules, $catData);

        $this->get_template_component()
            ->assign('reverse', true)
            ->assign('backup_type', (int)$catData['isys_catg_backup_list__isys_backup_type__id'])
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules);
    }
}
