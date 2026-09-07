<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Helper\Unserialize;
use idoit\Component\Property\Property;
use idoit\Exception\JsonException;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter\DialogDataFormatter;
use isys_format_json;

class DialogData extends AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $params = $property->getUi()
            ->getParams();

        return $property->getInfo()
                ->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG && is_array($params) && isset($params['p_arData']) && !is_object($params['p_arData']);
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
        $rawData = $property->getUi()->getParams()['p_arData'];

        if (is_string($rawData)) {
            $data = isys_format_json::is_json_array($rawData)
                ? isys_format_json::decode($rawData)
                : Unserialize::toArray($rawData);
        } else {
            $data = $rawData;
        }

        if (!$data) {
            return [];
        }

        return $reformat ? DialogDataFormatter::reformat($data) : $data;
    }
}
