<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Tree base implementation
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @author     Leonard Fischer <lfischer@i-doit.org>
 * @version    0.9
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_tree extends isys_component
{
    /**
     * @var isys_component_tree[]
     */
    private static $m_instances = [];

    /**
     * This variable is used to set the selected node on the tree.
     *
     * @var string
     */
    protected $m_select_node = null;

    /**
     * This variable stores the tree-nodes.
     *
     * @var Array
     */
    protected $m_tree_childs = [];

    /**
     * The name of the tree, will be used as JS-variable name when rendered.
     *
     * @var String
     */
    protected $m_tree_name = '';

    /**
     * The tree and its options.
     *
     * @var Array
     */
    protected $m_tree_output = [];

    /**
     * @var bool
     */
    protected $m_tree_search = false;

    /**
     * @var bool
     */
    protected $m_tree_hideable = false;

    /**
     * Shall the tree be sorted?
     *
     * @var Boolean
     */
    protected $m_tree_sort = true;

    /**
     * @var int|string
     */
    private $selectedNode;

    /**
     * Tree's default name is menu_tree.
     *
     * @param string $p_name
     *
     * @return isys_component_tree
     */
    public static function factory($p_name = 'menu_tree')
    {
        if (!isset(self::$m_instances[$p_name])) {
            self::$m_instances[$p_name] = self::instance($p_name)->init();
        }

        return self::$m_instances[$p_name];
    }

    /**
     * @param $p_name
     *
     * @return isys_component_tree
     */
    private static function instance($p_name)
    {
        return new self($p_name);
    }

    /**
     * @param idoit\Tree\Node $p_tree
     */
    public function payload(idoit\Tree\Node $p_tree, isys_register $p_request)
    {
        $this->add_node($p_tree->id, -1, $p_tree->title, $p_tree->link, '', $p_tree->image, false, '', $p_tree->tooltip, $p_tree->accessRight, $p_tree->cssClass);

        $this->recurse_payload($p_tree->get_childs(), $p_request);
    }

    /**
     * @param bool $searchable
     *
     * @return $this
     */
    public function set_tree_search(bool $searchable)
    {
        $this->m_tree_search = $searchable;

        return $this;
    }

    /**
     * @param bool $sort
     *
     * @return $this
     */
    public function set_tree_sort(bool $sort)
    {
        $this->m_tree_sort = $sort;

        return $this;
    }

    /**
     * Count all the elements.
     *
     * @return Integer
     */
    public function count()
    {
        $count = is_countable($this->m_tree_childs) ? count($this->m_tree_childs) : 0;
        $count += is_countable($this->m_tree_output) ? count($this->m_tree_output) : 0;
        return $count;
    }

    /**
     * Method for finding a node by it's title. The id of the first match will be returned.
     *
     * @param string $title
     *
     * @return int
     */
    public function find_id_by_title(string $title): int
    {
        foreach ($this->m_tree_childs as $l_id => $l_node) {
            if (str_contains($l_id, $title)) {
                return (int)preg_replace('~' . $this->m_tree_name . '\.add\((\d+),(.*)~', '$1', $l_node);
            }
        }

        return 0;
    }

    /**
     * This method helps you to open all nodes to a certain node in a tree and select it.
     *
     * @param int|string $node
     * @param bool $selected
     *
     * @return void
     */
    public function select_node_by_id($node, bool $selected = true): void
    {
        // @see ID-10767 Use 'htmlentities' to mask potential XSS attempts.
        $id = is_numeric($node) ? (int)$node : "'" . htmlentities($node) . "'";

        $this->m_select_node = $this->m_tree_name . '.openTo(' . $id . ', ' . ((true === $selected) ? 'true' : 'false') . ');';
    }

    /**
     * Initializes the tree with the given name.
     *
     * @return $this
     */
    public function init()
    {
        $this->m_tree_childs = [];

        $this->m_tree_output = [
            "window['{$this->m_tree_name}'] = new dTree('{$this->m_tree_name}');"
        ];

        return $this;
    }

    /**
     * Adds a node with specified parameters.
     *
     * @param int|string $p_id        Node's identifier
     * @param int|string $p_parentid  Parent node's identifier
     * @param string     $p_title     Title
     * @param string     $p_url       (optional) URL. Default to an empty string.
     * @param string     $p_target    (optional) HTML target attribute. Defaults to an empty string.
     * @param string     $p_icon      (optional) Icon. Defaults to an empty string.
     * @param bool       $p_select    (optional) Select this node (1). Defaults to 0.
     * @param string     $p_backImage (optional) If set, the node will have the specified background image. Defaults to an empty string.
     * @param string     $p_tooltip   (optional) Tooltip. Defaults to an empty string.
     * @param bool       $p_has_right (optional) Should node be already expanded?
     * @param string     $p_cssclass
     * @param bool       $expanded
     *
     * @return int|string
     */
    public function add_node(
        $p_id,
        $p_parentid,
        $p_title,
        $p_url = "",
        $p_target = "",
        $p_icon = "",
        $p_select = false,
        $p_backImage = "",
        $p_tooltip = "",
        $p_has_right = true,
        $p_cssclass = '',
        $expanded = false
    ) {
        global $g_dirs;

        if (trim($p_backImage ?? '') !== '') {
            $p_backImage = $g_dirs['images'] . 'dtree/background/' . $p_backImage;
        }

        // @see ID-8400
        if (preg_match('/((?!\\\)|[^\\\\])([\'|"])/', $p_title)) {
            $p_title = addslashes($p_title);
        }

        $l_temp = $this->m_tree_name . ".add(" .
            (is_numeric($p_id) ? (int)$p_id : "'{$p_id}'") . ", " .
            (is_numeric($p_parentid) ? (int)$p_parentid : "'{$p_parentid}'") . ", " .
            "'{$p_title}', " .
            $this->prepareString($p_has_right ? $p_url : 'javascript:;') . ", " .
            "'{$p_tooltip}', " .
            $this->prepareString($p_target) . ", " .
            $this->prepareString($p_icon) . ", " .
            $this->prepareString($p_icon) . ", " .
            ((int)$expanded) . "," .
            "''," .
            ((int)$p_select) . "," .
            "'{$p_backImage}'," .
            "'{$p_cssclass}');";

        if ($p_parentid > -1) {
            // @see ID-9963 We clean the string a bit more and don't remove whitespaces.
            $key = trim(Purify::transliterate(strip_tags(html_entity_decode($p_title)))) . ' ' . count($this->m_tree_childs);

            $this->m_tree_childs[$key] = $l_temp;
        } else {
            $this->m_tree_output[] = $l_temp;
        }

        if ($p_select) {
            $this->selectedNode = is_numeric($p_id) ? (int)$p_id : (string)$p_id;
        }

        return is_numeric($p_id) ? (int)$p_id : (string)$p_id;
    }

    /**
     * @param $string
     *
     * @return string
     */
    private function prepareString($string): string
    {
        if (!is_scalar($string)) {
            return "''";
        }

        return "'" . str_replace(["\\\\", "\n", "'"], ["\\", "\\n", "\\'"], (string)$string) . "'";
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function remove_node($identifier): bool
    {
        foreach ($this->m_tree_childs as $key => $jsLine) {
            // @see ID-10974 / ID-10973 Fix the 'remove' part in case of string identifier.
            $searchTerm = is_numeric($identifier) ? ".add({$identifier}," : ".add('{$identifier}',";

            if (str_contains($jsLine, $searchTerm)) {
                unset($this->m_tree_childs[$key]);

                return true;
            }
        }

        return false;
    }

    /**
     * Configures the tree. Possible keys are:
     *    target            (default: null)
     *    folderLinks        (default: true)
     *    useSelection    (default: true)
     *    useCookies        (default: false)
     *    useLines        (default: true)
     *    useIcons        (default: true)
     *    useStatusText    (default: false)
     *    closeSameLevel    (default: false)
     *    inOrder            (default: false)
     *
     * @param   String $p_key
     * @param   String $p_val
     *
     * @return  boolean
     */
    public function config($p_key, $p_val)
    {
        $l_posskeys = [
            "target",
            "folderLinks",
            "useSelection",
            "useCookies",
            "useLines",
            "useIcons",
            "useStatusText",
            "closeSameLevel",
            "inOrder",
        ];

        if (in_array($p_key, $l_posskeys)) {
            $this->m_tree_output[] = $this->m_tree_name . ".config." . $p_key . "=" . $p_val . ";";

            return true;
        }

        return false;
    }

    /**
     * Processes the tree and returns it as string. Opens node specified by $p_opennode.
     *
     * @param   integer $p_opennode           Which node should be opened?
     * @param   string  $p_additional_process Optional for some extra javascript.
     *
     * @return  string
     */
    public function process($p_opennode = null, $p_additional_process = "")
    {
        isys_application::instance()->container->get('template')
            ->assign('bMenuTreeSearcheable', $this->m_tree_search)
            ->assign('bMenuTreeHideable', $this->m_tree_hideable);

        // Sort array by keys.
        if ($this->m_tree_sort && is_array($this->m_tree_childs)) {
            // @see ID-9963 Use 'strcmp' instead of 'strnatcasecmp' which messes up when comparing some specific strings.
            uksort($this->m_tree_childs, 'strcasecmp');
        }

        $l_tree_output = '<div id="' . $this->m_tree_name . '"></div>' . "\n";

        if (null !== $p_opennode) {
            $this->select_node_by_id($p_opennode);
        }

        if (empty($this->m_select_node)) {
            $this->select_node_by_id($this->selectedNode);
        }

        $l_tree_output .= isys_glob_js_print(implode("\n", $this->m_tree_output) . implode("\n", $this->m_tree_childs) . "$('" . $this->m_tree_name . "').update(" .
            $this->m_tree_name . ");" . $this->m_select_node . $p_additional_process);

        return $l_tree_output;
    }

    /**
     * Clears the tree output stack.
     */
    public function reinit()
    {
        $this->init();
    }

    /**
     * @param bool $hideable
     *
     * @return $this
     */
    public function set_tree_visibility(bool $hideable)
    {
        $this->m_tree_hideable = $hideable;

        return $this;
    }

    /**
     * @param array|idoit\Tree\Node[]|isys_tree_node[] $p_tree
     */
    private function recurse_payload($p_tree, isys_register $p_request)
    {
        foreach ($p_tree as $l_child) {
            $selectedNode = false;

            if ($l_child->link == $p_request->get('BASE') . ltrim($p_request->get('REQUEST'), '/')) {
                $this->select_node_by_id($l_child->id);

                $selectedNode = true;
            }

            $this->add_node(
                $l_child->id,
                $l_child->get_parent()->id,
                $l_child->title,
                $l_child->link,
                '',
                $l_child->image,
                $selectedNode,
                '',
                $l_child->tooltip,
                $l_child->accessRight,
                $l_child->cssClass
            );

            $this->recurse_payload($l_child->get_childs(), $p_request);
        }
    }

    /**
     * Initializes a tree with the given $p_name. The name has to be unique since it's used in JavaScript.
     *
     * @param  string $p_name
     */
    private function __construct($p_name)
    {
        $this->m_tree_name = $p_name;
        $this->selectedNode = 0;
    }
}
