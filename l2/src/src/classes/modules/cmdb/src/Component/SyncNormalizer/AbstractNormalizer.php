<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer;

use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use isys_cmdb_dao_category;

abstract class AbstractNormalizer
{
    /**
     * @var Config
     */
    protected Config $config;

    private static array $allowedProperties = [
        'isys_cmdb_dao_category_g_contact' => ['contact_object'],
    ];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public static function getAllowedProperties(string $class)
    {
        return self::$allowedProperties[$class] ?? [];
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int                    $objectId
     * @param int|null               $entryId
     *
     * @return array
     */
    protected function getCategoryData(isys_cmdb_dao_category $dao, int $objectId, ?int $entryId = null)
    {
        if (!$dao->is_multivalued()) {
            if ($dao instanceof \isys_cmdb_dao_category_g_custom_fields) {
                $data = $dao->get_data_as_array(null, $objectId);
                return !empty($data) ? current($data) : [];
            }
            return $dao->get_data(null, $objectId)->get_row();
        }

        if ($entryId) {
            return $dao->get_data($entryId)->get_row();
        }

        if ($dao instanceof ObjectBrowserReceiver || $dao instanceof ObjectBrowserAssignedEntries) {
            $result = $dao->get_data(null, $objectId);
            $data = [];
            while ($row = $result->get_row()) {
                $data[] = $row;
            }
            return $data;
        }

        return [];
    }
}
