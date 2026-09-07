<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\MultiObjectBrowserType;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\IntegerShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_application;
use isys_popup_browser_object_ng;

class MultiSelectBrowser implements DataNormalizerInterface
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
        return in_array($property->getInfo()->getType(), MultiObjectBrowserType::SUPPORTED_TYPES) &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] &&
            in_array($params['p_strPopupType'], MultiObjectBrowserType::SUPPORTED_BROWSERS);
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
        $newValue = [];
        $value = $valueShape->getValue();

        $dao = isys_application::instance()->container->get('cmdb_dao');

        foreach ($value as $val) {
            if ($val instanceof AbstractBrowserShape) {
                $val->handle($config, $propertyKey, $requestData);
                $newValue[] = (int) $val->getValue();
                continue;
            }

            if ($val instanceof StringShape) {
                if ($id = $dao->get_obj_id_by_title($val->getValue())) {
                    $newValue[] = (int) $id;
                }
            }

            if ($val instanceof IntegerShape) {
                $newValue[] = (int) $val->getValue();
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

        $newValue = [];
        $dao = isys_application::instance()->container->get('cmdb_dao');

        foreach ($list as $value) {
            if ($value instanceof AbstractBrowserShape) {
                $value->handle($config, $propertyKey, $requestData);
            }

            $val = $value->getValue();

            if (strlen((string)(int)$val) !== strlen((string)$val)) {
                continue;
            }

            $objectData = $dao->get_object_by_id($val)->get_row() ?? [];
            if (isset($objectData['isys_obj__title'])) {
                $newValue[] = $objectData['isys_obj__title'];
            }
        }

        if (!empty($newValue)) {
            $valueShape->setValue($newValue);
        }
    }
}
