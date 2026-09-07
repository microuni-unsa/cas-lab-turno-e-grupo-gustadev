<?php declare(strict_types = 1);

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * IpProcessor
 */
class IpProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__IP';

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
         * @see API-282 The property should contain a 'yes' or 'no' value, depending on if it's the default gateway.
         */
        if (!array_key_exists('use_standard_gateway', $syncData['properties'])) {
            $syncData['properties']['use_standard_gateway'][C__DATA__VALUE] = 0;
        }

        return $syncData;
    }
}
