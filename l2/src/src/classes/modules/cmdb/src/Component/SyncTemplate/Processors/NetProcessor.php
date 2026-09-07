<?php declare(strict_types = 1);

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage API
 * @author     Oscar Pohl <opohl@i-doit.de>
 * @version    1.12.2
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;

/**
 * Class NetProcessor
 *
 * @package idoit\Module\Api\Model\Cmdb\Category\Processor
 */
class NetProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATS__NET';

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        $layer2assignments = $syncData['properties']['layer2_assignments'][C__DATA__VALUE];

        // @see  API-276  prevent object from assigning itself
        if ($layer2assignments == $objectId) {
            $layer2assignments = $this->dao->get_assigned_layer_2_ids($objectId, true);
        } elseif (is_array($layer2assignments) && ($key = array_search($objectId, $layer2assignments)) !== false) {
            unset($layer2assignments[$key]);
            if (empty($layer2assignments)) {
                $layer2assignments = $this->dao->get_assigned_layer_2_ids($objectId, true);
            }
        }
        $syncData['properties']['layer2_assignments'][C__DATA__VALUE] = $layer2assignments;
        return $syncData;
    }
}
