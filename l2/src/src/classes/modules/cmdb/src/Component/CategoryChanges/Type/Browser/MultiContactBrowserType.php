<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;
use isys_contact_dao_reference;
use isys_format_json as JSON;
use isys_popup_browser_object_ng;
use isys_tenantsettings;

class MultiContactBrowserType extends ObjectBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
        $referenceTable = $property->getData()?->getReferences() ?? [];
        $params = $property->getUi()->getParams();

        return $referenceTable[0] === 'isys_contact' &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !$params[isys_popup_browser_object_ng::C__SECOND_SELECTION];
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int                    $contactId
     *
     * @return array
     * @throws \isys_exception_contact
     * @throws \isys_exception_database
     */
    private function retrieveContactsByContactId(isys_cmdb_dao_category $dao, int $contactId): array
    {
        $contactDaoReference = isys_contact_dao_reference::instance($dao->get_database_component());
        $contactDaoReference->load($contactId);
        $objectIds = array_keys($contactDaoReference->get_data_item_array() ?? []);

        return $this->getContacts($dao, $objectIds);
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param mixed                  $data
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function getContacts(isys_cmdb_dao_category $dao, mixed $data): array
    {
        if (is_string($data) && JSON::is_json_array($data)) {
            $data = JSON::decode($data);
        }

        if (is_array($data)) {
            // @see ID-11514 Process different value types.
            return array_filter(array_map(function ($item) use ($dao) {
                if (is_numeric($item)) {
                    return $dao->obj_get_title_by_id_as_string($item);
                }

                if (is_array($item) && !empty($item['id'])) {
                    return $dao->obj_get_title_by_id_as_string($item['id']);
                }

                return null;
            }, $data));
        }

        if (is_numeric($data)) {
            return $this->retrieveContactsByContactId($dao, (int)$data);
        }

        return [];
    }

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     * @param DefaultData            $currentDataProvider
     * @param DefaultData            $changedDataProvider
     * @param array                  $propertiesAlwaysInLogbook
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    ) {
        $emptyState = isys_tenantsettings::get('gui.empty_value', '-');
        $changedData = $changedDataProvider->getData();
        $currentData = $currentDataProvider->getData();
        $property = $this->getProperty();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $currentContacts = $this->getContacts($dao, $currentData[$tag]);
        $changedContacts = $this->getContacts($dao, $changedData[$tag]);

        natsort($changedContacts);
        natsort($currentContacts);

        if (empty(array_diff($changedContacts, $currentContacts)) && empty(array_diff($currentContacts, $changedContacts))) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => !empty($currentContacts) ? implode(', ', $currentContacts) : $emptyState,
                    self::CHANGES_TO =>  !empty($changedContacts) ? implode(', ', $changedContacts) : $emptyState
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
            self::CHANGES_TO => $changes,
        ];
    }
}
