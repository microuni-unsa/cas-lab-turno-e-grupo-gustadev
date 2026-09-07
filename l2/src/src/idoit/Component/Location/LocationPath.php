<?php

namespace idoit\Component\Location;

use idoit\Component\Settings\Tenant;
use isys_ajax_handler_quick_info;
use isys_application;
use isys_cmdb_dao_category_g_location;
use isys_component_database;
use isys_exception_database;

class LocationPath
{
    private const ELLIPSIS = '…'; // Use UTF8 character instead of HTML "&hellip;".
    private const LTR_CHAR = "&#x200E;"; // Use this to specify parts of a string as "left to right".
    private const SUGGESTION_LIST = '<li id=":objectId" class=":cssClass" title=":fullPath">:path</li>';
    private const ROOT_LOCATION = '<img class="vam" alt=":alt" src=":wwwPath/:iconPath" />';

    private isys_component_database $database;
    private isys_cmdb_dao_category_g_location $dao;
    private bool $alignLeft;
    private string $separator;
    private int $locationPathLength;
    private int $locationPathTitleLength;
    private int $locationPathObjectLimit;
    private bool $authInLocationTree;

    /**
     * @param isys_component_database $database
     * @param Tenant                  $tenantSettings
     */
    public function __construct(isys_component_database $database, Tenant $tenantSettings)
    {
        $this->database = $database;
        $this->dao = isys_cmdb_dao_category_g_location::instance($this->database);

        $this->alignLeft = $tenantSettings->get('gui.location_path.direction.rtl', 0) == 0;
        $this->separator = ' ' . trim($tenantSettings->get('gui.separator.location', ' > ')) . ' ';
        $this->locationPathLength = (int)$tenantSettings->get('maxlength.location.path', 100);
        $this->locationPathTitleLength = (int)$tenantSettings->get('maxlength.location.objects', 16);
        $this->locationPathObjectLimit = (int)$tenantSettings->get('cmdb.limits.location-path', 5);
        $this->authInLocationTree = (bool)$tenantSettings->get('auth.use-in-location-tree', false);
    }

    /**
     * @param int $objectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function getLocationPath(int $objectId): array
    {
        // @see ID-10992 Check if the object has any parent location.
        $directParent = $this->dao->get_parent_id_by_object($objectId);

        if ($directParent === false) {
            return [];
        }

        $path = $this->dao->get_location_path($objectId);
        $path[] = defined_or_default('C__OBJ__ROOT_LOCATION', 1);

        // Add one because we'll apply the current object a bit later.
        $originalPathLength = count($path);

        if (!$this->alignLeft) {
            $path = array_slice($path, 0, max(0, $this->locationPathObjectLimit));
        }

        $path = array_reverse($path);

        if ($this->alignLeft) {
            $path = array_slice($path, 0, max(0, $this->locationPathObjectLimit + 1));
        }

        $titlePath = [];

        foreach ($path as $pathItem) {
            $titlePath[] = new LocationPathItem($pathItem, $this->dao->obj_get_title_by_id_as_string($pathItem));
        }

        if (count($titlePath) < $originalPathLength) {
            if ($this->alignLeft) {
                $titlePath[] = new LocationPathItem(null, self::ELLIPSIS);
            } else {
                // Use 'left-to-right' marks to prevent weird strings like 'Building > Room > Server < ...'.
                array_unshift($titlePath, new LocationPathItem(null, self::LTR_CHAR . self::ELLIPSIS . self::LTR_CHAR));
            }
        }

        $titlePath[] = new LocationPathItem($objectId, $this->dao->obj_get_title_by_id_as_string($objectId));

        return $titlePath;
    }

    /**
     * @param string $term
     *
     * @return array
     * @throws isys_exception_database
     * @see ID-9615 Use proper 'LocationPath' component to create a proper result for suggestion context
     */
    public function getLocationPathForSuggestion(string $term): array
    {
        $return = [];

        $result = $this->dao->get_container_objects(
            $term,
            C__RECORD_STATUS__NORMAL,
            $this->authInLocationTree
        );

        $locationPathOffset = $this->alignLeft ? 0 : -$this->locationPathLength;
        $rtlClass = $this->alignLeft ? '' : 'text-rtl';

        while ($row = $result->get_row()) {
            $objectId = (int)$row["isys_obj__id"];

            if (!$objectId) {
                continue;
            }

            // @see ID-11168 Handle the root location directly.
            if ($objectId == defined_or_default('C__OBJ__ROOT_LOCATION', 1)) {
                return (array)strtr(self::SUGGESTION_LIST, [
                    ':objectId' => $objectId,
                    ':cssClass' => $rtlClass,
                    ':fullPath' => '',
                    ':path' => isys_application::instance()->container->get('language')->get('LC__OBJ__ROOT_LOCATION')
                ]);
            }

            $titlePath = $this->getLocationPath($objectId);

            if (count($titlePath) === 0) {
                continue;
            }

            $fullCombinedPath = implode($this->separator, array_map(fn (LocationPathItem $item) => $item->getLabel(), $titlePath));
            $combinedPath = implode($this->separator, array_map(fn (LocationPathItem $item) => $item->getLabel($this->locationPathTitleLength), $titlePath));

            // Only apply the 'hellip' if we are aligning the text right, otherwise weird rendering might happen.
            if (mb_strlen($combinedPath) > $this->locationPathLength && $this->alignLeft) {
                $combinedPath = mb_substr($combinedPath, $locationPathOffset, $this->locationPathLength) . self::ELLIPSIS;
            }

            $return[] = strtr(self::SUGGESTION_LIST, [
                ':objectId' => $objectId,
                ':cssClass' => $rtlClass,
                ':fullPath' => $fullCombinedPath,
                ':path' => $combinedPath
            ]);
        }

        return array_unique($return);
    }

    /**
     * @param int $objectId
     *
     * @return string|null
     * @throws isys_exception_database
     * @see ID-9616 Use proper 'LocationPath' component to create a proper result for object list context
     */
    public function getLocationPathForObjectList(int $objectId): ?string
    {
        $wwwPath = rtrim(isys_application::instance()->www_path, '/');
        $quickInfo = isys_ajax_handler_quick_info::instance();
        $locationPath = [
            strtr(self::ROOT_LOCATION, [
                ':alt' => isys_application::instance()->container->get('language')->get('LC__OBJ__ROOT_LOCATION'),
                ':wwwPath' => $wwwPath,
                ':iconPath' => 'images/axialis/construction/house-4.svg'
            ])
        ];

        /** @var LocationPathItem[] $locationPathItems */
        $locationPathItems = $this->getLocationPath($objectId);

        // @see ID-10992 Do not process objects without set location.
        if (count($locationPathItems) === 0) {
            return null;
        }

        foreach ($locationPathItems as $locationPathItem) {
            $locationObjectId = $locationPathItem->getObjectId();

            if ($locationObjectId == defined_or_default('C__OBJ__ROOT_LOCATION', 1)) {
                continue;
            }

            if ($locationObjectId === null) {
                $locationPath[] = $locationPathItem->getLabel($this->locationPathTitleLength);
                continue;
            }

            $locationPath[] = $quickInfo->get_quick_info(
                $locationObjectId,
                $locationPathItem->getLabel($this->locationPathTitleLength),
                C__LINK__OBJECT
            );
        }

        return implode($this->separator, $locationPath);
    }
}
