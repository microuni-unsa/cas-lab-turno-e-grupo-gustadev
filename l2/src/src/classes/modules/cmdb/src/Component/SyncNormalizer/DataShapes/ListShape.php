<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class ListShape extends AbstractShape implements ShapeInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = array_keys($value);
        $filteredKeys = array_filter($keys, 'is_numeric');

        return count($keys) === count($filteredKeys);
    }
}
