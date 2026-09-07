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

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreMergeModifierInterface;

/**
 * Class ModelProcessor
 */
class ModelProcessor extends AbstractProcessor implements PreMergeModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__MODEL';

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preMergeModify(array $syncData, int $objectId): array
    {
        // Initialize needed data
        $title = $syncData['properties']['title'][C__DATA__VALUE];
        $manufacturer = $syncData['properties']['manufacturer'][C__DATA__VALUE];

        // Check whether processing is necessary or not
        if (empty($title) && empty($manufacturer)) {
            return $syncData;
        }

        // Check whether creation is requested without setted manufacturer but title
        if (empty($manufacturer) && !empty($title)) {
            $syncData['properties']['title'][C__DATA__VALUE] = null;
        }

        return $syncData;
    }
}
