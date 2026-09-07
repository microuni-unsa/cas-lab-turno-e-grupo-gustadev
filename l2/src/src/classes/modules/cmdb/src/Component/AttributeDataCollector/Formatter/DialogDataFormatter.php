<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter;

class DialogDataFormatter
{
    /**
     * @param array $dialogData
     *
     * @return array
     */
    public static function reformat(array $dialogData): array
    {
        if (empty($dialogData)) {
            return [];
        }
        $newData = [];
        foreach ($dialogData as $id => $data) {
            $newData[] = [
                'id'    => $id,
                'title' => $data,
            ];
        }
        
        return $newData;
    }
}
