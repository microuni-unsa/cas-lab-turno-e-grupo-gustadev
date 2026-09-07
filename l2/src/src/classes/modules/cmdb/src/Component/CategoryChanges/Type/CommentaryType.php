<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;

/**
 * Class CommentaryType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class CommentaryType extends TextType implements TypeInterface
{
    /**
     * @var array
     */
    const TYPES = [
        Property::C__PROPERTY__INFO__TYPE__COMMENTARY,
        Property::C__PROPERTY__INFO__TYPE__TEXTAREA
    ];

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        return $tag !== 'description' && in_array($property->getInfo()->getType(), self::TYPES, true);
    }
}
