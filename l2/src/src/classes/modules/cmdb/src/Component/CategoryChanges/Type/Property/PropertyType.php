<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Property;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\AbstractType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;

class PropertyType extends AbstractType implements TypeInterface
{
    /**
     * @param TypeInterface[] $propertyTypes
     */
    public function __construct(private iterable $propertyTypes)
    {
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
        foreach ($this->propertyTypes as $propertyType) {
            if ($propertyType->isApplicable($property, $tag, $class)) {
                return true;
            }
        }
        return false;
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
        $property = $dao->get_property_by_key($tag);
        $class = get_class($dao);

        if (!$property instanceof Property) {
            $property = Property::createInstanceFromArray($property);
        }

        foreach ($this->propertyTypes as $propertyType) {
            if ($propertyType->isApplicable($property, $tag, $class)) {
                $propertyType->setProperty($property);
                return $propertyType->handlePostData(
                    $tag,
                    $dao,
                    $requestDataProvider,
                    $smartyDataProvider,
                    $currentData,
                    $propertiesAlwaysInLogbook
                );
            }
        }
        return null;
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param DefaultData            $currentDataProvider
     * @param DefaultData            $changedDataProvider
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array|null
     */
    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    ) {
        $property = $dao->get_property_by_key($tag);
        $class = get_class($dao);

        if (!$property instanceof Property) {
            $property = Property::createInstanceFromArray($property);
        }

        foreach ($this->propertyTypes as $propertyType) {
            if ($propertyType->isApplicable($property, $tag, $class)) {
                $propertyType->setProperty($property);
                return $propertyType->handleData(
                    $tag,
                    $dao,
                    $currentDataProvider,
                    $changedDataProvider,
                    $propertiesAlwaysInLogbook
                );
            }
        }
        return null;
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     *
     * @return ChangesData|null
     */
    public function getChangesWithDefaults(string $tag, isys_cmdb_dao_category $dao)
    {
        $property = $dao->get_property_by_key($tag);
        $class = get_class($dao);

        if (!$property instanceof Property) {
            $property = Property::createInstanceFromArray($property);
        }

        foreach ($this->propertyTypes as $propertyType) {
            if ($propertyType->isApplicable($property, $tag, $class)) {
                $propertyType->setProperty($property);
                return $propertyType->getChangesWithDefaults(
                    $tag,
                    $dao
                );
            }
        }
        return null;
    }
}
