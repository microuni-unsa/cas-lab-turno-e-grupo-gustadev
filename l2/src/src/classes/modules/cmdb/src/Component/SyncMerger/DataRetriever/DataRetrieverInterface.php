<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;
use isys_request;

interface DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return mixed
     */
    public static function isApplicable(Property $property);

    /**
     * @param string            $propertyKey
     * @param Property          $property
     * @param array             $properties
     * @param array             $categoryData
     * @param array             $currentData
     * @param isys_request|null $request
     *
     * @return mixed|null
     */
    public function retrieveValue(
        string $propertyKey,
        Property $property,
        array $properties,
        array $categoryData,
        array $currentData,
        isys_cmdb_dao_category $dao,
        ?isys_request $request = null
    );
}
