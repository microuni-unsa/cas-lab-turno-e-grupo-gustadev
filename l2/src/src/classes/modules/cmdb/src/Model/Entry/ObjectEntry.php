<?php

namespace idoit\Module\Cmdb\Model\Entry;

use ArrayObject;
use idoit\Module\Cmdb\Interfaces\EntryInterface;
use isys_cmdb_dao_category;

class ObjectEntry implements EntryInterface
{
    /**
     * @var int
     */
    private $objectid;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $sysid;

    /**
     * @var string
     */
    private $objecttype;

    /**
     * @var isys_cmdb_dao_category|null
     */
    private $dao = null;

    /**
     * @var ArrayObject|null
     */
    private $data = null;

    /**
     * @param int $objectId
     * @param string $title
     * @param string $sysId
     * @param string $objectType
     * @param isys_cmdb_dao_category|null $dao
     * @param ArrayObject|null $data
     */
    public function __construct(int $objectId, string $title, string $sysId, string $objectType, ?ArrayObject $data, ?isys_cmdb_dao_category $dao)
    {
        $this->objectid = $objectId;
        $this->title = $title;
        $this->sysid = $sysId;
        $this->dao = $dao;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getObjectid(): int
    {
        return $this->objectid;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSysid(): string
    {
        return $this->sysid;
    }

    /**
     * @return isys_cmdb_dao_category|null
     */
    public function getDao(): ?isys_cmdb_dao_category
    {
        return $this->dao;
    }

    /**
     * @return ArrayObject|null
     */
    public function getData(): ?ArrayObject
    {
        return $this->data;
    }

    /**
     * @param int $objectId
     * @param string $title
     * @param string $sysid
     * @param string $objectType
     * @param array $data
     * @param isys_cmdb_dao_category|null $dao
     *
     * @return ObjectEntry
     */
    public static function factory(int $objectId, string $title, string $sysid, string $objectType, array $data, ?isys_cmdb_dao_category $dao = null): ObjectEntry
    {
        $arrObject = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
        return new self($objectId, $title, $sysid, $objectType, $arrObject, $dao);
    }
}
