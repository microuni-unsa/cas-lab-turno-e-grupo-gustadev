<?php declare(strict_types = 1);

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @author     Selcuk Kekec <skekec@i-doit.de>
 * @version    1.10
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * SoftwareAssignmentProcessor
 *
 * @package    idoit\Module\Api\Model\Category
 */
class ApplicationProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__APPLICATION';

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
        if (!array_key_exists('assigned_database_schema', $syncData['properties'])) {
            $syncData['properties']['assigned_database_schema'] = null;
        }

        return $syncData;
    }
}
