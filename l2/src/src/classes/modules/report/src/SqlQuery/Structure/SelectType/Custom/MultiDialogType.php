<?php

namespace idoit\Module\Report\SqlQuery\Structure\SelectType\Custom;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectType\AbstractSelectType;
use idoit\Module\Report\SqlQuery\Structure\SelectType\TypeInterface;

class MultiDialogType extends AbstractSelectType implements TypeInterface
{
    public function isApplicable(Property $property): bool
    {
        $isMultiSelect = $property->getInfo()->getType() === C__PROPERTY__INFO__TYPE__MULTISELECT;
        $sourceTable = $property->getData()->getSourceTable();
        return $sourceTable === 'isys_catg_custom_fields_list' && $isMultiSelect;
    }

    /**
     * @return string
     */
    public function buildSelect(): string
    {
        $subSelect = clone $this->getProperty()->getData()->getSelect();
        $subSelect->getSelectGroupBy()->setGroupConcatSelection("GROUP_CONCAT({$subSelect->getSelection()})");

        foreach ($this->getConditions() as $condition) {
            $subSelect->getSelectCondition()->addCondition($condition);
        }

        return "$subSelect AS {$this->getAlias()}";
    }
}
