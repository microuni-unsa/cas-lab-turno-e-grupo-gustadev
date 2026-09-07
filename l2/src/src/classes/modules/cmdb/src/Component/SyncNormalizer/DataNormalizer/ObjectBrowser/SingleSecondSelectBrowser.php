<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\Dialog;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;
use isys_popup_browser_object_ng;

class SingleSecondSelectBrowser implements DataNormalizerInterface
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
        $params = $property->getUi()->getParams();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            !$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !!$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
            $params['p_strPopupType'] === 'browser_object_ng';
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
        if ($valueShape instanceof AbstractBrowserShape) {
            $valueShape->handle($config, $propertyKey, $requestData);
            return;
        }

        if (!$valueShape instanceof StringShape) {
            return;
        }

        $value = $valueShape->getValue();

        $dao = isys_application::instance()->container->get('cmdb_dao');
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
                "{$referenceTitleField} = {$dao->convert_sql_text($value)}"
            ])
        );

        if (($id = $dao->retrieve($subselect . '')->get_row_value($referenceIdField)) > 0) {
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
     * @throws \Exception
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        Dialog::denormalizeData($config, $propertyKey, $requestData, $valueShape);
    }
}
