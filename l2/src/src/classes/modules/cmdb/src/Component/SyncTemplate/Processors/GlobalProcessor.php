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
 * Class GeneralProcessor
 */
class GlobalProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__GLOBAL';

    /**
     * Modify sync data.
     *
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        // Get request information.
        $generalDao = $this->getDao();

        // @see  API-224  We need to deal with possible wrong references, due to property field definition.
        if ((!isset($syncData['properties']['tag']['value']) || !is_array($syncData['properties']['tag']['value']))
            && $generalDao instanceof \isys_cmdb_dao_category_g_global
        ) {
            // Read the tags of the given object.
            $assignedTagIds = $generalDao->get_assigned_tag($objectId, true);

            if (count($assignedTagIds) === 0) {
                return $syncData;
            }

            // Re-set the tag IDs.
            $syncData['properties']['tag']['value'] = array_map('intval', $assignedTagIds);
        }

        return $syncData;
    }
}
