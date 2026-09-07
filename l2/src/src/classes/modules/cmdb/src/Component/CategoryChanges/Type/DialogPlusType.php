<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;

/**
 * Class DialogPlusType
 *
 * Handling dialog plus types
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class DialogPlusType extends DialogType implements TypeInterface
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
        $references = $property->getData()->getReferences();
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_PLUS && is_array($references) && !empty($references);
    }
}
