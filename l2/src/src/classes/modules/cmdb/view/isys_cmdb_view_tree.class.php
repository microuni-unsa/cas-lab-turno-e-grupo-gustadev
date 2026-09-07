<?php

use idoit\Component\Helper\Purify;

/**
 * CMDB Tree view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view_tree extends isys_cmdb_view
{
    /**
     * The currently selected node.
     *
     * @var int|string
     */
    protected $m_select_node;

    /**
     * Tree component.
     *
     * @var  isys_component_tree
     */
    protected $m_tree;

    abstract public function tree_build();

    abstract public function tree_process();

    /**
     * Returns the tree component
     *
     * @return isys_component_tree
     */
    public function get_tree_component()
    {
        return $this->m_tree;
    }

    /**
     * Method for finding out, if the tree has already been processed.
     *
     * @return  boolean
     */
    public function processed()
    {
        if (is_null($this->m_tree)) {
            $this->m_tree = $this->get_module_request()
                ->get_menutree();
        }

        return ($this->m_tree->count() > 1);
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__TREEMODE] = true;
    }

    /**
     *
     * @param  array $l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
        // Nothing to do here.
    }

    /**
     * Process view and return HTML result.
     *
     * @return  mixed
     */
    public function process()
    {
        if (is_null($this->m_tree)) {
            $this->m_tree = $this->get_module_request()
                ->get_menutree();
        }

        if (is_object($this->m_tree)) {
            $this->m_tree->reinit();
            $this->tree_build();

            return $this->tree_process();
        }

        return null;
    }

    /**
     * Method for removing ajax parameter from the GET array.
     *
     * @param   array $p_get
     *
     * @return  array
     */
    protected function remove_ajax_parameters(&$p_get)
    {
        unset($p_get[C__GET__AJAX_CALL], $p_get[C__GET__AJAX_REQUEST], $p_get[C__GET__AJAX]);

        return $p_get;
    }

    /**
     * @param        $p_catData
     * @param        $p_catNodeBase
     * @param        $p_catNodeParent
     * @param string $p_catGet
     * @param string $p_tbl
     * @return bool
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 40!
     */
    protected function tree_create_subcategory($p_catData, $p_catNodeBase, $p_catNodeParent, $p_catGet = C__CMDB__GET__CATG, $p_tbl = "isysgui_catg")
    {
        global $g_dirs;

        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_dao = $this->get_dao_cmdb();
        $l_subcats = $l_dao->get_isysgui($p_tbl, null, null, null, $p_catData[$p_tbl . "__id"], $p_tbl . "__sort ASC");
        $nodesAdded = false;
        $objectId = (int)$l_gets[C__CMDB__GET__OBJECT];

        // @see ID-8833 Get some object data.
        $objectData = $l_dao->get_object($objectId)->get_row();
        $isTemplate = in_array($objectData['isys_obj__status'], [C__RECORD_STATUS__TEMPLATE, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE], false);
        $skipInTemplates = ['C__CATG__VIRTUAL_AUTH', 'C__CATS__BASIC_AUTH'];

        if ($l_subcats->num_rows() > 0) {
            while ($l_row = $l_subcats->get_row()) {
                // Skip processing when dao class does not exist.
                if (!class_exists($l_row[$p_tbl . '__class_name'])) {
                    continue;
                }

                if ($l_row['isysgui_cats__const'] === 'C__CATS__BASIC_AUTH' && !isys_auth_auth::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH')) {
                    continue;
                }

                // @see ID-8833 Skip 'auth' categories in template context.
                if ($isTemplate && in_array($l_row['isysgui_cats__const'], $skipInTemplates, true)) {
                    continue;
                }

                if (!isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $objectId, $l_row[$p_tbl . '__const'])) {
                    continue;
                }

                // Define node id.
                $l_nodeid = $p_catNodeBase + ($l_row[$p_tbl . "__id"] * 100);

                // Set selected node.
                if ($_GET[$p_catGet] == $l_row[$p_tbl . "__id"]) {
                    $this->m_select_node = $l_nodeid;
                }

                // Reset the Category selection parameters.
                if (method_exists($this, 'reduce_catspec_parameters')) {
                    $this->reduce_catspec_parameters($l_gets);
                }

                // Set them new according to subcategory settings.
                $l_getsJump[C__CMDB__GET__VIEWMODE] = C__CMDB__VIEW__CATEGORY;

                // Set category ID.
                $l_gets[$p_catGet] = $l_row[$p_tbl . "__id"];

                if (!empty($l_row[$p_tbl . "__list_multi_value"])) {
                    $l_viewmode = C__CMDB__VIEW__LIST_CATEGORY;
                } else {
                    $l_viewmode = C__CMDB__VIEW__CATEGORY;
                }

                $l_strIcon = (!empty($l_row[C__CMDB__TREE_ICON])) ? $g_dirs["images"] . "dtree/special/" . $l_row[C__CMDB__TREE_ICON] : "";

                // Remove ajax parameters.
                unset($l_gets["call"], $l_gets[C__GET__AJAX]);

                $l_link = "javascript:get_content_by_object('" . $objectId . "', '" . $l_viewmode . "', '" . $l_row[$p_tbl . "__id"] . "', '" . $p_catGet .
                    "');";

                $l_category_tooltip = isys_application::instance()->container->get('language')
                    ->get($l_row[$p_tbl . "__title"]);

                // Check if category has entries.
                if (empty($objectId)) {
                    $l_category_title = '<span class="noentries" >' . $l_category_tooltip . "</span>";
                } elseif (!$l_dao->check_category(
                    $objectId,
                    $l_row[$p_tbl . "__class_name"],
                    $l_row[$p_tbl . "__id"],
                    $l_row[$p_tbl . "__source_table"]
                )) {
                    $l_category_title = '<span class="noentries" >' . $l_category_tooltip . "</span>";
                } else {
                    $l_category_title = $l_category_tooltip;
                }
                $this->m_tree->add_node($l_nodeid, $p_catNodeParent, $l_category_title, $l_link, '', $l_strIcon, false, '', $l_category_tooltip, true, $l_row[$p_tbl . '__const']);

                $nodesAdded = true;
            }
        }

        return $nodesAdded;
    }

    /**
     * Constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);
    }
}
