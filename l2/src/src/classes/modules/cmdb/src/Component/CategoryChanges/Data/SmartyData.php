<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

/**
 * Class SmartyData
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class SmartyData extends AbstractData
{
    /**
     * @param mixed $data
     *
     * @return static
     */
    public static function factory($data)
    {
        $object = new static();
        $object->setData($data);

        return $object;
    }
}
