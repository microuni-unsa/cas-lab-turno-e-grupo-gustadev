<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_cmdb_dao_category;
use isys_tenantsettings;

class TimeType extends AbstractType implements TypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__TIME;
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
     * @deprecated
     */
    public function handlePostData(
        string $tag,
        isys_cmdb_dao_category $dao,
        RequestData $requestDataProvider,
        SmartyData $smartyDataProvider,
        array $currentData = [],
        array $propertiesAlwaysInLogbook = []
    ) {
        return [];
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
        $property = $this->getProperty();
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
     * @return ChangesData
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
