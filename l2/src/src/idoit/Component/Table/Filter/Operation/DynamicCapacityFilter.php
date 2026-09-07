<?php
/**
 * @package     i-doit
 * @subpackage
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use isys_cmdb_dao_list_objects;

/**
 * Sets additional having conditions with like
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class DynamicCapacityFilter extends PropertyOperation
{
    /**
     * Checks if the condition is met
     *
     * @param $filter
     *
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($filter, $value): bool
    {
        $property = $this->getProperty($filter);

        return isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__CONDITION]);
    }

    /**
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     * @throws \Exception
     */
    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value): bool
    {
        $property = $this->getProperty($name);
        $conditionGenerator = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__CONDITION];

        if (!is_callable($conditionGenerator)) {
            throw new \Exception('Condition is not a function.');
        }

        return $conditionGenerator($dao, $name, $value);
    }

    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value): bool
    {
        return false;
    }
}
