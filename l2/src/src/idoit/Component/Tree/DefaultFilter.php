<?php

namespace idoit\Component\Tree;

/**
 * Default tree filter.
 *
 * @package idoit\Component\Tree
 */
class DefaultFilter implements TreeFilterInterface
{
    /**
     * @param int|null    $parentObjectId
     * @param string $mode
     * @param int    $objectId
     *
     * @return bool
     */
    public function shouldSkip(?int $parentObjectId, string $mode, int $objectId): bool
    {
        return false;
    }
}
