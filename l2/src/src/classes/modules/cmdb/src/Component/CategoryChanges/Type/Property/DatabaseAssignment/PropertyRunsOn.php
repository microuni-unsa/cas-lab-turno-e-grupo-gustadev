<?php
namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\DatabaseAssignment;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\AbstractPropertyType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_cmdb_dao_category;

class PropertyRunsOn extends AbstractPropertyType implements TypeInterface
{
    protected const PROPERTY_CLASS = 'isys_cmdb_dao_category_g_database_assignment';
    protected const PROPERTY_TAG = 'runs_on';

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        return $class === self::PROPERTY_CLASS && $tag === self::PROPERTY_TAG;
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
     * @deprecated
     */
    public function handlePostData(
        string $tag,
        isys_cmdb_dao_category $dao,
        RequestData $requestDataProvider,
        SmartyData $smartyDataProvider,
        array $currentData = [],
        array $propertiesAlwaysInLogbook = []
    ) {
        return [];
    }

    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    ) {
        $changedData = $changedDataProvider->getData();
        $currentEntryId = $dao->get_list_id();
        $currentObjectId = $dao->get_object_id();
        $currentPropertyTag = $this->getCurrentPropertyTag($dao, $tag);

        $oldValue = null;
        $newValue = is_numeric($changedData['runs_on']) ? (int)$changedData['runs_on'] : null;

        // @see ID-12205 Fetch proper reference ID.
        if ($currentEntryId) {
            $query = "SELECT isys_catg_relation_list__isys_obj__id__slave AS runs_on
                FROM isys_cats_database_access_list
                INNER JOIN isys_catg_relation_list ON isys_catg_relation_list__id = isys_cats_database_access_list__isys_catg_relation_list__id
                WHERE isys_cats_database_access_list__id = {$currentEntryId}
                LIMIT 1;";

            $oldValue = (int)$dao->retrieve($query)->get_row_value('runs_on');
        }

        if ($newValue === $oldValue) {
            return [];
        }

        $changes = ChangesData::factory(
            [
                $currentPropertyTag => [
                    self::CHANGES_FROM => $this->fetchRunsOnObject($dao, $oldValue),
                    self::CHANGES_TO => $this->fetchRunsOnObject($dao, $newValue)
                ]
            ],
            $currentObjectId
        );

        return [
            self::CHANGES_CURRENT => $changes,
        ];
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param int|null               $id
     * @return string|null
     * @throws \isys_exception_database
     */
    private function fetchRunsOnObject(isys_cmdb_dao_category $dao, ?int $id): ?string
    {
        if (!$id) {
            return null;
        }

        $query = "SELECT isys_obj__title AS title, isys_obj_type__title AS typeTitle
            FROM isys_catg_relation_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_relation_list__isys_obj__id__master
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_relation_list__isys_obj__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return null;
        }

        return $this->getLanguage()->get_in_text($result['typeTitle'] . ' >> ' . $result['title']);
    }

    /**
     * Not necessary as it is a calculated property
     *
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     *
     * @return null
     */
    public function getChangesWithDefaults(string $tag, isys_cmdb_dao_category $dao)
    {
        return null;
    }
}
