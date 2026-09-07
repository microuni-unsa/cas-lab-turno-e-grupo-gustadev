<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class ObjectTypeExcludeFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class ObjectTypeExcludeFilter extends Filter
{
    /**
     * Method for retrieving a excluding object type query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition(): string
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            return ' AND isys_obj__isys_obj_type__id ' . $this->dao->prepare_in_condition($this->parameter, true) . ' ';
        }

        return '';
    }
}
