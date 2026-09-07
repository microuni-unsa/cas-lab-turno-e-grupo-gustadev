<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Property;

use idoit\Module\Cmdb\Component\CategoryChanges\Type\AbstractType;

abstract class AbstractPropertyType extends AbstractType
{
    /**
     * category dao class
     */
    protected const PROPERTY_CLASS = '';

    /**
     * property tag
     */
    protected const PROPERTY_TAG = '';
}
