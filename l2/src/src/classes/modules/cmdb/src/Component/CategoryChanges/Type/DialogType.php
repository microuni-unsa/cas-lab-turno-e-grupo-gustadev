<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_application;
use isys_cmdb_dao_category;
use isys_cmdb_dao_dialog;
use isys_tenantsettings;

/**
 * Class DialogType
 *
 * Handling dialog types
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DialogType extends AbstractType implements TypeInterface
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
        $references = $property->getData()->getReferences();
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG && is_array($references) && !empty($references);
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
        $uiField = $this->getProperty()->getUi()->getId();
        $currentObjectId = (int)$dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValueId = (int)$requestData[$uiField];
        $oldValueId = (int)$smartyData[$uiField]['p_strSelectedID'];

        return $this->handleDataHelper($currentObjectId, $currentPropertyTag, $newValueId, $oldValueId);
    }

    /**
     * @param int    $currentObjectId
     * @param string $currentPropertyTag
     * @param int    $newValueId
     * @param int    $oldValueId
     *
     * @return array
     * @throws \Exception
     */
    private function handleDataHelper(int $currentObjectId, string $currentPropertyTag, int $newValueId, int $oldValueId)
    {
        $property = $this->getProperty();
        $dataReference = $property->getData()->getReferences();
        $oldValue = [];
        $emptyState = isys_tenantsettings::get('gui.empty_value', '-');
        $dialogInstance = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
        $dialogInstance
            ->set_table($dataReference[0])
            ->load();

        if ($newValueId === $oldValueId || ($newValueId <= 0 && $oldValueId <= 0) || ($newValueId === '' && $oldValueId === 0) || ($oldValueId === '' && $newValueId === 0)) {
            return [];
        }

        if ($oldValueId > 0) {
            $oldValue = $dialogInstance->get_data($oldValueId);
        }

        if ($newValueId > 0) {
            $newValue = $dialogInstance->get_data($newValueId);
        }

        $oldValue = !empty($oldValue) ? $oldValue['title'] : $emptyState;
        $newValue = !empty($newValue) ? $newValue['title'] : $emptyState;

        // @see ID-10890 Final check, if there are any changes.
        if ($oldValue === $newValue && !$this->getProperty()->getInfo()->isAlwaysInLogbook()) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $oldValue,
                    self::CHANGES_TO =>  $newValue
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
        $currentObjectId = (int)$dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValueId = (int)$changedData[$tag];
        $oldValueId = (int)$currentData[$tag];

        return $this->handleDataHelper($currentObjectId, $currentPropertyTag, $newValueId, $oldValueId);
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
        $dataReference = $property->getData()->getReferences();
        $emptyState = isys_tenantsettings::get('gui.empty_value', '-');

        $dialogInstance = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
        $dialogInstance
            ->set_table($dataReference[0])
            ->load();

        $defaultValue = $defaultValue > 0 ? $dialogInstance->get_data($defaultValue)['title'] : null;

        return ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $emptyState,
                    self::CHANGES_TO =>  $defaultValue ?? $emptyState
                ]
            ],
            $currentObjectId
        );
    }
}
