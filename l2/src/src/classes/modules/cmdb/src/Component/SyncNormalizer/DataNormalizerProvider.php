<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\CustomDialogPlus;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\CustomMultiSelect;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\Dialog;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogData;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogList;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogPlus;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogPlusDialogDependency;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogPlusObjectDependency;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\DialogYesNo;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\MultiSelect;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\CableConnectionBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\ConnectorBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\ContactBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\LocationBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\LogicalPortBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\MultiContactBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\MultiSelectBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\SingleSecondSelectBrowser;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser\SingleSelectBrowser;
use isys_application;

class DataNormalizerProvider
{
    /**
     * @var DataNormalizerInterface[]
     */
    protected array $dataNormalizer;

    /**
     * @var DataNormalizerProviderConfig
     */
    protected DataNormalizerProviderConfig $config;

    /**
     * @param DataNormalizerProviderConfig $config
     */
    public function __construct(DataNormalizerProviderConfig $config)
    {
        $this->config = $config;

        $this->dataNormalizer = [
            new DialogPlusDialogDependency(),
            new DialogPlusObjectDependency(),
            new DialogYesNo(),
            new MultiSelect(),
            new CustomMultiSelect(),
            new DialogData(),
            new DialogList(),
            new DialogPlus(),
            new CustomDialogPlus(),
            new Dialog(),
            new LocationBrowser(),
            new MultiContactBrowser(),
            new MultiSelectBrowser(),
            new SingleSecondSelectBrowser(),
            new SingleSelectBrowser(),
            new ConnectorBrowser(),
            new CableConnectionBrowser(),
            new LogicalPortBrowser(),
            new ContactBrowser(),
        ];
    }

    /**
     * @param string $propertyKey
     * @param array  $requestData
     *
     * @return false|DataNormalizerInterface
     */
    private function getDataNormalizer(string $propertyKey, array $requestData)
    {
        $properties = $this->config->getProperties();

        foreach ($this->dataNormalizer as $normalizer) {
            if (isset($properties[$propertyKey]) && $normalizer::isApplicable($this->config, $propertyKey, $requestData)) {
                return $normalizer;
            }
        }
        return false;
    }

    /**
     * @param string $propertyKey
     * @param array  $requestData
     * @param        $value
     *
     * @return void
     */
    public function normalizeData(string $propertyKey, array $requestData, &$value)
    {
        $normalizer = $this->getDataNormalizer($propertyKey, $requestData);

        if (!$normalizer) {
            return;
        }

        $shapeProvider = isys_application::instance()->container->get('idoit.cmdb.normalizer.shape_provider');
        $shape = $shapeProvider->getShape($value);

        $normalizer::normalizeData($this->config, $propertyKey, $requestData, $shape);

        $value = $shape->getValue();
    }

    /**
     * @param string $propertyKey
     * @param array  $requestData
     * @param        $value
     *
     * @return void
     */
    public function denormalizeData(string $propertyKey, array $requestData, &$value)
    {
        if ($value == 'NULL' || empty($value)) {
            return;
        }

        $normalizer = $this->getDataNormalizer($propertyKey, $requestData);

        if (!$normalizer) {
            return;
        }
        $shapeProvider = isys_application::instance()->container->get('idoit.cmdb.normalizer.shape_provider');
        $shape = $shapeProvider->getShape($value);
        $normalizer::denormalizeData($this->config, $propertyKey, $requestData, $shape);

        $value = $shape->getValue();
    }
}
