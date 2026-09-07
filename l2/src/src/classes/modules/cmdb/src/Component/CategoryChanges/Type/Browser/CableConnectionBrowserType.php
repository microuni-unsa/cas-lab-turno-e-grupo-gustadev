<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;
use isys_tenantsettings;

/**
 * Class CableConnectionBrowserType
 *
 * Special handling for properties which are from ui type browser_cable_connection_ng
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class CableConnectionBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
        $cableConnectionCallback = 'cable_connection';
        $callback = $property->getFormat()->getCallback();
        $callbackFunc = !empty($callback) ? $callback[1] : '';

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_object_ng' &&
            $callbackFunc === $cableConnectionCallback;
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param RequestData            $requestDataProvider
     * @param SmartyData             $smartyDataProvider
     * @param array                  $currentData
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array
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
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $currentObjectId = $dao->get_object_id();
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');

        $newValue = $requestData[$uiField] ?? '';
        $newValueId = (int)$requestData[$uiHiddenField];
        $oldValueId = (int)(is_numeric($smartyData[$uiField]['p_strValue']) ? $smartyData[$uiField]['p_strValue'] : null);
        $oldValue = $emptyValue;

        if ($newValueId === $oldValueId) {
            return [];
        }

        if ($oldValueId > 0) {
            $objectData = $dao->get_object($oldValueId)->get_row();
            $type = $this->language->get($objectData['isys_obj_type__title']);
            $oldValue = $type . $separator . $objectData['isys_obj__title'];
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
            self::CHANGES_TO => $changes,
        ];
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param DefaultData            $currentDataProvider
     * @param DefaultData            $changedDataProvider
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
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $currentObjectId = $dao->get_object_id();
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');

        // Handle import data slightly different.
        if (is_numeric($changedData[$tag])) {
            $newValueId = (int)$changedData[$tag];
        } else {
            $newValueId = (int)($changedData[$tag]['ref_id'] ?? $changedData[$tag]['id'] ?? $changedData[$tag]);
        }

        // Handle import data slightly different.
        if (is_numeric($currentData[$tag])) {
            $oldValueId = (int)$currentData[$tag];
        } else {
            $oldValueId = (int)($currentData[$tag]['ref_id'] ?? $currentData[$tag]['id'] ?? $currentData[$tag]);
        }

        $newValue = $oldValue = $emptyValue;

        if ($newValueId === $oldValueId) {
            return [];
        }

        if ($newValueId > 0) {
            $objectData = $dao->get_object($newValueId)->get_row();
            $type = $this->language->get($objectData['isys_obj_type__title']);
            $newValue = $type . $separator . $objectData['isys_obj__title'];
        }

        if ($oldValueId > 0) {
            $objectData = $dao->get_object($oldValueId)->get_row();
            $type = $this->language->get($objectData['isys_obj_type__title']);
            $oldValue = $type . $separator . $objectData['isys_obj__title'];
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
            self::CHANGES_TO => $changes,
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
        return null;
    }
}
