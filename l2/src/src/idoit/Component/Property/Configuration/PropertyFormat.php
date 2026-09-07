<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyFormat extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * First Index: Instance or class name
     * Second Index: For instance public function name, for class name static function name
     *
     * [
     *     $this,
     *     'getTotalCapacitySdCard'
     * ]
     *
     * @var array|null
     */
    protected ?array $callback = [];

    /**
     * @var string|null
     */
    protected ?string $requires = null;

    /**
     * @var string|null
     */
    protected ?string $unit = null;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyFormat
     */
    public static function createInstanceFromArray(array $propertyArray = []): PropertyFormat
    {
        $propertyFormat = new static();

        return $propertyFormat->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyFormat
     */
    public function mapAttributes(array $propertyArray): PropertyFormat
    {
        $this->callback = $propertyArray[Property::C__PROPERTY__FORMAT__CALLBACK];
        $this->requires = $propertyArray[Property::C__PROPERTY__FORMAT__REQUIRES];
        $this->unit = $propertyArray[Property::C__PROPERTY__FORMAT__UNIT];

        return $this;
    }

    /**
     * @return array|null
     */
    public function getCallback(): ?array
    {
        return $this->callback;
    }

    /**
     * @param array $callback
     *
     * @return PropertyFormat
     */
    public function setCallback(array $callback): PropertyFormat
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequires(): ?string
    {
        return $this->requires;
    }

    /**
     * @param string $requires
     *
     * @return PropertyFormat
     */
    public function setRequires($requires): PropertyFormat
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return PropertyFormat
     */
    public function setUnit($unit): PropertyFormat
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            return $this->callback !== null;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            return $this->requires !== null;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            return $this->unit !== null;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            return $this->callback;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            return $this->requires;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            return $this->unit;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            $this->callback = $value;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            $this->requires = $value;
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            $this->unit = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        if ($offset === Property::C__PROPERTY__FORMAT__CALLBACK) {
            unset($this->callback);
        }

        if ($offset === Property::C__PROPERTY__FORMAT__REQUIRES) {
            unset($this->requires);
        }

        if ($offset === Property::C__PROPERTY__FORMAT__UNIT) {
            unset($this->unit);
        }
    }
}
