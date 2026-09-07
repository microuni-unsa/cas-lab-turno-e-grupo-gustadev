<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use isys_cmdb_dao_category;

class MaskedTextType extends TextType
{
    /**
     * @var string[]
     */
    private $maskedFields = [
        'C__CONTACT__PERSON_PASSWORD',
        'C__CATG__PASSWORD__PASSWORD',
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__PASSWORD ||
            in_array($property->getUi()->getId(), $this->maskedFields);
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
                    self::CHANGES_FROM => '***',
                    self::CHANGES_TO => '***'
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
        ];
    }
}
