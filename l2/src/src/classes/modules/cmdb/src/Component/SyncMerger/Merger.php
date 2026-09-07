<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

class Merger
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $syncData = [];

    /**
     * Merger constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     *
     * @return Merger
     */
    public static function instance(Config $config): Merger
    {
        $merger = new self($config);
        $merger->buildSyncData();
        return $merger;
    }

    /**
     * @return array
     */
    public function getDataForSync(): array
    {
        if (empty($this->syncData)) {
            $this->buildSyncData();
        }
        return $this->syncData;
    }

    /**
     * @return array
     */
    public function flattenSyncData(): array
    {
        return array_map(function ($item) {
            return $item['value'];
        }, $this->syncData[Config::CONFIG_PROPERTIES]);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isEmptyValue(mixed $value): bool
    {
        // @see ID-10890 Verify that we don't mistake '0' for an empty value (see 'yes / no' fields for example).
        if ($value === null || is_string($value) && trim($value) === '') {
            return true;
        }

        /**
         * Custom export helpers produce something like this with no values:
         * - ['identifier' => 'identifier']
         * - ['prop_type' => 'calendar']
         * - ['prop_type' => 'browser_object']
         * This needs to be catched and interpreted as null otherwise it will be used as value which is not correct
         */
        if ($this->config->isCustomCategory() &&
            is_array($value) &&
            count($value) === 1 &&
            (
                isset($value['identifier']) ||
                isset($value['prop_type'])
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Build Sync Data
     *
     * @return void
     */
    private function buildSyncData()
    {
        $missingProperties = $this->config->getMissingProperties();

        if (empty($missingProperties)) {
            // Nothing to build sync data is already complete
            $this->syncData = [
                Config::CONFIG_DATA_ID => $this->config->getDataId(),
                Config::CONFIG_PROPERTIES => $this->config->getCurrentData()
            ];
            return;
        }
        $currentData = $this->config->getCurrentData();

        $categtoryDataRetriever = CategoryDataRetriever::instance($this->config);

        foreach ($missingProperties as $propertyKey => $propertyInfo) {
            $propertyDefinition = $propertyInfo[Config::PROPERTY_DEFINITION];
            $propertyDataRetrieverType = $propertyInfo[Config::PROPERTY_DATARETRIEVERTYPE];
            $propertyRetriever = PropertyDataRetriever::instance($propertyKey, $propertyDefinition, $propertyDataRetrieverType, $currentData, $categtoryDataRetriever, $this->config);

            $currentData[$propertyKey] = $propertyRetriever->retrieveDataForProperty();
        }
        $newData = [];
        foreach ($currentData as $key => $value) {
            if (is_array($value) && (isset($value[C__DATA__VALUE]) || array_key_exists(C__DATA__VALUE, $value))) {
                $newData[$key] = $value;
                continue;
            }

            $cleanedValue = $value;

            if ($this->isEmptyValue($value)) {
                $cleanedValue = null;
            }

            $newData[$key] = [
                C__DATA__VALUE => $cleanedValue
            ];
        }

        $this->syncData = [
            Config::CONFIG_DATA_ID => $this->config->getDataId(),
            Config::CONFIG_PROPERTIES => $newData
        ];
    }
}
