<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer;

use idoit\Component\Property\Property;

class Normalizer extends AbstractNormalizer
{
    /**
      * @param int      $objectId
      * @param int|null $entryId
      *
      * @return array
      */
    public function normalizeData(int $objectId, ?int $entryId = null): array
    {
        $dao = $this->config->getDao();
        $properties = $dao->get_properties();
        $data = $this->config->getData();
        $categoryData = [];

        foreach ($properties as $propertyKey => &$property) {
            if (is_array($property)) {
                $property = Property::createInstanceFromArray($property);
            }
        }
        $categoryData = $this->getCategoryData($dao, $objectId, $entryId);

        $config = new DataNormalizerProviderConfig(
            $dao,
            $properties,
            $objectId,
            $entryId,
            $categoryData,
            $this->config->isCreateEntriesInAssignedObjects(),
            $this->config->isCreateObjects()
        );
        $dataNormalizerProvider = new DataNormalizerProvider($config);

        $order = array_keys($properties);

        uksort($data, function ($propertyKey1, $propertyKey2) use ($order) {
            return ((array_search($propertyKey1, $order) > array_search($propertyKey2, $order)) ? 1 : -1);
        });

        /** @var Property[] $properties **/
        $data = array_filter($data, function ($key) use ($properties) {
            if (isset($properties[$key]) && $properties[$key]->getProvides()->isImport()) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_KEY);

        foreach ($data as $propertyKey => &$value) {
            if (!isset($properties[$propertyKey])) {
                // Property from the request does not exist
                continue;
            }

            $dataNormalizerProvider->normalizeData($propertyKey, $data, $value);
        }

        return $data;
    }
}
