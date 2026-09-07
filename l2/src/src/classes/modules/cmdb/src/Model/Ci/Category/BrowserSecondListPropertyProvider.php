<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

use isys_popup_browser_object_ng;

class BrowserSecondListPropertyProvider
{

    public function __construct(
        /**
         * @var SecondListPropertyInterface[] $properties
         */
        private iterable $properties = []
    ) {
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return bool
     */
    public function isApplicable(string $className, string $method): bool
    {
        foreach ($this->properties as $property) {
            if ($property->isApplicable($className, $method)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int    $context
     * @param string $className
     * @param string $method
     *
     * @return mixed
     */
    public function getDataByContext(int $context, string $className, string $method): mixed
    {
        foreach ($this->properties as $property) {
            if ($property->isApplicable($className, $method)) {
                return match($context) {
                    isys_popup_browser_object_ng::C__CALL_CONTEXT__PRESELECTION => $property::preselectionData(),
                    isys_popup_browser_object_ng::C__CALL_CONTEXT__PREPARATION => $property::preparationData(),
                    isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST => $property::requestData(),
                };
            }
        }
        return null;
    }
}
