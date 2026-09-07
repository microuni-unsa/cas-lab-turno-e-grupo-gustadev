<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Filter;

use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Filter\Category\GlobalIpFilter;
use isys_cmdb_dao_category;

class PropertyFilter implements FilterInterface
{
    /**
     * @return FilterInterface[]
     */
    private static function getFilters(): array
    {
        return [
            GlobalIpFilter::class
        ];
    }

    /**
     * @param isys_cmdb_dao_category $dao
     *
     * @return bool
     */
    public static function isApplicable(isys_cmdb_dao_category $dao): bool
    {
        foreach (self::getFilters() as $filter) {
            if ($filter::isApplicable($dao)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $properties
     * @param DefaultData            $currentData
     * @param DefaultData            $changedData
     *
     * @return array
     */
    public static function filterProperties(isys_cmdb_dao_category $dao, array $properties, DefaultData $currentData, DefaultData $changedData): array
    {
        foreach (self::getFilters() as $filter) {
            if ($filter::isApplicable($dao)) {
                return $filter::filterProperties($dao, $properties, $currentData, $changedData);
            }
        }
        return $properties;
    }
}
