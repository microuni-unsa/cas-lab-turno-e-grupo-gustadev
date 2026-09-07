<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use isys_cmdb_dao_list_objects;

/**
 * Sets specific 'having' conditions for cable connections.
 *
 * @package idoit\Component\Table\Filter\Operation
 * @see ID-11858 Implement specific connection filter
 */
class ConnectionFilterOperation extends PropertyOperation
{
    /**
     * @param string $filter
     * @param string $value
     * @return bool
     */
    public function isApplicable($filter, $value): bool
    {
        return $filter === 'isys_cmdb_dao_category_g_cable__connection';
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     * @return bool
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value): bool
    {
        $items = [];

        // Fetch cable objects, based on connected object / connector names.
        $query = "SELECT isys_cable_connection__isys_obj__id AS id
            FROM isys_catg_connector_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
            INNER JOIN isys_cable_connection ON isys_cable_connection__id = isys_catg_connector_list__isys_cable_connection__id
            WHERE isys_obj__title LIKE {$value}
            OR isys_catg_connector_list__title LIKE {$value}";

        foreach ($listDao->get_database_component()->retrieveArrayFromResource($listDao->get_database_component()->query($query)) as $row) {
            $items[] = (int) $row['id'];
        }

        if (!empty($items)) {
            $ids = implode(',', array_unique($items));
            $listDao->add_additional_conditions(" AND obj_main.isys_obj__id IN ({$ids})");

            return true;
        }

        return false;
    }
}
