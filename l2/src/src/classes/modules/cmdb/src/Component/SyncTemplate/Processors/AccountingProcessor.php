<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreMergeModifierInterface;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;
use isys_tenantsettings;

class AccountingProcessor extends AbstractProcessor implements PreSyncModifierInterface, PreMergeModifierInterface
{
    protected static $categoryConst = 'C__CATG__ACCOUNTING';

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
            $syncData[Config::CONFIG_PROPERTIES]['contact'][C__DATA__VALUE] = [$syncData['properties']['contact'][C__DATA__VALUE]];
        }
        return $syncData;
    }

    /**
     * @param array $syncData
     * @param int   $objectId
     *
     * @return array
     */
    public function preMergeModify(array $syncData, int $objectId): array
    {
        $objectTypeId = $this->dao->get_type_by_object_id($objectId)->get_row_value('isys_obj__isys_obj_type__id');

        if (isys_tenantsettings::get('cmdb.objtype.' . $objectTypeId . '.auto-inventory-no', '') !== '') {
            $data = $this->dao->get_data(null, $objectId)->get_row();
            if (!empty($data)) {
                $syncData[Config::CONFIG_DATA_ID] = $data['isys_catg_accounting_list__id'];
                $syncData[Config::CONFIG_PROPERTIES]['inventory_no'] = $data['isys_catg_accounting_list__inventory_no'];
            }
        }
        return $syncData;
    }
}
