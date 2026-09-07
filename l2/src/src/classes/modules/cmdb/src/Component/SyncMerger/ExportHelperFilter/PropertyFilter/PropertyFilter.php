<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\PropertyFilter;

use idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\ExportHelperFilterInterface;
use isys_cmdb_dao_category;

class PropertyFilter implements ExportHelperFilterInterface
{
    /**
     * @return ExportHelperFilterInterface[]
     */
    private static function getPropertyFilters(): array
    {
        return [
            GlobalIpDefaultGatewayFilter::class,
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
        foreach (self::getPropertyFilters() as $filter) {
            if ($filter::isApplicable($data, $exportMethod, $categoryDao, $propertyKey)) {
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
        foreach (self::getPropertyFilters() as $filter) {
            if ($filter::isApplicable($data, $exportMethod, $categoryDao, $propertyKey)) {
                return $filter::filterValue($data, $exportMethod, $categoryDao, $propertyKey);
            }
        }
        return null;
    }
}
