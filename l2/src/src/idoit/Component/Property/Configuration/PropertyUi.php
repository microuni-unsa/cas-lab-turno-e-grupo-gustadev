<?php

namespace idoit\Component\Property\Configuration;

use idoit\Component\Property\Exception\UnknownTypeException;
use idoit\Component\Property\LegacyPropertyCreatorInterface;
use idoit\Component\Property\Property;
use idoit\Component\Property\PropertyConfiguration;

class PropertyUi extends PropertyConfiguration implements LegacyPropertyCreatorInterface
{
    /**
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * One of C__PROPERTY__UI__TYPE__*
     *
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var string|null
     */
    protected ?string $placeholder = null;

    /**
     * @var string|null
     */
    protected ?string $emptyMessage = null;

    // C__PROPERTY__UI__PARAMS START
    /*protected $table;
    protected $data;
    protected $identifier;
    protected $fieldNotNullable;
    protected $categoryFilter;
    protected $cssClass;
    protected $disableInputGroup;
    protected $infoIconSpacer;
    protected $paramPlaceholder;
    protected $paramDefault;
    protected $jsOnChange;
    protected $paramEmptyMessage;
    protected $multiselection;
    protected $inputGroupMarginClass;
    protected $popupType;
    protected $retrieveDataFunction;*/
    // C__PROPERTY__UI__PARAMS END

    protected ?array $params = null;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return PropertyUi
     *
     * @throws UnknownTypeException
     */
    public static function createInstanceFromArray(array $propertyArray = []): PropertyUi
    {
        if (!defined('C__PROPERTY__UI__TYPE__' . strtoupper(ltrim($propertyArray[Property::C__PROPERTY__UI__TYPE], 'f_')))) {
            throw new UnknownTypeException('Unknown type: ' . 'C__PROPERTY__UI__TYPE__' . strtoupper($propertyArray[Property::C__PROPERTY__UI__TYPE]));
        }

        $propertyUi = new static();

        return $propertyUi->mapAttributes($propertyArray);
    }

    /**
     * Sets all member variables
     *
     * @param array $propertyArray
     *
     * @return PropertyUi
     */
    public function mapAttributes(array $propertyArray): PropertyUi
    {
        $this->id = $propertyArray[Property::C__PROPERTY__UI__ID];
        $this->default = $propertyArray[Property::C__PROPERTY__UI__DEFAULT];
        $this->placeholder = $propertyArray[Property::C__PROPERTY__UI__PLACEHOLDER];
        $this->emptyMessage = $propertyArray[Property::C__PROPERTY__UI__EMPTYMESSAGE];
        $this->type = $propertyArray[Property::C__PROPERTY__UI__TYPE];
        $this->params = $propertyArray[Property::C__PROPERTY__UI__PARAMS];

        /*
        $this->table = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strTable'];
        $this->data = $propertyArray[C__PROPERTY__UI__PARAMS]['p_arData'];
        $this->identifier = $propertyArray[C__PROPERTY__UI__PARAMS]['identifier'];
        $this->fieldNotNullable = $propertyArray[C__PROPERTY__UI__PARAMS]['p_bDbFieldNN'];
        $this->categoryFilter = $propertyArray[C__PROPERTY__UI__PARAMS]['catFilter'];
        $this->cssClass = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strClass'];
        $this->disableInputGroup = $propertyArray[C__PROPERTY__UI__PARAMS]['disableInputGroup'];
        $this->infoIconSpacer = $propertyArray[C__PROPERTY__UI__PARAMS]['p_bInfoIconSpacer'];
        $this->paramPlaceholder = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strPlaceholder'];
        $this->paramDefault = $propertyArray[C__PROPERTY__UI__PARAMS]['default'];
        $this->jsOnChange = $propertyArray[C__PROPERTY__UI__PARAMS]['p_onChange'];
        $this->paramEmptyMessage = $propertyArray[C__PROPERTY__UI__PARAMS]['emptyMessage'];
        $this->multiselection = $propertyArray[C__PROPERTY__UI__PARAMS]['multiselection'];
        $this->inputGroupMarginClass = $propertyArray[C__PROPERTY__UI__PARAMS]['inputGroupMarginClass'];
        $this->popupType = $propertyArray[C__PROPERTY__UI__PARAMS]['p_strPopupType'];
        $this->retrieveDataFunction = $propertyArray[C__PROPERTY__UI__PARAMS]['dataretrieval'];
        */

        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return PropertyUi
     */
    public function setId($id): PropertyUi
    {
        $this->id = $id;

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
     * @return PropertyUi
     */
    public function setType($type): PropertyUi
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return PropertyUi
     */
    public function setDefault($default): PropertyUi
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @param string $placeholder
     *
     * @return PropertyUi
     */
    public function setPlaceholder($placeholder): PropertyUi
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmptyMessage(): ?string
    {
        return $this->emptyMessage;
    }

    /**
     * @param string $emptyMessage
     *
     * @return PropertyUi
     */
    public function setEmptyMessage($emptyMessage): PropertyUi
    {
        $this->emptyMessage = $emptyMessage;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array $params
     *
     * @return PropertyUi
     */
    public function setParams(array $params): PropertyUi
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            return $this->id !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            return $this->default !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            return $this->placeholder !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            return $this->emptyMessage !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            return $this->type !== null;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            return $this->params !== null;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            return $this->id;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            return $this->default;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            return $this->placeholder;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            return $this->emptyMessage;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            return $this->type;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            return $this->params;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            $this->id = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            $this->default = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            $this->placeholder = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            $this->emptyMessage = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            $this->type = $value;
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            $this->params = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        if ($offset === Property::C__PROPERTY__UI__ID) {
            unset($this->id);
        }

        if ($offset === Property::C__PROPERTY__UI__DEFAULT) {
            unset($this->default);
        }

        if ($offset === Property::C__PROPERTY__UI__PLACEHOLDER) {
            unset($this->placeholder);
        }

        if ($offset === Property::C__PROPERTY__UI__EMPTYMESSAGE) {
            unset($this->emptyMessage);
        }

        if ($offset === Property::C__PROPERTY__UI__TYPE) {
            unset($this->type);
        }

        if ($offset === Property::C__PROPERTY__UI__PARAMS) {
            unset($this->params);
        }
    }
}
