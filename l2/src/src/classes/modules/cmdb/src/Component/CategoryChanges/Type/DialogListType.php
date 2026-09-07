<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Helper\Unserialize;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_callback;
use isys_cmdb_dao_category;

/**
 * Class DialogListType
 *
 * Handling dialog list types
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DialogListType extends AbstractType implements TypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST;
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
        $sourceTable = $property->getData()->getSourceTable();
        $uiField = $property->getUi()->getId();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newConfig = $config = $newValue = $oldValue = $changes = [];
        $emptyState = '';
        $uiFieldSelectedBox = $uiField . '__selected_box';
        $uiFieldSelectedValues = $uiField . '__selected_values';

        if (isset($requestData[$uiFieldSelectedBox])) {
            $newValue = $requestData[$uiFieldSelectedBox];
        } elseif (isset($requestData[$uiFieldSelectedValues]) && $requestData[$uiFieldSelectedValues] !== '') {
            $newValue = explode(',', $requestData[$uiFieldSelectedValues]);
        } else {
            return [];
        }

        if (isset($smartyData[$uiField]['p_arData']) && is_string($smartyData[$uiField]['p_arData'])) {
            $config = Unserialize::toArray($smartyData[$uiField]['p_arData']);
        }

        if (!empty($config)) {
            foreach ($config as $item) {
                $newConfig[$item['id']] = $item['val'];
                if (!!$item['sel']) {
                    $oldValue[] = $item['id'];
                }
            }
        }
        natsort($newValue);
        natsort($oldValue);

        if (array_values($newValue) === array_values($oldValue)) {
            return [];
        }

        if (!empty($newValue)) {
            array_walk($newValue, function (&$item) use ($newConfig) {
                $item = $newConfig[$item];
            });
        }

        if (!empty($oldValue)) {
            array_walk($oldValue, function (&$item) use ($newConfig) {
                $item = $newConfig[$item];
            });
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
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $sourceTable = $property->getData()->getSourceTable();
        $uiParams = $property->getUi()->getParams();
        $config = $uiParams['p_arData'];

        if (is_string($config)) {
            $config = Unserialize::toArray($config);
        }

        if ($config instanceof isys_callback) {
            $request = new \isys_request();
            $request->set_object_id($currentObjectId);
            $request->set_category_data_id($dao->get_list_id());

            $config = $config->execute($request);
        }

        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValue = $oldValue = [];
        $emptyState = '';

        $newValueData = is_array($changedData[$tag]) ? $changedData[$tag] : explode(',', $changedData[$tag] ?? '');
        $oldValueData = is_array($currentData[$tag]) ? $currentData[$tag] : explode(',', $currentData[$tag] ?? '');
        $newValue = $oldValue = [];

        // @see ID-11157 Implement different logic for multi-dimensional values.
        $newValuesOneDimensional = count($newValueData) === count($newValueData, COUNT_RECURSIVE);
        $oldValuesOneDimensional = count($oldValueData) === count($oldValueData, COUNT_RECURSIVE);

        if (!empty($config)) {
            foreach ($config as $id => $item) {
                if (is_array($item)) {
                    if (!isset($item['id']) && !isset($item['ref_id'])) {
                        continue;
                    }

                    $checkId = $item['id'] ?? $item['ref_id'];

                    if ($newValuesOneDimensional) {
                        if (in_array($checkId, $newValueData)) {
                            $newValue[] = $item['val'];
                        }
                    } else {
                        // @see ID-11157
                        if (in_array($checkId, array_map(fn ($value) => $value['id'] ?? $value['ref_id'] ?? null, $newValueData))) {
                            $newValue[] = $item['val'];
                        }
                    }

                    if ($oldValuesOneDimensional) {
                        if (in_array($checkId, $oldValueData)) {
                            $oldValue[] = $item['val'];
                        }
                    } else {
                        // @see ID-11157
                        if (in_array($checkId, array_map(fn ($value) => $value['id'] ?? $value['ref_id'] ?? null, $oldValueData))) {
                            $oldValue[] = $item['val'];
                        }
                    }

                    continue;
                }

                if (!is_numeric($id)) {
                    continue;
                }

                if (in_array($id, $newValueData)) {
                    $newValue[] = $item;
                }

                if (in_array($id, $oldValueData)) {
                    $oldValue[] = $item;
                }
            }
        }
        natsort($newValue);
        natsort($oldValue);

        if (implode(',', array_values($newValue)) === implode(',', array_values($oldValue))) {
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
