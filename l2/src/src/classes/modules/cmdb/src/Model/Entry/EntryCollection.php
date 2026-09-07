<?php
namespace idoit\Module\Cmdb\Model\Entry;

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\EntryInterface;

class EntryCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * @param Entry $entry
     *
     * @return CollectionInterface
     */
    public function addEntry(Entry $entry): CollectionInterface
    {
        $this->entries[] = $entry;
        return $this;
    }

    public function hasEntry($id): bool
    {
        return !empty(array_filter($this->entries, fn (Entry $item) => $item->getEntryid() === (int)$id));
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function unsetEntry($id): void
    {
        $this->entries = array_filter($this->entries, fn (Entry $item) => $item->getEntryid() !== (int)$id);
    }

    /**
     * @param $id
     *
     * @return EntryInterface|null
     */
    public function getEntry($id): ?EntryInterface
    {
        return current(array_filter($this->entries, fn (Entry $item) => $item->getEntryid() === (int)$id));
    }


    /**
     * @return array
     */
    public function getIds(): array
    {
        $ids = [];
        foreach ($this->entries as $entry) {
            $ids[] = $entry->getEntryid();
        }
        return $ids;
    }
}
