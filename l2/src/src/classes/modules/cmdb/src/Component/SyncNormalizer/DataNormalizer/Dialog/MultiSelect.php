<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\CustomDialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\DialogShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;
use isys_cmdb_dao_dialog;

class MultiSelect implements DataNormalizerInterface
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

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__MULTISELECT
            && $property->getData()->getSourceTable()
            && ($property->getFormat()->getCallback()[1] ?? null) === 'dialog_multiselect';
    }

    /**
     * @param $idField
     * @param $titleField
     * @param $table
     * @param $inCondition
     *
     * @return SelectSubSelect
     */
    private static function getQuery($idField, $titleField, $table, $inCondition)
    {
        return SelectSubSelect::factory(
            "SELECT {$idField}, {$titleField} FROM {$table}",
            $table,
            $idField,
            '',
            '',
            '',
            SelectCondition::factory([
                $inCondition
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

        $table = $property->getData()->getSourceTable();
        $idField = $table . '__id';
        $titleField = $table . '__title';

        $result = $dao->retrieve(
            self::getQuery(
                $idField,
                $titleField,
                $table,
                "{$titleField} IN (" . implode(',', array_map([$dao, 'convert_sql_text'], $value)) . ")"
            ) . ''
        );

        while ($row = $result->get_row()) {
            if (($key = array_search($row[$titleField], $value)) !== false) {
                $value[$key] = (int) $row[$idField];
            }
        }

        $value = array_filter($value, fn ($item) => $item instanceof StringShape || $item instanceof CustomDialogShape);
        $daoDialog = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
        $dialogAdmin = \isys_cmdb_dao_dialog_admin::instance(isys_application::instance()->container->get('database'));

        array_walk($value, function (&$item) use ($table, $daoDialog, $dialogAdmin) {
            $title = $item->getValue();

            if ($item instanceof CustomDialogShape) {
                $title = $item->getValue()['title'];
                $identifier = $item->getValue()['identifier'];
                $daoDialog->set_table('isys_diaolog_plus_custom');

                if ($daoDialog->entryExists($title, $identifier)) {
                    while ($row = $dialogAdmin->get_custom_dialog_data($identifier)) {
                        if ($row['isys_dialog_plus_custom__title'] === $title) {
                            $item = (int)$row['isys_dialog_plus_custom__id'];
                            return;
                        }
                    }
                }
                $item = (int)$dialogAdmin->create('isys_dialog_plus_custom', $title, null, null, C__RECORD_STATUS__NORMAL, null, $identifier);
                return;
            }

            $item = (int)$daoDialog->check_dialog($table, $title);
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

        // @see ID-11804 This is only a workaround to prevent a PHP fatal error.
        // @see ID-12176 Filter non-shapes.
        $list = array_filter($valueShape->getValue(), fn ($value) => $value instanceof AbstractShape);

        if (empty($list)) {
            return;
        }

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $property = $config->getProperties()[$propertyKey];

        $table = $property->getData()->getSourceTable();
        $idField = $table . '__id';
        $titleField = $table . '__title';

        $inCondition = "{$idField} IN (" . implode(',', array_map(
            function ($val) use ($dao) {
                $id = $val->getValue();
                if ($val instanceof CustomDialogShape || $val instanceof DialogShape) {
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
                $inCondition
            ) . ''
        );
        $newValue = [];
        while ($row = $result->get_row()) {
            $newValue[] = $row[$titleField];
        }
        $valueShape->setValue($newValue);
    }
}
