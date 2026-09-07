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
use isys_cmdb_dao_category_g_custom_fields;

/**
 * Class CustomFieldsProcessor
 */
class CustomFieldsProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__CUSTOM_FIELDS';

    /**
     * Modify sync data
     *
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     * @throws \isys_exception_api_validation
     * @throws \Exception
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        // Just to go sure we always have the correct DAO.
        if (!$this->getDao() instanceof isys_cmdb_dao_category_g_custom_fields) {
            return $syncData;
        }

        if (\is_array($syncData['properties'])) {
            $dao = $this->getDao();
            $virtualProperties = [];
            $providesVirtual = $dao->convert_sql_int(defined_or_default('C__PROPERTY__PROVIDES__VIRTUAL', 128));
            $customCategoryId = $dao->convert_sql_id($this->getDao()->get_catg_custom_id());

            // First get the property keys.
            $propertyKeys = array_keys($syncData['properties']);

            // Then make the keys 'secure'.
            $propertyKeys = array_map(function ($key) use ($dao) {
                return $dao->convert_sql_text($key);
            }, $propertyKeys);

            // Check, just to go sure we don't risk a faulty SQL query.
            if (\count($propertyKeys) === 0) {
                return $syncData;
            }

            // And finally use them in the query as comma separated list.
            $properties = implode(',', $propertyKeys);

            $sql = "SELECT isys_property_2_cat__prop_key AS propertyKey
                FROM isys_property_2_cat
                WHERE isys_property_2_cat__prop_key IN ({$properties})
                AND isys_property_2_cat__isysgui_catg_custom__id = {$customCategoryId}
                AND isys_property_2_cat__prop_provides & {$providesVirtual};";

            $result = $dao->retrieve($sql);

            while ($row = $result->get_row()) {
                $virtualProperties[$row['propertyKey']] = 'This property is a "virtual" property and can not contain data.';
            }

            // If any virtual properties were found remove them from $syncData['properties']
            if (\count($virtualProperties)) {
                foreach ($virtualProperties as $propertyToRemove) {
                    unset($syncData['properties'][$propertyToRemove]);
                }
            }
        }

        return $syncData;
    }
}
