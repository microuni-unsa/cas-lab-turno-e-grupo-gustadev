<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class FloatShape extends AbstractShape implements ShapeInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        return is_numeric($value) && is_float($value) && is_double($value);
    }
}
