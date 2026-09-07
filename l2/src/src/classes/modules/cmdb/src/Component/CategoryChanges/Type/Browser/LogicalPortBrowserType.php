<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;
use isys_tenantsettings;

/**
 * Class LogicalPortBrowserType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
class LogicalPortBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_cable_connection_ng' &&
            isset($property->getUi()->getParams()['only_log_ports']) &&
            $property->getUi()->getParams()['only_log_ports'];
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
        $uiHiddenField = $uiField . '__HIDDEN';
        $changes = [];

        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);

        $newValueId = (int)$requestData[$uiHiddenField];
        $oldValueId = (int)($smartyData[$uiField]['p_strSelectedID'] ?:
            (is_numeric($smartyData[$uiField]['p_strValue']) ? $smartyData[$uiField]['p_strValue']: null));

        if ($newValueId === $oldValueId) {
            return [];
        }

        $fromData = $oldValueId > 0 ? $dao->get_data($oldValueId)->get_row() : null;
        $currentData = $requestData[C__GET__ID] > 0 ? $dao->get_data($requestData[C__GET__ID])->get_row() : null;
        $toData = $newValueId > 0 ? $dao->get_data($newValueId)->get_row() : null;

        return $this->dataHandlerHelper($tag, $dao, $oldValueId, $newValueId, $currentData, $fromData, $toData);
    }

    /**
     * @param $tag
     * @param $dao
     * @param $oldValueId
     * @param $newValueId
     * @param $currentData
     * @param $fromData
     * @param $toData
     *
     * @return array
     */
    private function dataHandlerHelper($tag, $dao, $oldValueId, $newValueId, $currentData, $fromData, $toData)
    {
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');

        if ($oldValueId > 0) {
            $changes[self::CHANGES_FROM][] = ChangesData::factory(
                [
                    'isys_cmdb_dao_category_g_network_ifacel::title' => [
                        self::CHANGES_FROM => $fromData['isys_catg_log_port_list__title'],
                        self::CHANGES_TO => $fromData['isys_catg_log_port_list__title']
                    ]
                ],
                $fromData['isys_obj__id']
            );
            $changes[self::CHANGES_FROM][] = ChangesData::factory(
                [
                    $currentPropertyTag => [
                        self::CHANGES_FROM => $currentData['isys_obj__title'] . " {$separator} " . $currentData['isys_catg_log_port_list__title'],
                        self::CHANGES_TO => $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED')
                    ]
                ],
                $fromData['isys_obj__id']
            );

            $this->setFromObjectId($fromData['isys_obj__id']);
        }

        if ($newValueId > 0) {
            $previousToTitle = $emptyValue;

            if (isset($toData['isys_catg_log_port_list__isys_catg_log_port_list__id']) && $toData['isys_catg_log_port_list__isys_catg_log_port_list__id']) {
                $previousTodata = $dao->get_data($toData['isys_catg_log_port_list__isys_catg_log_port_list__id'])->get_row();
                $previousToTitle = $previousTodata['isys_obj__title'] . " {$separator} " . $previousTodata['isys_catg_log_port_list__title'];
            }

            $changes[self::CHANGES_TO][] = ChangesData::factory(
                [
                    'isys_cmdb_dao_category_g_network_ifacel::title' => [
                        self::CHANGES_FROM => $toData['isys_catg_log_port_list__title'],
                        self::CHANGES_TO => $toData['isys_catg_log_port_list__title']
                    ]
                ],
                $toData['isys_obj__id']
            );

            $changes[self::CHANGES_TO][] = ChangesData::factory(
                [
                    $currentPropertyTag => [
                        self::CHANGES_FROM => $previousToTitle,
                        self::CHANGES_TO => $currentData['isys_obj__title'] . " {$separator} " . $currentData['isys_catg_log_port_list__title']
                    ]
                ],
                $toData['isys_obj__id']
            );

            $this->setToObjectId($toData['isys_obj__id']);
        }

        $changes[self::CHANGES_CURRENT][] = ChangesData::factory(
            [
                'isys_cmdb_dao_category_g_network_ifacel::title' => [
                    self::CHANGES_FROM => $currentData['isys_catg_log_port_list__title'],
                    self::CHANGES_TO => $currentData['isys_catg_log_port_list__title']
                ]
            ],
            $currentObjectId
        );
        $changes[self::CHANGES_CURRENT][] = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $fromData['isys_catg_log_port_list__title']
                        ? ($fromData['isys_obj__title'] . " {$separator} " . $fromData['isys_catg_log_port_list__title'])
                        : '',
                    self::CHANGES_TO => isset($toData['isys_catg_log_port_list__title'])
                        ? ($toData['isys_obj__title'] . " {$separator} " . $toData['isys_catg_log_port_list__title'])
                        : $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED')
                ]
            ],
            $currentObjectId
        );

        return $changes;
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
        $currentData = $currentDataProvider->getData();
        $changedData = $changedDataProvider->getData();
        $property = $this->getProperty();

        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);

        // Handle import data slightly different.
        if (is_numeric($changedData[$tag])) {
            $newValueId = (int)$changedData[$tag];
        } else {
            $newValueId = (int)($changedData[$tag]['ref_id'] ?? $changedData[$tag]['logPortID'] ?? $changedData[$tag]);
        }

        // Handle import data slightly different.
        if (is_numeric($currentData[$tag])) {
            $oldValueId = (int)$currentData[$tag];
        } else {
            $oldValueId = (int)($currentData[$tag]['ref_id'] ?? $currentData[$tag]['logPortID'] ?? $currentData[$tag]);
        }

        if ($newValueId === $oldValueId) {
            return [];
        }

        $fromData = $oldValueId > 0 ? $dao->get_data($oldValueId)->get_row() : null;
        $currentData = $dao->get_data($dao->get_list_id())->get_row();
        $toData = $newValueId > 0 ? $dao->get_data($newValueId)->get_row() : null;

        return $this->dataHandlerHelper($tag, $dao, $oldValueId, $newValueId, $currentData, $fromData, $toData);
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
        return null;
    }
}
