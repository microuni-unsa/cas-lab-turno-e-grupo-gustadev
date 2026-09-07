<?php

namespace idoit\Model;

/**
 * i-doit Breadcrumb Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @deprecated  Please refer to 'breadcrumb' service. Will be removed in i-doit 40!
 */
class Breadcrumb
{

    /**
     * URL Parameters
     *
     * @var array
     */
    public $parameters = [];

    /**
     * View title
     *
     * @var string title
     */
    public $title;

    /**
     * @param string $title
     * @param string $moduleID
     */
    public function __construct($title, $moduleID, $parameters = [])
    {
        $this->title = $title;
        $this->parameters = $parameters + [
                C__GET__MODULE_ID => $moduleID
            ];
    }

}
