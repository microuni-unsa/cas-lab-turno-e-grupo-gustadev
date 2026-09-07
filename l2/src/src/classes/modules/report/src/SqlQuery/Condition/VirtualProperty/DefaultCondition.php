<?php
namespace idoit\Module\Report\SqlQuery\Condition\VirtualProperty;

use idoit\Module\Report\SqlQuery\Condition\ConditionType;
use idoit\Module\Report\SqlQuery\Condition\ConditionTypeInterface;

class DefaultCondition extends ConditionType implements ConditionTypeInterface
{
    /**
     * @return bool
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function format()
    {
        return '';
    }
}
