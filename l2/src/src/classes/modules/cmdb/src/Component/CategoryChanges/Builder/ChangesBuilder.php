<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Builder;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception\ProcessWithArrayDataException;
use idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception\ProcessWithPostDataException;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesDataCollection;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DataProvider;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\PropertyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Filter\PropertyFilter;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\AbstractBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\MultiObjectBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\ObjectBrowserTypeInterface;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_contact;
use isys_cmdb_dao_category_g_custom_fields;
use isys_notify;

/**
 * Class ChangesBuilder
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Builder
 */
class ChangesBuilder extends AbstractChangesBuilder
{
    /**
     * @var array
     */
    private $propertiesAlwaysInLogbook = [];

    /**
     * @var array
     */
    private $registeredObjectBrowserCategories = [];

    /**
     * @param DataProvider $dataProvider
     *
     * @return bool
     */
    public function process(DataProvider $dataProvider)
    {
        $objectId = $dataProvider->getObjectId();
        $propertyProvider = $dataProvider->getPropertyData();
        $currentDataProvider = $dataProvider->getCurrentData();
        $changedDataProvider = $dataProvider->getChangedData();
        $requestDataProvider = $dataProvider->getRequestData();
        $smartyDataProvider = $dataProvider->getSmartyData();

        try {
            if ($currentDataProvider->hasData() && $changedDataProvider->hasData()) {
                $this->processWithArrayData($dataProvider);
            }

            if ($requestDataProvider->hasData() && $smartyDataProvider->hasData()) {
                $this->processWithRequestData($dataProvider);
            }
        } catch (ProcessWithPostDataException $e) {
            isys_notify::error(
                $e->getMessage(),
                ['sticky' => true]
            );
            return false;
        } catch (ProcessWithArrayDataException $e) {
            isys_notify::error(
                $e->getMessage(),
                ['sticky' => true]
            );
            return false;
        }

        return true;
    }

    /**
     * Helper for allowing object browser categories to have multiple changes in logbook
     *
     * @param isys_cmdb_dao_category $dao
     *
     * @return bool
     */
    private function allowedObjectBrowserCategoriesForData(isys_cmdb_dao_category $dao)
    {
        if ($dao instanceof isys_cmdb_dao_category_g_contact) {
            return true;
        }

        return false;
    }

    /**
     * @param DataProvider $dataProvider
     */
    private function processWithArrayData(DataProvider $dataProvider)
    {
        $propertyProvider = $dataProvider->getPropertyData();
        $currentData = $dataProvider->getCurrentData();
        $changedData = $dataProvider->getChangedData();
        $properties = $propertyProvider->getData();
        $dao = $propertyProvider->getDao();
        $dao->set_object_id($dataProvider->getObjectId());
        $dao->set_list_id($dataProvider->getEntryId());
        $this->getChangesCurrent()->setDao($dao);
        $this->getChangesCurrent()->setObjectId($dataProvider->getObjectId());
        $propertiesIndex = get_class($dao);
        $isObjectBrowserCategory = $dao instanceof ObjectBrowserReceiver;
        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $propertiesIndex .= '::' . $dao->get_catg_custom_id();
        }
        $this->prepare($propertiesIndex, $properties[$propertiesIndex]);
        $daoBackwardsProperty = null;
        $defaultChanges = [];

        if (PropertyFilter::isApplicable($dao)) {
            $properties[$propertiesIndex] = PropertyFilter::filterProperties($dao, $properties[$propertiesIndex], $currentData, $changedData);
        }

        foreach ($properties[$propertiesIndex] as $tag => $propertyTypeData) {
            /**
             * @var TypeInterface $propertyTypeData
             */
            $logbookTag = $propertyTypeData[PropertyData::LOGBOOK_TAG];
            $propertyData = $propertyTypeData[PropertyData::PROPERTY_TYPE_DATA];

            if (!$propertyData instanceof TypeInterface) {
                continue;
            }

            $property = $propertyData->getProperty();

            if ($property->getData()->isReadOnly()) {
                continue;
            }

            if ($isObjectBrowserCategory) {
                if (!$propertyData instanceof ObjectBrowserTypeInterface && !$this->allowedObjectBrowserCategoriesForData($dao)) {
                    continue;
                }
            }
            $changes = $propertyData->handleData($tag, $dao, $currentData, $changedData, $this->propertiesAlwaysInLogbook);
            $changesWithDefaults = $propertyData->getChangesWithDefaults($tag, $dao);

            if ($propertyData instanceof MultiObjectBrowserType
                && $this->getChangesTo()->isMultipleObjects() === false
                && $isObjectBrowserCategory
                && !$this->allowedObjectBrowserCategoriesForData($dao)
            ) {
                $daoBackwardsProperty = '';
                $this->getChangesFrom()->setMultipleObjects(true);
                $this->getChangesTo()->setMultipleObjects(true);
            }

            if ($propertyData instanceof ObjectBrowserTypeInterface && $propertyData->getBackwardPropertyTag() &&
                ($propertyData->getFromObjectId() || $propertyData->getToObjectId())) {
                if ($this->getFromObjectId() === null) {
                    $this->setFromObjectId($propertyData->getFromObjectId());
                }

                if ($this->getToObjectId() === null) {
                    $this->setToObjectId($propertyData->getToObjectId());
                }

                if ($daoBackwardsProperty === null) {
                    [$daoBackwardsPropertyClass] = explode('::', $propertyData->getBackwardPropertyTag());
                    $daoBackwardsProperty = $daoBackwardsPropertyClass::instance($dao->get_database_component());

                    $this->getChangesFrom()
                        ->setDao($daoBackwardsProperty)
                        ->setObjectId($this->getFromObjectId());
                    $this->getChangesTo()
                        ->setDao($daoBackwardsProperty)
                        ->setObjectId($this->getToObjectId());
                    $daoBackwardsProperty = '';
                }
            }
            if (!empty($changes)) {
                $this->setChanges($changes);
                $defaultChanges[] = $changesWithDefaults;
            }
        }
        $this->cleanupChanges($defaultChanges);

        if ($daoBackwardsProperty === null) {
            $this->getChangesTo()->resetData();
        }
    }

    /**
     * @param DataProvider $dataProvider
     *
     * @throws ProcessWithPostDataException
     */
    private function processWithRequestData(DataProvider $dataProvider)
    {
        $propertyProvider = $dataProvider->getPropertyData();
        $currentDataProvider = $dataProvider->getCurrentData();
        $smartyDataProvider = $dataProvider->getSmartyData();
        $requestDataProvider = $dataProvider->getRequestData();
        $currentData = $currentDataProvider->getData();
        $properties = $propertyProvider->getData();
        $dao = $propertyProvider->getDao();
        $this->getChangesCurrent()->setDao($dao);
        $this->getChangesCurrent()->setObjectId($dataProvider->getObjectId());
        $propertiesIndex = get_class($dao);
        $defaultChanges = [];
        $entryId = $requestDataProvider->getData()[C__GET__ID] ?? null;
        $isObjectBrowserCategory = $dao instanceof ObjectBrowserReceiver;

        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $propertiesIndex .= '::' . $dao->get_catg_custom_id();
        }

        try {
            $this->prepare($propertiesIndex, $properties[$propertiesIndex]);
            $daoBackwardsProperty = null;

            foreach ($properties[$propertiesIndex] as $tag => $propertyTypeData) {
                /**
                 * @var TypeInterface $propertyTypeData
                 */
                $logbookTag = $propertyTypeData[PropertyData::LOGBOOK_TAG];
                $propertyData = $propertyTypeData[PropertyData::PROPERTY_TYPE_DATA];

                if (!$propertyData instanceof TypeInterface) {
                    continue;
                }

                $property = $propertyData->getProperty();

                if ($isObjectBrowserCategory) {
                    if (!$propertyData instanceof ObjectBrowserTypeInterface) {
                        continue;
                    }
                }

                $changes = $propertyData->handlePostData($tag, $dao, $requestDataProvider, $smartyDataProvider, $currentData, $this->propertiesAlwaysInLogbook);
                $changesWithDefaults = $propertyData->getChangesWithDefaults($tag, $dao);

                if ($propertyData instanceof MultiObjectBrowserType
                    && $this->getChangesTo()->isMultipleObjects() === false
                    && $isObjectBrowserCategory
                ) {
                    $daoBackwardsProperty = '';
                    $this->getChangesFrom()->setMultipleObjects(true);
                    $this->getChangesTo()->setMultipleObjects(true);
                }

                if ($propertyData instanceof ObjectBrowserTypeInterface && $propertyData->getBackwardPropertyTag() &&
                    ($propertyData->getFromObjectId() || $propertyData->getToObjectId())) {
                    if ($this->getFromObjectId() === null) {
                        $this->setFromObjectId($propertyData->getFromObjectId());
                    }

                    if ($this->getToObjectId() === null) {
                        $this->setToObjectId($propertyData->getToObjectId());
                    }

                    if ($daoBackwardsProperty === null) {
                        [$daoBackwardsPropertyClass] = explode('::', $propertyData->getBackwardPropertyTag());
                        $daoBackwardsProperty = $daoBackwardsPropertyClass::instance($dao->get_database_component());

                        $this->getChangesFrom()
                            ->setDao($daoBackwardsProperty)
                            ->setObjectId($this->getFromObjectId());
                        $this->getChangesTo()
                            ->setDao($daoBackwardsProperty)
                            ->setObjectId($this->getToObjectId());
                        $daoBackwardsProperty = '';
                    }
                }

                $this->setChanges($changes);

                if (!empty($changes)) {
                    $defaultChanges[] = $changesWithDefaults;
                }
            }
            $this->cleanupChanges($defaultChanges);
        } catch (\Exception $e) {
            throw new ProcessWithPostDataException($e->getMessage());
        }

        if ($daoBackwardsProperty === null) {
            $this->getChangesTo()->resetData();
        }
    }

    /**
     * Helper method to cleanup changes for backwards categories which are object browser categories
     *
     * @param ChangesDataCollection $changesCollection
     */
    private function cleanupBackwardsChange(ChangesDataCollection $changesCollection)
    {
        $dao = $changesCollection->getDao();

        if ($dao instanceof isys_cmdb_dao_category
            && $changesCollection->hasData()
        ) {
            $className = get_class($dao);

            if (!in_array($className, $this->registeredObjectBrowserCategories)) {
                return $changesCollection;
            }

            $newChange = [];
            foreach ($changesCollection->getData() as $changeData) {
                $change = $changeData->getData();
                $logbookEntryKey = key($change);

                if (strpos($logbookEntryKey, $className) !== false) {
                    $newChange[] = $changeData;
                }
            }

            if (!empty($newChange)) {
                $changesCollection->resetData();

                foreach ($newChange as $change) {
                    $changesCollection->addData($change);
                }
            }
        }
    }

    /**
     * Cleanup changes for:
     * - backwards category is a object browser category
     * - compare current changes with the default values
     *
     * @param array $defaultChanges
     */
    private function cleanupChanges(array $defaultChanges = [])
    {
        if (!empty($this->registeredObjectBrowserCategories)) {
            $this->cleanupBackwardsChange($this->getChangesTo());
            $this->cleanupBackwardsChange($this->getChangesFrom());
        }

        if ($this->getChangesCurrent()->hasData()) {
            $this->compareWithDefaultValues($this->getChangesCurrent()->getData(), $defaultChanges);
            $this->checkChanges($this->getChangesCurrent());
        }

        if ($this->getChangesTo()->hasData()) {
            $this->checkChanges($this->getChangesTo());
        }

        if ($this->getChangesFrom()->hasData()) {
            $this->checkChanges($this->getChangesFrom());
        }
    }

    /**
     *
     *
     * @param ChangesDataCollection $changesCollection
     *
     * @return void
     */
    private function checkChanges(ChangesDataCollection $changesCollection)
    {
        $data = $changesCollection->getData();
        $dao = $changesCollection->getDao();

        $filteredData = array_filter($data, function ($change) use ($dao) {
            $changeData = $change->getData();
            return !empty(array_filter($changeData, function ($attributeChange, $key) use ($dao) {
                if ($dao instanceof isys_cmdb_dao_category) {
                    [, $propertyKey] = explode('::', $key);

                    $property = $dao->get_property_by_key($propertyKey);
                    if ($property !== null) {
                        if (!$property instanceof Property) {
                            $property = Property::createInstanceFromArray($property);
                        }

                        if ($property->getInfo()
                                ->getType() === Property::C__PROPERTY__INFO__TYPE__PASSWORD) {
                            return true;
                        }
                    }
                }

                $result = trim($attributeChange[TypeInterface::CHANGES_FROM]) !== trim($attributeChange[TypeInterface::CHANGES_TO]);
                return $result;
            }, ARRAY_FILTER_USE_BOTH));
        });

        if (empty($filteredData)) {
            $changesCollection->resetData();
        }
    }

    /**
     * @param $index
     * @param $properties
     */
    private function prepare($index, $properties)
    {
        foreach ($properties as $propertyKey => $propertyTypeData) {
            $propertyData = $propertyTypeData[PropertyData::PROPERTY_TYPE_DATA];

            if (!$propertyData instanceof TypeInterface) {
                continue;
            }
            $property = $propertyData->getProperty();
            if ($property->getInfo()->isAlwaysInLogbook()) {
                $this->propertiesAlwaysInLogbook[$index][$propertyKey] = $property;
            }

            if ($property->getInfo()->getBackwardProperty() && $propertyData instanceof AbstractBrowserType) {
                $backwardProperty = (string)$property->getInfo()->getBackwardProperty();
                $propertyCombinedKeyList = [$backwardProperty];
                if (strpos($backwardProperty, ';') !== false) {
                    $propertyCombinedKeyList = explode(';', $backwardProperty);
                }
                foreach ($propertyCombinedKeyList as $key) {
                    $singlePropertyData = $propertyData->loadBackwardProperty((string) $key);
                    if ($singlePropertyData->getDao() instanceof ObjectBrowserReceiver) {
                        $this->registeredObjectBrowserCategories[] = get_class($singlePropertyData->getDao());
                    }
                }
            }
        }
    }

    /**
     * @param array $currentChanges
     * @param array $defaultChanges
     *
     * @return void
     */
    private function compareWithDefaultValues(array $currentChanges, array $defaultChanges)
    {
        if (!empty($defaultChanges) && $defaultChanges[0] !== null) {
            $diff = array_udiff_assoc($currentChanges, $defaultChanges, function (?ChangesData $current, ?ChangesData $default) {
                if ($current === null || $default === null) {
                    return 0;
                }

                $change = current($current->getData());
                $defaultChange = current($default->getData());

                if ($change['from'] === null && $change['to'] === null) {
                    array_walk($defaultChange, function (&$item) {
                        $item = ($item === '') ? null : trim($item);
                    });
                }

                if ($change['from'] === '' && $change['to'] === '') {
                    array_walk($defaultChange, function (&$item) {
                        $item = ($item === null) ? '' : trim($item);
                    });
                }

                return (int) (\isys_format_json::encode($change) != \isys_format_json::encode($defaultChange));
            });

            if (empty($diff)) {
                $this->getChangesCurrent()->resetData();
            }
        }
    }
}
