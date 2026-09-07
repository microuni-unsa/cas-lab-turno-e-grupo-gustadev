<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;

/**
 * Condition type for multiselect
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Multiselect extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        $uiParams = $this->getProperty()->getUi()->getParams();

        // @see ID-7549 otherwise the condition with checkboxes will not work properly
        if (isset($uiParams['popup']) && $uiParams['popup'] === 'checkboxes') {
            return false;
        }

        return $this->getProperty()->getInfo()->getType() == Property::C__PROPERTY__INFO__TYPE__MULTISELECT;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    public function format()
    {
        $database = \isys_application::instance()->container->get('database');

        $conditionQueryObject = clone $this->getProperty()->getData()->getSelect();
        $sourceTable = $this->getProperty()->getData()->getSourceTable();
        $conditionData = $this->getConditionData();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $parentConditionField = $this->getConditionField();

        $conditionQuery = $conditionQueryObject->getSelectQuery();
        $conditionObjectField = $conditionQueryObject->getSelectFieldObjectID();
        $conditionField = $sourceTable . '__id';
        $isCustomCategory = $conditionQueryObject->getSelectTable() === 'isys_catg_custom_fields_list';

        if ($isCustomCategory) {
            $conditionField = 'isys_dialog_plus_custom__id';
        }

        preg_match('/(?<=SELECT ).[a-zA-Z0-9._]*/', $conditionQuery, $match);

        if (empty($match)) {
            return null;
        }

        $comparison = $conditionComparison === '='
            ? ($conditionValue === '-1' ? ' NOT IN ' : ' IN ')
            : ($conditionValue === '-1' ? ' IN ' : ' NOT IN ');
        $conditionQueryPattern = "{$parentConditionField} {$comparison} (%s)";

        $replaceField = $match[0];
        $conditionFieldAlias = substr($replaceField, 0, strpos($replaceField, '.'));
        $conditionQuery = str_replace($replaceField, $conditionObjectField, $conditionQuery);

        $conditionQueryObject->setSelectQuery($conditionQuery);

        if ($conditionValue === '-1') {
            $conditions[] = ' AND ' . ($conditionFieldAlias ? $conditionFieldAlias . '.' : '') . $conditionField . ' IS NOT NULL ';
        } else {
            if (is_array($conditionValue)) {
                $conditions[] = ' AND ' . ($conditionFieldAlias ? $conditionFieldAlias . '.' : '') . $conditionField . ' ' . $comparison . ' (' . implode(',', array_map([$database, 'escape_string'], $conditionValue)) .')';
            } else {
                $conditions[] = ' AND ' . ($conditionFieldAlias ? $conditionFieldAlias . '.' : '') . $conditionField . ' = \'' . $database->escape_string($conditionValue) .
                    '\'';
            }

            // @see  ID-8317  Add this additional condition in order to verify that only affected fields will be selected.
            if ($isCustomCategory && $this->getProperty()->getUi()->getId()) {
                [$rootAlias] = explode('.', $conditionObjectField);

                $conditions[] = " AND {$rootAlias}.isys_catg_custom_fields_list__field_key = '{$database->escape_string($this->getProperty()->getUi()->getId())}'";
            }
        }

        $conditionQueryObject->setSelectCondition(SelectCondition::factory($conditions));
        $conditionQueryObject->setSelectGroupBy(null);

        return ' (' . sprintf($conditionQueryPattern, $conditionQueryObject) . ') ';
    }
}
