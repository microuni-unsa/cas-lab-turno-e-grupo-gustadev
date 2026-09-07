<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Property;

use idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\DatabaseAssignment\PropertyRunsOn;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Property\Location\PropertyGps;

class PropertyTypeFacade
{
    /**
     * @return PropertyType
     */
    public static function getService(): PropertyType
    {
        $properties = [
            new PropertyRunsOn(),
            new PropertyGps()
        ];

        return new PropertyType($properties);
    }
}
