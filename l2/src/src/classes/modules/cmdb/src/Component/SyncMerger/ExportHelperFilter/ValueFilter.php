<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter;

use isys_cmdb_dao_category;

class ValueFilter implements ExportHelperFilterInterface
{
    /**
     * @param array                  $data
     * @param string                 $exportMethod
     * @param isys_cmdb_dao_category $categoryDao
     * @param string                 $propertyKey
     *
     * @return bool
     */
    public static function isApplicable(array $data, string $exportMethod, isys_cmdb_dao_category $categoryDao, string $propertyKey): bool
    {
        return isset($data[C__DATA__VALUE]);
    }

    /**
     * @param array                  $data
     * @param string                 $exportMethod
     * @param isys_cmdb_dao_category $categoryDao
     * @param string                 $propertyKey
     *
     * @return mixed
     */
    public static function filterValue(array $data, string $exportMethod, isys_cmdb_dao_category $categoryDao, string $propertyKey): mixed
    {
        return $data[C__DATA__VALUE];
    }
}
