<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Helper\Unserialize;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_application;
use isys_callback;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_request;
use isys_tenantsettings;

/**
 * Class DialogDataType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DialogDataType extends AbstractType implements TypeInterface
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
        $params = $property->getUi()->getParams();
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG && is_array($params) && isset($params['p_arData']);
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
        $currentObjectId = $dao->get_object_id();
        $currentEntryId = $dao->get_list_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        if (!isset($smartyData[$uiField])) {
            return [];
        }

        $newValueId = trim($requestData[$uiField]);
        $oldValueId = trim($smartyData[$uiField]['p_strSelectedID']);
        $rawData = $smartyData[$uiField]['p_arData'] ?? [];

        if (empty($rawData) || $newValueId === $oldValueId || ($newValueId === '' && $oldValueId == -1) || ($oldValueId === '' && $newValueId == -1)) {
            return [];
        }

        return $this->dataHandlerHelper($currentObjectId, $currentPropertyTag, $rawData, $oldValueId, $newValueId, $currentEntryId ?: null);
    }

    /**
     * @param          $currentObjectId
     * @param          $currentPropertyTag
     * @param          $arData
     * @param          $oldValueId
     * @param          $newValueId
     * @param int|null $entryId
     * @return array
     */
    private function dataHandlerHelper($currentObjectId, $currentPropertyTag, $arData, $oldValueId, $newValueId, ?int $entryId = null)
    {
        $newValue = $oldValue = isys_tenantsettings::get('gui.empty_value', '-');

        if (is_string($arData)) {
            $data = isys_format_json::is_json_array($arData)
                ? isys_format_json::decode($arData)
                : Unserialize::toArray($arData);
        } elseif ($arData instanceof isys_callback) {
            $request = new isys_request();
            $request->set_object_id($currentObjectId);
            if ($entryId !== null) {
                $request->set_category_data_id($entryId);
            }
            $data = $arData->execute($request);
        } else {
            $data = $arData;
        }
        $lang = isys_application::instance()->container->get('language');

        if (($oldValueId >= 0 || $oldValueId !== '') && is_scalar($oldValueId)) {
            foreach ($data as $value) {
                if (!is_array($value)) {
                    if (isset($data[$oldValueId])) {
                        $oldValue = html_entity_decode($lang->get($data[$oldValueId]));
                        break;
                    } elseif (in_array($oldValueId, $data, true)) {
                        // @see ID-11162, ID-11234 and API-576 we needed to check the contents of the array, instead of the keys.
                        $oldValue = $oldValueId;
                        break;
                    }
                }

                // @see ID-11105 needs to check if $value is an array otherwise it could be possible that we retrieve a wrong value from a string
                if (is_array($value) && isset($value[$oldValueId])) {
                    $oldValue = $lang->get($value[$oldValueId]);
                    break;
                }
            }
        }

        if (($newValueId >= 0 || $newValueId !== '') && is_scalar($newValueId)) {
            foreach ($data as $value) {
                if (!is_array($value)) {
                    if (isset($data[$newValueId])) {
                        $newValue = html_entity_decode($lang->get($data[$newValueId]));
                        break;
                    } elseif (in_array($newValueId, $data, true)) {
                        // @see ID-11162, ID-11234 and API-576 we needed to check the contents of the array, instead of the keys.
                        $newValue = $newValueId;
                        break;
                    }
                }

                // @see ID-11105 needs to check if $value is an array otherwise it could be possible that we retrieve a wrong value from a string
                if (is_array($value) && isset($value[$newValueId])) {
                    $newValue = $lang->get($value[$newValueId]);
                    break;
                }
            }
        }

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
     * @param DefaultData            $currentDataProvider
     * @param DefaultData            $changedDataProvider
     * @param array                  $propertiesAlwaysInLogbook
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
        $arData = $property->getUi()->getParams()['p_arData'] ?? [];
        $currentObjectId = $dao->get_object_id();
        $currentEntryId = $dao->get_list_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValueId = $changedData[$tag];
        $oldValueId = $currentData[$tag];

        if (is_string($newValueId) && str_starts_with($newValueId, 'LC_')) {
            $newValueId = isys_application::instance()->container->get('language')->get($newValueId);
        }

        if (is_string($oldValueId) && str_starts_with($oldValueId, 'LC_')) {
            $oldValueId = isys_application::instance()->container->get('language')->get($oldValueId);
        }

        if (empty($arData) || $newValueId === $oldValueId || ($newValueId === null && $oldValueId == -1) || ($oldValueId === null && $newValueId == -1)) {
            return [];
        }

        return $this->dataHandlerHelper($currentObjectId, $currentPropertyTag, $arData, $oldValueId, $newValueId, $currentEntryId ?: null);
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
        $rawData = $property->getUi()->getParams()['p_arData'] ?? [];
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $emptyState = isys_tenantsettings::get('gui.empty_value', '-');

        if (is_string($rawData)) {
            $data = isys_format_json::is_json_array($rawData)
                ? isys_format_json::decode($rawData)
                : Unserialize::toArray($rawData);
        } elseif ($rawData instanceof isys_callback) {
            $data = $rawData->execute();
        } else {
            $data = $rawData;
        }

        $defaultValue = $data[$defaultValue] ?? $emptyState;

        return ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $emptyState,
                    self::CHANGES_TO => $defaultValue
                ]
            ],
            $currentObjectId
        );
    }
}
