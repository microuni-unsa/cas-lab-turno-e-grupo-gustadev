<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;

class DataNormalizerProviderConfig
{
    protected isys_cmdb_dao_category $dao;

    /**
     * @var Property[]
     */
    protected array $properties = [];

    /**
     * @var int
     */
    protected int $objectId;

    /**
     * @var int|null
     */
    protected ?int $entryId;

    /**
     * @var array
     */
    protected array $categoryData = [];

    /**
     * @var bool
     */
    protected bool $createCategoryEntryInDependency = false;

    /**
     * @var bool
     */
    protected bool $createObjectInDependency = false;

    /**
     * @param Property[] $properties
     * @param int        $objectId
     * @param int|null   $entryId
     */
    public function __construct(isys_cmdb_dao_category $dao, array $properties, int $objectId, ?int $entryId = null, array $categoryData = [], bool $createCategoryEntryInDependency = false, bool $createObjectInDependency = false)
    {
        $this->dao = $dao;
        $this->properties = $properties;
        $this->objectId = $objectId;
        $this->entryId = $entryId;
        $this->createCategoryEntryInDependency = $createCategoryEntryInDependency;
        $this->createObjectInDependency = $createObjectInDependency;
        $this->categoryData = $categoryData;
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
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @return int|null
     */
    public function getEntryId(): ?int
    {
        return $this->entryId;
    }

    /**
     * @return array
     */
    public function getCategoryData(): array
    {
        return $this->categoryData;
    }

    /**
     * @return bool
     */
    public function isCreateCategoryEntryInDependency(): bool
    {
        return $this->createCategoryEntryInDependency;
    }

    /**
     * @return bool
     */
    public function isCreateObjectInDependency(): bool
    {
        return $this->createObjectInDependency;
    }
}
