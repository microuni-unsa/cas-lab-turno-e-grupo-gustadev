<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces;

interface PreSyncModifierInterface
{
    /**
     * @param array $data
     * @param int $objectId
     *
     * @return array
     */
    public function preSyncModify(array $data, int $objectId): array;
}
