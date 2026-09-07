<?php

/**
 * i-doit category data value
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_data_multivalue extends isys_cmdb_dao_category_data_value
{

    /**
     * Category value
     *
     * @var array
     */
    public $m_value = [];

    /**
     * @param $value
     *
     * @return $this
     */
    public function addValue($value)
    {
        $this->m_value[] = $value;
    }

    /**
     * String conversion
     *
     * @return string
     */
    public function __toString()
    {
        return implode(', ', $this->m_value);
    }

}
