<?php
namespace idoit\Module\Report\SqlQuery\Condition\Filter;

class FilterProcessorDynamic extends AbstractFilterProcessor implements FilterProcessorInterface
{
    public function process()
    {
        $field = $this->getField();

        if (strpos($field, '.')) {
            $fieldArr = explode('.', $this->getField());
            $field = $fieldArr[1];
        }

        $row[$field] = $this->getId();

        $dao = $this->getDao();
        $method = $this->getMethod();
        $return = [];

        /**
         * @var AbstractFilterProcessorValue $processorConditionValue
         */
        $processorConditionValue = $this->getProcessorConditionValue();
        $callBackValue = $dao->$method($row);
        $processorConditionValue->setCheckValue($callBackValue);

        if (is_numeric($this->getId()) && strlen((string)$this->getId()) === strlen((string)(int)$this->getId())) {
            $conditionValue = $dao->convert_sql_int($this->getId());
        } else {
            $conditionValue = $dao->convert_sql_text($this->getId());
        }

        if ($processorConditionValue->checkValue()) {
            $this->addProcessedValueIdsPositive($conditionValue);
        } else {
            $this->addProcessedValueIdsNegative($conditionValue);
        }
    }
}
