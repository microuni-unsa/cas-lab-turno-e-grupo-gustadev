<?php
/**
 * i-doit
 *
 * MPTT
 *
 * @package    i-doit
 * @subpackage Components
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Notes:
 * ------
 * * Action stack ist eine Hash table, wobei als Index die Node-ID verwendet
 *   wird, um einen direkten Zusammenhang zwischen Stack und Node zu bilden.
 *   Ein Eintrag beinhaltet wiederrum ein Array mit den Aktionsdaten.
 * * Die Konstanten befinden sich im Konstantenmanager deklariert
 */
class isys_component_dao_mptt extends isys_component_dao
{
    /**
     * @var array
     */
    private $m_actionstack = [];

    /**
     * Cache for method has_children
     *
     * @var array
     */
    private $m_child_cache = [];

    /**
     * @return int
     * @throws isys_exception_database
     */
    public function get_next_node_as_integer(): int
    {
        $sql = "SELECT MAX(isys_catg_location_list__isys_obj__id) AS cnt FROM isys_catg_location_list;";

        return 1 + ((int) $this->retrieve($sql)->get_row_value('cnt'));
    }

    /**
     * @param $nodeId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_by_node_id($nodeId): isys_component_dao_result
    {
        $nodeId = $this->convert_sql_id($nodeId);
        $sql = "SELECT *
            FROM isys_catg_location_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
            WHERE isys_catg_location_list__isys_obj__id = {$nodeId}
            ORDER BY isys_obj__title;";

        return $this->retrieve($sql);
    }

    /**
     * @param $parentId
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_by_parent_id($parentId): isys_component_dao_result
    {
        $parentId = $this->convert_sql_id($parentId);
        $sql = "SELECT *
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__parentid = {$parentId};";

        return $this->retrieve($sql);
    }

    /**
     * Get all children of $p_parent_id, this includes an entry of the node itself ($p_parent_id) as well
     *
     * @param $p_parent_id
     *
     * @return isys_component_dao_result|null
     * @throws isys_exception_database
     */
    public function get_tree($p_parent_id): ?isys_component_dao_result
    {
        $l_node = $this->get_by_node_id($p_parent_id)->get_row();

        if ($l_node && isset($l_node['isys_catg_location_list__lft']) && isset($l_node['isys_catg_location_list__rgt'])) {
            return $this->get_left_by_left_right($l_node['isys_catg_location_list__lft'], $l_node['isys_catg_location_list__rgt'], C__RECORD_STATUS__NORMAL);
        }

        return null;
    }

    /**
     * Get all children of $p_parent_id, this does NOT includes an entry of the node itself ($p_parent_id) as well
     *
     * @param $p_parent_id
     * @param $p_nRecStatus
     *
     * @return isys_component_dao_result|null
     * @throws isys_exception_database
     */
    public function get_children($p_parent_id, $p_nRecStatus = C__RECORD_STATUS__NORMAL): ?isys_component_dao_result
    {
        $l_node = $this->get_by_node_id($p_parent_id)->get_row();

        if ($l_node && isset($l_node['isys_catg_location_list__lft']) && isset($l_node['isys_catg_location_list__rgt'])) {
            return $this->get_left_by_left_right($l_node['isys_catg_location_list__lft'], $l_node['isys_catg_location_list__rgt'], $p_nRecStatus, false);
        }

        return null;
    }

    /**
     * Recursively searches for children in the tree.
     *
     * @param $rootObjectId
     * @param $childObjectId
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function has_children($rootObjectId, $childObjectId): bool
    {
        if (isset($this->m_child_cache[$rootObjectId][$childObjectId])) {
            return $this->m_child_cache[$rootObjectId][$childObjectId];
        }

        if ($rootObjectId > 0) {
            $l_node = $this->get_by_node_id($rootObjectId)->get_row();

            if ($l_node && isset($l_node['isys_catg_location_list__lft']) && isset($l_node['isys_catg_location_list__rgt'])) {
                $left = $this->convert_sql_int($l_node['isys_catg_location_list__lft']);
                $right = $this->convert_sql_int($l_node['isys_catg_location_list__rgt']);
                $convertedChildObjectId = $this->convert_sql_id($childObjectId);

                $sql = "SELECT isys_catg_location_list__id,
                    isys_catg_location_list__isys_obj__id,
                    isys_catg_location_list__parentid,
                    isys_catg_location_list__const,
                    isys_catg_location_list__lft,
                    isys_catg_location_list__rgt,
                    isys_obj__title,
                    isys_obj_type__id,
                    isys_obj_type__title
                    FROM isys_catg_location_list
                    INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
                    INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
                    WHERE isys_catg_location_list__lft BETWEEN {$left} AND {$right}
                    AND isys_catg_location_list__isys_obj__id = {$convertedChildObjectId};";

                return $this->m_child_cache[$rootObjectId][$childObjectId] = $this->retrieve($sql)->num_rows() > 0;
            }
        }

        return false;
    }

    /**
     * @param int $objectId
     *
     * @return string
     * @throws isys_exception_database
     * @see ID-11261 We use the result as sub-query when checking location rights
     */
    public function getObjectsWithSubQueryString(int $objectId): string
    {
        $leftRightQuery = "SELECT isys_catg_location_list__lft AS lft, isys_catg_location_list__rgt AS rgt
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__isys_obj__id = {$objectId}
            LIMIT 1;";

        $leftRight = $this->retrieve($leftRightQuery)
            ->get_row();

        $lft = (int)$leftRight['lft'];
        $rgt = (int)$leftRight['rgt'];

        if (($lft + 1) === $rgt) {
            return $objectId;
        }

        // Attention: Do NOT put a semikolon at the end, this is ment to be used as sub-query.
        return "SELECT isys_catg_location_list__isys_obj__id
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__lft BETWEEN {$lft} AND {$rgt}";
    }

    /**
     * Get items between left and right.
     *
     * @param $p_left
     * @param $p_right
     * @param $p_nRecStatus
     * @param bool $includeSelf
     *
     * @return isys_component_dao_result|null
     * @throws isys_exception_database
     */
    public function get_left_by_left_right($p_left, $p_right, $p_nRecStatus = null, bool $includeSelf = true): ?isys_component_dao_result
    {
        if (!is_numeric($p_left) || !is_numeric($p_right)) {
            return null;
        }

        $l_q = "SELECT isys_catg_location_list__id,
            isys_catg_location_list__isys_obj__id,
            isys_catg_location_list__parentid,
            isys_catg_location_list__const,
            isys_catg_location_list__lft,
            isys_catg_location_list__rgt,
            isys_obj__title,
            isys_obj_type__id,
            isys_obj_type__title
            FROM isys_catg_location_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_catg_location_list__lft BETWEEN " . $this->convert_sql_int($p_left) . " AND " . $this->convert_sql_int($p_right);

        if (!is_null($p_nRecStatus)) {
            $l_q .= " AND isys_obj__status = " . $this->convert_sql_int($p_nRecStatus) . " ";
        }

        if (!$includeSelf) {
            // @todo  This should be checked... Is this actually correct?
            $l_q .= ' AND isys_obj__id != ' . $this->convert_sql_int($p_left);
        }

        $l_q .= " ORDER BY isys_catg_location_list__lft ASC";

        return $this->retrieve($l_q);
    }

    /**
     * @param $p_left
     * @param $p_right
     *
     * @return isys_component_dao_result|null
     * @throws isys_exception_database
     */
    public function get_outer_by_left_right($p_left, $p_right): ?isys_component_dao_result
    {
        if (!is_numeric($p_left) || !is_numeric($p_right)) {
            return null;
        }

        $l_q = "SELECT *
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__lft < $p_left
            AND isys_catg_location_list__rgt > $p_right
            GROUP BY isys_catg_location_list__isys_obj__id
            ORDER BY isys_catg_location_list__lft ASC;";

        return $this->retrieve($l_q);
    }

    /**
     * @param $p_action
     * @param $p_data
     *
     * @return bool
     */
    public function action_stack_add($p_action, $p_data)
    {
        if ($p_action > C__MPTT__ACTION_BEGIN && $p_action < C__MPTT__ACTION_END) {
            array_push($this->m_actionstack, [
                $p_action,
                $p_data
            ]);

            return true;
        }

        return false;
    }

    /**
     * Public wrapper function for recursive writing algorithm.
     *
     * @param $p_callback
     *
     * @return int|mixed|null
     */
    public function write($p_callback = null)
    {
        return $this->_write($p_callback);
    }

    /**
     * @param $p_id
     * @param $p_callback
     * @param $p_userdata
     *
     * @return array|bool|null
     * @throws isys_exception_database
     */
    public function read($p_id, $p_callback = null, $p_userdata = null)
    {
        $l_resultset = [];
        $l_treeres = $this->get_by_node_id($p_id);
        $l_use_callback = (!is_null($p_callback));

        if ($l_treeres && $l_treeres->num_rows()) {
            $l_rstack = [];

            // Fetch data of origin object.
            $l_startrow = $l_treeres->get_row();

            if ($l_startrow['isys_catg_location_list__lft'] > $l_startrow['isys_catg_location_list__rgt']) {
                $l_left = $l_startrow['isys_catg_location_list__lft'];
                $l_startrow['isys_catg_location_list__lft'] = $l_startrow['isys_catg_location_list__rgt'];
                $l_startrow['isys_catg_location_list__rgt'] = $l_left;
            }

            // Fetch results of sub-tree.
            $l_subres = $this->get_left_by_left_right($l_startrow['isys_catg_location_list__lft'], $l_startrow['isys_catg_location_list__rgt'], C__RECORD_STATUS__NORMAL);

            if ($l_subres) {
                // Iterate through result set.
                while ($l_subrow = $l_subres->get_row()) {
                    // If there are entries in the stack ...
                    if (count($l_rstack)) {
                        // Remove all entries smaller than the current one.
                        while ($l_rstack[count($l_rstack) - 1] < $l_subrow['isys_catg_location_list__rgt']) {
                            if (count($l_rstack) - 1 < 0) {
                                break;
                            }
                            array_pop($l_rstack);
                        }
                    }

                    // Decide whether to use callback or append it to the result set.
                    if ($l_use_callback) {
                        $p_callback->mptt_read(
                            'isys_catg_location_list',
                            count($l_rstack), /* Level */
                            $l_subrow['isys_catg_location_list__id'],
                            $l_subrow['isys_catg_location_list__isys_obj__id'],
                            $l_subrow['isys_catg_location_list__parentid'],
                            $l_subrow['isys_catg_location_list__const'],
                            $l_subrow['isys_catg_location_list__lft'],
                            $l_subrow['isys_catg_location_list__rgt'],
                            $p_userdata,
                            $l_subrow["isys_obj__title"]
                        );
                    } else {
                        $l_resultset[] = $l_subrow;
                    }

                    /* Add current right entry to stack */
                    $l_rstack[] = $l_subrow['isys_catg_location_list__rgt'];
                }
            }

            if ($l_use_callback) {
                return true;
            }

            return $l_resultset;
        }

        if (isset($p_callback)) {
            return false;
        }

        return null;
    }

    /**
     * @param array $p_data
     *
     * @return string|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function action_handler_add(array $p_data)
    {
        // Check some required parameters.
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id!";
        }
        if (!array_key_exists("parent_id", $p_data)) {
            return "Please set parent_id!";
        }

        // Check if node is existent.
        $l_existsdao = $this->retrieve("SELECT isys_catg_location_list__id
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__isys_obj__id = {$p_data["node_id"]}
            LIMIT 1;");

        // If yes, break proc.
        if ($l_existsdao && $l_existsdao->num_rows() > 0) {
            return "Node already existent!";
        }

        // Add node
        $this->update("INSERT INTO isys_catg_location_list SET
            isys_catg_location_list__isys_obj__id = '{$p_data["node_id"]}',
            isys_catg_location_list__parentid = '{$p_data["parent_id"]}',
            isys_catg_location_list__const = '{$p_data["const"]}',
            isys_catg_location_list__lft = 0,
            isys_catg_location_list__rgt = 0;");

        return null;
    }

    /**
     * @param $p_data
     *
     * @return string|null
     * @throws isys_exception_dao
     */
    private function action_handler_update($p_data): ?string
    {
        if (!is_array($p_data) || !array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }

        $nodeId = $this->convert_sql_id($p_data["node_id"]);
        unset($p_data["node_id"]);

        $l_strextra = "";
        $l_ci = 0;
        $count = count($p_data);

        foreach ($p_data as $l_field => $l_data) {
            $l_strextra .= "$l_field='" . (is_numeric($l_data) ? $l_data : $this->m_db->escape_string($l_data)) . "' ";

            if ($l_ci < $count - 1) {
                $l_strextra .= ", ";
            }

            $l_ci++;
        }

        $l_q = "UPDATE isys_catg_location_list
            SET isys_catg_location_list__isys_obj__id = {$nodeId},
            $l_strextra
            WHERE isys_catg_location_list__isys_obj__id = {$nodeId};";

        if (!$this->update($l_q)) {
            return "Update was not successful ($l_q)!";
        }

        return null;
    }

    /**
     * @param $p_data
     *
     * @return string|null
     * @throws isys_exception_dao
     */
    private function action_handler_delete($p_data)
    {
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }

        // Delete node.
        $this->update("DELETE FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = {$p_data["node_id"]};");

        // Everything was okay.
        return null;
    }

    /**
     * @param array $p_data
     *
     * @return string|null
     * @throws isys_exception_dao
     */
    private function action_handler_move($p_data): ?string
    {
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }
        if (!array_key_exists("parent_id", $p_data)) {
            return "Please set node_id";
        }

        // Delete node.
        $this->update("UPDATE isys_catg_location_list
            SET isys_catg_location_list__parentid = {$p_data["parent_id"]}
            WHERE isys_catg_location_list__isys_obj__id = {$p_data["node_id"]};");

        // Everything was okay.
        return null;
    }

    /**
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function action_stack_process()
    {
        if (is_countable($this->m_actionstack) && count($this->m_actionstack) > 0) {
            $this->begin_update();

            foreach ($this->m_actionstack as $l_action_data) {
                [$l_action, $l_data] = $l_action_data;
                $l_ret = null;

                switch ($l_action) {
                    case C__MPTT__ACTION_ADD:
                        $l_ret = $this->action_handler_add($l_data);
                        break;
                    case C__MPTT__ACTION_DELETE:
                        $l_ret = $this->action_handler_delete($l_data);
                        break;
                    case C__MPTT__ACTION_MOVE:
                        $l_ret = $this->action_handler_move($l_data);
                        break;
                    case C__MPTT__ACTION_UPDATE:
                        $l_ret = $this->action_handler_update($l_data);
                        break;
                    default:
                        throw new isys_exception_dao("MPTT: Invalid action $l_action!");
                }

                if ($l_ret != null) {
                    throw new isys_exception_dao("MPTT: Could not handle action $l_action: $l_ret");
                }
            }

            $this->apply_update();

            unset($this->m_actionstack);
            $this->m_actionstack = [];
        }
    }

    /**
     * @param $p_callback
     * @param $p_node_id
     * @param $p_left
     *
     * @return int|mixed|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function _write($p_callback = null, $p_node_id = null, &$p_left = null)
    {
        if ($p_node_id === null) {
            $p_node_id = defined_or_default('C__OBJ__ROOT_LOCATION');
        }

        // Get node data.
        $l_noderes = $this->get_by_node_id($p_node_id);

        if ($l_noderes && $l_noderes->num_rows() > 0) {
            $l_noderow = $l_noderes->get_row();

            // Before writing anything, we process the action stack, but from the root node, since changes can modify the whole tree!
            if ($l_noderow['isys_catg_location_list__isys_obj__id'] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                $p_left = 1; /* Start from 1, please */

                // Process respective action stack.
                $this->action_stack_process();
            }

            $l_right = $p_left + 1;

            // Enumerate child nodes.
            $l_childres = $this->get_by_parent_id($l_noderow['isys_catg_location_list__isys_obj__id']);

            if ($l_childres->num_rows() > 0) {
                while ($l_childrow = $l_childres->get_row()) {
                    $l_right = $this->_write($p_callback, $l_childrow['isys_catg_location_list__isys_obj__id'], $l_right);
                }
            }

            $l_dowrite = true;

            // Call callback, if developer wants to change the vars again.
            if ($p_callback) {
                // A callback can influence the behaviour. If the callback does not return a true value, the update transaction won't be commited.
                $l_dowrite = $p_callback->mptt_write(
                    $p_node_id,
                    $l_noderow['isys_catg_location_list__parentid'],
                    $l_noderow['isys_catg_location_list__const'],
                    $p_left,
                    $l_right
                );
            }

            if ($l_dowrite) {
                // Update transaction.
                $sql = "UPDATE isys_catg_location_list
                    SET isys_catg_location_list__lft = '$p_left',
                    isys_catg_location_list__rgt = '$l_right'
                    WHERE isys_catg_location_list__isys_obj__id = '$p_node_id';";

                // Update node entry.
                $this->update($sql);
            }

            return $l_right + 1;
        }

        return $p_left;
    }
}

/**
 * isys_mptt_callback is the interface you have to implement in a class,
 * which is reponsible to handle a reading / writing iteration from the
 * read / write methods of isys_component_dao_mptt.
 */
interface isys_mptt_callback
{
    /**
     * Implement this method in order to define a handler where you accept
     * incoming reading iterations. I.e. you can directly build up a JavaScript
     * tree by passing a isys_component_tree-object to $p_userdata
     *
     * @param string  $p_table
     * @param integer $p_level
     * @param integer $p_id
     * @param integer $p_node_id
     * @param integer $p_parent_id
     * @param string  $p_const
     * @param integer $p_left
     * @param integer $p_right
     * @param mixed   $p_userdata
     */
    public function mptt_read($p_table, $p_level, $p_id, $p_node_id, $p_parent_id, $p_const, $p_left, $p_right, $p_userdata);

    /**
     * Implement this method in order to define a handler where you can change
     * data to write before writing. This method has to return true in
     * its implementation in order to commit the write. Otherwise, with
     * false, nothing will be written.
     *
     * @param $p_node_id
     * @param $p_parent_id
     * @param $p_const
     * @param $p_left
     * @param $p_right
     *
     * @return mixed
     */
    public function mptt_write(&$p_node_id, &$p_parent_id, &$p_const, &$p_left, &$p_right);
}
