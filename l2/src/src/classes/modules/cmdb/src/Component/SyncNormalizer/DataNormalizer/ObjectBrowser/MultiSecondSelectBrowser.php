<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\Dialog\MultiSelect;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;
use isys_popup_browser_object_ng;

class MultiSecondSelectBrowser implements DataNormalizerInterface
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
            $params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            $params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
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
        if (!$valueShape instanceof ListShape) {
            return;
        }

        $newValue = [];
        $value = $valueShape->getValue();
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $property = $config->getProperties()[$propertyKey];

        $inConditionValue = [];

        foreach ($value as $val) {
            if ($val instanceof AbstractBrowserShape) {
                $val->handle($config, $propertyKey, $requestData);
                $newValue[] = $val->getValue();
                continue;
            }

            if ($val instanceof StringShape) {
                $inConditionValue[] = $dao->convert_sql_text($val);
            }
        }

        if (!empty($inConditionValue)) {
            $referenceTable = $property->getData()->getSourceTable();
            $referenceTitleField = $referenceTable . '__title';
            $referenceIdField = $referenceTable . '__id';

            $inCondition = "{$referenceTitleField} IN (" . implode(',', $inConditionValue) . ")";

            $subselect = SelectSubSelect::factory(
                "SELECT {$referenceIdField}, {$referenceTitleField} FROM {$referenceTable}",
                $referenceTable,
                $referenceIdField,
                '',
                '',
                '',
                SelectCondition::factory([
                    $inCondition
                ]),
            );

            $result = $dao->retrieve($subselect . '');

            while ($row = $result->get_row()) {
                if (in_array($row[$referenceTitleField], $value)) {
                    $newValue[] = (int) $row[$referenceIdField];
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
        MultiSelect::denormalizeData($config, $propertyKey, $requestData, $valueShape);
    }
}
