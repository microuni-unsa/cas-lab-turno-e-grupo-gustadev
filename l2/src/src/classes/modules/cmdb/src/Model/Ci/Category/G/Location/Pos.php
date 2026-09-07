<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Location;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;

/**
 * i-doit
 *
 * Callback renderer for 'position in rack'.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Pos implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param $data
     * @param $extra
     *
     * @return string
     * @throws \Exception
     */
    public static function render($data, $extra = null)
    {
        if (!is_string($data) || trim($data) === '') {
            return '';
        }

        $propData = self::parseDataToArray($data);

        if ($propData['option'] !== C__RACK_INSERTION__HORIZONTAL) {
            return "Slot #{$propData['position']}";
        }

        $to = null;

        if ($propData['obj_ru'] === 1) {
            $from = $propData['rack_sorting'] === 'asc'
                ? $propData['position']
                : $propData['rack_ru'] - $propData['position'] + 1;
        } else {
            if ($propData['rack_sorting'] === 'asc') {
                $from = $propData['position'];
                $to = $from + $propData['obj_ru'] - 1;
            } else {
                $to = $propData['rack_ru'] - $propData['position'] + 1;
                $from = $to - $propData['obj_ru'] + 1;
            }
        }

        $rackUnitAbbreviation = isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__RACKUNITS_ABBR');

        if ($to === null) {
            return "{$rackUnitAbbreviation} {$from}" . self::getPositionExceedsRackWarning($propData['rack_ru'], $from);
        }

        return "{$rackUnitAbbreviation} {$from} &rarr; {$to}" . self::getPositionExceedsRackWarning($propData['rack_ru'], $from, $to);
    }

    /**
     * @param int      $rackHeight
     * @param int      $from
     * @param int|null $to
     *
     * @return string
     */
    private static function getPositionExceedsRackWarning(int $rackHeight, int $from, ?int $to = null): string
    {
        // Check if the 'from' position is out of bounds
        if ($from < 1 || $from > $rackHeight) {
            return self::generateExceedsWarning();
        }

        // Check if the 'to' position is out of bounds (if provided)
        if ($to !== null && ($to < 1 || $to > $rackHeight)) {
            return self::generateExceedsWarning();
        }

        // No issues, return an empty string
        return '';
    }

    /**
     * @return string
     */
    private static function generateExceedsWarning(): string
    {
        $iconSource = isys_application::instance()->www_path . 'images/axialis/basic/button-info-red.svg';
        $exceedMessage = isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__LOCATION_POS_EXCEEDS_RACK');

        return sprintf('<img src="%s" class="vam ml5" data-tooltip="1" title="%s" />', $iconSource, $exceedMessage);
    }

    /**
     * Parses a string in the format "key1:value1,key2:value2" into an associative array.
     *
     * @param string $data
     *
     * @return array
     */
    private static function parseDataToArray(string $data): array
    {
        if (empty($data) || !is_string($data)) {
            return [];
        }

        $result = [];
        $pairs = explode(',', $data);

        foreach ($pairs as $pair) {
            [$key, $value] = explode(':', $pair, 2);

            $result[$key] = is_numeric($value) ? (int)$value : $value;
        }

        return $result;
    }
}
