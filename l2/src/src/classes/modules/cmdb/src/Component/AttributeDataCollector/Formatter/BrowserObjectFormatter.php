<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter;

class BrowserObjectFormatter
{
    /**
     * @param array $data
     *
     * @return array
     */
    public static function reformat(array $data): array
    {
        $browserData = $data['data'];
        $browserHeader = $data['header'];
        $newData = [];

        foreach ($browserData as $browserDataItem) {
            $newData[] = array_combine($browserHeader, $browserDataItem);
        }

        return $newData;
    }
}
