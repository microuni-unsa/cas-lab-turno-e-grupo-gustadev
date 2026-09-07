<?php

namespace idoit\Module\Cmdb\Model\Entry;

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\EntryInterface;

class ObjectCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * @param ObjectEntry $entry
     *
     * @return ObjectCollection
     */
    public function addEntry(ObjectEntry $entry): ObjectCollection
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasEntry($id): bool
    {
        return !empty(array_filter($this->entries, fn (ObjectEntry $item) => $item->getObjectid() === (int)$id));
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function unsetEntry($id): void
    {
        $this->entries = array_filter($this->entries, fn (ObjectEntry $item) => $item->getObjectid() !== (int)$id);
    }

    /**
     * @param $id
     *
     * @return EntryInterface|null
     */
    public function getEntry($id): ?EntryInterface
    {
        return current(array_filter($this->entries, fn (ObjectEntry $item) => $item->getObjectid() === (int)$id));
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        $ids = [];
        foreach ($this->entries as $objectEntry) {
            $ids[] = $objectEntry->getObjectId();
        }
        return $ids;
    }
}
