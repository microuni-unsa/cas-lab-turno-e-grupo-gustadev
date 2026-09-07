<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Helper\Unserialize;
use idoit\Component\Property\Property;
use isys_callback;
use isys_cmdb_dao_category;
use isys_format_json;
use isys_request;

class ByUiCallback implements DataRetrieverInterface
{
    /**
     * @var array
     */
    private static $ignoredCallbackMethods = [
        'callback_property_type',
    ];

    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $uiParams = $property->getUi()->getParams();
        $callbackObject = $uiParams['p_arData'];

        if (!$callbackObject instanceof isys_callback) {
            return false;
        }

        return $property->getData()->getField() && !in_array($callbackObject->getMethod(), self::$ignoredCallbackMethods);
    }

    /**
     * @param string            $propertyKey
     * @param Property          $property
     * @param array             $properties
     * @param array             $categoryData
     * @param array             $currentData
     * @param isys_request|null $request
     *
     * @return mixed|null
     * @throws \idoit\Exception\JsonException
     */
    public function retrieveValue(
        string $propertyKey,
        Property $property,
        array $properties,
        array $categoryData,
        array $currentData,
        isys_cmdb_dao_category $dao,
        ?isys_request $request = null
    ) {
        $uiParams = $property->getUi()->getParams();
        $data = $uiParams['p_arData']->execute($request);
        $dbField = $property->getData()->getField();
        $value = $categoryData[$dbField] ?? null;

        if ($value !== null) {
            return $value;
        }

        if (isys_format_json::is_json_array($data)) {
            $data = isys_format_json::decode($data);
        }

        if (is_string($data)) {
            $data = Unserialize::toArray($data);
        }

        if (isset($data[$dbField])) {
            return $data[$dbField];
        }

        return null;
    }
}
