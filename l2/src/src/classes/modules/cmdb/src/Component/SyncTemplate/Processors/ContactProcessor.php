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
 * Class ContactProcessor
 */
class ContactProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__CONTACT';

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
        // @see API-264
        if (isset($syncData['properties']['contact_object']) && !isset($syncData['properties']['contact'])) {
            $syncData['properties']['contact'] = $syncData['properties']['contact_object'];
        }
        return $syncData;
    }
}
