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
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\CollectionDataExtractor;
use idoit\Module\Cmdb\Model\Entry\Entry;
use isys_application;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_popup_browser_object_ng;

class MultiSecondSelectObjectBrowserType extends MultiObjectBrowserType implements TypeInterface, ObjectBrowserTypeInterface
{
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            $params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
            $params['p_strPopupType'] === 'browser_object_ng';
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
        $uiField = $property->getUi()
            ->getId();
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $uiHiddenField = $uiField . '__HIDDEN';
        $uiConfigField = $uiField . '__CONFIG';
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $changes = [];

        if (!$requestDataProvider->hasKey($uiField)
            && $requestDataProvider->hasKey(C__POST__POPUP_RECEIVER)
            && $dao instanceof ObjectBrowserAssignedEntries
        ) {
            return $this->handlePopupReceiver($tag, $dao, $requestDataProvider, $smartyDataProvider, $currentData);
        }

        $oldValueConfig = is_string($requestData[$uiConfigField]) ? isys_format_json::decode($requestData[$uiConfigField]) : [];
        $oldValueId = str_replace('"', '', (string)$oldValueConfig['p_strSelectedID']);
        $oldValue = $oldValueConfig['p_strValue'] ? explode(', ', trim((string)$oldValueConfig['p_strValue'])) : [];
        $newValueId = (string)$requestData[$uiHiddenField];
        $newValue = [];
        $formatSelectionListFormat = $oldValueConfig[isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT];
        [$selectionClass, $selectionMethod] = explode('::', $formatSelectionListFormat);

        if (isys_format_json::is_json_array($newValueId)) {
            $newValueId = isys_format_json::decode($newValueId);
            asort($newValueId);
        }

        if (isys_format_json::is_json_array($oldValueId)) {
            $oldValueId = isys_format_json::decode($oldValueId);
            asort($oldValueId);
        }

        $valueCheck = empty(array_diff((array)$oldValueId, (array)$newValueId)) && empty(array_diff((array)$newValueId, (array)$oldValueId));
        $selectionClassCheck = class_exists($selectionClass);

        if (!$selectionClassCheck || ($valueCheck && !$alwaysInLogbook)) {
            return [];
        }

        $selectionClassInstance = $selectionClass::instance(isys_application::instance()->container->get('database'));
        if (!method_exists($selectionClassInstance, $selectionMethod)) {
            return [];
        }

        if (is_array($newValueId) && !empty($newValueId)) {
            $newValueArr = [];
            foreach ($newValueId as $id) {
                $newValueArr[] = (string)$selectionClassInstance->$selectionMethod($id, true);
            }
            $newValue = $newValueArr;
        }
        if (is_array($oldValue)) {
            asort($oldValue);
        }

        if (is_array($newValue)) {
            asort($newValue);
        }

        $oldValue = implode(', ', $oldValue);
        $newValue = implode(', ', $newValue);

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
            self::CHANGES_TO => $changes,
        ];
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param RequestData            $requestDataProvider
     * @param SmartyData             $smartyDataProvider
     * @param array                  $currentData
     *
     * @return array
     * @throws Exception
     * @throws \idoit\Exception\JsonException
     */
    private function handlePopupReceiver(string $tag, isys_cmdb_dao_category $dao, RequestData $requestDataProvider, SmartyData $smartyDataProvider, array $currentData = [])
    {
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $currentObjectTitle = $dao->obj_get_title_by_id_as_string($currentObjectId);
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $backwardPropertyCombinedKey = (string)$property->getInfo()->getBackwardProperty();
        $uiField = $property->getUi()->getId();
        $uiHiddenField = $uiField . '__HIDDEN';
        $assignedEntries = $requestDataProvider->getByKey(C__POST__POPUP_RECEIVER) ?:
            ($requestDataProvider->getByKey($uiHiddenField) ?:
                ($requestDataProvider->getByKey($uiField) ?: null));
        $backwardProperty = $this->loadBackwardProperty((string) $backwardPropertyCombinedKey);
        $currentEntriesAsIds = $newEntriesAsIds = $toChanges = $fromChanges = $newValue = $oldValue = [];

        if (isys_format_json::is_json_array($assignedEntries)) {
            $assignedEntries = isys_format_json::decode($assignedEntries);
        }

        $currentEntries = $dao->getAttachedEntries($currentObjectId);

        if (is_array($assignedEntries) && !empty($assignedEntries)) {
            $currentEntriesAsIds = CollectionDataExtractor::extractPropertyAsArray('id', $currentEntries);
            $newEntries = $dao->getAttachedEntries($assignedEntries, $tag);
            $newEntriesAsIds = CollectionDataExtractor::extractPropertyAsArray('id', $newEntries);
            // Retrieve objectids
            $objectIds = array_unique(CollectionDataExtractor::extractPropertyAsArray('objectid', $newEntries));

            foreach ($newEntries->getEntries() as $entry) {
                $newValue[] = $entry->getTitle();

                if (!$entry instanceof Entry || !$backwardProperty instanceof SinglePropertyData) {
                    continue;
                }

                if (!in_array($entry->getEntryid(), $currentEntriesAsIds)) {
                    $toChanges[] = $this->processBackwardsEntryPropertyChange(
                        $currentObjectId,
                        $currentObjectTitle,
                        $entry,
                        $backwardProperty,
                        self::CHANGES_TO
                    );
                }
            }
        }

        foreach ($currentEntries->getEntries() as $entry) {
            $oldValue[] = $entry->getTitle();

            if (!$entry instanceof Entry || !$backwardProperty instanceof SinglePropertyData) {
                continue;
            }

            if (!in_array($entry->getEntryid(), $newEntriesAsIds)) {
                $fromChanges[] = $this->processBackwardsEntryPropertyChange(
                    $currentObjectId,
                    $currentObjectTitle,
                    $entry,
                    $backwardProperty,
                    self::CHANGES_FROM
                );
            }
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => implode(', ', $oldValue),
                    self::CHANGES_TO => implode(', ', $newValue)
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
            self::CHANGES_TO => $toChanges,
            self::CHANGES_FROM => $fromChanges
        ];
    }

    public function handleRanking()
    {
        // TODO: Implement handleRanking() method.
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param DefaultData $currentDataProvider
     * @param DefaultData $changedDataProvider
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array|void
     */
    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    ) {
        $currentData = $currentDataProvider->getData();
        $changedData = $changedDataProvider->getData();
        $property = $this->getProperty();
        $uiParams = $property->getUi()->getParams();
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $oldValue = !empty($currentData[$tag]) ? $currentData[$tag] : [];
        $newValue = !empty($changedData[$tag]) ? $changedData[$tag] : [];

        if (empty($oldValue) && empty($newValue)) {
            return [];
        }

        if (isys_format_json::is_json_array($oldValue)) {
            $oldValue = isys_format_json::decode($oldValue, true);
        }

        if (isys_format_json::is_json_array($newValue)) {
            $newValue = isys_format_json::decode($newValue, true);
        }

        // Handle import data slightly different.
        if (is_numeric($newValue)) {
            $newValue = [$newValue];
        } elseif (is_array($newValue)) {
            if (isset($newValue['value']) || isset($newValue['id']) || isset($newValue['ref_id'])) {
                $newValue = [(int)($newValue['ref_id'] ?? $newValue['id'] ?? $newValue['value'])];
            } else {
                $newValue = array_map(fn ($item) => is_array($item) ? (int)($item['ref_id'] ?? $item['id'] ?? $item['value'] ?? null) : (int)$item, $newValue);
            }
            $newValue = array_filter($newValue, fn ($item) => $item > 0);
        }

        // Handle import data slightly different.
        if (is_numeric($oldValue)) {
            $oldValue = [$oldValue];
        } elseif (is_array($oldValue)) {
            if (isset($oldValue['value']) || isset($oldValue['id']) || isset($oldValue['ref_id'])) {
                $oldValue = [$oldValue['ref_id'] ?? $oldValue['id'] ?? $oldValue['value']];
            } else {
                $oldValue = array_map(fn ($item) => is_array($item) ? (int)($item['ref_id'] ?? $item['id'] ?? $item['value'] ?? null) : (int)$item, $oldValue);
            }
            $oldValue = array_filter($oldValue, fn ($item) => $item > 0);
        }

        asort($oldValue);
        asort($newValue);

        $formatSelectionListFormat = $uiParams[isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT];
        [$selectionClass, $selectionMethod] = explode('::', $formatSelectionListFormat);

        $valueCheck = empty(array_diff($oldValue, $newValue)) && empty(array_diff($newValue, $oldValue));
        $selectionClassCheck = class_exists($selectionClass);

        if (!$selectionClassCheck || ($valueCheck && !$alwaysInLogbook)) {
            return [];
        }

        $selectionClassInstance = $selectionClass::instance(isys_application::instance()->container->get('database'));
        if (!method_exists($selectionClassInstance, $selectionMethod)) {
            return [];
        }

        if ($dao instanceof ObjectBrowserAssignedEntries) {
            return $this->handleDataHelper($tag, $dao, $oldValue, $newValue);
        }

        $newValueArr = [];
        foreach ($newValue as $id) {
            $newValueArr[] = (string)$selectionClassInstance->$selectionMethod($id, true);
        }
        $newValue = $newValueArr;

        $oldValueArr = [];
        foreach ($oldValue as $id) {
            $oldValueArr[] = (string)$selectionClassInstance->$selectionMethod($id, true);
        }
        $oldValue = $oldValueArr;

        $oldValue = implode(', ', $oldValue);
        $newValue = implode(', ', $newValue);

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
            self::CHANGES_TO => $changes,
        ];
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param array                  $currentData
     * @param array                  $changedData
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function handleDataHelper(string $tag, isys_cmdb_dao_category $dao, array $currentData, array $changedData): array
    {
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $currentObjectTitle = $dao->obj_get_title_by_id_as_string($currentObjectId);
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $backwardPropertyCombinedKey = (string)$property->getInfo()->getBackwardProperty();
        $backwardProperty = $this->loadBackwardProperty((string) $backwardPropertyCombinedKey);

        $currentEntries = $dao->getAttachedEntries($currentData, $tag);
        $changedEntries = $dao->getAttachedEntries($changedData, $tag);

        $toChanges = $fromChanges = $oldValue = $newValue = $currentEntriesAsIds = $newEntriesAsIds = [];

        if ($currentEntries instanceof CollectionInterface && !empty($currentEntries->getEntries())) {
            $currentEntriesAsIds = CollectionDataExtractor::extractPropertyAsArray('id', $currentEntries);
        }

        if ($changedEntries instanceof CollectionInterface && !empty($changedEntries->getEntries())) {
            $newEntriesAsIds = CollectionDataExtractor::extractPropertyAsArray('id', $changedEntries);
        }

        foreach ($changedEntries->getEntries() as $entry) {
            $newValue[] = $entry->getTitle();

            if (!$entry instanceof Entry || !$backwardProperty instanceof SinglePropertyData) {
                continue;
            }

            if (!empty($currentEntriesAsIds) && !in_array($entry->getEntryid(), $currentEntriesAsIds)) {
                $toChanges[] = $this->processBackwardsEntryPropertyChange(
                    $currentObjectId,
                    $currentObjectTitle,
                    $entry,
                    $backwardProperty,
                    self::CHANGES_TO
                );
            }
        }

        foreach ($currentEntries->getEntries() as $entry) {
            $oldValue[] = $entry->getTitle();

            if (!$entry instanceof Entry || !$backwardProperty instanceof SinglePropertyData) {
                continue;
            }

            if (!empty($newEntriesAsIds) && !in_array($entry->getEntryid(), $newEntriesAsIds)) {
                $fromChanges[] = $this->processBackwardsEntryPropertyChange(
                    $currentObjectId,
                    $currentObjectTitle,
                    $entry,
                    $backwardProperty,
                    self::CHANGES_FROM
                );
            }
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => implode(', ', $oldValue),
                    self::CHANGES_TO => implode(', ', $newValue)
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
            self::CHANGES_TO => $toChanges,
            self::CHANGES_FROM => $fromChanges
        ];
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
