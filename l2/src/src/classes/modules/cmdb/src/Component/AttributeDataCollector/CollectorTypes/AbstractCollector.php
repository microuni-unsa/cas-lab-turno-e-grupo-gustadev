<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Exceptions\NoProperCollectorTypeFoundException;

abstract class AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    abstract public function isApplicable(Property $property): bool;

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     */
    abstract protected function fetchData(Property $property, bool $reformat): array;

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws NoProperCollectorTypeFoundException
     */
    public function collectData(Property $property, bool $reformat): array
    {
        if (!$this->isApplicable($property)) {
            throw new NoProperCollectorTypeFoundException('No proper Collector type found for property.');
        }

        return $this->fetchData($property, $reformat);
    }
}
