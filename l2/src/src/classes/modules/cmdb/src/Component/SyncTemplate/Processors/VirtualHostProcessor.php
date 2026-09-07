<?php declare(strict_types = 1);

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.11
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * Class VirtualHostProcessor
 *
 * @package idoit\Module\Api\Model\Cmdb\Category\Processor
 */
class VirtualHostProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__VIRTUAL_HOST';

    /**
     * Modify sync data.
     *
     * @param array $syncData
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function preSyncModify(array $syncData, ?int $objectId = null): array
    {
        // Get request information.
        $virtualHostDao = $this->getDao();

        if (isset($syncData['data_id']) && is_numeric($syncData['data_id'])) {
            $virtualHostData = $virtualHostDao
                ->get_data($syncData['data_id'])
                ->get_row();

            if (empty($virtualHostData)) {
                return $syncData;
            }

            /*
             * @see  API-228  If no license_server was passed, the sync data needs to remain empty.
             * The problem here is that the ID of isys_connection gets filled in, but the sync method will use this as object ID.
             *
             * By the way: we can't use isset() here, since the value might be explicitly NULL to remove the reference.
             */
            if (!array_key_exists('license_server', $syncData['properties'])) {
                // In this case the parameter was never set.
                $syncData['properties']['license_server'][C__DATA__VALUE] = $virtualHostData['licence_server_connection__object'];
            }

            // @see  API-228  Same as above
            if (!array_key_exists('administration_service', $syncData['properties'])) {
                // In this case the parameter was never set.
                $syncData['properties']['administration_service'][C__DATA__VALUE] = $virtualHostData['administration_service_connection__object'];
            }
        }

        return $syncData;
    }
}
