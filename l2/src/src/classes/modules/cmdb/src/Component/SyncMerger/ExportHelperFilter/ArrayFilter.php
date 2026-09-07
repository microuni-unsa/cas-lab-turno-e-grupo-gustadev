<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter;

use isys_application;
use isys_cmdb_dao_category;

class ArrayFilter implements ExportHelperFilterInterface
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
        $checkData = array_pop($data);
        return is_array($checkData) && (
            isset($data['ref_id']) ||
            isset($data['id']) ||
            isset($data[C__DATA__VALUE]) ||
            isset($data['title'])
        );
    }

    /**
     * @param array                  $data
     * @param string                 $exportMethod
     * @param isys_cmdb_dao_category $categoryDao
     * @param string                 $propertyKey
     *
     * @return mixed
     * @throws \Exception
     */
    public static function filterValue(array $data, string $exportMethod, isys_cmdb_dao_category $categoryDao, string $propertyKey): mixed
    {
        $returnData = [];
        $language = isys_application::instance()->container->get('language');
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                if (isset($item['ref_id'])) {
                    $returnData[] = $item['ref_id'];
                    continue;
                }

                if (isset($item['id'])) {
                    $returnData[] = $item['id'];
                    continue;
                }

                if (isset($item[C__DATA__VALUE])) {
                    $returnData[] = $item[C__DATA__VALUE];
                    continue;
                }

                if (isset($item['title'])) {
                    $returnData[] = $language->get($item['title']);
                    continue;
                }
            }
        }
        return $returnData;
    }
}
