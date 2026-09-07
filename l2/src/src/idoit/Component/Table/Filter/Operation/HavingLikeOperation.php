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

use isys_cmdb_dao_list_objects;

/**
 * Sets additional having conditions with like
 *
 * @package idoit\Component\Table\Filter\Operation
 */
class HavingLikeOperation extends Operation
{
    protected function applyFormatted(isys_cmdb_dao_list_objects $dao, $name, $value): bool
    {
        // @see ID-10511 remove html tags from the field for the having condition
        $name = "REGEXP_REPLACE({$dao->get_database_component()->escapeColumnName($name)}, '<[^>]*>+', '')";

        // @see  ID-8256 Search for encoded umlaute (in 'description' fields for example).
        // @see  ID-8982 Specify second parameter, because this changes in PHP 8.1.
        $htmlValue = htmlentities($value, ENT_COMPAT);

        // Only do the 'OR' search if the values are actually different.
        if ($htmlValue !== $value) {
            // @see  ID-8372  Go sure that the value can contain either HTML or native umlaute (search for both "jörg" and "j&ouml;rg").
            $dao->add_additional_having_conditions("({$name} LIKE {$htmlValue} OR {$name} LIKE {$value})");
            return true;
        }

        $dao->add_additional_having_conditions("{$name} LIKE {$htmlValue}");
        return true;
    }
}
