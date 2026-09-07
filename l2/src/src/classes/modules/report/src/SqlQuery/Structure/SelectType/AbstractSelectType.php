<?php

namespace idoit\Module\Report\SqlQuery\Structure\SelectType;

use idoit\Component\Property\Property;

abstract class AbstractSelectType
{
    /**
     * @var Property
     */
    private Property $property;

    /**
     * @var string
     */
    private string $alias;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @return string
     */
    abstract public function buildSelect(): string;

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property): void
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param string $condition
     *
     * @return void
     */
    public function addCondition(string $condition): void
    {
        $this->conditions[] = $condition;
    }
}
