<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;

class DialogPlus extends Dialog
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $references = $property->getData()
            ->getReferences();
        
        return $property->getInfo()
                ->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_PLUS && is_array($references) && !empty($references);
    }
}
