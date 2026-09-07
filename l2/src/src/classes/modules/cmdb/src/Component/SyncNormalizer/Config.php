<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer;

use isys_cmdb_dao_category;

class Config
{
    /**
     * @var isys_cmdb_dao_category
     */
    protected isys_cmdb_dao_category $dao;

    /**
     * @var bool
     */
    protected bool $createEntriesInAssignedObjects = true;

    /**
     * @var bool
     */
    protected bool $createObjects = true;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $data
     */
    public function __construct(isys_cmdb_dao_category $dao, array $data, $createEntriesInAssignedObjects = true, $createObjects = true)
    {
        $this->dao = $dao;
        $this->data = $data;
        $this->createEntriesInAssignedObjects = $createEntriesInAssignedObjects;
        $this->createObjects = $createObjects;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function getDao(): isys_cmdb_dao_category
    {
        return $this->dao;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isCreateEntriesInAssignedObjects(): bool
    {
        return $this->createEntriesInAssignedObjects;
    }

    /**
     * @return bool
     */
    public function isCreateObjects(): bool
    {
        return $this->createObjects;
    }
}
