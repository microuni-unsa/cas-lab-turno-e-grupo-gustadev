<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class StringShape extends AbstractShape implements ShapeInterface
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        return is_string($value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }
}
