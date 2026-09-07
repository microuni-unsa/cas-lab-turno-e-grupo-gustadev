<?php

namespace idoit\Module\Cmdb\Component\Category\Item;

use isys_cmdb_dao_object_type;
use isys_notify;
use Throwable;

/**
 * Specific category item class.
 */
class SpecificItem extends AbstractItem
{
    public const PREFIX = 's';

    /**
     * @param int $objectId
     *
     * @return string
     */
    public function getUrl(int $objectId): string
    {
        $viewType = (int)($this->getDao()->is_multivalued() ? C__CMDB__VIEW__LIST_CATEGORY : C__CMDB__VIEW__CATEGORY);

        return "javascript:get_content_by_object({$objectId}, {$viewType}, {$this->getId()}, '" . C__CMDB__GET__CATS . "');";
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
            try {
                $this->hasData = $dao->check_category($objectId, get_class($this->getDao()), $this->getId(), $this->table);
            } catch (Throwable $l_exception) {
                isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
            }
        }

        return $this->hasData;
    }
}
