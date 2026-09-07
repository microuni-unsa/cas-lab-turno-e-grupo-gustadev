<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges;

use idoit\Module\Cmdb\Component\CategoryChanges\Builder\ChangesBuilder;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesDataCollection;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DataProvider;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\PropertyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_cmdb_dao_category;

/**
 * Class Changes
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges
 */
class Changes
{
    /**
     * Constant for data_id
     */
    const ENTRY_DATA_ID = 'data_id';

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var isys_cmdb_dao_category
     */
    private $dao;

    /**
     * @var ChangesBuilder
     */
    private $changesBuilder;

    /**
     * Changes constructor.
     *
     * @param isys_cmdb_dao_category $dao
     */
    public function __construct(isys_cmdb_dao_category $dao)
    {
        $this->dao = $dao;
        $this->changesBuilder = new ChangesBuilder();
    }

    /**
     * @return DataProvider
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * @param DataProvider $dataProvider
     *
     * @return Changes
     */
    public function setDataProvider(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        return $this;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int                    $objectId Holds the object id
     * @param int|null               $entryId Holds the category entry id
     * @param array                  $currentData Holds the current data of the entry
     * @param array                  $changedData Holds the chanced data for the entry
     * @param array                  $smartyData Holds all Smarty Post data
     * @param array                  $requestData Holds the post data
     *
     * @return Changes|static
     */
    public static function instance(isys_cmdb_dao_category $dao, int $objectId, ?int $entryId = null, array $currentData = [], array $changedData = [], array $smartyData = [], array $requestData = [])
    {
        $currentDataObject = DefaultData::factory($dao, $currentData);
        if (empty($currentData) && $entryId !== null && $entryId > 0) {
            $currentDataObject->loadData($objectId, $entryId);
        }

        // @see ID-10676 Always return new instances to prevent caching issues.
        return (new static($dao))
            ->setDataProvider(
                DataProvider::factory(
                    $objectId,
                    $entryId,
                    $currentDataObject,
                    DefaultData::factory($dao, $changedData),
                    PropertyData::factory($dao),
                    RequestData::factory($requestData),
                    SmartyData::factory($smartyData)
                )
            );
    }

    /**
     * Process changes for current object, all assigned objects.
     */
    public function processChanges()
    {
        if ($this->changesBuilder->process($this->dataProvider)) {
            $this->changesBuilder
                ->getChangesCurrent()
                ->reformatChangesDataCollection();
            $this->changesBuilder
                ->getChangesTo()
                ->reformatChangesDataCollection();
            $this->changesBuilder
                ->getChangesFrom()
                ->reformatChangesDataCollection();
        }
    }

    /**
     * @return ChangesDataCollection|null
     */
    public function getCurrentChangesCollection()
    {
        return $this->changesBuilder->getChangesCurrent();
    }

    /**
     * @return ChangesDataCollection|null
     */
    public function getFromChangesCollection()
    {
        return $this->changesBuilder->getChangesFrom();
    }

    /**
     * @return ChangesDataCollection|null
     */
    public function getToChangesCollection()
    {
        return $this->changesBuilder->getChangesTo();
    }

    /**
     * @return array
     */
    public function getCurrentReformatedChanges()
    {
        return $this->changesBuilder->getChangesCurrent()->getReformatedData();
    }

    /**
     * @return array
     */
    public function getFromReformatedChanges()
    {
        return $this->changesBuilder->getChangesFrom()->getReformatedData();
    }

    /**
     * @return array
     */
    public function getToReformatedChanges()
    {
        return $this->changesBuilder->getChangesTo()->getReformatedData();
    }

    /**
     * @return ChangesBuilder
     */
    public function getChangesBuilder()
    {
        return $this->changesBuilder;
    }

    /**
     * @return bool
     */
    public function hasAnyChanges()
    {
        return $this->changesBuilder->getChangesFrom()->hasData() ||
            $this->changesBuilder->getChangesTo()->hasData() ||
            $this->changesBuilder->getChangesCurrent()->hasData();
    }

    /**
     * @deprecated
     * @return void
     */
    public function resetInstance(): void
    {
        return;
    }
}
