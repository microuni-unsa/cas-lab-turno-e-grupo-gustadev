<?php

namespace idoit\Module\Cmdb\Interfaces;

interface CollectionInterface
{
    /**
     * @return array
     */
    public function getEntries(): array;

    /**
     * @param $id
     *
     * @return EntryInterface|null
     */
    public function getEntry($id): ?EntryInterface;

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasEntry($id): bool;
}
