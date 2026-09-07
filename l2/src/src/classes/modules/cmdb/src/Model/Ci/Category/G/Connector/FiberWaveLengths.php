<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Connector;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;

/**
 * i-doit
 *
 * Connector Category property "fiber_wave_lengths" callback.
 *
 * @see         ID-10752 Implement callback for 'fiber wave length'
 * @package     i-doit
 * @subpackage  Cmdb
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class FiberWaveLengths implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param mixed  $extra
     *
     * @return mixed
     */
    public static function render($data, $extra = null)
    {
        if (empty($data)) {
            return '';
        }

        $isCommaSeparated = str_contains($data, ',');

        if ($isCommaSeparated) {
            $data = array_map('intval', explode(',', strip_tags_deep($data)));
        } else {
            $data = str_replace('</li><li>', "</li>\n<li>", $data);
            $data = array_map('intval', explode("\n", strip_tags_deep($data)));
        }

        $return = [];
        $dao = \isys_cmdb_dao_category_g_connector::instance(\isys_application::instance()->container->get('database'));

        foreach ($data as $connectorId) {
            $result = $dao->get_assigned_fiber_wave_lengths(null, $connectorId);
            $colors = [];

            while ($row = $result->get_row()) {
                $colors[] = $row['isys_fiber_wave_length__title'];
            }

            $return[] = implode(', ', $colors);
        }

        if ($isCommaSeparated) {
            return implode(', ', $return);
        }

        return '<ul><li>' . implode('</li><li>', $return) . '</li></ul>';
    }
}
