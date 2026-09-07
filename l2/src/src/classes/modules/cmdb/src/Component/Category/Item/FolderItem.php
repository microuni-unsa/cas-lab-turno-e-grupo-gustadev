<?php

namespace idoit\Module\Cmdb\Component\Category\Item;

use isys_cmdb_dao_object_type;

/**
 * Specific category item class.
 */
class FolderItem extends AbstractItem
{
    public const PREFIX = 'f';

    /**
     * @param int $objectId
     *
     * @return string
     */
    public function getUrl(int $objectId): string
    {
        return "";
    }

    /**
     * @param isys_cmdb_dao_object_type $dao
     * @param int                       $objectId
     *
     * @return bool|null
     */
    public function hasData(isys_cmdb_dao_object_type $dao, int $objectId): ?bool
    {
        if (!count($this->children)) {
            return false;
        }

        foreach ($this->children as $child) {
            if ($child->hasData($dao, $objectId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractItem $id
     *
     * @return $this
     */
    public function addChild(AbstractItem $id): self
    {
        $this->children[] = $id;

        return $this;
    }
}
