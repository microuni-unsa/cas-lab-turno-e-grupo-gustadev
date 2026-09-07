<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter;

use idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\PropertyFilter\PropertyFilter;
use isys_cmdb_dao_category;

class ExportHelperFilter implements ExportHelperFilterInterface
{
    /**
     * The order is important
     *
     * @return ExportHelperFilterInterface[]
     */
    private static function getValueFilters(): array
    {
        return [
            PropertyFilter::class,
            CombinedTypeFilter::class,
            ReferenceIdFilter::class,
            ReferenceTitleFilter::class,
            IdFilter::class,
            ValueFilter::class,
            TitleFilter::class,
            ArrayFilter::class,
            EmptyObjectFilter::class
        ];
    }

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
        foreach (self::getValueFilters() as $filterClass) {
            if ($filterClass::isApplicable($data, $exportMethod, $categoryDao, $propertyKey, $propertyKey)) {
                return true;
            }
        }
        return false;
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
        foreach (self::getValueFilters() as $filterClass) {
            if ($filterClass::isApplicable($data, $exportMethod, $categoryDao, $propertyKey)) {
                return $filterClass::filterValue($data, $exportMethod, $categoryDao, $propertyKey);
            }
        }
        return null;
    }
}
