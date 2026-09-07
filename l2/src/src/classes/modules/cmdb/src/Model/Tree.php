<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Component\Tree\DefaultFilter;
use idoit\Component\Tree\TreeFilterInterface;
use idoit\Model\Dao\Base;
use isys_application as Application;
use isys_auth;
use isys_auth_cmdb_objects;
use isys_cmdb_dao_category_g_logical_unit;
use isys_component_dao_result;
use isys_component_database;

/**
 * i-doit Tree Model
 *
 * @package     idoit\Module\Cmdb\Model
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.11.1
 */
class Tree extends Base
{
    private TreeFilterInterface $treeFilter;

    /**
     * Constant for the tree-mode "physical".
     * @var string
     */
    const MODE_PHSYICAL = 'physical';

    /**
     * Constant for the tree-mode "logical".
     * @var string
     */
    const MODE_LOGICAL = 'logical';

    /**
     * Constant for the tree-mode "combined" (logical + physical).
     * @var string
     */
    const MODE_COMBINED = 'combined';

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        $this->treeFilter = new DefaultFilter();
    }

    /**
     * @param string $identifier
     *
     * @return $this
     * @throws \Exception
     */
    public function setTreeFilter(string $identifier): self
    {
        $treeFilter = Application::instance()->container->get('tree_filter');

        if ($treeFilter->exists($identifier)) {
            $this->treeFilter = $treeFilter->get($identifier);
        }

        return $this;
    }

    /**
     * Retrieve information of a given object.
     *
     * @param int         $objectId
     * @param string|null $mode
     * @param bool|null   $onlyContainer
     * @param int|null    $levels
     * @param bool|null   $considerRights
     * @param string|null $treeFilter
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function getLocationChildren(int $objectId, ?string $mode = null, ?bool $onlyContainer = null, ?int $levels = null, ?bool $considerRights = null): array
    {
        $language = Application::instance()->container->get('language');

        $mode = $mode ?? self::MODE_COMBINED;
        $onlyContainer = $onlyContainer ?? false;
        $considerRights = $considerRights ?? false;
        $levels = $levels ?? 1;
        $isRoot = $objectId == C__OBJ__ROOT_LOCATION;

        // @see ID-3236 and ID-6808 Instead of simply displaying children of the root-location, display objects that we are allowed to see.
        if ($isRoot) {
            $result = $this->prepareRoot($considerRights);
        } else {
            $result = $this->prepareNode($objectId);
        }

        $nextLevel = $levels - 1;
        $children = 0;
        $result['nodeId'] = (int)$result['nodeId'];
        $result['nodeTypeId'] = (int)$result['nodeTypeId'];
        $result['nodeTitle'] = $language->get($result['nodeTitle']);
        $result['nodeTypeTitle'] = $language->get($result['nodeTypeTitle']);

        if ($mode === self::MODE_PHSYICAL || $mode === self::MODE_COMBINED) {
            $physicalChildren = $this->getPhysicalChildren($objectId, $onlyContainer, $considerRights);
            $childrenCount = count($physicalChildren);

            if ($levels > 0 && $childrenCount) {
                foreach ($physicalChildren as $child) {
                    $result['children'][$child] = $this->getLocationChildren($child, $mode, $onlyContainer, $nextLevel, $considerRights);

                    // @see ID-9041 Show logical locations underneath their physical locations.
                    if ($mode === self::MODE_COMBINED) {
                        $result['children'][$child] += $this->getLocationChildren($child, $mode, $onlyContainer, $nextLevel, $considerRights);
                    }
                }
            }

            $children += $childrenCount;
        }

        // @see ID-9041 Only skip if we try to render logical locations on root (in 'combined' view).
        if ($mode === self::MODE_LOGICAL || ($mode === self::MODE_COMBINED && !$isRoot)) {
            $logicalChildren = $this->getLogicalChildren(($isRoot ? null : $objectId), $onlyContainer, $considerRights);
            $childrenCount = count($logicalChildren);

            if ($levels > 0 && $childrenCount) {
                foreach ($logicalChildren as $child) {
                    if (isset($result['children'][$child])) {
                        // Skip objects we already found.
                        continue;
                    }

                    $result['children'][$child] = $this->getLocationChildren($child, $mode, $onlyContainer, $nextLevel, $considerRights);
                }
            }

            $children += $childrenCount;
        }

        $result['children'] = array_values($result['children']);
        $result['hasChildren'] = $children > 0;

        return $result;
    }

    /**
     * Retrieve the root node.
     *
     * @param  bool $considerRights
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function prepareRoot(bool $considerRights): array
    {
        if ($considerRights && !\isys_auth_cmdb_objects::instance()->is_allowed_to(isys_auth::VIEW, 'obj_id/' . C__OBJ__ROOT_LOCATION)) {
            // The root location is NOT allowed, we set a placeholder root node.
            return [
                'nodeId'        => 1,
                'nodeTitle'     => 'LC__CMDB__OBJECT_BROWSER__LOCATION_VIEW',
                'nodeTypeId'    => 0,
                'nodeTypeTitle' => '',
                'nodeTypeColor' => '#ffffff',
                'nodeTypeIcon'  => Application::instance()->www_path . 'images/axialis/construction/house-4.svg',
                'isContainer'   => true,
                'children'      => [],
            ];
        }

        // The root location is allowed, we set the root node accordinly.
        $return = $this->prepareNode(C__OBJ__ROOT_LOCATION);
        $return['nodeTitle'] = 'LC__OBJ__ROOT_LOCATION';
        return $return;
    }

    /**
     * Retrieve a node.
     *
     * @param  int $objectId
     *
     * @return array
     * @throws \isys_exception_database
     */
    private function prepareNode(int $objectId): array
    {
        $select = parent::selectImplode([
            'isys_obj__id'             => 'nodeId',
            'isys_obj__title'          => 'nodeTitle',
            'isys_obj_type__id'        => 'nodeTypeId',
            'isys_obj_type__title'     => 'nodeTypeTitle',
            'isys_obj_type__color'     => 'nodeTypeColor',
            'isys_obj_type__container' => 'isContainer',
        ]);

        $sql = 'SELECT ' . $select . '
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

        $result = $this->retrieve($sql)->get_row();
        $routeGenerator = Application::instance()->container->get('route_generator');

        $result['nodeTypeColor'] = '#' . ($result['nodeTypeColor'] ?: 'ffffff');
        $result['nodeTypeIcon'] = $routeGenerator->generate('cmdb.object-type.icon', ['objectTypeId' => $result['nodeTypeId']]);
        $result['isContainer'] = (bool)$result['isContainer'];
        $result['children'] = [];

        return $result;
    }

    /**
     * @param  int  $objectId
     * @param  bool $onlyContainer
     * @param  bool $considerRights
     *
     * @see    ID-3236 and ID-6808  Instead of simply displaying children of the root-location, display objects that we are allowed to see.
     * @return array
     * @throws \isys_exception_database
     */
    private function getPhysicalChildren(int $objectId, bool $onlyContainer, bool $considerRights): array
    {
        $result = [];

        if ($considerRights) {
            $daoResult = isys_auth_cmdb_objects::instance()->get_allowed_locations($objectId);

            if (!($daoResult instanceof isys_component_dao_result)) {
                return $result;
            }

            while ($row = $daoResult->get_row()) {
                if ($onlyContainer && !$row['isys_obj_type__container']) {
                    continue;
                }

                // @see  ID-8837  This can happen, when we retrieve all 'allowed objects'.
                if ($row['isys_obj__id'] == C__OBJ__ROOT_LOCATION) {
                    continue;
                }

                if ($this->treeFilter->shouldSkip($objectId, self::MODE_PHSYICAL, (int)$row['isys_obj__id'])) {
                    continue;
                }

                $result[] = (int)$row['isys_obj__id'];
            }
        } else {
            $sql = 'SELECT isys_obj__id
                FROM isys_catg_location_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                WHERE isys_catg_location_list__parentid = ' . $this->convert_sql_id($objectId) . '
                AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

            if ($onlyContainer) {
                $sql .= ' AND isys_obj_type__container = 1';
            }

            $daoResult = $this->retrieve($sql . ';');

            while ($row = $daoResult->get_row()) {
                if ($this->treeFilter->shouldSkip($objectId, self::MODE_PHSYICAL, (int)$row['isys_obj__id'])) {
                    continue;
                }

                $result[] = (int)$row['isys_obj__id'];
            }
        }

        return $result;
    }

    /**
     * @param  int|null  $objectId
     * @param  bool $onlyContainer
     * @param  bool $considerRights
     *
     * @see    ID-3236 and ID-6808  Instead of simply displaying children of the root-location, display objects that we are allowed to see.
     * @return array
     * @throws \isys_exception_database
     */
    private function getLogicalChildren(?int $objectId, bool $onlyContainer, bool $considerRights): array
    {
        $result = [];

        // Instead of relying on plain SQL we use this method, since it handles the 'root location' situation correctly.
        $daoResult = isys_cmdb_dao_category_g_logical_unit::instance($this->m_db)->get_data_by_parent($objectId, $considerRights);

        while ($row = $daoResult->get_row()) {
            if ($onlyContainer && !$row['isys_obj_type__container']) {
                continue;
            }

            if ($this->treeFilter->shouldSkip($objectId, self::MODE_LOGICAL, (int)$row['isys_obj__id'])) {
                continue;
            }

            $result[] = (int)$row['isys_obj__id'];
        }

        return $result;
    }
}
