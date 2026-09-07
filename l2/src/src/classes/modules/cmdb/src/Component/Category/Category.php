<?php

namespace idoit\Module\Cmdb\Component\Category;

use Exception;
use idoit\Component\FeatureManager\FeatureCheckInterface;
use idoit\Module\Cmdb\Component\Category\Item\AbstractItem;
use idoit\Module\Cmdb\Component\Category\Item\CustomItem;
use idoit\Module\Cmdb\Component\Category\Item\FolderItem;
use idoit\Module\Cmdb\Component\Category\Item\GlobalItem;
use idoit\Module\Cmdb\Component\Category\Item\SpecificItem;
use idoit\Module\Pro\Model\CategoryFolders\Category as CategoryModel;
use idoit\Module\Pro\Model\CategoryFolders\Config as ConfigModel;
use idoit\Module\Pro\Model\CategoryFolders\Folder as FolderModel;
use isys_application;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_category_g_virtual;
use isys_cmdb_dao_category_global;
use isys_cmdb_dao_category_specific;
use isys_cmdb_dao_object_type;
use isys_component_database;
use isys_component_template_language_manager;
use isys_custom_fields_dao;
use isys_module_custom_fields;
use isys_notify;
use Throwable;

/**
 * Category component to fetch assigned categories of an object (type).
 */
class Category
{
    private ?int $objectId;

    private ?int $configurationId = null;

    private int $objectTypeId;

    private bool $isLegacy = false;

    private bool $skipSticky = true;

    private array $categoryAssignments;

    private array $categories;

    private isys_component_database $database;

    private isys_component_template_language_manager $language;

    private isys_cmdb_dao_object_type $dao;

    private string $defaultIcon;

    public const STICKY_CATEGORIES = [
        'C__CATG__LOGBOOK',
        'C__CATG__MULTIEDIT',
        'C__CATG__OVERVIEW',
        'C__CATG__PLANNING',
        'C__CATG__RELATION',
        'C__CATG__VIRTUAL_AUTH',
        'C__CATG__VIRTUAL_TICKETS',
        'C__CATG__VIRTUAL_VIVA'
    ];

    // @see ID-9684 Duplicated categories, that should be hidden.
    public const DUPLICATED_CATEGORIES = [
        'C__CATS__ORGANIZATION_MASTER_DATA',
        'C__CATS__PERSON_MASTER',
        'C__CATS__PERSON_GROUP_MASTER',
        'C__CATG__VIRTUAL_MACHINE', // @see ID-11030
    ];

    /**
     * @param int      $objectTypeId
     * @param int|null $objectId
     *
     * @throws Exception
     */
    public function __construct(int $objectTypeId, ?int $objectId = null)
    {
        $this->objectId = $objectId;
        $this->objectTypeId = $objectTypeId;
        $this->database = isys_application::instance()->container->get('database');
        $this->language = isys_application::instance()->container->get('language');
        $this->dao = isys_cmdb_dao_object_type::instance($this->database);

        $this->defaultIcon = class_exists('isys_module_custom_fields')
            ? isys_module_custom_fields::DEFAULT_CATEGORY_ICON
            : 'images/axialis/documents-folders/document-color-grey.svg';
    }

    /**
     * @param bool $skip
     *
     * @return void
     */
    public function skipStickyCategories(bool $skip): void
    {
        $this->skipSticky = $skip;
    }

    /**
     * @return AbstractItem[]
     * @throws Exception
     */
    public function getAssignedCategories(): array
    {
        $this->isLegacy = false;
        $configuration = null;
        $this->categories = [];
        $this->categoryAssignments = [];

        if (class_exists(ConfigModel::class) && class_exists(FolderModel::class) && class_exists(CategoryModel::class)) {
            $configuration = ConfigModel::instance($this->database)->getByObjectType($this->objectTypeId);

            $this->configurationId = (int)$configuration['id'];
        }

        $this->loadGlobalCategoryAssignments();
        $this->loadCustomCategoryAssignments();
        $this->loadSpecificCategoryAssignments();

        if ($configuration === null) {
            $this->isLegacy = true;

            return $this->categoryAssignments;
        }

        $this->loadCategoryFoldersStructure();

        // @see ID-9584 Check if there's a difference between '$this->categoryAssignments' and '$this->categories'.
        return $this->processDelta($this->categories, $this->categoryAssignments);
    }

    /**
     * @return bool
     */
    public function isLegacy(): bool
    {
        return $this->isLegacy;
    }

    /**
     * @return int|null
     */
    public function getConfigurationId(): ?int
    {
        return $this->configurationId;
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function loadCategoryFoldersStructure(): void
    {
        $orderHelper = 0;
        $result = FolderModel::instance($this->database)->getAllByConfig($this->configurationId);
        $categoryModel = CategoryModel::instance($this->database);
        $wwwPath = isys_application::instance()->www_path;

        while ($row = $result->get_row()) {
            $this->categories[str_pad($row['order'], 4, '0', STR_PAD_LEFT) . '.' . ($orderHelper++)] = $currentFolder = new FolderItem(
                (int) $row['id'],
                isys_cmdb_dao_category_g_virtual::instance($this->database),
                $row['title'],
                $this->language->get($row['title']),
                $row['parent'] === null ? null : (int)$row['parent'],
                [],
                '',
                (int) $row['id']
            );

            $categoryResult = $categoryModel->getAllByFolder($row['id']);

            while ($category = $categoryResult->get_row()) {
                if ($category['g_id'] !== null) {
                    if (!class_exists($category['g_class_name']) || !is_a($category['g_class_name'], isys_cmdb_dao_category_global::class, true)) {
                        continue;
                    }

                    $item = GlobalItem::class;
                    $dao = $category['g_class_name']::instance($this->database);
                    $prefix = 'g';
                } elseif ($category['c_id'] !== null) {
                    $item = CustomItem::class;
                    // @see ID-10905 Do not use 'instance' in case of custom categories (same issue as ID-10102).
                    $dao = (new isys_cmdb_dao_category_g_custom_fields($this->database))->set_catg_custom_id($category['c_id']);
                    $prefix = 'c';
                } elseif ($category['s_id'] !== null) {
                    if (!class_exists($category['s_class_name']) || !is_a($category['s_class_name'], isys_cmdb_dao_category_specific::class, true)) {
                        continue;
                    }

                    $item = SpecificItem::class;
                    $dao = $category['s_class_name']::instance($this->database);
                    $prefix = 's';
                } else {
                    continue;
                }

                // @see ID-9684 Hide duplicated categories.
                if ($this->shouldSkipCategory($category["{$prefix}_const"])) {
                    continue;
                }

                $dao->set_object_type_id($this->objectTypeId);

                if ($this->objectId !== null) {
                    $dao->set_object_id($this->objectId);
                }

                $this->categories[str_pad($category['order'], 4, '0', STR_PAD_LEFT) . '.' . ($orderHelper++)] = $currentItem = new $item(
                    (int)$category["{$prefix}_id"],
                    $dao,
                    $category["{$prefix}_title"],
                    $this->language->get($category["{$prefix}_title"]),
                    $currentFolder->getId(),
                    [], // Categories won't have children.
                    $category["{$prefix}_const"],
                    (int)$category['id'],
                    $category["{$prefix}_source_table"],
                    $wwwPath . ($category["{$prefix}_icon"] ?? $this->defaultIcon)
                );

                $currentFolder->addChild($currentItem);
            }
        }

        // @see ID-10011 Add folders to parent folders, in order to make the 'hasData' calls work.
        foreach ($this->categories as $folder) {
            if (!($folder instanceof FolderItem)) {
                continue;
            }

            foreach ($this->categories as $subFolder) {
                if ($subFolder instanceof FolderItem && $folder->getId() === $subFolder->getParent()) {
                    $folder->addChild($subFolder);
                }
            }
        }

        ksort($this->categories);

        $this->categories = array_values($this->categories);
    }

    /**
     * @param AbstractItem[] $categories
     * @param AbstractItem[] $assignedCategories
     * @return array
     * @see ID-9584
     */
    private function processDelta(array $categories, array $assignedCategories): array
    {
        $configuredCategoryIdentifiers = array_map(
            fn (AbstractItem $item) => $item->getIdentifier(),
            array_filter(
                $categories,
                fn (AbstractItem $item) => !($item instanceof FolderItem) // Disregard folders.
            )
        );

        $assignedCategoryIdentifiers = array_map(
            fn (AbstractItem $item) => $item->getIdentifier(),
            array_filter(
                $assignedCategories,
                fn (AbstractItem $item) => !($item instanceof FolderItem) // Disregard folders.
            )
        );

        // Remove 'not assigned' categories.
        $categories = array_filter($categories, fn (AbstractItem $configuredCategory) => ($configuredCategory instanceof FolderItem) || in_array($configuredCategory->getIdentifier(), $assignedCategoryIdentifiers));

        /** @var FolderItem $rootItem */
        $rootItem = current(array_filter($categories, fn (AbstractItem $item) => $item instanceof FolderItem && $item->getParent() === null));

        $addedFolders = [];

        // Add missing categories.
        foreach ($assignedCategories as $assignedCategory) {
            if (!($assignedCategory instanceof FolderItem) && !in_array($assignedCategory->getIdentifier(), $configuredCategoryIdentifiers)) {
                // @see ID-10669 Check if the delta category is located in the root or somewhere else.
                if ($assignedCategory->getParent() !== null && $assignedCategory->getParent() != $rootItem->getId()) {
                    // Check if the parent has been added in a previous iteration.
                    if (!in_array($assignedCategory->getParent(), $addedFolders)) {
                        // Find the folder to add.
                        $folder = array_values(array_filter(
                            $assignedCategories,
                            fn (AbstractItem $item) => $item instanceof FolderItem && $item->getId() == $assignedCategory->getParent()
                        ));

                        // Found a folder - add it to the categories.
                        if (count($folder) > 0) {
                            /*
                             * In theory we could/should also check the parent of '$folder[0]' but because specific categories
                             * are always only "one" level deep, we can skip that additional logic.
                             */
                            array_unshift($categories, $folder[0]);
                            $addedFolders[] = $folder[0]->overwriteParent($rootItem->getId())->getId();
                        } else {
                            // No parent was found, move the category to the top.
                            array_unshift($categories, $assignedCategory->overwriteParent($rootItem->getId())->resetChildren());
                        }
                    }

                    // Add the delta category, but keep the original parent.
                    array_unshift($categories, $assignedCategory);
                } else {
                    // @see ID-10072 Move unmigrated elements to the top.
                    array_unshift($categories, $assignedCategory->overwriteParent($rootItem->getId())->resetChildren());
                }
            }
        }

        return $categories;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadGlobalCategoryAssignments(): void
    {
        // Get categories by object type.
        $result = $this->dao->get_global_categories($this->objectTypeId);

        while ($category = $result->get_row()) {
            $categoryId = (int)$category['isysgui_catg__id'];
            $categoryConstant = $category['isysgui_catg__const'];
            $categoryClass = $category['isysgui_catg__class_name'];
            $parentCategoryId = $categoryId;
            $isAlreadyAsChild = false;

            if (!defined($categoryConstant)) {
                continue;
            }

            // @see ID-9684 Hide duplicated categories.
            if ($this->shouldSkipCategory($categoryConstant)) {
                continue;
            }

            // Skip category when class does not exist.
            if (!class_exists($categoryClass) || !is_a($categoryClass, isys_cmdb_dao_category_global::class, true)) {
                continue;
            }

            $interfaces = class_implements($categoryClass);

            if (in_array(FeatureCheckInterface::class, $interfaces) && !$categoryClass::isFeatureEnabled()) {
                continue;
            }

            // Don't show a node for the overview page.
            if ($category['isysgui_catg__property'] & C__RECORD_PROPERTY__NOT_SHOW_IN_LIST) {
                continue;
            }

            $children = [];

            try {
                $children = $this->loadCategoryChildrenAssignments($parentCategoryId, 'g');

                // If we hae 'children' create a folder for that.
                if (count($children)) {
                    $this->categoryAssignments[] = new FolderItem(
                        $parentCategoryId,
                        $this->getPreparedDao(isys_cmdb_dao_category_g_virtual::class),
                        $category['isysgui_catg__title'],
                        $this->language->get($category['isysgui_catg__title']),
                        null,
                        $children,
                        $categoryConstant,
                        0 // In this context we have no raw ID
                    );
                }
            } catch (Throwable $l_exception) {
                isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
            }

            if (substr_compare($categoryConstant, '_ROOT', -5, 5) === 0) {
                $categoryConstantA = substr($categoryConstant, 0, strlen($categoryConstant) - 5);
                $isAlreadyAsChild = !empty(array_filter($children, fn ($item) => $item->getConstant() === $categoryConstantA));
            }

            if (!$isAlreadyAsChild) {
                $this->categoryAssignments[] = new GlobalItem(
                    $categoryId,
                    $this->getPreparedDao($categoryClass),
                    $category['isysgui_catg__title'],
                    $this->language->get($category['isysgui_catg__title']),
                    count($children) ? $parentCategoryId : null,
                    [], // Categories won't have children.
                    $categoryConstant,
                    0, // In this context we have no raw ID
                    $category['isysgui_catg__source_table'] // @todo The DAO should contain this already :(
                );
            }

            // Append children AFTER the parent, so that the references work out.
            foreach ($children as $child) {
                $this->categoryAssignments[] = $child;
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadCustomCategoryAssignments(): void
    {
        if (!defined('C__MODULE__CUSTOM_FIELDS') || !defined('C__CATG__CUSTOM_FIELDS') || !class_exists('isys_custom_fields_dao')) {
            return;
        }

        $wwwPath = isys_application::instance()->www_path;

        $result = isys_custom_fields_dao::instance($this->database)
            ->get_assignments(null, $this->objectTypeId);

        while ($category = $result->get_row()) {
            $categoryId = (int)$category['isysgui_catg_custom__id'];
            $categoryConstant = $category['isysgui_catg_custom__const'];

            if (!defined($categoryConstant)) {
                continue;
            }

            $this->categoryAssignments[] = new CustomItem(
                $categoryId,
                $this->getPreparedDao(isys_cmdb_dao_category_g_custom_fields::class)->set_catg_custom_id($categoryId),
                $category['isysgui_catg_custom__title'],
                $this->language->get($category['isysgui_catg_custom__title']),
                null,
                [], // Categories won't have children.
                $categoryConstant,
                0, // In this context we have no raw ID
                null,
                $wwwPath . ($category['isysgui_catg_custom__icon'] ?? $this->defaultIcon)
            );
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadSpecificCategoryAssignments(): void
    {
        $result = $this->dao->get_specific_category($this->objectTypeId);

        // Objects can only have one specific category assigned
        if (count($result) !== 1) {
            return;
        }

        $category = $result->get_row();

        $categoryId = (int)$category['isysgui_cats__id'];
        $categoryConstant = $category['isysgui_cats__const'];
        $categoryClass = $category['isysgui_cats__class_name'];

        if (!defined($categoryConstant)) {
            return;
        }

        // @see ID-9684 Hide duplicated categories.
        if ($this->shouldSkipCategory($categoryConstant)) {
            return;
        }

        // Skip category when class does not exist.
        if (!class_exists($categoryClass) || !is_a($categoryClass, isys_cmdb_dao_category_specific::class, true)) {
            return;
        }

        $interfaces = class_implements($categoryClass);

        if (in_array(FeatureCheckInterface::class, $interfaces) && !$categoryClass::isFeatureEnabled()) {
            return;
        }

        $folder = null;
        $children = [];

        // Don't create sub entry for category net in objecttype supernet. Supernets should not have any ip addresses or dhcp ranges.
        if ($this->objectTypeId != defined_or_default('C__OBJTYPE__SUPERNET') || $categoryId != defined_or_default('C__CATS__NET')) {
            try {
                $children = $this->loadCategoryChildrenAssignments($categoryId, 's');

                // If we hae 'children' create a folder for that.
                if (count($children)) {
                    $this->categoryAssignments[] = $folder = new FolderItem(
                        $categoryId,
                        $this->getPreparedDao(isys_cmdb_dao_category_g_virtual::class),
                        $category['isysgui_cats__title'],
                        $this->language->get($category['isysgui_cats__title']),
                        null,
                        $children,
                        $categoryConstant,
                        0 // In this context we have no raw ID
                    );
                }
            } catch (Throwable $l_exception) {
                isys_notify::error($l_exception->getMessage(), ['sticky' => true]);
            }
        }

        $this->categoryAssignments[] = $specific = new SpecificItem(
            $categoryId,
            $this->getPreparedDao($categoryClass),
            $category['isysgui_cats__title'],
            $this->language->get($category['isysgui_cats__title']),
            count($children) ? $categoryId : null,
            [], // Categories won't have children.
            $categoryConstant,
            0, // In this context we have no raw ID
            $category['isysgui_cats__source_table'] // @todo The DAO should contain this already :(
        );

        // @see ID-11531 Add the specific category to the folder, if that has not happened yet.
        if ($folder !== null) {
            $alreadyAdded = array_map(fn (AbstractItem $item) => $item->getIdentifier(), $folder->getChildren());

            if (!in_array($specific->getIdentifier(), $alreadyAdded)) {
                $folder->addChild($specific);
            }
        }

        // Append children AFTER the parent, so that the references work out.
        foreach ($children as $child) {
            $this->categoryAssignments[] = $child;
        }
    }

    /**
     * @param int    $parentCategoryId
     * @param string $type
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function loadCategoryChildrenAssignments(int $parentCategoryId, string $type): array
    {
        $children = [];
        $table = $type === 'g' ? 'isysgui_catg' : 'isysgui_cats';
        $itemInstance = $type === 'g' ? GlobalItem::class : SpecificItem::class;
        $result = $this->dao->get_isysgui($table, null, null, null, $parentCategoryId);

        while ($category = $result->get_row()) {
            $categoryId = (int)$category["{$table}__id"];
            $categoryConstant = $category["{$table}__const"];
            $categoryClass = $category["{$table}__class_name"];

            // Skip processing when dao class does not exist.
            if (!class_exists($categoryClass)) {
                continue;
            }

            // @see ID-9684 Hide duplicated categories.
            if ($this->shouldSkipCategory($categoryConstant)) {
                continue;
            }

            if (!is_a($categoryClass, isys_cmdb_dao_category_global::class, true) && !is_a($categoryClass, isys_cmdb_dao_category_specific::class, true)) {
                continue;
            }

            $interfaces = class_implements($categoryClass);

            if (in_array(FeatureCheckInterface::class, $interfaces) && !$categoryClass::isFeatureEnabled()) {
                continue;
            }

            $children[] = new $itemInstance(
                $categoryId,
                $this->getPreparedDao($categoryClass),
                $category["{$table}__title"],
                $this->language->get($category["{$table}__title"]),
                $parentCategoryId,
                [],
                $categoryConstant,
                0, // In this context we have no raw ID
                $category["{$table}__source_table"] // @todo The DAO should contain this already :(
            );
        }

        return $children;
    }

    /**
     * @param string $daoClass
     *
     * @return isys_cmdb_dao_category|null
     */
    private function getPreparedDao(string $daoClass): ?isys_cmdb_dao_category
    {
        if (!is_a($daoClass, isys_cmdb_dao_category::class, true)) {
            return null;
        }

        // @see ID-10102 Custom categories need their own instance (no singleton)
        if ($daoClass === isys_cmdb_dao_category_g_custom_fields::class) {
            $dao = new isys_cmdb_dao_category_g_custom_fields($this->database);
        } else {
            $dao = $daoClass::instance($this->database);
        }

        $dao->set_object_type_id($this->objectTypeId);

        if ($this->objectId !== null) {
            $dao->set_object_id($this->objectId);
        }

        return $dao;
    }

    /**
     * @param string $constant
     *
     * @return bool
     * @see ID-9684 Hide duplicated categories.
     */
    private function shouldSkipCategory(string $constant): bool
    {
        return in_array($constant, self::DUPLICATED_CATEGORIES, true)
            || ($this->skipSticky && in_array($constant, self::STICKY_CATEGORIES, true));
    }
}
