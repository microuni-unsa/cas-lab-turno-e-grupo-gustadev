<?php

namespace idoit\Module\Cmdb\Model\Entry;

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\EntryInterface;

class CollectionDataExtractor
{
    /**
     * @param string              $propertyKey
     * @param CollectionInterface $collection
     *
     * @return array
     */
    public static function extractPropertyAsArray(string $propertyKey, CollectionInterface $collection): array
    {
        $entries = $collection->getEntries();
        $return = [];
        foreach ($entries as $entry) {
            if (!$entry instanceof EntryInterface) {
                continue;
            }

            if (property_exists($entry, $propertyKey)) {#
                $method = 'get' . ucfirst($propertyKey);
                $return[] = $entry->{$method}();
                continue;
            }

            $data = $entry->getData();
            if ($data->offsetExists($propertyKey)) {
                $return[] = $data->offsetGet($propertyKey);
            }
        }

        return $return;
    }
}
