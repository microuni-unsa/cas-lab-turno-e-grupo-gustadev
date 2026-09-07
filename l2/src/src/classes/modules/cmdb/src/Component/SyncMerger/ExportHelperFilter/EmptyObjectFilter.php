<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter;

use isys_cmdb_dao_category;

class EmptyObjectFilter implements ExportHelperFilterInterface
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
        // @see ID-11830 This filter will return 'null' instead of an empty array, preventing SQL errors.
        return $exportMethod === 'object' && count($data) === 0;
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
        return null;
    }
}
