<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

abstract class AbstractShape
{
    protected $value;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
