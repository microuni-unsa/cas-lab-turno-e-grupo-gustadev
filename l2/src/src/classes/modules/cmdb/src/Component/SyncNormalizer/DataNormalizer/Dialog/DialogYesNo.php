<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;

class DialogYesNo implements DataNormalizerInterface
{
    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return bool
     */
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];
        $callback = $property->getFormat()->getCallback();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG
            && $callback[1] === 'get_yes_or_no';
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
        if (!$valueShape instanceof StringShape) {
            return;
        }

        $value = $valueShape->getValue();

        $property = $config->getProperties()[$propertyKey];
        $uiParams = $property->getUi()->getParams();
        $search = ucfirst(strtolower($value));
        $id = array_search($search, $uiParams['p_arData']);

        if (is_numeric($id)) {
            $valueShape->setValue((int) $id);
        }
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        $value = $valueShape->getValue();

        if (is_array($value)) {
            $value = current($value);
        }

        if (strlen((string)(int)$value) !== strlen((string)$value)) {
            return;
        }

        $property = $config->getProperties()[$propertyKey];
        $uiParams = $property->getUi()->getParams();

        if (!isset($uiParams['p_arData'][$value])) {
            return;
        }

        $valueShape->setValue($uiParams['p_arData'][$value]);
    }
}
