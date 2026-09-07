<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\CustomDialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_application;
use isys_cmdb_dao_dialog;

class DialogPlus implements DataNormalizerInterface
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

        return $callback[1] === 'dialog_plus' && $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_PLUS;
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return void
     * @throws \Exception
     */
    public static function normalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        if (!$valueShape instanceof StringShape && !$valueShape instanceof CustomDialogShape) {
            return;
        }

        $value = $valueShape->getValue();

        if ($valueShape instanceof CustomDialogShape) {
            $value = $valueShape->getValue()['title'];
        }

        $property = $config->getProperties()[$propertyKey];
        $table = $property->getData()->getReferences()[0];

        $valueShape->setValue((int) isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'))->check_dialog(
            $table,
            $value
        ));
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return mixed|void
     * @throws \Exception
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        Dialog::denormalizeData($config, $propertyKey, $requestData, $valueShape);
    }
}
