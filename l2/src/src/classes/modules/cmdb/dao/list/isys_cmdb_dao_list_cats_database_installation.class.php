<?php

/**
 * i-doit
 *
 * DAO: list for cluster members
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_database_installation extends isys_cmdb_dao_list_cats_application_assigned_obj
{
    /**
     * Return constant of category.
     *
     * @return  int|null
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__DATABASE_INSTALLATION');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * @param $row
     * @return void
     * @throws isys_exception_database
     */
    public function modify_row(&$row)
    {
        parent::modify_row($row);

        $assignedDatabases = $this->getAssignedDatabases((int)$row['isys_catg_application_list__id']);
        $row['assigned_databases'] = isys_tenantsettings::get('gui.empty_value', '-');

        if (count($assignedDatabases)) {
            $list = [];
            $quickInfo = isys_ajax_handler_quick_info::instance();
            $groupAsCommaList = $this->getConfiguration()->getGroupingType() === isys_cmdb_dao_category_property_ng::C__GROUPING__COMMA;

            foreach ($assignedDatabases as $database) {
                $urlParameters = [
                    C__CMDB__GET__VIEWMODE   => defined_or_default('C__CMDB__VIEW__CATEGORY'),
                    C__CMDB__GET__TREEMODE   => defined_or_default('C__CMDB__VIEW__TREE_OBJECT'),
                    C__CMDB__GET__CATG       => defined_or_default('C__CATG__DATABASE_SA'),
                    C__CMDB__GET__OBJECT     => $database['objectId'],
                    C__CMDB__GET__CATLEVEL   => $database['id'],
                ];

                $list[] = $quickInfo->get_link('', $database['title'], isys_helper_link::create_url($urlParameters));
            }

            $row['assigned_databases'] = $groupAsCommaList
                ? implode(', ', $list)
                : '<ul><li>' . implode('</li><li>', $list) . '</li></ul>';
        }

        $row['version_sort'] = preg_replace_callback(
            '/\d+/',
            fn ($matches) => str_pad($matches[0], 6, '0', STR_PAD_LEFT),
            $row['assigned_version']
        );
    }

    /**
     * Get assigned databases by application assignment id
     *
     * @param int $applicationId
     * @return array
     * @throws isys_exception_database
     */
    private function getAssignedDatabases(int $applicationId): array
    {
        $data = [];

        $query = "SELECT isys_catg_database_sa_list__id as id, isys_obj__id as objectId, isys_catg_database_sa_list__title as title
            FROM isys_catg_database_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_database_list__isys_obj__id
            LEFT JOIN isys_catg_database_sa_list ON isys_catg_database_sa_list__isys_catg_application_list__id = isys_catg_database_list__isys_catg_application_list__id
            WHERE isys_catg_database_list__isys_catg_application_list__id = {$applicationId}";

        $result = $this->retrieve($query);

        while ($row = $result->get_row()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'rel_obj_title'                       => 'LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE',
            'main_obj_title'                      => 'LC__UNIVERSAL__INSTALLED_ON',
            'assigned_license'                    => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
            'assigned_version'                    => 'LC__CATG__VERSION_TITLE_AND_PATCHLEVEL',
            'isys_cats_app_variant_list__variant' => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
            'assigned_databases'                  => 'LC__CATS__DATABASE_INSTALLATION__ASSIGNED_DATABASES',
            'version_sort'                        => false
        ];
    }

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'assigned_version') {
            $p_column = 'version_sort';
        }

        return $p_column . " " . $p_direction;
    }
}
