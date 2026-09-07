<?php

namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;
use isys_request;

class ByDataset implements DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $dbField = $property->getData()->getField();
        $references = $property->getData()->getReferences();

        return $dbField && empty($references);
    }

    /**
     * @param string                 $propertyKey
     * @param Property               $property
     * @param array                  $properties
     * @param array                  $categoryData
     * @param array                  $currentData
     * @param isys_cmdb_dao_category $dao
     * @param isys_request|null      $request
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
    ) {
        $dbField = $property->getData()->getField();

        if (isset($categoryData[$dbField])) {
            return $categoryData[$dbField];
        }

        return null;
    }
}
