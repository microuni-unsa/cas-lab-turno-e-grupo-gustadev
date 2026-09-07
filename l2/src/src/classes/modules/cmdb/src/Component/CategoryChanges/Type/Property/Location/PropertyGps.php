<?php
namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\Location;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\AbstractPropertyType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;

class PropertyGps extends AbstractPropertyType implements TypeInterface
{
    protected const PROPERTY_CLASS = 'isys_cmdb_dao_category_g_location';
    protected const PROPERTY_TAG = 'gps';

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        return $class === self::PROPERTY_CLASS && $tag === self::PROPERTY_TAG;
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
        $defaultValue =$property->getUi()->getDefault();
        $currentEntryId = $dao->get_list_id();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $currentDataLatitude = (string)$currentData['latitude'];
        $currentDataLongitude = (string)$currentData['longitude'];
        $changedDataLatitude = (string)$changedData['latitude'];
        $changedDataLongitude = (string)$changedData['longitude'];

        $newValue = "{$changedDataLatitude},{$changedDataLongitude}";
        $oldValue = "{$currentDataLatitude},{$currentDataLongitude}";

        if ($newValue === $oldValue) {
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
        ];
    }

    /**
     * Not necessary as it is a calculated property
     *
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     *
     * @return null
     */
    public function getChangesWithDefaults(string $tag, isys_cmdb_dao_category $dao)
    {
        return null;
    }
}
