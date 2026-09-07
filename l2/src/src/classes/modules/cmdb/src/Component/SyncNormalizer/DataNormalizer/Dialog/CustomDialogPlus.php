<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_application;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_dialog_admin;

class CustomDialogPlus implements DataNormalizerInterface
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

        return $config->getDao() instanceof isys_cmdb_dao_category_g_custom_fields
            && $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG_PLUS;
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
        if (!$valueShape instanceof StringShape) {
            return;
        }

        $property = $config->getProperties()[$propertyKey];
        $identifier = trim($property->getUi()->getParams()['p_identifier'] ?? '');
        $value = trim($valueShape->getValue());

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $queryIdentifier = $dao->convert_sql_text($identifier);
        $queryValue = $dao->convert_sql_text($value);

        $query = "SELECT isys_dialog_plus_custom__id AS id
            FROM isys_dialog_plus_custom
            WHERE isys_dialog_plus_custom__identifier = {$queryIdentifier}
            AND isys_dialog_plus_custom__title = {$queryValue}
            LIMIT 1;";

        $foundEntry = (int)$dao->retrieve($query)->get_row_value('id');

        if ($foundEntry > 0) {
            $valueShape->setValue($foundEntry);
            return;
        }

        $valueShape->setValue((int) isys_cmdb_dao_dialog_admin::instance(isys_application::instance()->container->get('database'))
            ->create('isys_dialog_plus_custom', $value, null, null, C__RECORD_STATUS__NORMAL, null, $identifier));
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
