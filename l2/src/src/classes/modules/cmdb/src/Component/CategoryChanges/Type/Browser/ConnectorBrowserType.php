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
use isys_cmdb_dao_category_g_connector;
use isys_tenantsettings;

/**
 * Class ConnectorBrowserType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
class ConnectorBrowserType extends AbstractBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
            (!isset($property->getUi()->getParams()['only_log_ports']) || !$property->getUi()->getParams()['only_log_ports']);
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
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);
        $uiField = $property->getUi()->getId();
        $uiParams = $property->getUi()->getParams();
        $uiHiddenField = $uiField . '__HIDDEN';
        $changes = $currentEntry = [];

        $currentEntryId = $requestData[C__GET__ID];

        if (!$dao instanceof isys_cmdb_dao_category_g_connector) {
            $data = $dao->get_data($currentEntryId)->get_row();
            $currentEntryId = $data['isys_catg_connector_list__id'];
        }

        $oldValueOutput = $newValueOutput = isys_tenantsettings::get('gui.empty_value', '-');
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);
        $linkedProperty = $property->getInfo()->getLinkedProperty() ?? null;

        if ($linkedProperty) {
            [$linkedPropertyClass, $linkedPropertyTag] = explode('::', $linkedProperty);
        }

        $newValueId = (int)$requestData[$uiHiddenField];
        $oldValueId = (int)($smartyData[$uiField]['p_strSelectedID'] ?:
            (is_numeric($smartyData[$uiField]['p_strValue']) ? $smartyData[$uiField]['p_strValue']: null));

        if ($newValueId === 0 && $oldValueId === 0) {
            return [];
        }

        return $this->handleConnector($dao, $currentPropertyTag, $currentEntryId, $linkedProperty, $newValueId, $oldValueId);
    }

    /**
     * @param $dao
     * @param $currentPropertyTag
     * @param $currentEntryId
     * @param $linkedProperty
     * @param $newValueId
     * @param $oldValueId
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function handleConnector($dao, $currentPropertyTag, $currentEntryId, $linkedProperty, $newValueId, $oldValueId)
    {
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        $cableConnectionDao = isys_cmdb_dao_category_g_connector::instance(isys_application::instance()->container->get('database'));
        $currentEntry = $newValue = $oldValue = $changes = [];
        $oldValueOutput = $newValueOutput = isys_tenantsettings::get('gui.empty_value', '-');

        if ($linkedProperty) {
            [$linkedPropertyClass, $linkedPropertyTag] = explode('::', $linkedProperty);
        }

        if ($currentEntryId) {
            $currentEntry = $cableConnectionDao->getConnectorNameByConnectorId($currentEntryId, false);
            if ($linkedProperty !== null && isset($linkedPropertyTag) && !empty($currentEntry)) {
                $currentChanges[self::CHANGES_CURRENT][] = ChangesData::factory(
                    $this->setChanges($linkedProperty, $currentEntry['connector'], $currentEntry['connector']),
                    $dao->get_object_id()
                );
            }
        }

        if ($newValueId === $oldValueId && $this->getBackwardPropertyTag() !== null) {
            $connectorData = $cableConnectionDao->getConnectorNameByConnectorId($newValueId, false);

            if ($connectorData !== null) {
                if ($linkedProperty !== null && isset($linkedPropertyTag) && !empty($currentEntry)) {
                    $changes[self::CHANGES_TO][] = ChangesData::factory(
                        $this->setChanges($linkedProperty, $currentEntry['connector'], $currentEntry['connector']),
                        $connectorData['objID']
                    );
                }

                $this->setToObjectId($connectorData['objID']);
                $changes[self::CHANGES_TO][] = $this->handlePostDataHelper($dao, $connectorData['objID'], $connectorData, [], self::CHANGES_BOTH);
            }
            return $changes;
        }

        if ($oldValueId > 0 &&
            $oldValueId !== $newValueId &&
            $this->getBackwardPropertyTag() !== null
        ) {
            $connectorData = $cableConnectionDao->getConnectorNameByConnectorId($oldValueId, false);

            if ($connectorData !== null) {
                if ($linkedProperty !== null && isset($linkedPropertyTag)) {
                    $changes[self::CHANGES_FROM][] = ChangesData::factory(
                        $this->setChanges($linkedProperty, $connectorData['connector'], $connectorData['connector']),
                        $connectorData['objID']
                    );
                }

                $changes[self::CHANGES_FROM][] = $this->handlePostDataHelper($dao, $connectorData['objID'], $connectorData, $currentEntry, self::CHANGES_FROM);
                $this->setFromObjectId($connectorData['objID']);
                unset($connectorData['id'], $connectorData['objID'], $connectorData['type'], $connectorData['connectorID']);
                $oldValueOutput = implode($separator, $connectorData);
            }
        }

        if ($newValueId > 0 &&
            $newValueId !== $oldValueId
        ) {
            $connectorData = $cableConnectionDao->getConnectorNameByConnectorId($newValueId, false);
            $oldConnectorData = $cableConnectionDao->getAssignedObjectByConnectorId($connectorData['connectorID'])->get_row() ?? $oldValue;
            if ($connectorData !== null) {

                if ($linkedProperty !== null && isset($linkedPropertyTag)) {
                    $changes[self::CHANGES_TO][] = ChangesData::factory(
                        $this->setChanges($linkedProperty, $connectorData['connector'], $connectorData['connector']),
                        $connectorData['objID']
                    );
                }

                $changes[self::CHANGES_TO][] = $this->handlePostDataHelper($dao, $connectorData['objID'], $currentEntry, $oldConnectorData, self::CHANGES_TO);
                $this->setToObjectId($connectorData['objID']);
                unset($connectorData['id'], $connectorData['objID'], $connectorData['type'], $connectorData['connectorID']);
                $newValueOutput = implode($separator, $connectorData);
            }
        }

        $currentChanges[self::CHANGES_CURRENT][] =
            ChangesData::factory(
                [
                    $currentPropertyTag => [
                        self::CHANGES_FROM => $oldValueOutput,
                        self::CHANGES_TO   => $newValueOutput
                    ]
                ],
                $dao->get_object_id()
            );

        $changes =  array_merge($currentChanges, $changes);

        return $changes;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int                    $referenceId
     * @param array                  $connectorData
     * @param array|null             $oldConnectorData
     * @param string                 $direction
     *
     * @return ChangesData
     */
    private function handlePostDataHelper(isys_cmdb_dao_category $dao, int $referenceId, array $connectorData, ?array $oldConnectorData = [], $direction = self::CHANGES_FROM)
    {
        $backwardProperty = (string)$this->getProperty()->getInfo()->getBackwardProperty();
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $changes = [];
        $outputOld = $outputNew = $emptyValue;

        if (!empty($connectorData)) {
            unset($connectorData['id'], $connectorData['objID'], $connectorData['type'], $connectorData['connectorID']);
            $outputNew = implode($separator, $connectorData);
        }

        if (!empty($oldConnectorData)) {
            unset($oldConnectorData['id'], $oldConnectorData['objID'], $oldConnectorData['type'], $oldConnectorData['connectorID']);
            $outputOld = implode($separator, $oldConnectorData);
        }

        switch ($direction) {
            case self::CHANGES_FROM:
                $changes = $this->setChanges(
                    $backwardProperty,
                    $outputOld,
                    $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED')
                );
                break;
            case self::CHANGES_TO:
                $changes = $this->setChanges(
                    $backwardProperty,
                    $emptyValue,
                    $outputNew
                );
                break;
            case self::CHANGES_BOTH:
                $changes = $this->setChanges(
                    $backwardProperty,
                    $outputNew,
                    $outputNew
                );
                break;
        }

        return ChangesData::factory($changes, $referenceId);
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
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $currentEntryId = $dao->get_list_id();

        if (!$dao instanceof isys_cmdb_dao_category_g_connector) {
            $data = $dao->get_data($currentEntryId)->get_row();
            $currentEntryId = $data['isys_catg_connector_list__id'];
        }

        $this->setBackwardPropertyTag($property->getInfo()->getBackwardProperty() ?: null);
        $linkedProperty = $property->getInfo()->getLinkedProperty() ?? null;

        // Handle import data slightly different.
        if (is_numeric($changedData[$tag])) {
            $newValueId = (int)$changedData[$tag];
        } else {
            $newValueId = (int)($changedData[$tag]['ref_id'] ?? $changedData[$tag]['id'] ?? $changedData[$tag]);
        }

        // Handle import data slightly different.
        if (is_numeric($currentData[$tag])) {
            $oldValueId = (int)$currentData[$tag];
        } else {
            $oldValueId = (int)($currentData[$tag]['ref_id'] ?? $currentData[$tag]['id'] ?? $currentData[$tag]);
        }

        if ($newValueId === $oldValueId) {
            return [];
        }

        return $this->handleConnector($dao, $currentPropertyTag, $currentEntryId, $linkedProperty, $newValueId, $oldValueId);
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
