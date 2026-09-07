<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;

abstract class AbstractBrowserShape extends AbstractShape
{
    protected const KEYS = [
        'title',
        'type'
    ];

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return mixed
     */
    abstract public function handle(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData);

    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = array_keys($value);
        $filteredKeys = array_filter($keys, fn ($item) => in_array($item, static::KEYS));

        return count($filteredKeys) >= count(static::KEYS);
    }
}
