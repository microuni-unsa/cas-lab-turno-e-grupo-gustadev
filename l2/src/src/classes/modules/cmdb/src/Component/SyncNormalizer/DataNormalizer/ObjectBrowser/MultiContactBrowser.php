<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\ObjectBrowser;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizer\DataNormalizerInterface;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\AbstractShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\IntegerShape;
use idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes\ListShape;
use isys_application;
use isys_contact_dao_reference;
use isys_popup_browser_object_ng;

class MultiContactBrowser extends MultiSelectBrowser implements DataNormalizerInterface
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
        $referenceTable = $property->getData()?->getReferences() ?? [];
        $params = $property->getUi()->getParams();

        return $referenceTable[0] === 'isys_contact' &&
            !!$params[isys_popup_browser_object_ng::C__MULTISELECTION] &&
            !$params[isys_popup_browser_object_ng::C__SECOND_SELECTION];
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     * @param AbstractShape                $valueShape
     *
     * @return void
     * @throws \isys_exception_contact
     */
    public static function denormalizeData(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData, AbstractShape $valueShape)
    {
        if ($valueShape instanceof ListShape) {
            parent::denormalizeData($config, $propertyKey, $requestData, $valueShape);
            return;
        }

        // We have a contact ID
        if ($valueShape instanceof IntegerShape) {
            $contactId = $valueShape->getValue();
            $daoContactReference = isys_contact_dao_reference::instance(isys_application::instance()->container->get('database'));
            $cmdbDao = isys_application::instance()->container->get('cmdb_dao');
            $daoContactReference->load($contactId);

            $objectIds = array_keys($daoContactReference->get_data_item_array());
            $denormalizedData = array_filter(
                array_map(fn ($item) => is_numeric($item) ? $cmdbDao->obj_get_title_by_id_as_string($item) : null, $objectIds)
            );

            if (!empty($denormalizedData)) {
                $valueShape->setValue($denormalizedData);
            }
        }
    }
}
