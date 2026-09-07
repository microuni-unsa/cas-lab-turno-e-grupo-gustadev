<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

class PropertyDependency extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var string|null
     */
    protected ?string $propkey = null;

    /**
     * @var array|null
     */
    protected ?array $smartyParams = [];

    /**
     * @var string|null
     */
    protected ?string $condition = null;

    /**
     * @var string|null
     */
    protected ?string $conditionValue = null;

    /**
     * @var SelectSubSelect|null
     */
    protected ?SelectSubSelect $select = null;

    /**
     * @return string|null
     */
    public function getPropkey(): ?string
    {
        return $this->propkey;
    }

    /**
     * @param string $propkey
     */
    public function setPropkey($propkey)
    {
        $this->propkey = $propkey;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSmartyParams(): ?array
    {
        return $this->smartyParams;
    }

    /**
     * @param array $smartyParams
     */
    public function setSmartyParams($smartyParams)
    {
        $this->smartyParams = $smartyParams;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConditionValue(): ?string
    {
        return $this->conditionValue;
    }

    /**
     * @param string $conditionValue
     */
    public function setConditionValue($conditionValue)
    {
        $this->conditionValue = $conditionValue;

        return $this;
    }

    /**
     * @return SelectSubSelect|null
     */
    public function getSelect(): ?SelectSubSelect
    {
        return $this->select;
    }

    /**
     * @param SelectSubSelect $select
     */
    public function setSelect($select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyDependency
     */
    public static function createInstanceFromArray(array $propertyArray = []): PropertyDependency
    {
        $propertyData = new static();

        return $propertyData->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyDependency
     */
    public function mapAttributes(array $propertyArray): PropertyDependency
    {
        $this->propkey = $propertyArray[Property::C__PROPERTY__DEPENDENCY__PROPKEY];
        $this->smartyParams = $propertyArray[Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS];
        $this->condition = $propertyArray[Property::C__PROPERTY__DEPENDENCY__CONDITION];
        $this->conditionValue = $propertyArray[Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE];
        $this->select = $propertyArray[Property::C__PROPERTY__DEPENDENCY__SELECT];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            return $this->smartyParams !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            return $this->condition !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            return $this->propkey !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            return $this->conditionValue !== null;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            return $this->select !== null;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            return $this->smartyParams;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            return $this->condition;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            return $this->propkey;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            return $this->conditionValue;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            return $this->select;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            $this->smartyParams = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            $this->condition = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            $this->propkey = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            $this->conditionValue = $value;
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            $this->select = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        if ($offset === Property::C__PROPERTY__DEPENDENCY__SMARTYPARAMS) {
            unset($this->smartyParams);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION) {
            unset($this->condition);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__PROPKEY) {
            unset($this->propkey);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__CONDITION_VALUE) {
            unset($this->conditionValue);
        }

        if ($offset === Property::C__PROPERTY__DEPENDENCY__SELECT) {
            unset($this->select);
        }
    }
}
