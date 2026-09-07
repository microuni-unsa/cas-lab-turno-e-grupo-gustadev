<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * ChassisDeviceProcessor
 */
class ChassisDeviceProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATS__CHASSIS_DEVICES';

    /**
     * Modify sync data
     *
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        /**
         * @see  API-178  Preparing the necessary structure for assigned slots.
         */
        if (isset($syncData['properties']['assigned_slots']['value']) && is_array($syncData['properties']['assigned_slots']['value'])) {
            foreach ($syncData['properties']['assigned_slots']['value'] as &$value) {
                $value = ['id' => $value];
            }
        }

        return $syncData;
    }
}
