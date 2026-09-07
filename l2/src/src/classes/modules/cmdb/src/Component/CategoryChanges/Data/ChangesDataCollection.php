<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

use isys_cmdb_dao_category;

/**
 * Class ChangesDataCollection
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class ChangesDataCollection extends AbstractChangesDataCollection
{
    /**
     * @var isys_cmdb_dao_category|null
     */
    protected $dao;

    /**
     * @var int|null
     */
    protected $objectId = null;

    /**
     * @var bool
     */
    protected $multipleObjects = false;

    /**
     * @return bool
     */
    public function isMultipleObjects(): bool
    {
        return $this->multipleObjects;
    }

    /**
     * @param bool $multipleObjects
     *
     * @return ChangesDataCollection
     */
    public function setMultipleObjects(bool $multipleObjects)
    {
        $this->multipleObjects = $multipleObjects;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int|null $objectId
     *
     * @return ChangesDataCollection
     */
    public function setObjectId(?int $objectId = null)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return isys_cmdb_dao_category|null
     */
    public function getDao(): ?isys_cmdb_dao_category
    {
        return $this->dao;
    }

    /**
     * @param isys_cmdb_dao_category|null $dao
     *
     * @return ChangesDataCollection
     */
    public function setDao(?isys_cmdb_dao_category $dao)
    {
        $this->dao = $dao;
        return $this;
    }
}
