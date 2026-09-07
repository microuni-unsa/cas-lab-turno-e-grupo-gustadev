<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * ClusterMembersProcessor
 */
class ClusterMembersProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__CLUSTER_MEMBERS';

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        $connected_object = $syncData['properties']['member'][C__DATA__VALUE];
        if (is_array($connected_object) && key_exists(0, $connected_object)) {
            $syncData['properties']['member'][C__DATA__VALUE] = $connected_object[0];
        }
        return $syncData;
    }
}
