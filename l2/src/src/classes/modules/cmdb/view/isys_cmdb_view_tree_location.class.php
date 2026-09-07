<?php

/**
 * CMDB Tree view for locations
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_location extends isys_cmdb_view_tree
{
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_LOCATION;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    public function get_name()
    {
        return "Location tree";
    }

    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    }

    /**
     * Build the tree.
     */
    public function tree_build()
    {
        // Logic is no longer necessary.
    }

    public function tree_process()
    {
        return $this->m_tree->process($this->m_select_node);
    }
}
