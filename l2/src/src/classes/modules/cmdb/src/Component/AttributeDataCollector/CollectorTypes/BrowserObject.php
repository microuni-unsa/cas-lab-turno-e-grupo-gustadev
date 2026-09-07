<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Helper\Unserialize;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter\BrowserObjectFormatter;
use idoit\Module\Cmdb\Controller\Browse;
use isys_application;
use isys_cmdb_dao;
use isys_exception_database;
use isys_popup_browser_object_ng;
use isys_tenantsettings;

class BrowserObject extends AbstractCollector
{
    /**
     * @var array|null
     */
    private ?array $overwriteAttributes = null;

    /**
     * @var bool|null
     */
    private ?bool $displayAttributeCategories = null;

    /**
     * @var bool|null
     */
    private ?bool $returnAsArray = null;

    /**
     * @return array
     */
    private function getBlockedObjectTypes(): array
    {
        // See isys_quick_configuration_wizard_dao $m_skipped_objecttypes
        return filter_defined_constants([
            'C__OBJTYPE__GENERIC_TEMPLATE',
            'C__OBJTYPE__LOCATION_GENERIC',
            'C__OBJTYPE__CONTAINER',
            'C__OBJTYPE__PARALLEL_RELATION',
            'C__OBJTYPE__SOA_STACK'
        ]);
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $ignoreObjectBrowserWithCallback = [
            'cable_connection'
        ];

        $callback = $property->getFormat()->getCallback();
        $callbackFunc = !empty($callback) ? $callback[1] : '';
        $params = $property->getUi()->getParams();

        return ($property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER ||
            $params['p_strPopupType'] === 'browser_object_ng') &&
            !!$params[isys_popup_browser_object_ng::C__SECOND_SELECTION] === false &&
            !in_array($callbackFunc, $ignoreObjectBrowserWithCallback);
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws isys_exception_database
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $params = $property->getUi()->getParams();
        $categoryFilter = $params[isys_popup_browser_object_ng::C__CAT_FILTER];
        $objectTypeBlacklist = $this->getBlockedObjectTypes();

        if (isset($params[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST])
            && $params[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST] !== ''
        ) {
            $objectTypeBlacklist = array_replace($objectTypeBlacklist, explode(';', $params[isys_popup_browser_object_ng::C__TYPE_BLACK_LIST]));
        }

        $settingsId = $property->getUi()->getId();

        $callback = $property->getFormat()->getCallback();
        if (isset($callback[0]) && $callback[0] === 'isys_global_custom_fields_export_helper') {
            $settingsId = 'C__CATG__CUSTOM__' . $settingsId;
        }

        $objectTypeFilter = $this->getObjectTypeFilter($settingsId, $params);

        return $this->getDataForObjectBrowser($property->getUi()->getId(), $reformat, $categoryFilter, $objectTypeFilter, implode(';', $objectTypeBlacklist));
    }

    /**
     * @param string $settingsId
     * @param array  $params
     *
     * @return string
     */
    private function getObjectTypeFilter(string $settingsId, array $params): string
    {
        $objectTypeFilter = $params[isys_popup_browser_object_ng::C__TYPE_FILTER] ?
            [$params[isys_popup_browser_object_ng::C__TYPE_FILTER]] : [];

        $configuredObjectTypes = isys_tenantsettings::get('cmdb.object-browser.' . $settingsId . '.objectTypes');
        $defaultObjectTypes = isys_tenantsettings::get('cmdb.object-browser.' . $settingsId . '.defaultObjectType');

        if ($configuredObjectTypes !== '') {
            if (is_string($configuredObjectTypes)) {
                $configuredObjectTypes = strpos($configuredObjectTypes, ',')
                    ? Unserialize::toArray($configuredObjectTypes)
                    : [$configuredObjectTypes];
            }

            $objectTypeFilter = array_unique(array_merge($objectTypeFilter, (array)$configuredObjectTypes));
        } elseif ($defaultObjectTypes !== '') {
            if (is_string($defaultObjectTypes)) {
                $defaultObjectTypes = strpos($defaultObjectTypes, ',')
                    ? Unserialize::toArray($defaultObjectTypes)
                    : [$defaultObjectTypes];
            }

            $objectTypeFilter = array_unique(array_merge($objectTypeFilter, (array)$defaultObjectTypes));
        }

        return implode(',', array_map('defined_or_default', $objectTypeFilter));
    }

    /**
     * @param string|null $categoryFilter
     * @param string|null $objectTypeFilter
     * @param string|null $objectTypeBlacklist
     *
     * @return array
     * @throws isys_exception_database
     */
    private function getOnlyObjectData(
        ?string $categoryFilter = null,
        ?string $objectTypeFilter = null,
        ?string $objectTypeBlacklist = null
    ) {
        if ($objectTypeFilter !== null) {
            $objectTypeFilter = array_filter(array_map(function ($item) {
                return defined_or_default($item);
            }, explode(';', $objectTypeFilter)));
        }

        $dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));
        $result = $dao->search_objects(
            '',
            $objectTypeFilter,
            null,
            '',
            false,
            false,
            'isys_obj__title ASC',
            false,
            C__RECORD_STATUS__NORMAL,
            $objectTypeBlacklist,
            $categoryFilter
        );
        $data = [];
        while ($row = $result->get_row()) {
            $data[] = [
                'id' => $row['isys_obj__id'],
                'title' => $row['isys_obj__title'],
                'sysid' => $row['isys_obj__sysid'],
                'type-title' => isys_application::instance()->container->get('language')
                    ->get($row['isys_obj_type__title']),
                'type-id' => $row['isys_obj_type__id']
            ];
        }

        return $data;
    }

    /**
     * Experiment to retrieve data with Browse controller
     *
     * @param string $uiId
     * @param bool $reformat
     * @param string|null $categoryFilter
     * @param string|null $objectTypeFilter
     * @param string|null $objectTypeBlacklist
     *
     * @return mixed
     * @throws isys_exception_database
     */
    private function getDataForObjectBrowser(
        string $uiId,
        bool $reformat = false,
        ?string $categoryFilter = null,
        ?string $objectTypeFilter = null,
        ?string $objectTypeBlacklist = null
    ) {
        $globalCategories = $specificCategories = [];

        if ($categoryFilter !== null) {
            $categoryFilter = explode(';', $categoryFilter);

            $globalCategories = array_filter($categoryFilter, function ($categoryConstant) {
                return strpos($categoryConstant, '_CATG_') !== false;
            });
            $specificCategories = array_filter($categoryFilter, function ($categoryConstant) {
                return strpos($categoryConstant, '_CATS_') !== false;
            });
        }

        $browser = new Browse(new \isys_module_cmdb());

        $register = new \isys_register();
        $register->set('POST', array_filter([
            'objects'                      => [],
            'output'                       => 'objects',
            'key'                          => $uiId,
            'filter'                       => \isys_format_json::encode([
                Browse::BROWSE_FILTER_GLOBAL_CATEGORY     => $globalCategories,
                Browse::BROWSE_FILTER_OBJECT_TYPE         => $objectTypeFilter,
                Browse::BROWSE_FILTER_OBJECT_TYPE_EXCLUDE => $objectTypeBlacklist,
                Browse::BROWSE_FILTER_SPECIFIC_CATEGORY   => $specificCategories,
            ]),
            'attributes'                   => $this->overwriteAttributes !== null ? \isys_format_json::encode($this->overwriteAttributes) : null,
            'display-attribute-categories' => $this->displayAttributeCategories !== null ? (int)$this->displayAttributeCategories : null,
            'return-as-array' => $this->returnAsArray
        ], function ($value) {
            return $value !== null;
        }));

        $browser->objectType($register);
        $response = $browser->getResponse();

        return $reformat ? BrowserObjectFormatter::reformat($response['data']): $response;
    }

    /**
     * @param array|null $attributes
     *
     * @return BrowserObject
     */
    public function overwriteAttributeConfiguration(?array $attributes = null): BrowserObject
    {
        $this->overwriteAttributes = $attributes;

        return $this;
    }

    /**
     * @param bool|null $displayAttributeCategories
     *
     * @return BrowserObject
     */
    public function displayAttributeCategories(?bool $displayAttributeCategories = null): BrowserObject
    {
        $this->displayAttributeCategories = $displayAttributeCategories;

        return $this;
    }

    /**
     * @param bool|null $returnAsArray
     *
     * @return $this
     */
    public function setReturnAsArray(?bool $returnAsArray): BrowserObject
    {
        $this->returnAsArray = $returnAsArray;

        return $this;
    }
}
