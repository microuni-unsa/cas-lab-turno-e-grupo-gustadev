<?php

/**
 * i-doit
 *
 * DAO: Category list for backup servers.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_backup_assigned_objects extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__BACKUP__ASSIGNED_OBJECTS');
    }

    /**
     * Return constant of category type.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * @param array $row
     */
    public function modify_row(&$row)
    {
        if ($row['isys_obj__id'] != null) {
            $l_link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $row['isys_obj__id'],
                C__CMDB__GET__OBJECTTYPE => $row['isys_obj__isys_obj_type__id'],
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__BACKUP'),
                C__CMDB__GET__TREEMODE   => $_GET['tvMode']
            ]);

            $row['isys_obj__title'] = isys_ajax_handler_quick_info::instance()->getQuickInfoReplacement(
                $row['isys_obj__id'],
                $row['isys_obj__title'],
                $l_link
            );
        }

        $dialogDao = isys_cmdb_dao_dialog::instance($this->m_db);

        $row['backup_type'] = '';
        $row['cycle'] = '';

        if ($row['isys_catg_backup_list__isys_backup_cycle__id']) {
            $backupCycleData = $dialogDao->set_table('isys_backup_cycle')->get_data($row['isys_catg_backup_list__isys_backup_cycle__id']);
            $row['cycle'] = $backupCycleData['title'];
        }

        if ($row['isys_catg_backup_list__isys_backup_type__id']) {
            $backupCycleData = $dialogDao->set_table('isys_backup_type')->get_data($row['isys_catg_backup_list__isys_backup_type__id']);
            $row['backup_type'] = $backupCycleData['title'];
        }
    }

    /**
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_backup_list__title'        => 'LC__CMDB__CATG__BACKUP__TITLE',
            'isys_obj__title'                     => 'LC__CMDB__CATG__BACKUP__BACKUPS',
            'isys_catg_backup_list__path_to_save' => 'LC__CMDB__CATG__BACKUP__PATH_TO_SAVE',
            'backup_type'                         => 'LC__CMDB__CATG__BACKUP__BACKUP_TYPE',
            'cycle'                               => 'LC__CMDB__CATG__BACKUP__CYCLE',
            'isys_catg_backup_list__description'  => 'LC__CMDB__CATG__DESCRIPTION'
        ];
    }
}
