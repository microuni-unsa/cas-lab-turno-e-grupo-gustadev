<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

class AuditProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    protected static $categoryConst = 'C__CATG__AUDIT';

    /**
     * @param array $syncData
     * @param int   $objectId
     *
     * @return array
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        if (isset($syncData['properties']['contact'][C__DATA__VALUE])
          && is_numeric($syncData['properties']['contact'][C__DATA__VALUE])
        ) {
            $syncData['properties']['contact'][C__DATA__VALUE] = [$syncData['properties']['contact'][C__DATA__VALUE]];
        }

        if (isset($syncData['properties']['involved'][C__DATA__VALUE])
          && is_numeric($syncData['properties']['involved'][C__DATA__VALUE])
        ) {
            $syncData['properties']['involved'][C__DATA__VALUE] = [$syncData['properties']['involved'][C__DATA__VALUE]];
        }

        return $syncData;
    }
}
