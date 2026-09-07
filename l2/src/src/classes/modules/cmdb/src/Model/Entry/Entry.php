<?php
namespace idoit\Module\Cmdb\Model\Entry;

use ArrayObject;
use idoit\Module\Cmdb\Interfaces\EntryInterface;
use isys_cmdb_dao_category;

class Entry implements EntryInterface
{
    /**
     * @var int
     */
    private $entryid;

    /**
     * @var int
     */
    private $objectid;

    /**
     * @var string
     */
    private $title;

    /**
     * @var isys_cmdb_dao_category|null
     */
    private $dao = null;

    /**
     * @var ArrayObject|null
     */
    private $data;

    /**
     * @param int $entryId
     * @param int $objectId
     * @param ArrayObject|null $data
     * @param isys_cmdb_dao_category|null $dao
     *
     */
    public function __construct(int $entryId, int $objectId, string $title, ?ArrayObject $data, ?isys_cmdb_dao_category $dao)
    {
        $this->entryid = $entryId;
        $this->objectid = $objectId;
        $this->title = $title;
        $this->dao = $dao;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getEntryid(): int
    {
        return $this->entryid;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getObjectid(): int
    {
        return $this->objectid;
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
     * @param int $entryId
     * @param int $objectId
     * @param string $title
     * @param array $data
     * @param isys_cmdb_dao_category|null $dao
     *
     * @return Entry
     */
    public static function factory(int $entryId, int $objectId, string $title, array $data, ?isys_cmdb_dao_category $dao = null): Entry
    {
        $arrObject = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
        return new self($entryId, $objectId, $title, $arrObject, $dao);
    }
}
