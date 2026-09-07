<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\Interfaces\PreSyncModifierInterface;
use isys_format_json;

/**
 * SlaProcessor
 *
 * @package    idoit\Module\Api\Model\Category
 */
class SlaProcessor extends AbstractProcessor implements PreSyncModifierInterface
{
    /**
     * @var string
     */
    protected static $categoryConst = 'C__CATG__SLA';

    /**
     * @param array $syncData
     * @param int $objectId
     *
     * @return array
     */
    public function preSyncModify(array $syncData, int $objectId): array
    {
        $days = bindec($syncData['properties']['days']['value']);
        $timeperiods = [
            64 => 'monday_time',
            32 => 'tuesday_time',
            16 => 'wednesday_time',
            8  => 'thursday_time',
            4  => 'friday_time',
            2  => 'saturday_time',
            1  => 'sunday_time'
        ];

        foreach ($timeperiods as $dayValue => $property) {
            $value = $syncData['properties'][$property]['value'];
            if (!isset($value)) {
                $days = $days & ~$dayValue;
                $syncData['properties'][$property]['value'] = isys_format_json::encode([
                    'from' => 0,
                    'to'   => 0
                ]);
                continue;
            } elseif (isys_format_json::is_json_array($value)) {
                $decoded = isys_format_json::decode($value);
                if (isset($decoded['from']) && $decoded['from'] > 0 && isset($decoded['to']) && $decoded['to'] > 0) {
                    $days = $days | $dayValue;
                }
                continue;
            }

            preg_match_all('/\d\d:\d\d/', $value, $matches);
            $from = $matches[0][0];
            $to = $matches[0][1];

            $syncData['properties'][$property]['value'] = isys_format_json::encode([
                'from' => $this->dao::calculate_time_to_seconds($from),
                'to'   => $this->dao::calculate_time_to_seconds($to)
            ]);

            $days = $days | $dayValue;
        }
        $syncData['properties']['days']['value'] = decbin($days);

        return $syncData;
    }
}
