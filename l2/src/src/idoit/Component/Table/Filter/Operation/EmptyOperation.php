<?php
/**
 * @package     i-doit
 * @subpackage
 * @author      Oscar Pohl <opohl@i-doit.com>
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
class EmptyOperation extends Operation
{
    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value): bool
    {
        $name = $dao->get_database_component()->escapeColumnName($name);
        $emptyStrings =['\'\'', '\'-\''];
        if (strpos($name, 'f_dialog')) {
            $emptyStrings[] = '\'<ul><li>-</li></ul>\'';
        }
        $pattern = '(%s IS NULL OR %s IN (%s))';
        $dao->add_additional_having_conditions(sprintf($pattern, $name, $name, implode(', ', $emptyStrings)));

        return true;
    }
}
