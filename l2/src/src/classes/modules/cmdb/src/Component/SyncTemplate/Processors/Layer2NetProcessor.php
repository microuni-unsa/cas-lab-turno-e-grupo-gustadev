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
 * IpAddressesProcessor
 */
class Layer2NetProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATS__LAYER2_NET';

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        /** @var array $properties */
        $properties = $this->getDao()->get_properties();

        $dialogDao = \isys_cmdb_dao_dialog::instance();
        $dialogDao->set_table('isys_layer2_iphelper_type')
          ->load();

        // We'll validate the provided data.
        foreach ($syncData['properties']['ip_helper_addresses'][C__DATA__VALUE] as &$address) {
            if (is_int($address['type_title'])) {
                // In the case of an passed integer, we'll get the corresponding title (int = entry ID).
                $address['type_title'] = $dialogDao->get_data($address['type_title']);
            }
        }

        return $syncData;
    }
}
