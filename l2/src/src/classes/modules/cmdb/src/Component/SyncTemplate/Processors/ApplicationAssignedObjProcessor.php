<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * Class ModelProcessor
 */
class ApplicationAssignedObjProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATS__APPLICATION_ASSIGNED_OBJ';

    /**
       * Modify sync data
       *
       * @param array $syncData
       * @param int$objectId
       *
       * @return array
       */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        if (!array_key_exists('assigned_database_schema', $syncData['properties'])) {
            $syncData['properties']['assigned_database_schema'] = null;
        }
        if (!array_key_exists('assigned_it_service', $syncData['properties'])) {
            $syncData['properties']['assigned_it_service'] = null;
        }

        return $syncData;
    }
}
