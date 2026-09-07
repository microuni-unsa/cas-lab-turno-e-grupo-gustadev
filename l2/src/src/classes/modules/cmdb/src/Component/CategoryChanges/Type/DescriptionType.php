<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;

/**
 * Class DescriptionType
 *
 * Special handling for the description property
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DescriptionType extends TextType implements TypeInterface
{
    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        return $tag === 'description' && $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__COMMENTARY;
    }
}
