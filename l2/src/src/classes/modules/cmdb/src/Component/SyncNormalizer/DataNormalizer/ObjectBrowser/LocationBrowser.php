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
use isys_cmdb_dao_category_g_location;
use isys_popup_browser_location;

class LocationBrowser implements DataNormalizerInterface
{
    public static function isApplicable(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData): bool
    {
        $property = $config->getProperties()[$propertyKey];
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_location';
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

        $value = $valueShape;

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
                "AND isys_obj_type__container = 1",
            ]),
        );

        $objectId = $dao->retrieve($subselect . '')->get_row_value('isys_obj__id');

        if (!$objectId) {
            return;
        }

        $valueShape->setValue((int) $objectId);
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
        $value = $valueShape->getValue();

        if ($value instanceof AbstractBrowserShape) {
            $value->handle($config, $propertyKey, $requestData);
            $value = $value->getValue();
        }

        if (strlen((string)(int)$value) !== strlen((string)$value)) {
            return;
        }

        $dao = isys_cmdb_dao_category_g_location::instance(isys_application::instance()->container->get('database'));
        // Strip all tags
        $valueShape->setValue(
            isys_popup_browser_location::instance()
                ->set_format_exclude_self(false)
                ->set_format_as_text(true)
                ->format_selection((int)$value, true)
        );
    }
}
