<?php

namespace idoit\Module\Report\SqlQuery\Condition\PropertyType;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;

/**
 * @package     i-doit
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ObjectBrowser extends ConditionType implements ConditionTypeInterface
{
    private function getUnsupportedOperators()
    {
        return [
            'under_location'
        ];
    }

    /**
     * @return bool
     */
    public function isApplicable()
    {
        $property = $this->getProperty();
        $comparison = $this->getConditionComparison();

        return ($property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) &&
            !in_array($comparison, $this->getUnsupportedOperators());
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        $conditionData = $this->getConditionData();
        $conditionField = $this->getConditionField();
        $conditionComparison = $this->getConditionComparison();
        $conditionValue = $this->getConditionValue();
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $condition = $conditionField . ' ' . $conditionComparison . ' ' . $dao->convert_sql_text($conditionValue) . ' ';

        $uiParams = $this->getProperty()->getUi()->getParams();
        $isMultiselection = isset($uiParams[\isys_popup_browser_object_ng::C__MULTISELECTION]) && $uiParams[\isys_popup_browser_object_ng::C__MULTISELECTION];

        // @see ID-11962 Check if it's a multiselection browser and the value contains something like '1,2,3'.
        if ($isMultiselection && str_contains($conditionValue, ',')) {
            $conditionValue = explode(',', $conditionValue);
        }

        if (is_array($conditionValue)) {
            if ($conditionComparison === '=') {
                $conditionComparison = ' IN ';
            } else {
                $conditionComparison = ' NOT IN ';
            }

            $condition = sprintf("{$conditionField} {$conditionComparison} (%s)", implode(',', array_map([$dao, 'convert_sql_text'], $conditionValue)));
        }

        if (str_contains($conditionComparison, 'PLACEHOLDER.')) {
            return '(' . $condition . ')';
        }

        if ($conditionComparison === 'IS NULL' || $conditionComparison === 'IS NOT NULL') {
            $condition = '';
        }

        if (empty($condition) || (int)$conditionValue === 0 || (int)$conditionValue === -1) {
            if ($conditionComparison === '=') {
                $conditionComparison = 'IS NULL';
                $condition .= ' OR ';
            }
            if ($conditionComparison === '!=') {
                $conditionComparison = 'IS NOT NULL';
                $condition .= ' AND ';
            }

            $condition .= $conditionField . ' ' . $conditionComparison;
        }

        return '(' . $condition . ')';
    }
}
