<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

/**
 * Class DataProvider
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class DataProvider
{
    /**
     * @var DataProvider
     */
    private static $instance;

    /**
     * @var DefaultData
     */
    private $currentData;

    /**
     * @var DefaultData
     */
    private $changedData;

    /**
     * @var PropertyData
     */
    private $propertyData;

    /**
     * @var RequestData
     */
    private $requestData;

    /**
     * @var SmartyData
     */
    private $smartyData;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var int|null
     */
    private $entryId;

    /**
     * DataProvider constructor.
     *
     * @param int               $objectId
     * @param int|null          $entryId
     * @param DefaultData|null  $currentData
     * @param DefaultData|null  $changedData
     * @param PropertyData|null $propertyData
     * @param RequestData|null  $requestData
     * @param SmartyData|null   $smartyData
     */
    public function __construct(int $objectId, ?int $entryId = null, ?DefaultData $currentData = null, ?DefaultData $changedData = null, ?PropertyData $propertyData = null, ?RequestData $requestData = null, ?SmartyData $smartyData = null)
    {
        $this->objectId = $objectId;
        $this->entryId = $entryId;
        $this->currentData = $currentData;
        $this->changedData = $changedData;
        $this->propertyData = $propertyData;
        $this->requestData = $requestData;
        $this->smartyData = $smartyData;
    }

    /**
     * @param int               $objectId
     * @param int|null          $entryId
     * @param DefaultData|null  $currentData
     * @param DefaultData|null  $changedData
     * @param PropertyData|null $propertyData
     * @param RequestData|null  $requestData
     * @param SmartyData|null   $smartyData
     *
     * @return DataProvider
     */
    public static function factory(int $objectId, ?int $entryId = null, ?DefaultData $currentData = null, ?DefaultData $changedData = null, ?PropertyData $propertyData = null, ?RequestData $requestData = null, ?SmartyData $smartyData = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($objectId);
        }

        self::$instance
            ->setObjectId($objectId)
            ->setEntryId($entryId)
            ->setCurrentData($currentData)
            ->setChangedData($changedData)
            ->setSmartyData($smartyData)
            ->setPropertyData($propertyData)
            ->setRequestData($requestData);

        return self::$instance;
    }

    /**
     * @return DefaultData
     */
    public function getCurrentData()
    {
        return $this->currentData;
    }

    /**
     * @param DefaultData $currentData
     *
     * @return DataProvider
     */
    public function setCurrentData(DefaultData $currentData)
    {
        $this->currentData = $currentData;
        return $this;
    }

    /**
     * @return DefaultData
     */
    public function getChangedData()
    {
        return $this->changedData;
    }

    /**
     * @param DefaultData $defaultData
     *
     * @return DataProvider
     */
    public function setChangedData(DefaultData $defaultData)
    {
        $this->changedData = $defaultData;
        return $this;
    }

    /**
     * @return PropertyData
     */
    public function getPropertyData()
    {
        return $this->propertyData;
    }

    /**
     * @param PropertyData $propertyData
     *
     * @return DataProvider
     */
    public function setPropertyData(PropertyData $propertyData)
    {
        $this->propertyData = $propertyData;
        return $this;
    }

    /**
     * @return RequestData
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @param RequestData $requestData
     *
     * @return DataProvider
     */
    public function setRequestData(RequestData $requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }

    /**
     * @return SmartyData
     */
    public function getSmartyData()
    {
        return $this->smartyData;
    }

    /**
     * @param SmartyData $smartyData
     *
     * @return DataProvider
     */
    public function setSmartyData(SmartyData $smartyData)
    {
        $this->smartyData = $smartyData;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param int|null $entryId
     *
     * @return DataProvider
     */
    public function setEntryId($entryId = null)
    {
        $this->entryId = $entryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return DataProvider
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }
}
