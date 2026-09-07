<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Exception\UnknownTypeException;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyInfo extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * @todo maybe PropertyTypeInterface?
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * @var bool|null
     */
    protected ?bool $primaryField = false;

    /**
     * @var bool|null
     */
    protected ?bool $backwardCompatible = false;

    /**
     * @var string|null
     */
    protected $backwardProperty = null;

    /**
     * @var string|null
     */
    protected $linkedProperty = null;

    /**
     * @var bool|null
     */
    protected ?bool $alwaysInLogbook = false;

    /**
     * @var null
     */
    protected $backwardCategory = null;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyInfo
     *
     * @throws UnknownTypeException
     */
    public static function createInstanceFromArray(array $propertyArray = []): PropertyInfo
    {
        if (!defined('C__PROPERTY__INFO__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__INFO__TYPE]))) {
            throw new UnknownTypeException('Unkown type: ' . 'C__PROPERTY__INFO__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__INFO__TYPE]));
        }

        $propertyInfo = new static();

        return $propertyInfo->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyInfo
     */
    public function mapAttributes(array $propertyArray): PropertyInfo
    {
        $this->title = $propertyArray[Property::C__PROPERTY__INFO__TITLE];
        $this->description = $propertyArray[Property::C__PROPERTY__INFO__DESCRIPTION];
        $this->type = $propertyArray[Property::C__PROPERTY__INFO__TYPE];
        $this->primaryField = (bool)$propertyArray[Property::C__PROPERTY__INFO__PRIMARY];
        $this->backwardCompatible = (bool)$propertyArray[Property::C__PROPERTY__INFO__BACKWARD];
        $this->backwardProperty = $propertyArray[Property::C__PROPERTY__INFO__BACKWARD_PROPERTY];
        $this->linkedProperty = $propertyArray[Property::C__PROPERTY__INFO__LINKED_PROPERTY];
        $this->alwaysInLogbook = (bool)$propertyArray[Property::C__PROPERTY__INFO__ALWAYS_IN_LOGBOOK];
        $this->backwardCategory = $propertyArray[Property::C__PROPERTY__INFO__BACKWARD_CATEGORY];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return PropertyInfo
     */
    public function setTitle($title): PropertyInfo
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return PropertyInfo
     */
    public function setDescription($description): PropertyInfo
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return PropertyInfo
     */
    public function setType($type): PropertyInfo
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimaryField(): bool
    {
        return (bool) $this->primaryField;
    }

    /**
     * @param bool $primaryField
     *
     * @return PropertyInfo
     */
    public function setPrimaryField($primaryField): PropertyInfo
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBackwardCompatible(): bool
    {
        return (bool) $this->backwardCompatible;
    }

    /**
     * @param bool $backwardCompatible
     *
     * @return PropertyInfo
     */
    public function setBackwardCompatible($backwardCompatible): PropertyInfo
    {
        $this->backwardCompatible = $backwardCompatible;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackwardProperty(): ?string
    {
        return $this->backwardProperty;
    }

    /**
     * @param string|null $backwardProperty
     *
     * @return $this
     */
    public function setBackwardProperty(?string $backwardProperty = null): static
    {
        $this->backwardProperty = $backwardProperty;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getLinkedProperty(): ?string
    {
        return $this->linkedProperty;
    }

    /**
     * @param string|null $linkedProperty
     *
     * @return $this
     */
    public function setLinkedProperty(?string $linkedProperty = null): static
    {
        $this->linkedProperty = $linkedProperty;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlwaysInLogbook(): bool
    {
        return (bool) $this->alwaysInLogbook;
    }

    /**
     * @param bool $alwaysInLogbook
     *
     * @return $this
     */
    public function setAlwaysInLogbook(bool $alwaysInLogbook): static
    {
        $this->alwaysInLogbook = $alwaysInLogbook;

        return $this;
    }

    /**
     * @return null
     */
    public function getBackwardCategory(): mixed
    {
        return $this->backwardCategory;
    }

    /**
     * @param null $backwardCategory
     */
    public function setBackwardCategory($backwardCategory): void
    {
        $this->backwardCategory = $backwardCategory;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            return $this->title !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            return $this->description !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            return $this->type !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            return $this->primaryField !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            return $this->backwardCompatible !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_PROPERTY) {
            return $this->backwardProperty !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__LINKED_PROPERTY) {
            return $this->linkedProperty !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__ALWAYS_IN_LOGBOOK) {
            return $this->alwaysInLogbook !== null;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_CATEGORY) {
            return $this->backwardCategory !== null;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            return $this->title;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            return $this->description;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            return $this->type;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            return $this->primaryField;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            return $this->backwardCompatible;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_PROPERTY) {
            return $this->backwardProperty;
        }

        if ($offset === Property::C__PROPERTY__INFO__LINKED_PROPERTY) {
            return $this->linkedProperty;
        }

        if ($offset === Property::C__PROPERTY__INFO__ALWAYS_IN_LOGBOOK) {
            return $this->alwaysInLogbook;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_CATEGORY) {
            return $this->backwardCategory;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            $this->title = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            $this->description = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            $this->type = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            $this->primaryField = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            $this->backwardCompatible = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_PROPERTY) {
            $this->backwardProperty = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__LINKED_PROPERTY) {
            $this->linkedProperty = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__ALWAYS_IN_LOGBOOK) {
            $this->alwaysInLogbook = $value;
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_CATEGORY) {
            $this->backwardCategory = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        if ($offset === Property::C__PROPERTY__INFO__TITLE) {
            unset($this->title);
        }

        if ($offset === Property::C__PROPERTY__INFO__DESCRIPTION) {
            unset($this->description);
        }

        if ($offset === Property::C__PROPERTY__INFO__TYPE) {
            unset($this->type);
        }

        if ($offset === Property::C__PROPERTY__INFO__PRIMARY) {
            unset($this->primaryField);
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD) {
            unset($this->backwardCompatible);
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_PROPERTY) {
            unset($this->backwardProperty);
        }

        if ($offset === Property::C__PROPERTY__INFO__LINKED_PROPERTY) {
            unset($this->linkedProperty);
        }

        if ($offset === Property::C__PROPERTY__INFO__ALWAYS_IN_LOGBOOK) {
            unset($this->alwaysInLogbook);
        }

        if ($offset === Property::C__PROPERTY__INFO__BACKWARD_CATEGORY) {
            unset($this->backwardCategory);
        }
    }
}
