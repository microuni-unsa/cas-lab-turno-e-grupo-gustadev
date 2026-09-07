<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use isys_application;

class CableConnectionBrowser implements DataNormalizerInterface
{
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];
        $cableConnectionCallback = 'cable_connection';
        $callback = $property->getFormat()->getCallback();
        $callbackFunc = !empty($callback) ? $callback[1] : '';

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_object_ng' &&
            $callbackFunc === $cableConnectionCallback;
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
        if ($valueShape instanceof AbstractBrowserShape) {
            $valueShape->handle($config, $propertyKey, $requestData);
            return;
        }

        if (!$valueShape instanceof StringShape) {
            return;
        }

        $value = $valueShape->getValue();
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $subselect = SelectSubSelect::factory(
            'SELECT isys_obj__id FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id',
            'isys_obj',
            'isys_obj__id',
            '',
            '',
            '',
            SelectCondition::factory([
                "isys_obj__title = {$dao->convert_sql_text($value)}",
                "AND isys_obj_type__const = {$dao->convert_sql_text('C__OBJTYPE__CABLE')}",
            ]),
        );

        $valueShape->setValue($dao->retrieve($subselect . '')->get_row_value('isys_obj__id') ?? null);
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return void
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        SingleSelectBrowser::denormalizeData($config, $propertyKey, $requestData, $valueShape);
    }
}
