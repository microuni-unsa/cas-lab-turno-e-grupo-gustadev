<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_application;
use isys_cmdb_dao_category;
use isys_tenantsettings;

/**
 * Class DateTimeType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DateTimeType extends AbstractType implements TypeInterface
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
        $uiParams = $property->getUi()->getParams();
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DATETIME &&
            ((isset($uiParams['p_strPopupType']) && $uiParams['p_strPopupType'] === 'calendar') ||
            (isset($uiParams['popup']) && $uiParams['popup'] === 'calendar')) &&
            !!$uiParams['p_bReadonly'] === false;
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
        $uiFieldHidden = $uiField . '__HIDDEN';
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValue = $requestData[$uiFieldHidden];
        $oldValue = $smartyData[$uiField]['p_strValue'];

        return $this->dataHandlerHelper($currentObjectId, $currentPropertyTag, $oldValue, $newValue);
    }

    /**
     * @param $currentObjectId
     * @param $currentPropertyTag
     * @param $oldValue
     * @param $newValue
     *
     * @return array
     * @throws \Exception
     */
    private function dataHandlerHelper($currentObjectId, $currentPropertyTag, $oldValue, $newValue)
    {
        if ($newValue === $oldValue) {
            return [];
        }

        $oldValue = strtotime($oldValue) ?: '';
        $newValue = strtotime($newValue) ?: '';

        // @see API-433 resolve data to date format
        if ($oldValue !== '') {
            $oldValue = isys_application::instance()->container->get('locales')->fmt_datetime(date('Y-m-d H:i:s', $oldValue));
        }

        if ($newValue !== '') {
            $newValue = isys_application::instance()->container->get('locales')->fmt_datetime(date('Y-m-d H:i:s', $newValue));
        }

        // @see ID-10890 Final check, if there are any changes.
        if ($oldValue === $newValue && !$this->getProperty()->getInfo()->isAlwaysInLogbook()) {
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
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $newValue = (string)$changedData[$tag];
        $oldValue = (string)$currentData[$tag];

        return $this->dataHandlerHelper($currentObjectId, $currentPropertyTag, $oldValue, $newValue);
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
