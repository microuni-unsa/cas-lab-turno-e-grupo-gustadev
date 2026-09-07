<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter\DialogFormatter;

class Multiselect extends AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        return $property->getInfo()
                ->getType() === Property::C__PROPERTY__INFO__TYPE__MULTISELECT;
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws \Exception
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $sourceTable = $property
            ->getData()
            ->getSourceTable();

        $dialogInstance = Dialog::getDialogInstance($sourceTable, $property);

        $data = $dialogInstance->set_table($sourceTable)
            ->load()
            ->get_data();

        if (!$data) {
            return [];
        }

        return $reformat ? DialogFormatter::reformat($data, $sourceTable) : $data;
    }
}
