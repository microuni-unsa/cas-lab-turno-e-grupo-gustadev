<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

/**
 * Class FilterProcessorConditionValue
 */
class FilterProcessorConditionValue extends AbstractFilterProcessorValue implements FilterProcessorValueInterface
{
    public function checkValue()
    {
        $value = strip_tags($this->getValue());
        $valueToCheck = strip_tags($this->getCheckValue());

        if ($this->checkOnNumber($value) && $this->checkOnNumber($valueToCheck)) {
            return (int)$valueToCheck === (int)$value;
        }
        return stripos(strip_tags($this->getCheckValue()), strip_tags($this->getValue())) !== false;
    }

    /**
     * Check if value is a real number
     *
     * @param $value
     *
     * @return bool
     */
    private function checkOnNumber($value)
    {
        return strlen((string)(int)$value) === strlen($value);
    }
}
