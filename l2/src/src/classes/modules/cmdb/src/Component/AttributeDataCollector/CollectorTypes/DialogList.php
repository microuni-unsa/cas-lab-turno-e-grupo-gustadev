<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;

class DialogList extends AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        return $property->getInfo()
                ->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST;
    }

    /**
     * Collecting data is only possible if we have an objectId.
     * Because the data comes from object.
     *
     * @return array
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        return [];
    }
}
