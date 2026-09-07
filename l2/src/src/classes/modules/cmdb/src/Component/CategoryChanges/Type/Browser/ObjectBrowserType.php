<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\CollectionDataExtractor;
use idoit\Module\Cmdb\Model\Entry\EntryCollection;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_popup_browser_object_ng;
use isys_tenantsettings;

/**
 * Class ObjectBrowserType
 *
 * Handles changes for property types for normal Object-Browsers
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class ObjectBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
{
    /**
     * @var isys_popup_browser_object_ng
     */
    private $objectBrowserPlugin = null;

    public function __construct()
    {
        parent::__construct();
        $this->objectBrowserPlugin = new isys_popup_browser_object_ng();
        $this->objectBrowserPlugin->set_format_quick_info(false);
    }

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        $ignoreObjectBrowserWithCallback = [
            'cable_connection'
        ];

        $callback = $property->getFormat()->getCallback();
        $callbackFunc = !empty($callback) ? $callback[1] : '';
        $params = $property->getUi()->getParams();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $params['p_strPopupType'] === 'browser_object_ng' &&
            !!$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] === false &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] === false &&
            !in_array($callbackFunc, $ignoreObjectBrowserWithCallback);
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
        $uiField = $property->getUi()->getId();
        $uiHiddenField = $uiField . '__HIDDEN';
        $uiViewField = $uiField . '__VIEW';
        $uiConfigField = $uiField . '__CONFIG';
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $changes = [];

        $newValue = $requestData[$uiField];
        $newValueId = (int)$requestData[$uiHiddenField];
        $oldValueId = (int)(!empty($smartyData[$uiField]['p_strSelectedID']) ?
            $smartyData[$uiField]['p_strSelectedID'] : (is_numeric($smartyData[$uiField]['p_strValue']) ? $smartyData[$uiField]['p_strValue'] : ''));
        $oldValueConfig = \isys_format_json::is_json_array($requestData[$uiConfigField]) ?
            \isys_format_json::decode($requestData[$uiConfigField]) : [];
        $oldValue = $oldValueConfig['p_strValue'] ?? '';
        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);

        if ($newValueId === $oldValueId && !$alwaysInLogbook) {
            return [];
        }

        if ($newValueId === $oldValueId && $this->getBackwardPropertyTag() !== null && !$alwaysInLogbook) {
            $this->setToObjectId($newValueId);
            $changes[self::CHANGES_TO] = $this->handlePostDataHelper($dao, $currentObjectId, $newValueId, self::CHANGES_BOTH, $currentData, $propertiesAlwaysInLogbook);
            return $changes;
        }

        if ($oldValueId > 0 && $oldValue === '') {
            $oldValue = $this->getObjectTitleAsArray($oldValueId, $dao)[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION];
        }

        if ($oldValueId > 0 && $oldValueId !== $newValueId && $this->getBackwardPropertyTag() !== null) {
            $changes[self::CHANGES_FROM] = $this->handlePostDataHelper($dao, $currentObjectId, $oldValueId, self::CHANGES_FROM, $currentData, $propertiesAlwaysInLogbook);
            $this->setFromObjectId($oldValueId);
        }

        if (($newValueId > 0 && $newValueId !== $oldValueId) || $alwaysInLogbook) {
            $changes[self::CHANGES_TO] = $this->handlePostDataHelper($dao, $currentObjectId, $newValueId, self::CHANGES_TO, $currentData, $propertiesAlwaysInLogbook);
            $this->setToObjectId($newValueId);
        }

        $currentChanges = [
            self::CHANGES_CURRENT => ChangesData::factory(
                [
                    $currentPropertyTag => [
                        self::CHANGES_FROM => $oldValue,
                        self::CHANGES_TO   => $newValue
                    ]
                ],
                $dao->get_object_id()
            )
        ];

        $changes =  $currentChanges + $changes;
        return $changes;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int                    $objectId
     * @param int                    $referenceId
     * @param string                 $direction
     *
     * @return ChangesData
     * @throws \isys_exception_database
     */
    private function handlePostDataHelper(isys_cmdb_dao_category $dao, $objectId, $referenceId, $direction = self::CHANGES_FROM, $currentData = [], $propertiesAlwaysInLogbook = [])
    {
        $currentObjectTitle = $this->getObjectTitleAsArray($objectId, $dao);
        $backwardPropertyCombinedKey = (string) $this->getProperty()->getInfo()->getBackwardProperty();
        $backwardPropertyDao = null;
        $changes = [];

        if ($backwardPropertyCombinedKey !== '') {
            $backwardProperty = $this->loadBackwardProperty((string) $backwardPropertyCombinedKey);
            $backwardPropertyDao = $backwardProperty->getDao();
        }

        switch ($direction) {
            case self::CHANGES_FROM:
                $changes[$backwardPropertyCombinedKey] =
                    [
                        self::CHANGES_FROM => $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION],
                        self::CHANGES_TO => $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED')
                    ];

                if ($backwardPropertyDao && $backwardPropertyDao instanceof ObjectBrowserAssignedEntries) {
                    $attachedObjects = $backwardPropertyDao->getAttachedEntries($referenceId);

                    if (!empty($attachedObjects->getEntries())) {
                        $changes[$backwardPropertyCombinedKey] = $this->handleObjectBrowserChanges(
                            $dao,
                            $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITHOUT_ADDITION],
                            $attachedObjects,
                            $currentData,
                            $propertiesAlwaysInLogbook,
                            self::CHANGES_FROM
                        );
                    }
                }
                break;
            case self::CHANGES_TO:
                $changesFrom = isys_tenantsettings::get('gui.empty_value', '-');
                $changesTo = $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION];

                $changes[$backwardPropertyCombinedKey] =
                    [
                        self::CHANGES_FROM => $changesFrom,
                        self::CHANGES_TO => $changesTo
                    ];

                if ($backwardPropertyDao && $backwardPropertyDao instanceof ObjectBrowserAssignedEntries) {
                    $attachedObjects = $backwardPropertyDao->getAttachedEntries($referenceId);

                    if (!empty($attachedObjects->getEntries())) {
                        $changes[$backwardPropertyCombinedKey] = $this->handleObjectBrowserChanges(
                            $dao,
                            $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITHOUT_ADDITION],
                            $attachedObjects,
                            $currentData,
                            $propertiesAlwaysInLogbook,
                            self::CHANGES_TO
                        );
                    }
                }

                break;
            case self::CHANGES_BOTH:
                $changes[$backwardPropertyCombinedKey] = [
                    self::CHANGES_FROM => $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION],
                    self::CHANGES_TO => $currentObjectTitle[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION]
                ];
                break;
        }

        return ChangesData::factory($changes, $referenceId);
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param string $currentObjectTitle
     * @param CollectionInterface $attachedObjects
     * @param array $currentData
     * @param array $propertiesAlwaysInLogbook
     * @param string $direction
     *
     * @return array
     */
    private function handleObjectBrowserChanges($dao, $currentObjectTitle, CollectionInterface $attachedObjects, $currentData, $propertiesAlwaysInLogbook, $direction)
    {
        $changesFrom = CollectionDataExtractor::extractPropertyAsArray('title', $attachedObjects);
        $changesTo = $changesFrom;

        if ($attachedObjects instanceof EntryCollection) {
            // We need to retrieve the property which is always in the logbook
            if (isset($propertiesAlwaysInLogbook[get_class($dao)])) {
                $tag = key($propertiesAlwaysInLogbook[get_class($dao)]);
                $currentObjectTitle = $currentData[$tag];
            }
        }

        switch ($direction) {
            case self::CHANGES_TO:
                $changesTo[] = $currentObjectTitle;
                break;
            case self::CHANGES_FROM:
                $index = array_search($currentObjectTitle, $changesTo);
                unset($changesTo[$index]);
                break;
            default:
                throw new Exception('Unkown direction please use ' . self::CHANGES_TO . ' or ' . self::CHANGES_FROM . '.');
        }

        return [
            self::CHANGES_FROM => implode(', ', $changesFrom),
            self::CHANGES_TO => implode(', ', $changesTo)
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
        $currentData = $currentDataProvider->getData();
        $changedData = $changedDataProvider->getData();
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $changes = [];

        if (isys_format_json::is_json_array($changedData[$tag])) {
            $obj = new MultiObjectBrowserType();
            $obj->setProperty($this->getProperty());

            return $obj->handleData($tag, $dao, $currentDataProvider, $changedDataProvider, $propertiesAlwaysInLogbook);
        }

        // Handle import data slightly different.
        if (!is_array($changedData[$tag])) {
            $newValueId = (int)$changedData[$tag];
        } else {
            $newValueId = (int)($changedData[$tag]['id'] ?? $changedData[$tag]['value'] ?? $changedData[$tag]);
        }

        // Handle import data slightly different.
        if (!is_array($currentData[$tag])) {
            $oldValueId = (int)$currentData[$tag];
        } else {
            $oldValueId = (int)($currentData[$tag]['id'] ?? $currentData[$tag]['value'] ?? $currentData[$tag]);
        }

        $oldValue = $newValue = '';
        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);

        if (($newValueId === 0 && $oldValueId === 0) || ($newValueId === $oldValueId && !$alwaysInLogbook)) {
            return [];
        }

        if ($newValueId === $oldValueId && $this->getBackwardPropertyTag() !== null && !$alwaysInLogbook) {
            $this->setToObjectId($newValueId);
            $changes[self::CHANGES_TO] = $this->handlePostDataHelper($dao, $currentObjectId, $newValueId, self::CHANGES_BOTH, $currentData, $propertiesAlwaysInLogbook);
            return $changes;
        }

        if ($oldValueId > 0) {
            $oldValue = $this->getObjectTitleAsArray($oldValueId, $dao)[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION];
        }

        if ($newValueId > 0) {
            $newValue = $this->getObjectTitleAsArray($newValueId, $dao)[AbstractBrowserType::OBJECT_TITLE_WITH_ADDITION];
        }

        if ($oldValueId > 0 && $oldValueId !== $newValueId && $this->getBackwardPropertyTag() !== null) {
            $changes[self::CHANGES_FROM] = $this->handlePostDataHelper($dao, $currentObjectId, $oldValueId, self::CHANGES_FROM, $currentData, $propertiesAlwaysInLogbook);
            $this->setFromObjectId($oldValueId);
        }

        if (($newValueId > 0 && $newValueId !== $oldValueId) || $alwaysInLogbook) {
            $changes[self::CHANGES_TO] = $this->handlePostDataHelper($dao, $currentObjectId, $newValueId, self::CHANGES_TO, $currentData, $propertiesAlwaysInLogbook);
            $this->setToObjectId($newValueId);
        }

        $currentChanges = [
            self::CHANGES_CURRENT => ChangesData::factory(
                [
                    $currentPropertyTag => [
                        self::CHANGES_FROM => $oldValue,
                        self::CHANGES_TO   => $newValue
                    ]
                ],
                $dao->get_object_id()
            )
        ];

        $changes =  $currentChanges + $changes;
        return $changes;
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
        $changes = [];

        $defaultValue = $defaultValue > 0 ? $this->objectBrowserPlugin->format_selection((int) $defaultValue, true) : '';

        return ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $defaultValue,
                    self::CHANGES_TO   => $defaultValue
                ]
            ],
            $currentObjectId
        );
    }

    /**
     * @return string
     */
    protected function getSeparator()
    {
        return ' >> ';
    }
}
