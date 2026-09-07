<?php

namespace idoit\Module\Cmdb\Component\Category\Item;

use isys_cmdb_dao_object_type;

/**
 * Custom category item class.
 */
class CustomItem extends AbstractItem
{
    public const PREFIX = 'c';

    /**
     * @param int $objectId
     *
     * @return string
     */
    public function getUrl(int $objectId): string
    {
        $viewType = (int)($this->getDao()->is_multivalued() ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY);
        $customCategoryId = C__CATG__CUSTOM_FIELDS;

        return "javascript:get_content_by_object({$objectId}, {$viewType}, {$customCategoryId}, '" . C__CMDB__GET__CATG . "', {$this->getId()});";
    }

    /**
     * @param isys_cmdb_dao_object_type $dao
     * @param int                       $objectId
     *
     * @return bool|null
     */
    public function hasData(isys_cmdb_dao_object_type $dao, int $objectId): ?bool
    {
        if ($this->hasData === null) {
            $this->hasData = (bool)$this->getDao()->get_count($objectId);
        }

        return $this->hasData;
    }
}
