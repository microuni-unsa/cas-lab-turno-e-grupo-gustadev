<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SinglePropertyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\Config;
use idoit\Module\Cmdb\Component\SyncNormalizer\DeNormalizer;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\CollectionDataExtractor;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_popup_browser_object_ng;

/**
 * Class MultiObjectBrowserType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
class MultiObjectBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
{
    const SUPPORTED_BROWSERS = [
        'browser_object_ng',
        'browser_object_relation',
    ];

    const SUPPORTED_TYPES = [
        Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER,
        Property::C__PROPERTY__INFO__TYPE__N2M
    ];

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        $params = $property->getUi()->getParams();
        return in_array($property->getInfo()->getType(), self::SUPPORTED_TYPES) &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
            in_array($params['p_strPopupType'], self::SUPPORTED_BROWSERS);
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param RequestData            $requestDataProvider
     * @param SmartyData             $smartyDataProvider
     * @param array                  $currentData
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array|null
     */
    public function handlePostData(
        string $tag,
        isys_cmdb_dao_category $dao,
        RequestData $requestDataProvider,
        SmartyData $smartyDataProvider,
        array $currentData = [],
        array $propertiesAlwaysInLogbook = []
    ) {
        $requestData = $requestDataProvider->getData();
        $smartyData = $smartyDataProvider->getData();
        $property = $this->getProperty();
        $propertyUi = $property->getUi();
        $uiField = $propertyUi->getId();
        $uiParams = $propertyUi->getParams();
        $uiHiddenField = $uiField . '__HIDDEN';
        $uiConfigField = $uiField . '__CONFIG';
        $currentObjectId = $dao->get_object_id();
        $currentObjectData = $dao->get_object($currentObjectId)->get_row();
        $currentObjectTitle = $currentObjectData['isys_obj__title'];
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $backwardProperty = (string)$property->getInfo()->getBackwardProperty();
        $this->setBackwardPropertyTag($backwardProperty ?: null);
        $backwardPropertyList = [];
        $fromChanges = $toChanges = $changes = [];

        if (strpos($backwardProperty, ';')) {
            $backwardPropertyList = explode(';', $backwardProperty);
        } else {
            $backwardProperty = $this->loadBackwardProperty($backwardProperty);
        }

        $newObjects = $requestDataProvider->getByKey(C__POST__POPUP_RECEIVER) ?:
            ($requestDataProvider->getByKey($uiHiddenField) ?:
                ($requestDataProvider->getByKey($uiField) ?: null));

        if ($dao instanceof ObjectBrowserAssignedEntries) {
            $newValue = $newArrayData = [];

            if (isys_format_json::is_json_array($newObjects)) {
                $newObjects = isys_format_json::decode($newObjects);
            }
            $currentEntriesCollection = $dao->getAttachedEntries($currentObjectId, $tag);
            $oldArrayData = CollectionDataExtractor::extractPropertyAsArray('title', $currentEntriesCollection);
            $oldArrayDataAsId = CollectionDataExtractor::extractPropertyAsArray('objectid', $currentEntriesCollection);

            if (!is_array($newObjects)) {
                $newObjects = (array)$newObjects;
            }

            if (!empty($newObjects)) {
                $newObjectsResult = $dao->get_object($newObjects);

                while ($row = $newObjectsResult->get_row()) {
                    $newValue[] = $row['isys_obj__title'];

                    if (!empty($backwardPropertyList)) {
                        $backwardProperty = $this->determineBackwardProperty($dao, $backwardPropertyList, $row['isys_obj__id']);
                    }

                    if ($backwardProperty === null) {
                        continue;
                    }

                    if (!$backwardProperty instanceof SinglePropertyData) {
                        throw new Exception('Backwards property of ' . $tag . ' from class ' . get_class($dao) . ' could not be determined.');
                    }

                    $toChange = $this->processBackwardsObjectPropertyChange(
                        $currentObjectTitle,
                        $currentObjectId,
                        $backwardProperty,
                        $row['isys_obj__id'],
                        self::CHANGES_TO
                    );
                    if ($toChange) {
                        $toChanges[] = $toChange;
                    }
                }
                $newArrayData = $newValue;
            }

            if (!isset($uiParams[isys_popup_browser_object_ng::C__DATARETRIEVAL])) {
                $newArrayData = array_merge($newValue, $oldArrayData);
                $newObjects = array_merge($newObjects, $oldArrayDataAsId);
            }

            $objectIdDiff = array_diff($oldArrayDataAsId, $newObjects);

            if (!empty($objectIdDiff) && $backwardProperty) {
                foreach ($oldArrayDataAsId as $objectId) {
                    if (!empty($backwardPropertyList)) {
                        $backwardProperty = $this->determineBackwardProperty($dao, $backwardPropertyList, $objectId);
                    }

                    if (in_array($objectId, $newObjects) || $backwardProperty === null) {
                        continue;
                    }

                    if (!$backwardProperty instanceof SinglePropertyData) {
                        throw new Exception('Backwards property of ' . $tag . ' from class ' . get_class($dao) . ' could not be determined.');
                    }

                    $fromChange = $this->processBackwardsObjectPropertyChange(
                        $currentObjectTitle,
                        $currentObjectId,
                        $backwardProperty,
                        $objectId,
                        self::CHANGES_FROM
                    );
                    if ($fromChange) {
                        $fromChanges[] = $fromChange;
                    }
                }
            }

            $oldValue = implode(', ', $oldArrayData);
            $newValue = implode(', ', $newArrayData);
        } else {
            $newValue = trim($requestData[$uiField] ?? '');
            $oldValueConfig = is_string($requestData[$uiConfigField]) ? isys_format_json::decode($requestData[$uiConfigField]) : [];
            $oldValue = trim($oldValueConfig['p_strValue'] ?? '');
        }

        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();

        if ($newValue === '' && $oldValue === '' && !$alwaysInLogbook) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $oldValue,
                    self::CHANGES_TO => $newValue
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
            self::CHANGES_TO => $toChanges,
            self::CHANGES_FROM => $fromChanges,
        ];
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param DefaultData $currentDataProvider
     * @param DefaultData $changedDataProvider
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array
     */
    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    ) {
        $changedData = $changedDataProvider->getData();
        $currentData = $currentDataProvider->getData();
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $currentObjectData = $dao->get_object($currentObjectId)->get_row();
        $currentObjectTitle = $currentObjectData['isys_obj__title'];
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $backwardProperty = (string)$property->getInfo()->getBackwardProperty();
        $this->setBackwardPropertyTag($backwardProperty ?: null);
        $backwardPropertyList = [];
        $fromChanges = $toChanges = $changes = [];

        if (strpos($backwardProperty, ';')) {
            $backwardPropertyList = explode(';', $backwardProperty);
        } else {
            $backwardProperty = $this->loadBackwardProperty($backwardProperty);
        }

        $newObjects = !empty($changedData[$tag]) ? $changedData[$tag] : [];
        $currentObjects = !empty($currentData[$tag]) ? $currentData[$tag] : [];
        $newValue = $newArrayData = [];

        if (empty($newObjects) && empty($currentObjects)) {
            return [];
        }

        if (isys_format_json::is_json_array($newObjects)) {
            $newObjects = isys_format_json::decode($newObjects, true);
        }

        if (isys_format_json::is_json_array($currentObjects)) {
            $currentObjects = isys_format_json::decode($currentObjects, true);
        }

        // Handle import data slightly different.
        if (is_numeric($newObjects)) {
            $newObjects = [(int)$newObjects];
        } elseif (is_array($newObjects)) {
            if (isset($newObjects['value']) || isset($newObjects['id'])) {
                $newObjects = [(int)($newObjects['id'] ?? $newObjects['value'])];
            } else {
                $newObjects = array_map(fn ($item) => is_array($item) ? (int)($item['id'] ?? $item['value'] ?? null) : (int)$item, $newObjects);
            }
            $newObjects = array_filter($newObjects, fn ($item) => $item > 0);
        }

        // Handle import data slightly different.
        if (is_numeric($currentObjects)) {
            $currentObjects = [(int)$currentObjects];
        } elseif (is_array($currentObjects)) {
            if (isset($currentObjects['value']) || isset($currentObjects['id'])) {
                $currentObjects = [(int)($currentObjects['id'] ?? $currentObjects['value'])];
            } else {
                $currentObjects = array_map(fn ($item) => is_array($item) ? (int)($item['id'] ?? $item['value'] ?? null) : (int)$item, $currentObjects);
            }
            $currentObjects = array_filter($currentObjects, fn ($item) => $item > 0);
        }

        if (!empty($newObjects)) {
            foreach ($newObjects as $newObj) {
                $newObjectsResult = $dao->get_object($newObj);

                while ($row = $newObjectsResult->get_row()) {
                    $newValue[] = $row['isys_obj__title'];

                    if (!empty($backwardPropertyList)) {
                        $backwardProperty = $this->determineBackwardProperty($dao, $backwardPropertyList, $row['isys_obj__id']);
                    }

                    if ($backwardProperty === null) {
                        continue;
                    }

                    if (!$backwardProperty instanceof SinglePropertyData) {
                        throw new Exception('Backwards property of ' . $tag . ' from class ' . get_class($dao) . ' could not be determined.');
                    }

                    $toChange = $this->processBackwardsObjectPropertyChange($currentObjectTitle, $currentObjectId, $backwardProperty, $row['isys_obj__id'], self::CHANGES_TO);
                    if ($toChange) {
                        $toChanges[] = $toChange;
                    }
                }
            }
            $newArrayData = $newValue;
        }

        $objectIdDiff = array_diff($currentObjects, $newObjects);

        if (!empty($objectIdDiff) && $backwardProperty) {
            foreach ($currentObjects as $objectId) {
                if (!empty($backwardPropertyList)) {
                    $backwardProperty = $this->determineBackwardProperty($dao, $backwardPropertyList, $objectId);
                }

                if (in_array($objectId, $newObjects) || $backwardProperty === null) {
                    continue;
                }

                if (!$backwardProperty instanceof SinglePropertyData) {
                    throw new Exception('Backwards property of ' . $tag . ' from class ' . get_class($dao) . ' could not be determined.');
                }

                $fromChange = $this->processBackwardsObjectPropertyChange(
                    $currentObjectTitle,
                    $currentObjectId,
                    $backwardProperty,
                    $objectId,
                    self::CHANGES_FROM
                );
                if ($fromChange) {
                    $fromChanges[] = $fromChange;
                }
            }
        }

        $config = new Config($dao, $currentData);
        $normalizer = new DeNormalizer($config);
        $currentObjects = $normalizer->normalizeData($currentObjectId, null);

        if (!isset($currentObjects[$tag]) || !is_array($currentObjects[$tag])) {
            $currentObjects[$tag] = [];
        }

        $oldValue = implode(', ', $currentObjects[$tag]);
        $newValue = implode(', ', $newArrayData);
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();

        if ((($newValue === '' && $oldValue === '') || $oldValue === $newValue) && !$alwaysInLogbook) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $oldValue,
                    self::CHANGES_TO => $newValue
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
            self::CHANGES_TO => $toChanges,
            self::CHANGES_FROM => $fromChanges,
        ];
    }

    public function handleRanking()
    {
        // TODO: Implement handleRanking() method.
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     *
     * @return ChangesData|null
     */
    public function getChangesWithDefaults(string $tag, isys_cmdb_dao_category $dao)
    {
        $property = $this->getProperty();
        $defaultValue =$property->getUi()->getDefault();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        return ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => '',
                    self::CHANGES_TO => $defaultValue
                ]
            ],
            $currentObjectId
        );
    }
}
