<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\CustomFields\Popup;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;

/**
 * i-doit
 *
 * Custom fields Category for Property type Link callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.9.4
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Datetime implements DynamicCallbackInterface
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
        return isys_application::instance()->container->get('locales')->fmt_datetime($data, true, false);
    }
}
