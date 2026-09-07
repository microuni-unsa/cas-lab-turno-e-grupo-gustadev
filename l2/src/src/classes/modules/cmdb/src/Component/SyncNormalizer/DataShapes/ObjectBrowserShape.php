<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use isys_application;

class ObjectBrowserShape extends AbstractBrowserShape implements ShapeInterface
{
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
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $title = $this->getValue()['title'];
        $type = $this->getValue()['type'];

        if (($objectId = $dao->get_obj_id_by_title(
            $title,
            (
                is_numeric($type) ? $type :
                    (is_string($type) ? $dao->getObjectTypeId($type) : null)
            )
        )) > 0
        ) {
            $this->setValue((int) $objectId);
            return;
        }
        $this->setValue(null);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $value = $this->getValue();

        if (is_array($value) && isset($value['title'])) {
            return (string) $value['title'];
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }
}
