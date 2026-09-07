<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use isys_application;
use isys_cmdb_dao_category;

class MoneyType extends TextType
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__MONEY;
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
        $currentEntryId = $dao->get_list_id();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();

        $newValue = (string)$changedData[$tag];
        $oldValue = (string)$currentData[$tag];

        if (is_numeric($newValue)) {
            $newValue = isys_application::instance()->container->get('locales')->fmt_monetary($newValue);
        }

        if (is_numeric($oldValue)) {
            $oldValue = isys_application::instance()->container->get('locales')->fmt_monetary($oldValue);
        }

        if ($oldValue === $newValue && !$alwaysInLogbook) {
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
}
