<?php

namespace idoit\Module\Cmdb\Model\Matcher\Protocol;

use idoit\Module\Cmdb\Model\Matcher\MatchData;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Matchable
{

    /**
     * @param array $matchKeywords
     *
     * @return MatchData|null
     */
    public function match(array $matchKeywords);
}
