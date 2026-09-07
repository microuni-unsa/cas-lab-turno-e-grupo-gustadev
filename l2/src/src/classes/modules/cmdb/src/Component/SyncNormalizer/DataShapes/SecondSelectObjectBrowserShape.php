<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;

class SecondSelectObjectBrowserShape extends AbstractBrowserShape implements ShapeInterface
{
    protected const KEYS = [
        'title',
        'type',
        'entry-title'
    ];

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function handle(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData)
    {
        $objectTitle = $this->getValue()['title'];
        $objectType = $this->getValue()['type'];
        $entryTitle = $this->getValue()['entry-title'];

        $dao = isys_application::instance()->container->get('cmdb_dao');

        $objectId = $dao->get_obj_id_by_title(
            $objectTitle,
            (
                is_numeric($objectType) ? $objectType :
                    (is_string($objectType) ? $dao->getObjectTypeId($objectType) : null)
            )
        );

        $property = $config->getProperties()[$propertyKey];
        $references = $property->getData()->getReferences();
        $referenceTable = $references[0];
        $referenceTitleField = $references[2] ?? $referenceTable . '__title';
        $referenceIdField = $referenceTable . '__id';

        $subselect = SelectSubSelect::factory(
            "SELECT {$referenceIdField} FROM {$referenceTable}",
            $referenceTable,
            $referenceIdField,
            '',
            '',
            '',
            SelectCondition::factory([
                "{$referenceTable}__isys_obj__id = {$dao->convert_sql_id($objectId)}",
                "AND {$referenceTitleField} = {$dao->convert_sql_text($value)}"
            ])
        );

        if (($id = $dao->retrieve($subselect . '')->get_row_value($referenceIdField)) > 0) {
            $this->setValue((int) $id);
        }
    }
}
