<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\DataRetrieverException;

class PropertyDataRetriever
{
    /**
     * @var string
     */
    private $propertyKey;

    /**
     * @var Property
     */
    private $property;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $currentData;

    /**
     * @var CategoryDataRetriever
     */
    private $categoryDataRetriever;

    /**
     * @var PropertyConfig
     */
    private $propertyConfig;

    /**
     * PropertyDataRetriever constructor.
     *
     * @param        $property
     * @param Config $config
     */
    public function __construct($property, Config $config)
    {
        if (is_array($property)) {
            $property = Property::createInstanceFromArray($property);
        }
        $this->property = $property;
        $this->config = $config;
    }

    /**
     * @param                        $propertyKey
     * @param array|Property         $property
     * @param PropertyConfig         $propertyConfig
     * @param array                  $currentData
     * @param CategoryDataRetriever  $categoryDataRetriever
     * @param Config                 $config
     *
     * @return PropertyDataRetriever
     */
    public static function instance($propertyKey, $property, PropertyConfig $propertyConfig, array $currentData, CategoryDataRetriever $categoryDataRetriever, Config $config)
    {
        $instance = new self($property, $config);
        $instance->propertyKey = $propertyKey;
        $instance->currentData = $currentData;
        $instance->categoryDataRetriever = $categoryDataRetriever;
        $instance->propertyConfig = $propertyConfig;

        return $instance;
    }

    /**
     * @return bool|float|int|mixed|string
     * @throws Exception
     */
    public function retrieveDataForProperty()
    {
        if ($this->categoryDataRetriever->getCount() == 0) {
            // Set Default value for new entries
            $uiData = $this->property->getUi();
            $uiParams = $uiData->getParams();

            if ($uiData->getDefault() || (is_scalar($uiData->getDefault()) && trim($uiData->getDefault()) !== '')) {
                return $uiData->getDefault();
            }

            return $uiParams['default'] ?? null;
        }
        try {
            return $this->retrieveValue();
        } catch (DataRetrieverException $e) {
            $e->write_log();
        }

        return null;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    private function retrieveValue()
    {
        $dataRetriever = $this->propertyConfig->getDataRetriever();

        if ($dataRetriever === null) {
            throw new DataRetrieverException('Dataretriever type could not be processed for property: ' . $this->propertyKey);
        }

        return $dataRetriever->retrieveValue(
            $this->propertyKey,
            $this->property,
            (array)$this->config->getProperties(),
            (array)$this->categoryDataRetriever->getCategoryData($this->config->getDataId()),
            (array)$this->currentData,
            $this->config->getCategoryDao(),
            $this->categoryDataRetriever->getRequestObject($this->config->getDataId())
        );
    }
}
