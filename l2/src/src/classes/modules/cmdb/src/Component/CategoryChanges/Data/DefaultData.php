<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

use idoit\Module\Cmdb\Component\SyncMerger\Config;
use isys_cmdb_dao_category;

/**
 * Class DefaultData
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class DefaultData extends AbstractData
{
    /**
     * @var isys_cmdb_dao_category
     */
    private $dao;

    /**
     * @return isys_cmdb_dao_category
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     *
     * @return DefaultData
     */
    public function setDao(isys_cmdb_dao_category $dao)
    {
        $this->dao = $dao;
        return $this;
    }

    /**
     * @param int $objectId
     * @param int $entryId
     *
     * @return DefaultData
     */
    public function loadData(int $objectId, int $entryId)
    {
        $data = $this->getDao()->getCurrentEntry($entryId, $objectId);
        $reformattedData = [];

        foreach ($data[Config::CONFIG_PROPERTIES] as $propertyKey => $propertyValue) {
            $reformattedData[$propertyKey] = $propertyValue[C__DATA__VALUE];
        }

        return $this->setData($reformattedData);
    }

    /**
     * @param mixed $data
     *
     * @return static
     */
    public static function factory(isys_cmdb_dao_category $dao, $data)
    {
        $object = new static();
        $object->setData($data);
        $object->setDao($dao);

        return $object;
    }
}
