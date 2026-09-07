<?php

namespace idoit\Module\Cmdb\Model\Entry;

use idoit\Module\Cmdb\Interfaces\EntryInterface;

abstract class AbstractCollection
{
    /**
     * @var EntryInterface[]
     */
    public $entries = [];

    /**
     * @return EntryInterface[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param $id
     *
     * @return EntryInterface|null
     */
    abstract public function getEntry($id): ?EntryInterface;

    /**
     * @param $id
     *
     * @return bool
     */
    abstract public function hasEntry($id): bool;

    /**
     * @param int $id
     *
     * @return void
     */
    abstract public function unsetEntry($id): void;

    /**
     * @return array
     */
    abstract public function getIds(): array;
}
