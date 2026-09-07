<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use isys_popup_browser_cable_connection_ng;

class LogicalPortBrowser implements DataNormalizerInterface
{
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_cable_connection_ng' &&
            isset($property->getUi()->getParams()['only_log_ports']) &&
            $property->getUi()->getParams()['only_log_ports'];
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     */
    public static function normalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        ConnectorBrowser::normalizeData($config, $propertyKey, $requestData, $valueShape);
    }

    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        $value = $valueShape->getValue();

        if ($value instanceof AbstractBrowserShape) {
            $value->handle($config, $propertyKey, $requestData);
            $value = $value->getValue();
        }

        if (is_array($value)) {
            $value = current($value);
        }

        if (strlen((string)(int)$value) !== strlen((string)$value)) {
            return;
        }

        $popup = new isys_popup_browser_cable_connection_ng();
        $popup->setOnlyLogicalPorts(true);
        $result = $popup->format_selection((int) $value, true);

        $valueShape->setValue($result);
    }
}
