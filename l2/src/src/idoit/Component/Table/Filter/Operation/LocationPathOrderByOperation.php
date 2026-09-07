<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use idoit\Component\Property\Property;
use isys_cmdb_dao_list_objects;
use isys_smarty_plugin_f_dialog;

class LocationPathOrderByOperation extends PropertyOperation
{
    const MAX_JOINS = 59;

    /**
     * @param $filter
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($filter, $value): bool
    {
        $property = $this->getProperty($filter);

        return $property[Property::C__PROPERTY__DATA][Property::C__PROPERTY__DATA__FIELD] === 'isys_catg_location_list__parentid';
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value): bool
    {
        $items = [];

        $orderDirection = strtolower(trim($_GET['orderByDir'])) === 'asc' ? 'ASC' : 'DESC';
        $objectTypeId = $listDao->convert_sql_int($_GET['objTypeID']);

        $query = \isys_cmdb_dao_category_g_location::build_location_path_query(
            self::MAX_JOINS,
            100,
            " WHERE objMain.isys_obj__isys_obj_type__id = {$objectTypeId} ORDER BY title {$orderDirection};",
            true
        );

        $query = substr_replace($query, 'SELECT objMain.isys_obj__id,', strpos($query, 'SELECT '), \strlen('SELECT '));

        $result = $listDao->retrieve($query);

        while ($row = $result->get_row()) {
            $items[] = (int) $row['isys_obj__id'];
        }

        // @see ID-8442 Add object IDs that do not have an entry in the location category.

        // First we select all objects that HAVE an entry in the location category and use that as a subquery.
        $subQuery = "SELECT isys_catg_location_list__isys_obj__id
            FROM isys_catg_location_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
            WHERE isys_obj__isys_obj_type__id = {$objectTypeId}";

        // We use the subquery to select all object IDs that HAVE NO own location category entry.
        $query = "SELECT isys_obj__id
            FROM isys_obj
            WHERE isys_obj__id NOT IN ({$subQuery})
            AND isys_obj__isys_obj_type__id = {$objectTypeId}
            ORDER BY isys_obj__id ASC;";

        $result = $listDao->retrieve($query);

        // Depending on the sort direction, we put the found object IDs at the beginning or the end of the other IDs.
        while ($row = $result->get_row()) {
            if ($orderDirection === 'ASC') {
                $items[] = (int)$row['isys_obj__id'];
            } else {
                array_unshift($items, (int)$row['isys_obj__id']);
            }
        }

        if (!empty($items)) {
            $ids = implode(',', $items);
            $listDao->set_order_by("FIELD(obj_main.isys_obj__id, {$ids})", '');
            return true;
        }

        return false;
    }
}
