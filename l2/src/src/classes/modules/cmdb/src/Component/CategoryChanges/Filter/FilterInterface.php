<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Filter;

use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use isys_cmdb_dao_category;

interface FilterInterface
{
    /**
     * @param isys_cmdb_dao_category $dao
     *
     * @return bool
     */
    public static function isApplicable(isys_cmdb_dao_category $dao): bool;

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $properties
     * @param DefaultData            $currentData
     * @param DefaultData            $changedData
     *
     * @return array
     */
    public static function filterProperties(isys_cmdb_dao_category $dao, array $properties, DefaultData $currentData, DefaultData $changedData): array;
}
