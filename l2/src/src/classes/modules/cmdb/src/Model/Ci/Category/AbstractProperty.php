<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

use isys_application;
use isys_component_database;
use isys_component_template_language_manager;

abstract class AbstractProperty
{
    protected static string $method = '';

    protected static string $className = '';

    /**
     * @param string $className
     * @param string $method
     *
     * @return bool
     */
    public function isApplicable(string $className, string $method): bool
    {
        return static::$className === $className && static::$method === $method;
    }
}
