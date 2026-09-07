<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\TimeperiodProperty;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_cmdb_dao_category;
use isys_tenantsettings;

/**
 * Class DateTimeperiodType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DateTimeperiodType extends AbstractType implements TypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__TIMEPERIOD;
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
        $fromAddition = TimeperiodProperty::UI_ID_ADDITION_FROM;
        $toAddition = TimeperiodProperty::UI_ID_ADDITION_TO;
        $count = 1;

        if ((strpos(strrev($uiField), strrev($fromAddition)) === 0)) {
            $uiField = strrev(substr_replace(strrev($uiField), '', strpos(strrev($uiField), strrev($fromAddition)), strlen($fromAddition)));
        } elseif ((strpos(strrev($uiField), strrev($toAddition)) === 0)) {
            $uiField = strrev(substr_replace(strrev($uiField), '', strpos(strrev($uiField), strrev($toAddition)), strlen($toAddition)));
        }

        $uiFieldFrom = $uiField . $fromAddition;
        $uiFieldTo = $uiField . $toAddition;

        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValueFrom = $requestData[$uiFieldFrom] ?? '';
        $newValueTo = $requestData[$uiFieldTo] ?? '';
        $oldValueFrom = $smartyData[$uiFieldFrom]['p_strValue'];
        $oldValueTo = $smartyData[$uiFieldTo]['p_strValue'];

        if ($newValueFrom === $oldValueFrom && $newValueTo === $oldValueTo) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $oldValueFrom . ' - ' . $oldValueTo,
                    self::CHANGES_TO => $newValueFrom . ' - ' . $newValueTo
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
        $count = 1;
        $oldValue = (string)$currentData[$tag];
        $newValue = (string)$changedData[$tag];
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        if ($oldValue === $newValue) {
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
            self::CHANGES_TO => $changes,
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
        $emptyState = isys_tenantsettings::get('gui.empty_value', '-');

        return ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => '',
                    self::CHANGES_TO => $defaultValue ?? $emptyState
                ]
            ],
            $currentObjectId
        );
    }
}
