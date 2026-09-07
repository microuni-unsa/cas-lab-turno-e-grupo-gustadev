<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_callback;
use isys_request;

class DialogData implements DataNormalizerInterface
{
    /**
     * @param          $arrayData
     * @param int      $objectId
     * @param int|null $entryId
     *
     * @return array
     * @throws \Exception
     */
    public static function getData($arrayData, array $categoryData, int $objectId, ?int $entryId = null)
    {
        if ($arrayData instanceof isys_callback) {
            $request = isys_request::factory();
            $request->set_object_id($objectId)
                ->set_category_data_id($entryId)
                ->set_row($categoryData);

            $arrayData = $arrayData->execute($request);
        }

        return is_array($arrayData) ? $arrayData: [];
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return bool
     * @throws \Exception
     */
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];
        $uiParams = $property->getUi()->getParams();

        if (!isset($uiParams['p_arData'])) {
            return false;
        }

        $arData = self::getData($uiParams['p_arData'], $config->getCategoryData(), $config->getObjectId(), $config->getEntryId());

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG
            && !empty($arData);
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

        $arData = self::getData($uiParams['p_arData'], $config->getCategoryData(), $config->getObjectId(), $config->getEntryId());

        if (is_array($value)) {
            $newValue = [];
            foreach ($value as $item) {
                if (($id = array_search($item, $arData))) {
                    $newValue[] = (int) $id;
                }
            }

            if (!empty($newValue)) {
                $valueShape->setValue($newValue);
            }

            return;
        }

        $id = array_search($value, $arData);
        if ($id > 0) {
            $valueShape->setValue($id);
        }
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     * @throws Exception
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
        $arData = self::getData($uiParams['p_arData'], $config->getCategoryData(), $config->getObjectId(), $config->getEntryId());

        if (!isset($arData[$value])) {
            return;
        }

        $valueShape->setValue($arData[$value]);
    }
}
