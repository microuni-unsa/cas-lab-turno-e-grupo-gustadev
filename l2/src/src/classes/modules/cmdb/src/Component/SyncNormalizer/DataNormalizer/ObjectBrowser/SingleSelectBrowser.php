<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractBrowserShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\StringShape;
use isys_application;
use isys_popup_browser_object_ng;

class SingleSelectBrowser implements DataNormalizerInterface
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
        $ignoreObjectBrowserWithCallback = [
            'cable_connection'
        ];
        $property = $config->getProperties()[$propertyKey];
        $callback = $property->getFormat()->getCallback();
        $callbackFunc = !empty($callback) ? $callback[1] : '';
        $params = $property->getUi()->getParams();

        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $params['p_strPopupType'] === 'browser_object_ng' &&
            !!$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] === false &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] === false &&
            !in_array($callbackFunc, $ignoreObjectBrowserWithCallback);
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

        if (($objectId = $dao->get_obj_id_by_title($value)) > 0) {
            $valueShape->setValue((int) $objectId);
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
        $value = $valueShape->getValue();

        // @see ID-11302 ID-11321 Add this in case we have a 'AbstractBrowserShape' inside an array.
        if (is_array($value)) {
            $value = current($value);
        }

        if ($value instanceof AbstractBrowserShape) {
            $value->handle($config, $propertyKey, $requestData);
            $value = $value->getValue();
        }

        if (is_array($value)) {
            $value = current($value);
        }

        if (strlen((string)(int)$value) !== strlen((string)$value)) {
            return;
        }

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $valueShape->setValue($dao->get_object_by_id((int)$value)->get_row_value('isys_obj__title'));
    }
}
