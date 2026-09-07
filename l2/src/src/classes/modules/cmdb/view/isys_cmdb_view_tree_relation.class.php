<?php

/**
 * CMDB Tree view
 *
 * @package    i-doit
 * @subpackage CMDB_Views
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_relation extends isys_cmdb_view_tree
{
    /**
     * Returns the view mode ID.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__TREE_RELATION;
    }

    /**
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    /**
     * Returns the tree name.
     *
     * @return  string
     */
    public function get_name()
    {
        return 'Relation tree';
    }

    /**
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);
    }

    /**
     * Method for building the tree.
     */
    public function tree_build()
    {
        global $g_comp_database;

        $language = isys_application::instance()->container->get('language');

        $l_dao_relation = new isys_cmdb_dao_category_g_relation($g_comp_database);

        $currentNode = 1;
        $rootNode = $this->m_tree->add_node(
            $currentNode++,
            -1,
            $language->get('LC__CMDB__CATG__RELATION'),
            '',
            '',
            '',
            false,
            '',
            '',
            true,
            'mb5'
        );

        $l_reltypes = $l_dao_relation->get_relation_type();
        if (defined('C__OBJTYPE__RELATION') && isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__RELATION')) {
            $allNode = $this->m_tree->add_node(
                $currentNode++,
                $rootNode,
                $language->get('LC__CMDB__RECORD_STATUS__ALL'),
                isys_helper_link::create_url([
                    'viewMode' => C__CMDB__VIEW__LIST_OBJECT,
                    C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_RELATION,
                    C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__RELATION
                ]),
                '',
                '',
                empty($_GET['type']) && $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__RELATION
            );

            while ($l_row = $l_reltypes->get_row()) {
                $addition = $l_row['isys_relation_type__master']
                    ? ' (' . $language->get($l_row['isys_relation_type__master']) . ')'
                    : '';

                $this->m_tree->add_node(
                    $currentNode++,
                    $allNode,
                    $language->get($l_row['isys_relation_type__title']) . $addition,
                    isys_helper_link::create_url([
                        'viewMode' => C__CMDB__VIEW__LIST_OBJECT,
                        C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_RELATION,
                        C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__RELATION,
                        'type' => $l_row['isys_relation_type__id']
                    ]),
                    '',
                    '',
                    $_GET['type'] == $l_row['isys_relation_type__id'] && $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__RELATION
                );
            }
        }

        if (defined('C__OBJTYPE__PARALLEL_RELATION') && isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::VIEW, 'OBJ_IN_TYPE/C__OBJTYPE__PARALLEL_RELATION')) {
            $this->m_tree->add_node(
                $currentNode++,
                $rootNode,
                $language->get('LC__RELATION__PARALLEL_RELATIONS'),
                isys_helper_link::create_url([
                    C__CMDB__GET__VIEWMODE   => C__CMDB__VIEW__LIST_OBJECT,
                    C__CMDB__GET__TREEMODE   => C__CMDB__VIEW__TREE_RELATION,
                    C__CMDB__GET__OBJECTTYPE => C__OBJTYPE__PARALLEL_RELATION
                ]),
                '',
                '',
                $_GET[C__CMDB__GET__OBJECTTYPE] == C__OBJTYPE__PARALLEL_RELATION
            );
        }

        $this->m_tree->set_tree_sort(false);

        isys_application::instance()->container->get('template')
            ->assign('bShowMenuTreeButtons', false);

        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.extendRelationTree', $this->m_tree);
    }

    /**
     * @return  string
     */
    public function tree_process()
    {
        // Using the node ID as defined above (type ID + 5).
        return $this->m_tree
            ->set_tree_search(true)
            ->process();
    }
}
