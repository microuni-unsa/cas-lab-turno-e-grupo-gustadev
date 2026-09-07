<?php

namespace idoit\Component\Browser\Filter;

use idoit\Component\Browser\Filter;

/**
 * Class SpecificCategoryFilter
 *
 * @package idoit\Component\Browser\Filter
 */
class SpecificCategoryFilter extends Filter
{
    /**
     * Method for retrieving a specific category query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition(): string
    {
        if (is_countable($this->parameter) && count($this->parameter)) {
            /** @noinspection SyntaxError */
            $subSelect = 'SELECT DISTINCT isys_obj_type__id
                FROM isys_obj_type
                WHERE isys_obj_type__isysgui_cats__id ' . $this->dao->prepare_in_condition($this->parameter);

            return ' AND isys_obj__isys_obj_type__id IN (' . $subSelect . ') ';
        }

        return '';
    }
}
