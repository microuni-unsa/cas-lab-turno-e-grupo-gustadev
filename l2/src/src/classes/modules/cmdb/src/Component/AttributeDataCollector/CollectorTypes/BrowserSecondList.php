<?php

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;
use isys_application;
use isys_format_json;
use isys_popup_browser_object_ng;

class BrowserSecondList extends AbstractCollector
{
    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $uiParams = $property->getUi()->getParams();

        if (!$uiParams[isys_popup_browser_object_ng::C__SECOND_SELECTION] ||
            !isset($uiParams[isys_popup_browser_object_ng::C__SECOND_LIST]) ||
            (!is_array($uiParams[isys_popup_browser_object_ng::C__SECOND_LIST]) && !is_string($uiParams[isys_popup_browser_object_ng::C__SECOND_LIST]))
        ) {
            return false;
        }

        return $uiParams['p_strPopupType'] !== 'browser_cable_connection_ng';
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws \idoit\Exception\JsonException
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $uiParams = $property->getUi()->getParams();
        $class = $method = null;
        $parameters = [];

        if (is_array($uiParams[isys_popup_browser_object_ng::C__SECOND_LIST])) {
            [$class, $method] = explode('::', $uiParams[isys_popup_browser_object_ng::C__SECOND_LIST][0]);
            $parameters = $uiParams[isys_popup_browser_object_ng::C__SECOND_LIST][1] ?? [];
        } elseif (is_string($uiParams[isys_popup_browser_object_ng::C__SECOND_LIST])) {
            [$class, $method] = explode('::', $uiParams[isys_popup_browser_object_ng::C__SECOND_LIST]);
        }

        $secondListPropertyProvider = isys_application::instance()->container->get('idoit.cmdb.object-browser-second-list.property-provider');

        if (!$class ||
            !$method ||
            !class_exists($class) ||
            !$secondListPropertyProvider->isApplicable($class, $method)
        ) {
            return [];
        }

        $data = $secondListPropertyProvider->getDataByContext(isys_popup_browser_object_ng::C__CALL_CONTEXT__REQUEST, $class, $method);

        if (is_string($data) && isys_format_json::is_json($data)) {
            $data = isys_format_json::decode($data);
        }

        return $data;
    }
}
