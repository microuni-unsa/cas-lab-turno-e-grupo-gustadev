<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\S\OrganizationMaster;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_helper;

/**
 * i-doit
 *
 * Organization property "website" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Denis Koroliov<d.korolov@i-doit.com>
 * @version     1.17.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Website implements DynamicCallbackInterface
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

        return isys_helper::covertUrlsToHtmlLinks($data, true);
    }
}
