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
use isys_format_json;

/**
 * Class MultiselectType
 *
 * Handling multiselect types
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class MultiselectType extends AbstractType implements TypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__MULTISELECT;
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
        $currentObjectId = (int) $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValue = $requestData[$uiField] ?? [];
        $oldValue = (strlen($smartyData[$uiField]['p_strSelectedID']) > 0 ?
            explode(',', $smartyData[$uiField]['p_strSelectedID']) : []);

        return $this->handleDataHelper($currentObjectId, $currentPropertyTag, $newValue, $oldValue);
    }

    /**
     * @param int    $currentObjectId
     * @param string $currentPropertyTag
     * @param array  $newValue
     * @param array  $oldValue
     *
     * @return array
     * @throws \Exception
     */
    private function handleDataHelper(int $currentObjectId, string $currentPropertyTag, array $newValue, array $oldValue)
    {
        $emptyState = '';
        $property = $this->getProperty();
        $references = $property->getData()->getReferences();
        $sourceTable = $property->getData()->getSourceTable();

        natsort($newValue);
        natsort($oldValue);

        if (!empty($references) && !str_contains($references[0], '_2_')) {
            $sourceTable = $references[0];
        }

        $dialogInstance = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
        $dialogInstance
            ->set_table($sourceTable)
            ->load();

        if (!empty($newValue)) {
            array_walk($newValue, function (&$item) use ($dialogInstance) {
                $data = $dialogInstance->get_data((int)$item);
                $item = $data['title'] ?? '';
            });
        }

        if (!empty($oldValue)) {
            array_walk($oldValue, function (&$item) use ($dialogInstance) {
                $id = (int)$item;
                if (is_array($item) && isset($item['id'])) {
                    $id = (int)$item['id'];
                }
                $data = $dialogInstance->get_data((int)$id);
                $item = $data['title'] ?? '';
            });
        }

        // @see ID-12157 Check the diff, after the value structures where 'unified'.
        if (empty(array_diff($newValue, $oldValue)) && empty(array_diff($oldValue, $newValue))) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => !empty($oldValue) ? implode(', ', $oldValue) : $emptyState,
                    self::CHANGES_TO =>  !empty($newValue) ? implode(', ', $newValue) : $emptyState
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
        $currentObjectId = (int) $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $newValue = !empty($changedData[$tag]) ? $changedData[$tag] : [];
        $oldValue = !empty($currentData[$tag]) ? $currentData[$tag] :  [];

        if (isys_format_json::is_json_array($newValue)) {
            $newValue = isys_format_json::decode($newValue, true);
        }

        if (isys_format_json::is_json_array($oldValue)) {
            $oldValue = isys_format_json::decode($oldValue, true);
        }

        if (!is_array($newValue)) {
            $newValue = [$newValue];
        }

        if (!is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        return $this->handleDataHelper($currentObjectId, $currentPropertyTag, $newValue, $oldValue);
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
