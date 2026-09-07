<?php

namespace idoit\Module\Cmdb\Interfaces;

interface ObjectBrowserAssignedEntries
{
    /**
     * @param int|int[] $id
     * @param string $tag
     * @param bool $asId
     *
     * @return CollectionInterface
     */
    public function getAttachedEntries($id, $tag = '', $asId = false): CollectionInterface;
}
