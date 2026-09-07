<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

interface ShapeInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool;
}
