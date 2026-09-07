<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class IntegerShape extends AbstractShape implements ShapeInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        return is_int($value);
    }
}
