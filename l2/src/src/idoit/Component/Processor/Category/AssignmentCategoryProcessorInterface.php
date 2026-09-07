<?php

namespace idoit\Component\Processor\Category;

/**
 * Assignment category processor interface.
 */
interface AssignmentCategoryProcessorInterface extends CategoryProcessorInterface
{
    /**
     * Use this method to SET a list of objects.
     *
     * @param array $ids
     * @param int   $objectId
     *
     * @return void
     */
    public function set(array $ids, int $objectId): void;

    /**
     * Use this method to attach (add) new objects.
     *
     * @param array $ids
     * @param int   $objectId
     *
     * @return void
     */
    public function attach(array $ids, int $objectId): void;

    /**
     * Use this method to detach (remove) objects.
     *
     * @param array $ids
     * @param int   $objectId
     *
     * @return void
     */
    public function detach(array $ids, int $objectId): void;
}
