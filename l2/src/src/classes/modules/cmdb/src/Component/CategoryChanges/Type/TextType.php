<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_cmdb_dao_category;

/**
 * Class TextType
 *
 * Default type for all other property types
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class TextType extends AbstractType implements TypeInterface
{
    /**
     * @var array
     */
    private $types = [
        Property::C__PROPERTY__INFO__TYPE__TEXT,
        Property::C__PROPERTY__INFO__TYPE__AUTOTEXT,
        Property::C__PROPERTY__INFO__TYPE__INT,
        Property::C__PROPERTY__INFO__TYPE__DOUBLE,
        Property::C__PROPERTY__INFO__TYPE__FLOAT,
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
        return in_array($property->getInfo()->getType(), $this->types);
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
        $defaultValue =$property->getUi()->getDefault();
        $uiField = $property->getUi()->getId();
        $currentEntryId = $requestData[C__GET__ID];
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();

        if ((!array_key_exists($uiField, $requestData) || !array_key_exists($uiField, $smartyData)) && !$alwaysInLogbook) {
            return [];
        }

        $newValue = (string)$requestData[$uiField];
        $oldValue = (string)$smartyData[$uiField]['p_strValue'];

        if (($oldValue === $newValue && !$alwaysInLogbook) || (trim($oldValue) === '' && trim($newValue) === '')) {
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
        $defaultValue =$property->getUi()->getDefault();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();

        if (is_array($changedData[$tag]) && empty($changedData[$tag])) {
            $changedData[$tag] = $defaultValue;
        }

        if (is_array($currentData[$tag]) && empty($currentData[$tag])) {
            $currentData[$tag] = $defaultValue;
        }

        $newValue = (string)$changedData[$tag];
        $oldValue = (string)$currentData[$tag];

        if (($oldValue === $newValue && !$alwaysInLogbook) || (trim($oldValue) === '' && trim($newValue) === '')) {
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
