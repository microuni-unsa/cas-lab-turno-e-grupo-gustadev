<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;
use idoit\Exception\JsonException;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter\DialogDataFormatter;
use isys_callback;

class DialogCallbackData extends AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $params = $property->getUi()->getParams();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG
            && is_array($params)
            && isset($params['p_arData'])
            && is_a($params['p_arData'], isys_callback::class, true);
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws JsonException
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $params = $property->getUi()->getParams();

        $data = $params['p_arData']->execute();

        if (!is_array($data) || !$data) {
            return [];
        }

        return $reformat ? DialogDataFormatter::reformat($data) : $data;
    }
}
