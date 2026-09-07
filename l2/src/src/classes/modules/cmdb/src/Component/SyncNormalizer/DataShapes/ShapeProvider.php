<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

class ShapeProvider
{
    /**
     * @var iterable|ShapeInterface[]
     */
    private $shapes;

    /**
     * CollectionCategoryConfigurationSource constructor.
     *
     * @param iterable|ShapeInterface[] $shapes
     */
    public function __construct(iterable $shapes = [])
    {
        $this->shapes = $shapes;
    }

    /**
     * @param $value
     *
     * @return AbstractShape|null
     */
    public function getShape($value): ?AbstractShape
    {
        foreach ($this->shapes as $shape) {
            if ($shape->isApplicable($value)) {
                $clonedShape = clone $shape;
                if ($clonedShape instanceof ListShape) {
                    $shapeValue = $value;
                    array_walk($shapeValue, function (&$item) {
                        $item = $this->getShape($item);
                    });
                    $value = $shapeValue;
                }

                $clonedShape->setValue($value);

                return $clonedShape;
            }
        }
        return null;
    }
}
