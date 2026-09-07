<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;

class SinglePropertyData
{
    /**
     * @var string
     */
    private $logbookChangeKey;

    /**
     * @var string
     */
    private $propertyKey;

    /**
     * @var isys_cmdb_dao_category
     */
    private $dao;

    /**
     * @var Property
     */
    private $property;

    /**
     * @param string                 $logbookChangeKey
     * @param isys_cmdb_dao_category $dao
     * @param Property               $property
     */
    public function __construct(string $logbookChangeKey, string $propertyKey, isys_cmdb_dao_category $dao, Property $property)
    {
        $this->logbookChangeKey = $logbookChangeKey;
        $this->propertyKey = $propertyKey;
        $this->dao = $dao;
        $this->property = $property;
    }
    
    /**
     * @return string
     */
    public function getLogbookChangeKey(): string
    {
        return $this->logbookChangeKey;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function getDao(): isys_cmdb_dao_category
    {
        return $this->dao;
    }

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getPropertyKey(): string
    {
        return $this->propertyKey;
    }
}
