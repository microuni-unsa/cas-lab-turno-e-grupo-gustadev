<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class DialogShape extends AbstractShape implements ShapeInterface
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

        return isset($value['id'], $value['title']);
    }
}
