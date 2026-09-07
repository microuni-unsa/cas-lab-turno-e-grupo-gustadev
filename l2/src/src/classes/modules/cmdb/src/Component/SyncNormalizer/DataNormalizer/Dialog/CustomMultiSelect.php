<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\CustomDialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_dialog_admin;

class CustomMultiSelect implements DataNormalizerInterface
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
            && $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__MULTISELECT;
    }

    /**
     * @param $idField
     * @param $titleField
     * @param $table
     * @param $inCondition
     *
     * @return SelectSubSelect
     */
    private static function getQuery($idField, $titleField, $table, $inCondition, $identifier)
    {
        return SelectSubSelect::factory(
            "SELECT {$idField}, {$titleField} FROM {$table}",
            $table,
            $idField,
            '',
            '',
            '',
            SelectCondition::factory([
                $inCondition,
                "AND isys_dialog_plus_custom__identifier = {$identifier}"
            ]),
        );
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

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $property = $config->getProperties()[$propertyKey];

        $table = 'isys_dialog_plus_custom';
        $idField = $table . '__id';
        $titleField = $table . '__title';
        $identifier = $property->getUi()->getParams()['p_identifier'];

        $result = $dao->retrieve(
            self::getQuery(
                $idField,
                $titleField,
                $table,
                "{$titleField} IN (" . implode(',', array_map([$dao, 'convert_sql_text'], $value)) . ")",
                $dao->convert_sql_text($identifier)
            ) . ''
        );

        while ($row = $result->get_row()) {
            if (($key = array_search($row[$titleField], $value)) !== false) {
                $value[$key] = (int) $row[$idField];
            }
        }

        array_walk($value, function (&$item) use ($table, $identifier) {
            if ($item instanceof StringShape) {
                $item = (int) isys_cmdb_dao_dialog_admin::instance(isys_application::instance()->container->get('database'))
                    ->create($table, $item->getValue(), null, null, C__RECORD_STATUS__NORMAL, null, $identifier);
            }
        });

        $valueShape->setValue($value);
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
        if (!$valueShape instanceof ListShape) {
            return;
        }

        // @see ID-12176 Filter non-shapes.
        $list = array_filter($valueShape->getValue(), fn ($value) => $value instanceof AbstractShape);

        if (empty($list)) {
            return;
        }

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $property = $config->getProperties()[$propertyKey];

        $table = 'isys_dialog_plus_custom';
        $idField = $table . '__id';
        $titleField = $table . '__title';
        $identifier = $property->getUi()->getParams()['p_identifier'];

        $inCondition = "{$idField} IN (" . implode(',', array_map(
            function ($val) use ($dao) {
                $id = $val->getValue();

                if ($val instanceof CustomDialogShape) {
                    $id = $val->getValue()['id'];
                }

                return $dao->convert_sql_id($id);
            },
            $list
        )) . ")";

        $result = $dao->retrieve(
            self::getQuery(
                $idField,
                $titleField,
                $table,
                $inCondition,
                $dao->convert_sql_text($identifier)
            ) . ''
        );
        $newValue = [];
        while ($row = $result->get_row()) {
            $newValue[] = $row[$titleField];
        }
        $valueShape->setValue($newValue);
    }
}
