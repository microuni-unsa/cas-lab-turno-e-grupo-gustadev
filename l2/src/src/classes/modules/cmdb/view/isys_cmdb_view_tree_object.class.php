<?php

use idoit\Component\Helper\Purify;
use idoit\Module\Cmdb\Component\Category\Category;
use idoit\Module\Cmdb\Component\Category\Item\AbstractItem;
use idoit\Module\Cmdb\Component\Category\Item\CustomItem;
use idoit\Module\Cmdb\Component\Category\Item\FolderItem;
use idoit\Module\Cmdb\Component\Category\Item\GlobalItem;
use idoit\Module\Cmdb\Component\Category\Item\SpecificItem;

/**
 * CMDB Tree view for objects
 *
 * @package    i-doit
 * @subpackage CMDB_Views
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_object extends isys_cmdb_view_tree
{
    private bool $isTemplate;

    private array $categories;

    private isys_cmdb_dao_object_type $objectTypeDao;

    private int $objectId;

    private FolderItem $rootFolder;

    public function get_id()
    {
        return C__CMDB__VIEW__TREE_OBJECT;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    public function get_name()
    {
        return 'Objektbaum';
    }

    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECT] = true;
        $l_gets[C__CMDB__GET__OBJECTTYPE] = true;
        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    }

    /**
     * @return void
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function tree_build()
    {
        global $g_dirs;

        // Prepare some variables.
        $l_gets = $this->get_module_request()->get_gets();
        $l_gets = Purify::castIntValues($l_gets);
        $l_posts = $this->get_module_request()->get_posts();
        $this->objectId = (int)$l_gets[C__CMDB__GET__OBJECT];
        $template = isys_application::instance()->container->get('template');
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $this->objectTypeDao = isys_cmdb_dao_object_type::instance($database);

        if ($this->objectId <= 0) {
            throw new isys_exception_cmdb('Request problem: No object id found.');
        }

        $this->remove_ajax_parameters($l_gets);

        // @see ID-8833 Get some object data.
        $objectData = $this->objectTypeDao->get_object($this->objectId)->get_row();
        $this->isTemplate = in_array($objectData['isys_obj__status'], [C__RECORD_STATUS__TEMPLATE, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE]);

        $l_title = $l_posts['C__CATG__GLOBAL_TITLE'];

        if (empty($l_posts['C__CATG__GLOBAL_TITLE'])) {
            $l_title = $this->objectTypeDao->obj_get_title_by_id_as_string($this->objectId);
        }

        $objectTypeId = (int) ($l_gets[C__CMDB__GET__OBJECTTYPE] ?: $this->objectTypeDao->get_objTypeID($this->objectId));

        if ($objectTypeId !== 0) {
            // @see ID-9930 Prevent line-breaks in object titles.
            $l_title = addslashes(htmlentities(str_replace("\n", ' ', $l_title)));
            $viewType = (int)C__CMDB__VIEW__CATEGORY;
            $overviewCategoryId = (int) defined_or_default('C__CATG__OVERVIEW', 31);

            // Add root entry.
            $this->categories = [];
            $isLegacy = false;

            try {
                $categoryComponent = (new Category($objectTypeId, $this->objectId));
                $this->categories = $categoryComponent->getAssignedCategories();
                $isLegacy = $categoryComponent->isLegacy();

                if ($isLegacy) {
                    $this->rootFolder = new FolderItem(0, isys_cmdb_dao_category_g_virtual::instance($database), 'root', 'root', null, [], 'constant', 0);

                    // This is a specific fix for 'child categories' because the 'NULL' parents need to be located at the beginning.
                    usort($this->categories, fn (AbstractItem $a, AbstractItem $b) => $a->getParent() - $b->getParent());
                } else {
                    $rootFolder = current(array_filter($this->categories, fn (AbstractItem $item) => $item->getParent() === null));

                    if ($rootFolder instanceof FolderItem) {
                        $this->rootFolder = $rootFolder;
                    } else {
                        // This should not happen - but just to be sure.
                        $this->rootFolder = new FolderItem(0, isys_cmdb_dao_category_g_virtual::instance($database), 'root', 'root', null, [], 'constant', 0);
                    }
                }

                $this->m_tree->add_node(
                    $this->rootFolder->getIdentifier(),
                    C__CMDB__TREE_NODE__PARENT,
                    $l_title,
                    "javascript:get_content_by_object({$this->objectId}, {$viewType}, {$overviewCategoryId}, '" . C__CMDB__GET__CATG . "');",
                    '',
                    isys_application::instance()->container->get('route_generator')->generate('cmdb.object-type.icon', ['objectTypeId' => $objectTypeId]),
                    $overviewCategoryId == $l_gets[C__CMDB__GET__CATG],
                    '',
                    '',
                    true,
                    'mb5'
                );

                $this->processTreeByParent($this->rootFolder, $this->rootFolder->getIdentifier(), $l_gets);

                $template->assign('rootNodeIdentifier', $this->rootFolder->getIdentifier());
            } catch (Throwable $e) {
                isys_notify::error($e->getMessage());
            }

            $this->m_tree->set_tree_sort($isLegacy);

            $l_menu_sticky_links = [];

            // Prepare the sticky "CMDB-Explorer" link.
            if (defined('C__CMDB__VIEW__EXPLORER') && isys_auth_cmdb::instance()->is_allowed_to(isys_auth::VIEW, 'EXPLORER')) {
                $l_menu_sticky_links['explorer'] = [
                    'title' => 'CMDB-Explorer',
                    'icon'  => $g_dirs['images'] . 'axialis/industry-manufacturing/machine-learning.svg',
                    'link'  => isys_helper_link::create_url([
                        C__CMDB__GET__VIEWMODE      => C__CMDB__VIEW__EXPLORER,
                        C__CMDB__GET__OBJECT        => $this->objectId,
                        C__CMDB__VISUALIZATION_TYPE => C__CMDB__VISUALIZATION_TYPE__TREE,
                        C__CMDB__VISUALIZATION_VIEW => C__CMDB__VISUALIZATION_VIEW__OBJECT,
                    ])
                ];
            }

            // Preparing the sticky "Relation" link.
            if (isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $this->objectId, 'C__CATG__RELATION')) {
                $l_menu_sticky_links['relation'] = [
                    'title' => $language->get('LC__CMDB__CATG__RELATION'),
                    'icon'  => $g_dirs['images'] . 'axialis/user-interface/fullscreen.svg',
                    'link'  => "javascript:get_content_by_object('" . $this->objectId . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . defined_or_default('C__CATG__RELATION') . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            // Prepare the sticky "Planning" link.
            if (defined('C__CATG__PLANNING') && isys_application::isPro() && isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $this->objectId, 'C__CATG__PLANNING')) {
                $l_menu_sticky_links['planning'] = [
                    'title' => $language->get('LC__CMDB__CATG__PLANNING'),
                    'icon'  => $g_dirs['images'] . 'axialis/basic/calendar-1.svg',
                    'link'  => "javascript:get_content_by_object('" . $this->objectId . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . C__CATG__PLANNING . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            // Preparing the sticky "Logbook" link.
            if (isys_auth_cmdb::instance()->has_rights_in_obj_and_category(isys_auth::VIEW, $this->objectId, 'C__CATG__LOGBOOK')) {
                $l_menu_sticky_links['logbook'] = [
                    'title' => $language->get('LC__CMDB__CATG__LOGBOOK'),
                    'icon'  => $g_dirs['images'] . 'axialis/basic/book-open.svg',
                    'link'  => "javascript:get_content_by_object('" . $this->objectId . "','" . C__CMDB__VIEW__LIST_CATEGORY . "','" . defined_or_default('C__CATG__LOGBOOK') . "','" . C__CMDB__GET__CATG . "');"
                ];
            }

            $template->assign('menuTreeStickyLinks', $l_menu_sticky_links);

            // Emit a new signal with the parameters: <isys_obj__id>, <isys_obj_type__id>
            try {
                isys_component_signalcollection::get_instance()->emit('mod.cmdb.processMenuTreeLinks', $template, 'menuTreeStickyLinks', $this->objectId, $objectTypeId);
            } catch (Exception $e) {
                isys_notify::debug($e->getMessage(), ['sticky' => true]);
            }

            $userSettings = isys_application::instance()->container->get('settingsUser');

            $template->assign('treeHide', (int)$userSettings->get('gui.tree.hide-empty-categories', false));
        }

        // Sets the eye for hiding empty nodes
        $this->m_tree->set_tree_visibility(true);

        try {
            isys_component_signalcollection::get_instance()->emit('mod.cmdb.extendObjectTree', $this->m_tree);
        } catch (Exception $e) {
            isys_notify::debug($e->getMessage(), ['sticky' => true]);
        }
    }

    /**
     * @param AbstractItem $parent
     *
     * @return array<AbstractItem>
     * @see ID-10005 Do not render empty folders.
     */
    private function getChildren(AbstractItem $parent): array
    {
        return array_filter($this->categories, fn (AbstractItem $item) => ((int)$item->getParent()) === $parent->getId());
    }

    /**
     * @param AbstractItem $parent
     *
     * @return bool
     * @see ID-10005 Check if 'empty folders' are really empty (recursively)
     */
    private function hasChildren(AbstractItem $parent): bool
    {
        $children = $this->getChildren($parent);

        if (count($children) === 0) {
            return false;
        }

        foreach ($children as $child) {
            if ($child instanceof FolderItem) {
                if ($this->hasChildren($child)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractItem $item
     * @return string
     */
    private function getItemDisplayName(AbstractItem $item): string
    {
        $hasData = $item->hasData($this->objectTypeDao, $this->objectId);
        // @see ID-9930 Prevent line-breaks in object titles.
        $categoryName = addslashes(htmlentities(str_replace("\n", ' ', $item->getTranslatedName())));

        if ($hasData === false) {
            $categoryName = '<span class="noentries">' . $categoryName . '</span>';
        } elseif ($hasData === null) {
            $categoryName = '<del class="text-red">' . $categoryName . '</del>';
        }

        return $categoryName;
    }

    /**
     * @param AbstractItem $parent
     * @param string       $parentIdentifier
     * @param array        $getParameters
     *
     * @return int
     */
    private function processTreeByParent(AbstractItem $parent, string $parentIdentifier, array $getParameters): int
    {
        $addedCategories = 0;
        $createdFolder = null;

        if ($parent instanceof FolderItem && $parent->getIdentifier() !== $this->rootFolder->getIdentifier()) {
            $createdFolder = $this->m_tree->add_node(
                $parent->getIdentifier(),
                $parentIdentifier,
                $this->getItemDisplayName($parent),
                $parent->getUrl($this->objectId),
                '',
                (count($parent->getChildren()) === 0
                    ? isys_application::instance()->www_path . 'images/axialis/documents-folders/folder-open-filled.svg'
                    : ''),
                false,
                '',
                '', // @see ID-10094 The title is not necessary here.
                true,
                $parent->getConstant()
            );
        }

        $categories = $this->getChildren($parent);

        foreach ($categories as $category) {
            if ($this->skipCategory($category) || $category->getIdentifier() === $this->rootFolder->getIdentifier()) {
                continue;
            }

            if ($category instanceof FolderItem) {
                // @see ID-10005 Do not render empty folders.
                if ($this->hasChildren($category)) {
                    $addedCategories += $this->processTreeByParent($category, $parent->getIdentifier(), $getParameters);
                }

                continue;
            }

            $selected = ($category instanceof GlobalItem && $category->getId() == $getParameters[C__CMDB__GET__CATG])
                || ($category instanceof CustomItem && $category->getId() == $getParameters[C__CMDB__GET__CATG_CUSTOM])
                || ($category instanceof SpecificItem && $category->getId() == $getParameters[C__CMDB__GET__CATS]);

            if ($selected) {
                $this->m_select_node = $category->getIdentifier();
            }

            // Adds the tree node.
            $addedCategories++;
            $this->m_tree->add_node(
                $category->getIdentifier(),
                $parent->getIdentifier(),
                $this->getItemDisplayName($category),
                $category->getUrl($this->objectId),
                '',
                $category->getIcon(),
                $selected,
                '',
                '', // @see ID-10094 The title is not necessary here.
                true,
                $category->getConstant()
            );
        }

        // @see ID-10974 / ID-10973 Remove empty leftovers.
        if ($addedCategories === 0 && $createdFolder !== null) {
            $this->m_tree->remove_node($createdFolder);
        }

        return $addedCategories;
    }

    /**
     * Category tree specific logic to hide certain categories.
     *
     * @param AbstractItem $category
     *
     * @return bool
     */
    private function skipCategory(AbstractItem $category): bool
    {
        if ($category instanceof FolderItem) {
            return false;
        }

        $viewRight = isys_module_cmdb::getAuth()->has_rights_in_obj_and_category(isys_auth::VIEW, $this->objectId, $category->getConstant());

        // @see ID-4418 Skip folders if the user is not allowed to 'view' and they have no children.
        if (!$viewRight) {
            return count($category->getChildren()) === 0;
        }

        // Needs to be checked differently because of the wildcard check
        if ($category->getConstant() === 'C__CATS__BASIC_AUTH' && !isys_auth_auth::instance()->is_allowed_to(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH')) {
            return true;
        }

        // @see ID-8833 Skip 'auth' categories in template context.
        if ($this->isTemplate && in_array($category->getConstant(), ['C__CATG__VIRTUAL_AUTH', 'C__CATS__BASIC_AUTH'], true)) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return  string
     */
    public function tree_process()
    {
        return $this->m_tree
            ->set_tree_search(true)
            ->process($this->m_select_node);
    }

    /**
     * Removes all category-specific GET-parameters
     *
     * @param  array &$p_arGet
     * @param  array $p_arExceptions
     */
    protected function reduce_catspec_parameters(&$p_arGet, $p_arExceptions = null)
    {
        $l_toDelete = [
            C__CMDB__GET__CATG,
            C__CMDB__GET__CATS,
            C__CMDB__GET__CATLEVEL,
            C__CMDB__GET__CATLEVEL_1,
            C__CMDB__GET__CATLEVEL_2,
            C__CMDB__GET__CATLEVEL_3,
            C__CMDB__GET__CATLEVEL_4,
            C__CMDB__GET__CATLEVEL_5,
            C__CMDB__GET__CAT_LIST_VIEW,
            C__CMDB__GET__CAT_MENU_SELECTION
        ];

        if ($p_arExceptions) {
            $l_toDelete = array_diff($l_toDelete, $p_arExceptions);
        }

        foreach ($l_toDelete as $l_delP) {
            unset($p_arGet[$l_delP]);
        }
    }

    /**
     * Public constructor.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        $this->m_select_node = C__CMDB__TREE_NODE__PARENT;
    }
}
