<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces;

interface PreMergeModifierInterface
{
    /**
     * @param array $data
     * @param int $objectId
     *
     * @return array
     */
    public function preMergeModify(array $data, int $objectId): array;
}
