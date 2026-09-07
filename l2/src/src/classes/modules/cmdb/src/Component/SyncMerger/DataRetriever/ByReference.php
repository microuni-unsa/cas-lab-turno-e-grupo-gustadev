<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;
use isys_request;

class ByReference implements DataRetrieverInterface
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $references = $property->getData()
            ->getReferences();

        if (is_array($references) && isset($references[1])) {
            return true;
        }
        return false;
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
        $categoryTable = $dao->get_table();
        $references = $property->getData()->getReferences();

        if (!is_array($references) || empty($references)) {
            return null;
        }

        // @see ID-11109 Fetch non-unique reference values explicitly.
        if ($this->referenceUsedMoreThanOnce($references, $properties)) {
            $dataField = $property->getData()->getField();
            $referenceId = $categoryData[$dataField] ?? null;

            if ($referenceId !== null) {
                return $referenceId;
            }
        }

        if (isset($categoryData[$references[1]])) {
            return $categoryData[$references[1]];
        }

        // @see ID-10890 This can happen if the table was not joined.
        if (isset($categoryData[$categoryTable . '__' . $references[1]])) {
            return $categoryData[$categoryTable . '__' . $references[1]];
        }

        return null;
    }

    /**
     * Helper method to find out if 'reference' configuration is used more than once in the same category.
     * This can happen if two dialog fields use the same reference data (see 'SLA' category).
     *
     * @param array $references
     * @param array $properties
     *
     * @return bool
     */
    private function referenceUsedMoreThanOnce(array $references, array $properties): bool
    {
        $used = 0;

        foreach ($properties as $key => $property) {
            $propertyReferences = $property->getData()->getReferences();

            if (!is_array($propertyReferences) || empty($propertyReferences)) {
                continue;
            }

            // Go sure that we compare 'as plain as possible'.
            $references = array_values($references);
            $propertyReferences = array_values($propertyReferences);

            // Check if the arrays contain different values.
            if (count(array_diff($references, $propertyReferences)) === 0 || count(array_diff($propertyReferences, $references)) === 0) {
                $used ++;
            }
        }

        return $used > 1;
    }
}
