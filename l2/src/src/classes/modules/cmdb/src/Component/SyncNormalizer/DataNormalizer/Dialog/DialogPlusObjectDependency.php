<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;

class DialogPlusObjectDependency implements DataNormalizerInterface
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
        $dependentPropertyKey = $property->getDependency()->getPropkey() ?? null;
        $dependentProperty = $config->getProperties()[$dependentPropertyKey] ?? null;

        return $property
            && $dependentPropertyKey
            && $dependentProperty
            && isset($requestData[$dependentPropertyKey])
            && is_int($requestData[$dependentPropertyKey])
            && $dependentProperty->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER;
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
    public static function normalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        if (!$valueShape instanceof StringShape) {
            return;
        }

        $value = $valueShape->getValue();

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $property = $config->getProperties()[$propertyKey];
        $dependentPropertyKey = $property->getDependency()->getPropkey();
        $objectId = $requestData[$dependentPropertyKey];
        $references = $property->getData()->getReferences();
        $referenceObjectField = $references[0] . '__isys_obj__id';
        $referenceTitleField = $references[0] . '__title';

        $subselect = SelectSubSelect::factory(
            "SELECT {$references[1]} FROM {$references[0]}",
            $references[0],
            $references[1],
            $referenceObjectField,
            '',
            '',
            SelectCondition::factory([
                "{$referenceObjectField} = {$dao->convert_sql_id($objectId)}",
                "AND {$referenceTitleField} = {$dao->convert_sql_text($value)}"
            ]),
        );

        if (($id = $dao->retrieve($subselect . '')->get_row_value($references[1])) > 0) {
            $valueShape->setValue((int) $id);
            return;
        }

        // Value does not exist
        if ($config->isCreateCategoryEntryInDependency()) {
            $valueShape->setValue(self::createEntry($config, $references[0], $objectId, $value));
        }
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $table
     * @param int                          $objectId
     * @param                              $value
     *
     * @return int|null
     * @throws \Exception
     */
    private static function createEntry(DataNormalizerProviderConfig $config, string $table, int $objectId, $value)
    {
        if (!is_string($value) || empty($value)) {
            return null;
        }

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $query = "INSERT INTO {$table} SET {$table}__title = {$dao->convert_sql_text($value)} , {$table}__isys_obj__id =
            {$dao->convert_sql_id($objectId)} , {$table}__status = {$dao->convert_sql_int(C__RECORD_STATUS__NORMAL)};";

        $dao->update($query) && $dao->apply_update();

        return (int) $dao->get_last_insert_id();
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
