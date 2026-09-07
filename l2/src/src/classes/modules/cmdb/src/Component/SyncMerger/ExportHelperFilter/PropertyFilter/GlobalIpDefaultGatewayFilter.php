<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\PropertyFilter;

use idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\ExportHelperFilterInterface;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_ip;

class GlobalIpDefaultGatewayFilter implements ExportHelperFilterInterface
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
        $id = current($data);
        return $categoryDao instanceof isys_cmdb_dao_category_g_ip && $propertyKey === 'use_standard_gateway' && is_numeric($id) && $id > 0;
    }

    /**
     * @param array                  $data
     * @param string                 $exportMethod
     * @param isys_cmdb_dao_category $categoryDao
     * @param string                 $propertyKey
     *
     * @return mixed
     * @throws \isys_exception_database
     */
    public static function filterValue(array $data, string $exportMethod, isys_cmdb_dao_category $categoryDao, string $propertyKey): mixed
    {
        $id = current($data);
        $query = 'SELECT 1 FROM isys_cats_net_list WHERE isys_cats_net_list__isys_catg_ip_list__id = ' . $categoryDao->convert_sql_id($id);
        return (int)$categoryDao->retrieve($query)->count();
    }
}
