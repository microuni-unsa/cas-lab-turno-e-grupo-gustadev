<?php

/**
 * i-doit
 *
 * DAO: Category list for backup servers
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_backup extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category.
     *
     * @return integer
     * @author Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__BACKUP');
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
     *
     * @throws Exception
     */
    public function modify_row(&$row)
    {
        if ($row['isys_connection__isys_obj__id'] != null) {
            $dao = isys_application::instance()->container->get('cmdb_dao');

            $link = isys_helper_link::create_url([
                C__CMDB__GET__OBJECT     => $row['isys_connection__isys_obj__id'],
                C__CMDB__GET__OBJECTTYPE => $dao->get_objTypeID($row['isys_connection__isys_obj__id']),
                C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_CATEGORY,
                C__CMDB__GET__CATG       => defined_or_default('C__CATG__BACKUP__ASSIGNED_OBJECTS'),
                C__CMDB__GET__TREEMODE   => $_GET['tvMode']
            ]);

            $row['obj__title'] = isys_ajax_handler_quick_info::instance()->get_quick_info(
                $row['isys_connection__isys_obj__id'],
                $dao->get_obj_name_by_id_as_string($row['isys_connection__isys_obj__id']),
                $link
            );
        }
    }

    /**
     * This method returns the fields and translations.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_catg_backup_list__title'        => 'LC__CMDB__CATG__BACKUP__TITLE',
            'obj__title'                          => 'LC__CMDB__CATG__BACKUP__IS_BACKUPEP',
            'isys_backup_type__title'             => 'LC__CMDB__CATG__BACKUP__BACKUP_TYPE',
            'isys_backup_cycle__title'            => 'LC__CMDB__CATG__BACKUP__CYCLE',
            'isys_catg_backup_list__path_to_save' => 'LC__CMDB__CATG__BACKUP__PATH_TO_SAVE',
            'isys_catg_backup_list__description'  => 'LC__CMDB__CAT__COMMENTARY'
        ];
    }
}
