<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\CustomDialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;

class DialogList implements DataNormalizerInterface
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
        $uiParams = $property->getUi()->getParams();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST
            && $property->getData()->getSourceTable() !== 'isys_obj'
            && isset($uiParams['p_arData']);
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
        if (!$valueShape instanceof ListShape) {
            return;
        }
        $value = $valueShape->getValue();

        $property = $config->getProperties()[$propertyKey];
        $uiParams = $property->getUi()->getParams();
        $dialogData = DialogData::getData($uiParams['p_arData'], $config->getCategoryData(), $config->getObjectId(), $config->getEntryId());
        $newValue = [];

        foreach ($value as $val) {
            if (!$val instanceof AbstractShape) {
                continue;
            }
            $title = $val->getValue();
            if ($val instanceof CustomDialogShape) {
                $title = $val->getValue()['title'];
            }

            foreach ($dialogData as $dialogEntry) {
                if ($dialogEntry['val'] === $title) {
                    $newValue[] = (int) $dialogEntry['id'];
                }
            }
        }

        if (!empty($newValue)) {
            $valueShape->setValue($newValue);
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
        if (!$valueShape instanceof ListShape) {
            return;
        }

        // @see ID-12176 Filter non-shapes.
        $list = array_filter($valueShape->getValue(), fn ($value) => $value instanceof AbstractShape);

        if (empty($list)) {
            return;
        }

        $property = $config->getProperties()[$propertyKey];
        $uiParams = $property->getUi()->getParams();
        $dialogData = DialogData::getData($uiParams['p_arData'], $config->getCategoryData(), $config->getObjectId(), $config->getEntryId());
        $newValue = [];

        foreach ($list as $val) {
            $id = $val->getValue();

            if ($val instanceof CustomDialogShape) {
                $id = $val->getValue()['id'];
            }

            foreach ($dialogData as $dialogEntry) {
                if ((int)$dialogEntry['id'] === (int)$id) {
                    $newValue[] = $dialogEntry['val'];
                }
            }
        }

        if (!empty($newValue)) {
            $valueShape->setValue($newValue);
        }
    }
}
