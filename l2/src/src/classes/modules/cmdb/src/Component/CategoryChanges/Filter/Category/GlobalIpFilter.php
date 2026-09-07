<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Filter\Category;

use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Filter\FilterInterface;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_ip;

class GlobalIpFilter implements FilterInterface
{
    private const IPV4_PROPERTIES = [
        'ipv4_address',
        'ipv4_assignment',
        'zone',
    ];

    private const IPV6_PROPERTIES = [
        'ipv6_address',
        'ipv6_scope',
        'ipv6_assignment',
    ];

    /**
     * @param isys_cmdb_dao_category $dao
     *
     * @return bool
     */
    public static function isApplicable(isys_cmdb_dao_category $dao): bool
    {
        return $dao instanceof isys_cmdb_dao_category_g_ip;
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
        $currentDataNetType = $currentData->getData()['net_type'];
        $changedDataNetType = $changedData->getData()['net_type'];

        if ((int)$currentDataNetType === C__CATS_NET_TYPE__IPV6 && (int)$changedDataNetType === C__CATS_NET_TYPE__IPV6) {
            $propertiesToRemove = self::IPV4_PROPERTIES;
        } else {
            $propertiesToRemove = self::IPV6_PROPERTIES;
        }
        $properties = array_filter($properties, fn ($key) => !in_array($key, $propertiesToRemove), ARRAY_FILTER_USE_KEY);

        return $properties;
    }
}
