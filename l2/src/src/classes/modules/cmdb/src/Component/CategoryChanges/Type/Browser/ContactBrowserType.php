<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;

class ContactBrowserType extends ObjectBrowserType implements TypeInterface, ObjectBrowserTypeInterface
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
        $callback = $property->getFormat()->getCallback();

        return is_array($callback) && isset($callback[1]) && $callback[1] === 'exportContactAssignment';
    }
}
