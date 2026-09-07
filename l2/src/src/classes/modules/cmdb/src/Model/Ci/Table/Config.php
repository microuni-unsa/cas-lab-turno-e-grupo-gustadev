<?php

namespace idoit\Module\Cmdb\Model\Ci\Table;

use isys_application;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_category_property_ng;
use isys_format_json;

/**
 * i-doit
 *
 * @package    i-doit
 * @subpackage Cmdb
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Config
{
    private array $properties = [];

    private ?string $sortingProperty = null;

    private ?string $sortingDirection = null;

    private ?string $filterProperty = null;

    private ?string $filterValue = null;

    private bool $rowClickable = true;

    private int $groupingType = isys_cmdb_dao_category_property_ng::C__GROUPING__LIST;

    private ?bool $filterWildcard = false;

    private ?bool $broadsearch = false;

    private ?string $advancedOptionMemoryUnit = null;

    private ?int $paging = null;

    private ?int $rowsPerPage = null;

    private ?bool $showEmailLinks = true;

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param Property $properties
     *
     * @return $this
     */
    public function addProperty(Property $properties): self
    {
        $this->properties[] = $properties;

        return $this;
    }

    /**
     * @param Property[] $properties
     *
     * @return $this
     * @throws \isys_exception_general
     */
    public function setProperties(array $properties): self
    {
        foreach ($properties as $property) {
            if (!is_a($property, 'idoit\\Module\\Cmdb\\Model\\Ci\\Table\\Property')) {
                throw new \isys_exception_general('All array items need to be instances of "idoit\\Module\\Cmdb\\Model\\Ci\\Table\\Property"!');
            }
        }

        $this->properties = $properties;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSortingProperty(): ?string
    {
        return $this->sortingProperty;
    }

    /**
     * @param string|null $sortingProperty
     *
     * @return $this
     */
    public function setSortingProperty(?string $sortingProperty): self
    {
        $this->sortingProperty = $sortingProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortingDirection(): ?string
    {
        return $this->sortingDirection;
    }

    /**
     * @param string $sortingDirection
     *
     * @return $this
     */
    public function setSortingDirection(?string $sortingDirection): self
    {
        if ($sortingDirection === null) {
            return $this;
        }

        $sortingDirection = strtoupper($sortingDirection);

        if ($sortingDirection === 'ASC' || $sortingDirection === 'DESC') {
            $this->sortingDirection = $sortingDirection;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilterProperty(): ?string
    {
        return $this->filterProperty;
    }

    /**
     * @param string|null $filterProperty
     *
     * @return $this
     */
    public function setFilterProperty(?string $filterProperty): self
    {
        $this->filterProperty = $filterProperty;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilterValue(): ?string
    {
        return $this->filterValue;
    }

    /**
     * @param string|null $filterValue
     *
     * @return $this
     */
    public function setFilterValue(?string $filterValue): self
    {
        $this->filterValue = $filterValue;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRowClickable(): bool
    {
        return $this->rowClickable;
    }

    /**
     * @param bool $rowClickable
     *
     * @return $this
     */
    public function setRowClickable(bool $rowClickable): self
    {
        $this->rowClickable = $rowClickable;

        return $this;
    }

    /**
     * @return int
     */
    public function getGroupingType(): int
    {
        return (int)$this->groupingType;
    }

    /**
     * @param int $groupingType
     *
     * @return $this
     */
    public function setGroupingType(int $groupingType): self
    {
        $this->groupingType = $groupingType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFilterWildcard(): bool
    {
        return (bool)$this->filterWildcard;
    }

    /**
     * @param bool $filterWildcard
     *
     * @return $this
     */
    public function setFilterWildcard(bool $filterWildcard): self
    {
        $this->filterWildcard = $filterWildcard;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdvancedOptionMemoryUnit(): ?string
    {
        return $this->advancedOptionMemoryUnit;
    }

    /**
     * @param string|null $advancedOptionMemoryUnit
     *
     * @return $this
     */
    public function setAdvancedOptionMemoryUnit(?string $advancedOptionMemoryUnit): self
    {
        $this->advancedOptionMemoryUnit = $advancedOptionMemoryUnit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBroadsearch(): bool
    {
        return (bool)$this->broadsearch;
    }

    /**
     * @param bool $broadsearch
     *
     * @return $this
     */
    public function setBroadsearch(bool $broadsearch): self
    {
        $this->broadsearch = $broadsearch;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaging(): ?int
    {
        return $this->paging;
    }

    /**
     * @param int|null $paging
     *
     * @return $this
     */
    public function setPaging(?int $paging): self
    {
        $this->paging = $paging;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRowsPerPage(): ?int
    {
        return $this->rowsPerPage;
    }

    /**
     * @param int|null $rowsPerPage
     *
     * @return $this
     */
    public function setRowsPerPage(?int $rowsPerPage): self
    {
        $this->rowsPerPage = $rowsPerPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowEmailLinks(): bool
    {
        return (bool)$this->showEmailLinks;
    }

    /**
     * @param bool $showEmailLinks
     *
     * @return $this
     */
    public function setShowEmailLinks(bool $showEmailLinks): self
    {
        $this->showEmailLinks = $showEmailLinks;

        return $this;
    }

    /**
     * @param string        $property
     * @param isys_cmdb_dao $dao
     *
     * @return Property|null
     * @throws \isys_exception_database
     */
    private static function getProperty(string $property, isys_cmdb_dao $dao): ?Property
    {
        $database = isys_application::instance()->container->get('database');

        if (strpos($property, 'p::') === 0) {
            [, $propertyKey, $name, $filterable] = explode('::', $property);

            // Split the key up into 'field' and 'key.
            [$field, $key] = explode('__', $propertyKey);

            return new Property(
                $field,
                $key,
                $field,
                $name,
                true,
                null,
                null,
                (bool)$filterable
            );
        }

        [$categoryConstant, $key] = explode('::', $property);

        $categoryDefinition = $dao->get_cat_by_const($categoryConstant);

        if (empty($categoryDefinition)) {
            return null;
        }

        $className = $categoryDefinition['class_name'];

        if (!class_exists($className)) {
            return null;
        }

        if (!is_a($className, isys_cmdb_dao_category::class, true)) {
            return null;
        }

        /** @var isys_cmdb_dao_category $dao */
        $dao = $className::instance($database);

        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $dao->set_catg_custom_id($categoryDefinition['id']);
        }

        $propertyDefinition = $dao->get_property_by_key($key);

        if (!$propertyDefinition) {
            return null;
        }

        return new Property(
            $className,
            $key,
            $dao->getCategoryTitle(),
            $propertyDefinition[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE],
            $propertyDefinition[C__PROPERTY__DATA][C__PROPERTY__DATA__INDEX],
            $dao instanceof isys_cmdb_dao_category_g_custom_fields ? $categoryDefinition['id'] : null,
            $propertyDefinition[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
            $propertyDefinition[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__FILTERABLE]
        );
    }

    /**
     * @param string $json
     *
     * @return self
     * @throws \idoit\Exception\JsonException
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    public static function fromJson(string $json): self
    {
        $configurationData = isys_format_json::decode($json);
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $properties = [];

        foreach ($configurationData['properties'] as $propertyKey) {
            $properties[] = self::getProperty($propertyKey, $dao);
        }

        return (new self())
            ->setAdvancedOptionMemoryUnit($configurationData['advancedOptionMemoryUnit'])
            ->setBroadsearch((bool)$configurationData['broadsearch'])
            ->setFilterProperty($configurationData['filterProperty'])
            ->setFilterValue($configurationData['filterValue'])
            ->setFilterWildcard((bool)$configurationData['filterWildcard'])
            ->setGroupingType((int)$configurationData['groupingType'])
            ->setPaging($configurationData['paging'])
            ->setProperties(array_filter($properties))
            ->setRowClickable((bool)$configurationData['rowClickable'])
            ->setRowsPerPage($configurationData['rowsPerPage'])
            ->setShowEmailLinks((bool)$configurationData['showEmailLinks'])
            ->setSortingDirection($configurationData['sortingDirection'])
            ->setSortingProperty($configurationData['sortingProperty']);
    }

    /**
     * @param string|null $categoryContext
     *
     * @return string
     * @throws \Exception
     */
    public function toJson(?string $categoryContext = null): string
    {
        $database = isys_application::instance()->container->get('database');
        $properties = [];

        foreach ($this->getProperties() as $property) {
            if ($categoryContext !== null) {
                $properties[] = "p::{$property->getPropertyKey()}::{$property->getName()}::" . ((int)$property->isFilterable());
                continue;
            }

            $daoClassName = $property->getClass();

            if (!class_exists($daoClassName)) {
                continue;
            }

            if (!is_a($daoClassName, isys_cmdb_dao_category::class, true)) {
                continue;
            }

            $dao = $daoClassName::instance($database);
            $constant = $dao->get_category_const();

            if ($dao instanceof isys_cmdb_dao_category_g_custom_fields && $property->getCustomCatID()) {
                $dao->set_catg_custom_id($property->getCustomCatID());
                $dao->setCategoryInfo($property->getCustomCatID());
                $constant = $dao->get_catg_custom_const();
            }

            $properties[] = "{$constant}::{$property->getKey()}";
        }

        return isys_format_json::encode([
            'advancedOptionMemoryUnit' => $this->getAdvancedOptionMemoryUnit(),
            'broadsearch'              => (bool)$this->isBroadsearch(),
            'filterProperty'           => $this->getFilterProperty(),
            'filterValue'              => $this->getFilterValue(),
            'filterWildcard'           => (bool)$this->isFilterWildcard(),
            'groupingType'             => (int)$this->getGroupingType(),
            'paging'                   => $this->getPaging(),
            'properties'               => $properties,
            'rowClickable'             => $this->isRowClickable(),
            'rowsPerPage'              => $this->getRowsPerPage(),
            'showEmailLinks'           => (bool)$this->getShowEmailLinks(),
            'sortingDirection'         => $this->getSortingDirection(),
            'sortingProperty'          => $this->getSortingProperty(),
        ]);
    }
}
