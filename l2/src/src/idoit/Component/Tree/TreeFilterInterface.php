<?php

namespace idoit\Component\Tree;

/**
 * Tree filter interface
 *
 * @package idoit\Component\Tree
 */
interface TreeFilterInterface
{
    /**
     * @param int|null    $parentObjectId
     * @param string $mode Contains either 'logical' or 'physical'.
     * @param int    $objectId
     *
     * @return bool
     */
    public function shouldSkip(?int $parentObjectId, string $mode, int $objectId): bool;
}
