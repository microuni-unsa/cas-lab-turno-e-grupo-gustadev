<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_application;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_popup_browser_object_ng;
use isys_tenantsettings;

/**
 * Class SecondSelectObjectBrowserType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
class SecondSelectObjectBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            !$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !!$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
            $params['p_strPopupType'] === 'browser_object_ng';
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
        $requestData = $requestDataProvider->getData();
        $property = $this->getProperty();
        $uiField = $property->getUi()
            ->getId();
        $uiHiddenField = $uiField . '__HIDDEN';
        $uiConfigField = $uiField . '__CONFIG';
        $currentObjectId = $dao->get_object_id();
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $changes = [];
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $oldValueConfig = is_string($requestData[$uiConfigField]) ? isys_format_json::decode($requestData[$uiConfigField]) : [];
        $oldValueId = (int)$oldValueConfig['p_strSelectedID'];
        $oldValue = (string)$oldValueConfig['p_strValue'];
        $newValueId = (int)$requestData[$uiHiddenField];
        $newValue = isys_tenantsettings::get('gui.empty_value', '-');
        $formatSelectionListFormat = $oldValueConfig[isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT];
        [$selectionClass, $selectionMethod] = explode('::', $formatSelectionListFormat);

        if ((!class_exists($selectionClass) || $oldValueId === $newValueId) && !$alwaysInLogbook) {
            return [];
        }

        $selectionClassInstance = $selectionClass::instance(isys_application::instance()->container->get('database'));
        if (!method_exists($selectionClassInstance, $selectionMethod)) {
            return [];
        }

        if ($newValueId > 0) {
            $newValue = (string)$selectionClassInstance->$selectionMethod($newValueId, true);
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
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $alwaysInLogbook = $property->getInfo()->isAlwaysInLogbook();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $uiParams = $property->getUi()->getParams();
        $currentData = $currentDataProvider->getData();
        $changedData = $changedDataProvider->getData();

        // Handle import data slightly different.
        if (is_numeric($changedData[$tag])) {
            $changedId = (int)$changedData[$tag];
        } else {
            $changedId = (int)($changedData[$tag]['ref_id'] ?? $changedData[$tag]['id'] ?? $changedData[$tag]['value'] ?? $changedData[$tag]);
        }

        // Handle import data slightly different.
        if (is_numeric($currentData[$tag])) {
            $currentId = (int)$currentData[$tag];
        } else {
            $currentId = (int)($currentData[$tag]['ref_id'] ?? $currentData[$tag]['id'] ?? $currentData[$tag]['value'] ?? $currentData[$tag]);
        }

        if ($currentId === $changedId) {
            return [];
        }

        $oldValue = $newValue = isys_tenantsettings::get('gui.empty_value', '-');
        $formatSelectionListFormat = $uiParams[isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT];
        [$selectionClass, $selectionMethod] = explode('::', $formatSelectionListFormat);

        if ((!class_exists($selectionClass) || $currentId === $changedId) && !$alwaysInLogbook) {
            return [];
        }

        $selectionClassInstance = $selectionClass::instance(isys_application::instance()->container->get('database'));
        if (!method_exists($selectionClassInstance, $selectionMethod)) {
            return [];
        }

        if ($currentId > 0) {
            $oldValue = (string)$selectionClassInstance->$selectionMethod($currentId, true);
        }

        if ($changedId > 0) {
            $newValue = (string)$selectionClassInstance->$selectionMethod($changedId, true);
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

    public function handleRanking()
    {
        // TODO: Implement handleRanking() method.
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
