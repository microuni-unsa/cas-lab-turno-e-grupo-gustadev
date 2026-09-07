<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Access;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_helper;

/**
 * i-doit
 *
 * Access Category property "primary_url" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang<qhoang@i-doit.com>
 * @version     1.11.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PrimaryUrl implements DynamicCallbackInterface
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
        if ($data === null) {
            return '';
        }

        // @see ID-10741 Call specific logic to replace additional placeholders.
        if (is_array($extra) && isset($extra['__id__'])) {
            $dao = \isys_cmdb_dao_category_g_access::instance(\isys_application::instance()->container->get('database'));

            // @see ID-11195 Also apply 'isys_helper_link::handle_url_variables'.
            $data = \isys_helper_link::handle_url_variables(
                $dao->format_url($data, $extra['__id__']),
                $extra['__id__']
            );
        }

        return isys_helper::covertUrlsToHtmlLinks($data, true);
    }
}
