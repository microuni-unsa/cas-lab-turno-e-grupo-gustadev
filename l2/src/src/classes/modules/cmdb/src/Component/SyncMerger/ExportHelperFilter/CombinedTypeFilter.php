<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter;

use isys_cmdb_dao_category;

/**
 * @see ID-10853 Return concatenated 'ID + type' value for specific categories and attributes.
 */
class CombinedTypeFilter implements ExportHelperFilterInterface
{
    private const ALLOWED_TYPES = [
        'C__CATG__HBA',
        'C__CATG__NETWORK_INTERFACE',
        'C__CATG__POWER_CONSUMER',
    ];

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
        return (isset($data['type']) && in_array($data['type'], self::ALLOWED_TYPES, true)
            && isset($data['id']) && $data['id']);
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
        return $data['id'] . '_' . $data['type'];
    }
}
