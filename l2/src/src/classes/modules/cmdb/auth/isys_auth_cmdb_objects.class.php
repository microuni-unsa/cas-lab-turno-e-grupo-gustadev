<?php

/**
 * i-doit
 *
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_cmdb_objects extends isys_auth_cmdb
{
    /**
     * Container for singleton instance
     *
     * @var isys_auth_cmdb_objects
     */
    private static $m_instance = null;

    /**
     * @var string
     */
    private $m_allowed_object_types_condition = [];

    /**
     * @var string
     */
    private $m_allowed_object_types = [];

    /**
     * @var string
     */
    private $m_allowed_objects_condition = [];

    /**
     * Local variable that caches the retrieved objects by role.
     *
     * @var array
     */
    private static $contactRoleCache = [];

    /**
     * Local variable that caches the retrieved objects by physical location.
     *
     * @var array
     */
    private static $physicalLocationCache = [];

    /**
     * Local variable that caches the retrieved objects by logical location.
     *
     * @var array
     */
    private static $logicalLocationCache = [];

    /**
     * @param       $p_person_id
     * @param null  $p_module_id
     * @param array $p_paths
     *
     * @return isys_cache_keyvalue
     */
    public static function invalidate_cache($p_person_id, $p_module_id = null, $p_paths = [])
    {
        return isys_cache::keyvalue()
            ->ns($p_person_id)
            ->delete('auth.condition.allowed_objects')
            ->delete('auth.condition.allowed_object_types');
    }

    /**
     * Return ids of allowed object types
     *
     * @return string
     */
    public function get_allowed_object_types($p_right)
    {
        if (!isset($this->m_allowed_object_types[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_object_types[$p_right];
    }

    /**
     * Checks wheather p_right is set for p_object_type
     *
     * @return string
     */
    public function is_object_type_allowed($p_object_type, $p_right)
    {
        if (!isset($this->m_allowed_object_types[$p_right])) {
            $this->parse($p_right);
        }

        return isset($this->m_allowed_object_types[$p_right][$p_object_type]);
    }

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_cmdb_objects
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            global $g_comp_database;

            self::$m_dao = new isys_auth_dao($g_comp_database);
        }

        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Return SQL condition to identify all object types where the user has access rights to
     *
     * @return null|string
     */
    public function get_allowed_object_types_condition($p_right = isys_auth::VIEW)
    {
        if (!isset($this->m_allowed_object_types_condition[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_object_types_condition[$p_right];
    }

    /**
     * Return SQL condition to isolate objects the user is allowed to see
     *
     * @return string
     */
    public function get_allowed_objects_condition($p_right = isys_auth::VIEW)
    {
        if (!isset($this->m_allowed_objects_condition[$p_right])) {
            $this->parse($p_right);
        }

        return $this->m_allowed_objects_condition[$p_right];
    }

    /**
     * Return all allowed objects as an array. Note that this function does not include objects.
     *
     * @deprecated  DON'T USE SINCE THIS FUNCTION COULD BE VERY SLOW AND EXCEEDS THE MAX_ALLOWED_PACKETS RESTRICTION
     *
     * @param   integer $p_type_filter
     *
     * @return  array
     * @throws  isys_exception_database
     */
    public function get_allowed_objects($p_type_filter = null)
    {
        // Check for inactive auth .
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_return = [];

        $l_cache_obj = isys_caching::factory('auth-' . isys_application::instance()->container->get('session')->get_user_id());
        $l_cache = $l_cache_obj->get('allowed_objects');

        if ($l_cache === false) {
            if ($p_type_filter) {
                $l_type_condition = ' AND (isys_obj__isys_obj_type__id = ' . $p_type_filter . ')';
            } else {
                $l_type_condition = '';
            }

            $l_objects = isys_cmdb_dao::factory(isys_application::instance()->database)
                ->retrieve('SELECT isys_obj__id AS id FROM isys_obj WHERE TRUE ' . $this->get_allowed_objects_condition() . $l_type_condition . ' ORDER BY isys_obj__id ASC;');

            while ($l_row = $l_objects->get_row()) {
                $l_return[$l_row['id']] = $l_row['id'];
            }

            try {
                $l_cache_obj->set('allowed_objects', $l_return)
                    ->save();
            } catch (isys_exception_filesystem $e) {
                isys_notify::warning($e->getMessage());
            }
        }

        return $l_return;
    }

    /**
     * Condition to check if object is mine
     *
     * @return string
     */
    public function get_owner_condition()
    {
        $objId = (int) isys_application::instance()->container->get('session')->get_user_id();

        if ($objId == defined_or_default('C__OBJ__PERSON_API_SYSTEM')) {
            return 'TRUE';
        }

        return 'isys_obj__id IN (SELECT obj_own.isys_obj__id FROM isys_obj as obj_own WHERE obj_own.isys_obj__owner_id = ' . $objId . ')';
    }

    /**
     * Prepare SQL conditions.
     *
     * @param $p_right
     *
     * @return bool
     * @throws Exception
     */
    private function parse($p_right = isys_auth::VIEW)
    {
        $l_allowed_object_types = $l_allowed_objects = $l_conditions = [];

        // Check for inactive auth system.
        if (!$this->is_auth_active()) {
            return true;
        }

        // Get Caching instance in a user namespace.
        $l_cache_obj = isys_cache::keyvalue()->ns(isys_application::instance()->container->get('session')->get_user_id() . '-' . $p_right);

        $this->m_allowed_objects_condition[$p_right] = $l_cache_obj->get("auth.condition.allowed_objects");
        $this->m_allowed_object_types_condition[$p_right] = $l_cache_obj->get('auth.condition.allowed_object_types');

        $l_object_type_wildcard = false;

        // Start evaluating if cache is not set
        if (!$this->m_allowed_objects_condition[$p_right] && !$this->m_allowed_object_types_condition[$p_right]) {
            $this->m_allowed_objects_condition[$p_right] = ' AND FALSE';
            if (isset($this->m_paths['obj_owner'][isys_auth::EMPTY_ID_PARAM])
                && in_array($p_right, $this->m_paths['obj_owner'][isys_auth::EMPTY_ID_PARAM])
            ) {
                $this->m_allowed_objects_condition[$p_right] = ' AND (' . $this->get_owner_condition() . ')';
            }
            $this->m_allowed_object_types_condition[$p_right] = ' AND FALSE';

            if (isset($this->m_paths['obj_in_type'])) {
                // Wildcard for all objects is greater than any objtype condition, so we don't need to handle any objtype conditions when the user has access to all objects anyway.
                if (!isset($this->m_paths['obj_in_type'][isys_auth::WILDCHAR]) || !in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR])) {
                    if (!isset($this->m_paths['obj_in_type'][isys_auth::EMPTY_ID_PARAM]) || !in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::EMPTY_ID_PARAM])) {
                        foreach ($this->m_paths['obj_in_type'] as $l_key => $l_rights) {
                            if (array_sum($l_rights) & $p_right) {
                                $l_key = strtoupper($l_key);

                                if (defined($l_key)) {
                                    $l_allowed_object_types[constant($l_key)] = constant($l_key);
                                }
                            }
                        }

                        if (count($l_allowed_object_types) > 0) {
                            $l_conditions['objtype'] = '(isys_obj__id IN (SELECT authSelect.isys_obj__id FROM isys_obj authSelect WHERE authSelect.isys_obj__isys_obj_type__id IN (' .
                                implode(',', $l_allowed_object_types) . ')))';
                        }
                    }
                } else {
                    if (in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR])) {
                        $l_object_type_wildcard = true;
                        $this->m_allowed_object_types_condition[$p_right] = ' AND TRUE';
                    }
                }
            }

            // Get object types from object id rights.
            if (isset($this->m_paths['obj_id'])) {
                if (!isset($this->m_paths['obj_id'][isys_auth::WILDCHAR])) {
                    if (is_array($this->m_paths['obj_id']) && count($this->m_paths['obj_id']) > 0) {
                        $l_tmp = $this->m_paths['obj_id'];
                        unset($l_tmp[isys_auth::EMPTY_ID_PARAM], $l_tmp[isys_auth::WILDCHAR]);
                        $l_allowed_objects = array_keys($l_tmp);
                        unset($l_tmp);
                    }
                } else {
                    if (in_array($p_right, $this->m_paths['obj_id'][isys_auth::WILDCHAR])) {
                        $this->m_allowed_objects_condition[$p_right] = ' AND TRUE';
                    }
                }
            }

            // @see ID-8458 Implement secondary 'underneath location' rights.
            if (!$l_object_type_wildcard) {
                // Search for objects based on a location path.
                $objects = $this->retrieveAllowedObjectsByLocation($p_right);

                foreach ($objects as $object) {
                    $l_allowed_objects[] = $object['id'];
                    $l_allowed_object_types[] = $object['typeId'];
                }

                $objects = $this->retrieveAllowedObjectsFromLogicalLocation($p_right);

                foreach ($objects as $object) {
                    $l_allowed_objects[] = $object['id'];
                    $l_allowed_object_types[] = $object['typeId'];
                }

                // @see ID-8681 Implement rights according to 'contact assignment roles'.
                if (isset($this->m_paths['contact_role'])) {
                    $objects = $this->retrieveAllowedObjectsByRole($p_right);

                    foreach ($objects as $object) {
                        $l_allowed_objects[] = $object['id'];
                        $l_allowed_object_types[] = $object['typeId'];
                    }
                }
            }

            // Clean up the allowed objects and object types.
            $l_allowed_objects = array_filter(array_unique(array_values($l_allowed_objects)));
            $l_allowed_object_types = array_filter(array_unique(array_values($l_allowed_object_types)));

            // Addup all collected object ids to l_conditions:objids
            if (count($l_allowed_objects) && !$l_object_type_wildcard) {
                $l_conditions['objids'] = 'isys_obj__id IN (' . implode(',', $l_allowed_objects) . ')';
            } else {
                if ($l_object_type_wildcard) {
                    $this->m_allowed_objects_condition[$p_right] = '';
                }
            }

            // Build condition.
            if (count($l_conditions)) {
                // Allow view rights for own objects
                if (isset($this->m_paths['obj_owner'][isys_auth::EMPTY_ID_PARAM])
                    && in_array($p_right, $this->m_paths['obj_owner'][isys_auth::EMPTY_ID_PARAM])
                ) {
                    $l_conditions[] = '(' . $this->get_owner_condition() . ')';
                    $l_conditions = array_reverse($l_conditions);
                }

                $this->m_allowed_objects_condition[$p_right] = ' AND (' . implode(' OR ', $l_conditions) . ')';
            }

            if (count($l_allowed_object_types)) {
                $this->m_allowed_object_types_condition[$p_right] = ' AND (isys_obj_type__id IN (' . implode(',', $l_allowed_object_types) . ')';

                if (isset($l_conditions['objids'])) {
                    $this->m_allowed_object_types_condition[$p_right] .= ' OR isys_obj_type__id IN (SELECT isys_obj__isys_obj_type__id FROM isys_obj WHERE ' .
                        $l_conditions['objids'] . ')';
                }

                $this->m_allowed_object_types_condition[$p_right] .= ')';

                // Remember allowed object type ids
                $this->m_allowed_object_types[$p_right] = $l_allowed_object_types;
            } else {
                if ($this->m_allowed_objects_condition[$p_right] != '') {
                    $this->m_allowed_object_types_condition[$p_right] = ' AND (isys_obj_type__id IN (SELECT DISTINCT(isys_obj__isys_obj_type__id) FROM isys_obj WHERE TRUE ' .
                        $this->m_allowed_objects_condition[$p_right] . '))';
                }
            }

            try {
                $l_cache_obj
                    ->set('auth.condition.allowed_objects', $this->m_allowed_objects_condition[$p_right])
                    ->set('auth.condition.allowed_object_types', $this->m_allowed_object_types_condition[$p_right]);
            } catch (isys_exception_filesystem $e) {
                isys_notify::warning($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Method to retrieve allowed objects by location.
     *
     * @param int $right
     *
     * @return array
     * @throws Exception
     */
    private function retrieveAllowedObjectsByLocation(int $right): array
    {
        if (isset(self::$physicalLocationCache[$right]) && !empty(self::$physicalLocationCache[$right])) {
            return self::$physicalLocationCache[$right];
        }

        $return = [];
        $paths = [
            'location' => true,
            'location_excl' => false
        ];

        foreach ($paths as $path => $includeParent) {
            // The given ID could not be found directly, now we check the location path.
            if (!isset($this->m_paths[$path]) || isset($this->m_paths[$path][isys_auth::WILDCHAR]) || isset($this->m_paths[$path][isys_auth::EMPTY_ID_PARAM])) {
                continue;
            }

            $locationDao = isys_cmdb_dao_location::factory(isys_application::instance()->container->get('database'));

            foreach ($this->m_paths[$path] as $locationObjectId => $rights) {
                if (array_sum($rights) & $right) {
                    // Get child locations of the location auth-paths.
                    $childLocationResult = $locationDao->get_mptt()->get_children($locationObjectId, null);

                    if (!$childLocationResult) {
                        continue;
                    }

                    while ($row = $childLocationResult->get_row()) {
                        if ($locationObjectId == $row['isys_catg_location_list__isys_obj__id'] && !$includeParent) {
                            continue;
                        }

                        $return[] = [
                            'id' => $row['isys_catg_location_list__isys_obj__id'],
                            'typeId' => $row['isys_obj_type__id']
                        ];
                    }
                }
            }
        }

        return self::$physicalLocationCache[$right] = $return;
    }

    /**
     * Method to retrieve allowed objects by logical location.
     *
     * @param int $right
     *
     * @return array
     * @throws Exception
     */
    private function retrieveAllowedObjectsFromLogicalLocation(int $right): array
    {
        if (isset(self::$logicalLocationCache[$right]) && !empty(self::$logicalLocationCache[$right])) {
            return self::$logicalLocationCache[$right];
        }

        $return = [];
        $paths = [
            'logical_location' => true,
            'logical_location_excl' => false
        ];

        foreach ($paths as $path => $includeParent) {
            if (!isset($this->m_paths[$path]) || isset($this->m_paths[$path][isys_auth::WILDCHAR]) || isset($this->m_paths[$path][isys_auth::EMPTY_ID_PARAM])) {
                continue;
            }

            // The given ID could not be found directly, now we check the logical location path.
            $logicalLocationDao = isys_cmdb_dao_category_g_assigned_logical_unit::instance(isys_application::instance()->container->get('database'));

            foreach ($this->m_paths[$path] as $locationObjectId => $rights) {
                if (array_sum($rights) & $right) {
                    // Get child locations of the location auth-paths.
                    $childLocations = $logicalLocationDao->get_children($locationObjectId, true);

                    foreach ($childLocations as $row) {
                        if ($locationObjectId == $row['isys_obj__id'] && !$includeParent) {
                            continue;
                        }

                        $return[] = [
                            'id' => $row['isys_obj__id'],
                            'typeId' => $row['isys_obj_type__id']
                        ];
                    }
                }
            }
        }

        return self::$logicalLocationCache[$right] = $return;
    }

    /**
     * @param int $right
     *
     * @return array
     * @throws Exception
     */
    private function retrieveAllowedObjectsByRole(int $right): array
    {
        if (isset(self::$contactRoleCache[$right]) && !empty(self::$contactRoleCache[$right])) {
            return self::$contactRoleCache[$right];
        }

        $database = isys_application::instance()->container->get('database');
        $dao = isys_cmdb_dao_category_s_person_assigned_groups::instance($database);
        $userObjectId = (int)isys_application::instance()->container->get('session')->get_user_id();
        $roles = [];
        $return = [];
        $roleCondition = '';

        if (!isset($this->m_paths['contact_role'][isys_auth::WILDCHAR]) || !in_array($right, $this->m_paths['contact_role'][isys_auth::WILDCHAR])) {
            foreach ($this->m_paths['contact_role'] as $roleId => $rights) {
                if (in_array($right, $rights)) {
                    $roles[] = $roleId;
                }
            }

            $roleCondition = 'AND isys_catg_contact_list__isys_contact_tag__id ' . $dao->prepare_in_condition($roles);
        }

        $objects = [$userObjectId];

        // @see ID-8905 Consider groups which the current user is a member of.
        $groupResult = $dao->get_data(null, $userObjectId);

        while ($row = $groupResult->get_row()) {
            $objects[] = (int)$row['isys_person_2_group__isys_obj__id__group'];
        }

        $sql = "SELECT isys_obj__id AS id, isys_obj__isys_obj_type__id AS typeId
            FROM isys_catg_contact_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id
            INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
            WHERE isys_connection__isys_obj__id " . $dao->prepare_in_condition($objects) . "
            {$roleCondition};";

        $result = $dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $return[] = $row;
        }

        return self::$contactRoleCache[$right] = $return;
    }

    /**
     * Recursive method to fetch the first objects in the location tree, that a user is allowed to see.
     *
     * @param int $parent
     *
     * @return array|null
     * @throws Exception
     * @see ID-10341 / Zendesk #22337
     */
    private function fetchChildren(int $parent): ?array
    {
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $statusNormal = $dao->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $sql = "SELECT isys_catg_location_list__isys_obj__id
            FROM isys_catg_location_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__isys_obj__id
            WHERE isys_obj__status = {$statusNormal}
            AND isys_catg_location_list__parentid = {$parent};";

        $result = $dao->retrieve($sql);

        if (count($result) === 0) {
            return null;
        }

        // @see ID-11261 Check if we have rights for ANY object physically located beneath the current one. This can skip multiple thousands of iterations :)
        $mpttDao = isys_component_dao_mptt::factory(isys_application::instance()->container->get('database'));

        $countQuery = 'SELECT COUNT(1) AS cnt
            FROM isys_obj
            WHERE TRUE
            AND (isys_obj__id IN (' . $mpttDao->getObjectsWithSubQueryString($parent) . '))
            ' . $this->get_allowed_objects_condition() . ';';

        if ($dao->retrieve($countQuery)
                ->get_row_value('cnt') == 0) {
            return null;
        }

        $objects = [];

        while ($row = $result->get_row()) {
            $objectId = (int)$row['isys_catg_location_list__isys_obj__id'];

            try {
                $this->obj_id(self::VIEW, $objectId);

                $objects[] = $objectId;
            } catch (isys_exception_auth $e) {
                // The current object is not allowed to be viewed - continue searching the tree for other allowed objects.
                $children = $this->fetchChildren($objectId);

                if (is_array($children)) {
                    $objects = array_merge($children, $objects);
                }
            }
        }

        return $objects;
    }

    /**
     * Retrieve all allowed objects as "first level" (will be used, if the user has no rights on "all" objects).
     *
     * @param bool $hideRoot
     *
     * @return isys_component_dao_result|null
     * @throws Exception
     */
    public function get_allowed_locations(int $parentObjectId, bool $hideRoot = true): ?isys_component_dao_result
    {
        $currentUserId = (int) isys_application::instance()->container->get('session')->get_user_id();
        $cacheKey = "allowed-locations_{$currentUserId}_{$parentObjectId}";
        $cache = isys_cache_keyvalue::keyvalue()->ns('auth');

        // @see ID-10341 Cache the found objects.
        if ($cache->exists($cacheKey)) {
            $objects = $cache->get($cacheKey);
        } else {
            // New logic that is more understandable and should work better for big databases.
            $objects = $this->fetchChildren($parentObjectId);

            $tenantSettings = isys_application::instance()->container->get('settingsTenant');
            $cache->set($cacheKey, $objects, $tenantSettings->get('cache.default-auth-expiration-time', 600));
        }

        if ($objects === null) {
            return null;
        }

        $dao = isys_application::instance()->container->get('cmdb_dao');

        $sql = 'SELECT
            (SELECT COUNT(child.isys_catg_location_list__id) FROM isys_catg_location_list child WHERE child.isys_catg_location_list__parentid = locationObject.isys_obj__id) AS ChildrenCount,
            location.*,
            locationObject.*,
            locationObjectType.*
            FROM isys_catg_location_list AS location
            INNER JOIN isys_obj AS locationObject ON locationObject.isys_obj__id = location.isys_catg_location_list__isys_obj__id
            INNER JOIN isys_obj_type AS locationObjectType ON locationObjectType.isys_obj_type__id = locationObject.isys_obj__isys_obj_type__id
            LEFT JOIN isys_obj AS parentObject ON parentObject.isys_obj__id = location.isys_catg_location_list__parentid
            WHERE location.isys_catg_location_list__isys_obj__id ' . $dao->prepare_in_condition($objects);

        /**
         * @see ID-9792 Only apply the condition, if 'root' should be hidden.
         * Also change the last JOIN in the above query to 'LEFT JOIN' otherwise the root
         * location would also be skipped, since it does not have parent locations.
         */
        if ($hideRoot) {
            $rootLocationId = (int)defined_or_default('C__OBJ__ROOT_LOCATION', 1);

            $sql .= ' AND location.isys_catg_location_list__parentid >= ' . $dao->convert_sql_id($rootLocationId);
        }

        return $dao->retrieve($sql . ';');
    }
}
