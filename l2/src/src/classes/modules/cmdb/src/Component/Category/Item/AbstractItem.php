<?php

namespace idoit\Module\Cmdb\Component\Category\Item;

use idoit\Component\Helper\Purify;
use isys_cmdb_dao_category;
use isys_cmdb_dao_object_type;
use isys_module_custom_fields;

/**
 * Abstract category item class.
 */
abstract class AbstractItem
{
    public const PREFIX = '';

    protected int $id;

    protected isys_cmdb_dao_category $dao;

    protected string $name;

    protected string $translatedName;

    protected string $translatedNameAscii;

    protected ?int $parent;

    /**
     * @var AbstractItem[]
     */
    protected array $children;

    protected string $constant;

    protected int $rawId;

    protected ?string $table;

    protected string $icon;

    protected ?bool $hasData = null;

    /**
     * @param int                    $id
     * @param isys_cmdb_dao_category $dao
     * @param string                 $name
     * @param string                 $translatedName
     * @param int|null               $parent
     * @param array                  $children
     * @param string                 $constant
     * @param int                    $rawId
     * @param string|null            $table
     */
    public function __construct(
        int $id,
        isys_cmdb_dao_category $dao,
        string $name,
        string $translatedName,
        ?int $parent,
        array $children,
        string $constant,
        int $rawId,
        ?string $table = null,
        ?string $icon = null,
    ) {
        $defaultIcon = class_exists('isys_module_custom_fields')
            ? isys_module_custom_fields::DEFAULT_CATEGORY_ICON
            : 'images/axialis/documents-folders/document-color-grey.svg';

        $this->id = $id;
        $this->dao = $dao;
        $this->name = $name;
        $this->translatedName = $translatedName;
        $this->translatedNameAscii = Purify::transliterate($translatedName);
        $this->parent = $parent;
        $this->children = $children;
        $this->constant = $constant;
        $this->rawId = $rawId;
        $this->table = $table;
        $this->icon = $icon ?? $defaultIcon;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return static::PREFIX . $this->id;
    }

    /**
     * @return int
     */
    public function getRawId(): int
    {
        return $this->rawId;
    }

    /**
     * @return isys_cmdb_dao_category
     */
    public function getDao(): isys_cmdb_dao_category
    {
        return $this->dao;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTranslatedName(): string
    {
        return $this->translatedName;
    }

    /**
     * @return string
     */
    public function getTranslatedNameAscii(): string
    {
        return $this->translatedNameAscii;
    }

    /**
     * @return int|null
     */
    public function getParent(): ?int
    {
        return $this->parent;
    }

    /**
     * @param int|null $newParentId
     * @return $this
     */
    public function overwriteParent(?int $newParentId): self
    {
        $this->parent = $newParentId;

        return $this;
    }

    /**
     * @return $this
     */
    public function resetChildren(): self
    {
        $this->children = [];

        return $this;
    }

    /**
     * @return AbstractItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param AbstractItem[] $children
     *
     * @return void
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getConstant(): string
    {
        return $this->constant;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param int $objectId
     *
     * @return string
     */
    abstract public function getUrl(int $objectId): string;

    /**
     * @param isys_cmdb_dao_object_type $dao
     * @param int                       $objectId
     *
     * @return bool|null
     */
    abstract public function hasData(isys_cmdb_dao_object_type $dao, int $objectId): ?bool;
}
