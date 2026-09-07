<?php

/**
 * i-doit
 *
 * Auth: Class for CMDB module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_cmdb extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var isys_auth_cmdb
     */
    private static $m_instance;

    /**
     * This is a helper-array for the "categories in locations" path.
     *
     * @var  array
     */
    protected $m_categories_in_locations = [];

    /**
     * This is a helper-array for the "categories in objects" path.
     *
     * @var  array
     */
    protected $m_categories_in_objects = [];

    /**
     * @var array
     */
    private $m_object_cache = [];

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_cmdb
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
     * Protected method for combining "category" paths.
     *
     * @static
     *
     * @param   array $p_category_paths
     *
     * @return  array
     */
    protected static function combine_simple_values(array &$p_category_paths)
    {
        // Prepare some variables.
        $l_return = [];
        $l_keys = [];
        $l_last_rights_num = 0;

        // Sort the parameters, so that the foreach will do its job correctly.
        isys_auth::sort_paths_by_rights($p_category_paths);

        foreach ($p_category_paths as $l_key => $l_rights) {
            if ($l_key == self::WILDCHAR || $l_key == self::EMPTY_ID_PARAM) {
                $l_return[$l_key] = $l_rights;
                continue;
            }

            $l_rights_num = array_sum($l_rights);

            if ($l_last_rights_num == $l_rights_num) {
                $l_keys[] = $l_key;
            } else {
                if (count($l_keys)) {
                    $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
                }

                $l_keys = [$l_key];
            }

            $l_last_rights_num = $l_rights_num;
        }

        if (count($l_keys)) {
            $l_return[implode(',', $l_keys)] = isys_helper::split_bitwise($l_last_rights_num);
        }

        /**
         * @see ID-8237  Combine equal 'wildchar + empty-id-param' parameters.
         */
        if (isset($l_return[self::WILDCHAR], $l_return[self::EMPTY_ID_PARAM]) && implode(',', $l_return[self::WILDCHAR]) === implode(',', $l_return[self::EMPTY_ID_PARAM])) {
            $l_return[self::WILDCHAR . ',' . self::EMPTY_ID_PARAM] = $l_return[self::WILDCHAR];

            unset($l_return[self::WILDCHAR], $l_return[self::EMPTY_ID_PARAM]);
        }

        return $l_return;
    }

    /**
     * Method for checking, if the user has the right to view the CMDB-explorer.
     *
     * @return  bool
     * @throws  isys_exception_auth
     */
    public function explorer()
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('explorer', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_CMDB_EXPLORER')));
    }

    /**
     * Method for checking, if the user has the right to view/edit/delete the CMDB-explorer profiles.
     *
     * @param   int $p_right
     *
     * @return  bool
     * @throws  isys_exception_auth
     */
    public function explorer_profiles($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('explorer_profiles', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_CMDB_EXPLORER_PROFILES')), $p_right);
    }

    /**
     * Method for checking, if the user has the right to view the location view on the left menu tree.
     *
     * @param   int $p_right
     *
     * @return  bool
     * @throws  isys_exception_auth
     */
    public function location_view($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('location_view', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LOCATION_VIEW')), $p_right);
    }

    /**
     * Method for checking, if a certain OBJ-ID inherits the given rights.
     * If these could not be found, it will be checked for the OBJ-IN-TYPE.
     *
     * @param int $p_right
     * @param int $p_id
     *
     * @return bool
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function obj_id($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        if (isset($this->m_object_cache[$p_id][$p_right])) {
            return $this->m_object_cache[$p_id][$p_right];
        }

        if (isset($this->m_paths['obj_id'])) {
            // Check for wildcards.
            if (isset($this->m_paths['obj_id'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_id'][isys_auth::WILDCHAR])) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }

            // Check for actual rights on the given OBJ-ID.
            if (isset($this->m_paths['obj_id'][$p_id]) && in_array($p_right, $this->m_paths['obj_id'][$p_id])) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        }

        // Retrieve object.
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $l_obj = $dao->get_object($p_id, false, 1)
            ->get_row();

        // If we could find no rights for this object, we look if the object-type is allowed in general.
        try {
            if ($this->obj_in_type($p_right, strtolower($l_obj['isys_obj_type__const']))) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        } catch (isys_exception_auth $e) {
            ; // Do nothing here. The method will throw his own exception.
        }

        // If we could find no rights for this object, we look if the location is allowed in general.
        try {
            if ($this->location($p_right, $p_id)) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        } catch (isys_exception_auth $e) {
            ; // Do nothing here. The method will throw his own exception.
        }

        try {
            if ($this->logical_location($p_right, $p_id)) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        } catch (isys_exception_auth $e) {
            ; // Do nothing here. The method will throw his own exception.
        }

        try {
            if ($this->contact_role($p_right, $p_id)) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        } catch (isys_exception_auth $e) {
            ; // Do nothing here. The method will throw his own exception.
        }

        // @see ID-8906 Implement the 'allowed objects condition'.
        $condition = isys_auth_cmdb_objects::instance()->get_allowed_objects_condition($p_right);

        // @see ID-10341 If no rights are granted, the condition will end up empty and return false results.
        if (trim($condition) !== '') {
            $id = $dao
                ->retrieve('SELECT isys_obj__id FROM isys_obj WHERE isys_obj__id = ' . $dao->convert_sql_id($p_id) . $condition . ' LIMIT 1;')
                ->get_row_value('isys_obj__id');

            if ($id !== null) {
                $this->m_object_cache[$p_id][$p_right] = true;
                return true;
            }
        }

        $this->m_object_cache[$p_id][$p_right] = false;

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_ID', [
                $l_right_name,
                $l_obj['isys_obj__title']
            ]));
    }

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function obj_owner($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('obj_owner', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_ID')), $p_right);
    }

    /**
     * This method checks, if you are allowed to process an action for objects of a given type.
     *
     * @param   int    $p_right
     * @param   string $id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  bool
     */
    public function obj_in_type($p_right, $id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        $id = strtolower(trim($id));
        $constant = strtoupper($id);

        if (!defined($constant)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $constant));
        }

        if (isset($this->m_paths['obj_in_type'])) {
            // Check for wildchars.
            if (isset($this->m_paths['obj_in_type'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_in_type'][isys_auth::WILDCHAR])) {
                return true;
            }

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['obj_in_type'][$id]) && in_array($p_right, $this->m_paths['obj_in_type'][$id])) {
                return true;
            }
        }

        // Get some translations for the exception.
        $l_type_id = constant($constant);
        $l_right_name = isys_auth::get_right_name($p_right);
        $l_obj_type_name = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_objtype_name_by_id_as_string($l_type_id);

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_IN_TYPE', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_obj_type_name)
            ]));
    }

    /**
     * This method checks, if you are allowed to process an action for a certain OBJ-TYPE (used for object-type configuration).
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     */
    public function obj_type($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        $p_id = strtolower(trim($p_id ?? ''));
        $l_constant = strtoupper($p_id);

        if (isset($this->m_paths['obj_type'])) {
            // This checks for paths like "CMDB/OBJ_TYPE" without IDs (will be used to check, if the "new" button shall be displayed in the list-view).
            if (empty($l_constant) && isset($this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM]) &&
                in_array($p_right, $this->m_paths['obj_type'][isys_auth::EMPTY_ID_PARAM])) {
                return true;
            }

            if (!defined($l_constant)) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_constant));
            }

            // Check for wildchars.
            if (isset($this->m_paths['obj_type'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['obj_type'][isys_auth::WILDCHAR])) {
                return true;
            }

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['obj_type'][$p_id]) && in_array($p_right, $this->m_paths['obj_type'][$p_id])) {
                return true;
            }
        }

        // Get some translations for the exception.
        $l_right_name = isys_auth::get_right_name($p_right);

        if (empty($l_constant)) {
            $l_obj_type_name = isys_tenantsettings::get('gui.empty_value', '-');
        } else {
            $l_obj_type_name = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
                ->get_objtype_name_by_id_as_string(constant($l_constant));
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_TYPE', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_obj_type_name)
            ]));
    }

    /**
     * This method checks, if the user has right to see a certain category in a certain object type.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function category_in_obj_type($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        $p_id = strtolower(trim($p_id));
        [, $l_obj_type] = explode('+', $p_id);

        if (isset($this->m_paths['category_in_obj_type'])) {
            if (isset($this->m_paths['category_in_obj_type'][isys_auth::WILDCHAR . '+' . $l_obj_type]) &&
                in_array($p_right, $this->m_paths['category_in_obj_type'][isys_auth::WILDCHAR . '+' . $l_obj_type])) {
                return true;
            }

            if (isset($this->m_paths['category_in_obj_type'][$p_id]) && in_array($p_right, $this->m_paths['category_in_obj_type'][$p_id])) {
                return true;
            }
        }

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $p_id;
        $l_categories = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_cat_by_const(strstr($p_id, '+', true), ['title']);

        if ($l_categories && is_array($l_categories)) {
            [$l_category_title] = array_values($l_categories);
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_category_title)
            ]));
    }

    /**
     * This method checks, if the user has right to see a certain category in a certain object.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function category_in_object($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        [$l_category, $l_id] = explode('+', strtolower($p_id));

        if (isset($this->m_categories_in_objects[$l_category]) || isset($this->m_categories_in_objects[isys_auth::WILDCHAR])) {
            if (isset($this->m_categories_in_objects[isys_auth::WILDCHAR][$l_id]) && in_array($p_right, $this->m_categories_in_objects[isys_auth::WILDCHAR][$l_id])) {
                return true;
            }

            if (isset($this->m_categories_in_objects[$l_category][$l_id]) && in_array($p_right, $this->m_categories_in_objects[$l_category][$l_id])) {
                return true;
            }
        }

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_category;
        $l_category = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_cat_by_const($l_category, ['title']);

        if ($l_category && is_array($l_category)) {
            [$l_category_title] = array_values($l_category);
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_category_title)
            ]));
    }

    /**
     * This method checks, if the user has right to see a certain category underneath certain location.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function category_in_location($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        [$l_category, $l_object_id] = explode('+', strtolower($p_id));

        if (isset($this->m_categories_in_locations[isys_auth::WILDCHAR]) || isset($this->m_categories_in_locations[$l_category])) {
            if (isset($this->m_categories_in_locations[isys_auth::WILDCHAR][$l_object_id]) &&
                in_array($p_right, $this->m_categories_in_locations[isys_auth::WILDCHAR][$l_object_id])) {
                return true;
            }

            if (isset($this->m_categories_in_locations[$l_category][$l_object_id]) && in_array($p_right, $this->m_categories_in_locations[$l_category][$l_object_id])) {
                return true;
            }

            // The given ID could not be found directly, now we check the location path.
            $l_mptt = isys_cmdb_dao_location::instance(self::$m_dao->get_database_component())
                ->get_mptt();

            if (is_array($this->m_categories_in_locations[isys_auth::WILDCHAR])) {
                foreach ($this->m_categories_in_locations[isys_auth::WILDCHAR] as $l_location_id => $l_rights) {
                    if ($l_mptt->has_children($l_location_id, $l_object_id)) {
                        if (in_array($p_right, $l_rights)) {
                            $this->m_object_cache[$l_object_id][$p_right] = true;

                            return true;
                        }
                    }
                }
            }

            if (is_array($this->m_categories_in_locations[$l_category])) {
                foreach ($this->m_categories_in_locations[$l_category] as $l_location_id => $l_rights) {
                    if ($l_mptt->has_children($l_location_id, $l_object_id)) {
                        if (in_array($p_right, $l_rights)) {
                            return true;
                        }
                    }
                }
            }
        }

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_category;
        $l_category = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_cat_by_const($l_category, ['title']);

        if ($l_category && is_array($l_category)) {
            [$l_category_title] = array_values($l_category);
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_category_title)
            ]));
    }

    /**
     * This method checks, if the user has right to work with a certain category in "his" (or "her") objects.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function category_in_own_object($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        [$l_category, $l_id] = explode('+', strtolower($p_id));

        $l_dao = isys_cmdb_dao::instance(isys_application::instance()->database);
        $l_sql = 'SELECT isys_obj__id
                FROM isys_obj
                WHERE isys_obj__id = ' . $l_dao->convert_sql_id($l_id) . '
                AND ' . isys_auth_cmdb_objects::instance()
                ->get_owner_condition() . ';';
        $res = $l_dao->retrieve($l_sql);

        $l_is_own_object = is_countable($res) && $res->count() > 0;

        if ($l_is_own_object && isset($this->m_paths['category_in_own_object'])) {
            if (isset($this->m_paths['category_in_own_object'][self::WILDCHAR]) && in_array($p_right, $this->m_paths['category_in_own_object'][self::WILDCHAR])) {
                return true;
            }

            if (isset($this->m_paths['category_in_own_object'][$l_category]) && in_array($p_right, $this->m_paths['category_in_own_object'][$l_category])) {
                return true;
            }
        }

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_category;
        $l_category = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_cat_by_const($l_category, ['title']);

        if ($l_category && is_array($l_category)) {
            [$l_category_title] = array_values($l_category);
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_category_title)
            ]));
    }

    /**
     * This method checks wheather you are allowed to process an action for a certain location.
     *
     * @param $p_right
     * @param $p_id
     *
     * @return bool|mixed
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @deprecated Try to use other methods that are not so specific!
     * @todo       Make private ASAP
     */
    public function location($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        /**
         * Return cached result
         */
        if (isset($this->m_object_cache[$p_id][$p_right])) {
            return $this->m_object_cache[$p_id][$p_right];
        }

        if (isset($this->m_paths['location'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['location'][isys_auth::WILDCHAR])) {
            $this->m_object_cache[$p_id][$p_right] = true;
            return true;
        }

        if (isset($this->m_paths['location'][$p_id]) && in_array($p_right, $this->m_paths['location'][$p_id])) {
            $this->m_object_cache[$p_id][$p_right] = true;
            return true;
        }

        if (isset($this->m_paths['location'])) {
            // The given ID could not be found directly, now we check the location path.
            $l_mptt = isys_cmdb_dao_location::factory(isys_application::instance()->container->get('database'))->get_mptt();

            foreach ($this->m_paths['location'] as $l_location_id => $l_rights) {
                // Get child locations of the location auth-paths.
                if ($l_mptt->has_children($l_location_id, $p_id)) {
                    if (in_array($p_right, $this->m_paths['location'][$l_location_id])) {
                        $this->m_object_cache[$p_id][$p_right] = true;

                        return true;
                    }
                }
            }
        }

        $this->m_object_cache[$p_id][$p_right] = false;

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);
        $l_obj = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_object($p_id, false, 1)
            ->get_row();

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_LOCATION', [
                $l_right_name,
                $l_obj['isys_obj__title']
            ]));
    }

    /**
     * This method checks wheather you are allowed to process an action for a certain logical location.
     *
     * @param $p_right
     * @param $p_id
     *
     * @return bool|mixed
     * @throws isys_exception_auth
     * @throws isys_exception_database
     * @deprecated Try to use other methods that are not so specific!
     * @todo       Make private ASAP
     */
    public function logical_location($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        // Return cached result.
        if (isset($this->m_object_cache[$p_id][$p_right])) {
            return $this->m_object_cache[$p_id][$p_right];
        }

        if (isset($this->m_paths['logical_location'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['logical_location'][isys_auth::WILDCHAR])) {
            $this->m_object_cache[$p_id][$p_right] = true;
            return true;
        }

        if (isset($this->m_paths['logical_location'][$p_id]) && in_array($p_right, $this->m_paths['logical_location'][$p_id])) {
            $this->m_object_cache[$p_id][$p_right] = true;
            return true;
        }

        if (isset($this->m_paths['logical_location'])) {
            // The given ID could not be found directly, now we check the logical location path.
            $l_logigal_location_dao = isys_cmdb_dao_category_g_assigned_logical_unit::instance(isys_application::instance()->database);

            foreach ($this->m_paths['logical_location'] as $l_location_id => $l_rights) {
                // Get child locations of the location auth-paths.
                if ($l_logigal_location_dao->has_child($l_location_id, $p_id)) {
                    if (in_array($p_right, $this->m_paths['logical_location'][$l_location_id])) {
                        $this->m_object_cache[$p_id][$p_right] = true;
                        return true;
                    }
                }
            }
        }

        $this->m_object_cache[$p_id][$p_right] = false;

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);
        $l_obj = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_object($p_id, false, 1)
            ->get_row();

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_LOCATION', [$l_right_name, $l_obj['isys_obj__title']]));
    }

    /**
     * @param int $right
     * @param int $objectId
     *
     * @return bool
     * @throws isys_exception_auth
     * @throws isys_exception_database
     */
    private function contact_role(int $right, int $objectId): bool
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        // Return cached result.
        if (isset($this->m_object_cache[$objectId][$right])) {
            return $this->m_object_cache[$objectId][$right];
        }

        if (isset($this->m_paths['contact_role'])) {
            $dao = isys_application::instance()->container->get('cmdb_dao');

            if (!isset($this->m_paths['contact_role'][isys_auth::WILDCHAR]) || !in_array($right, $this->m_paths['contact_role'][isys_auth::WILDCHAR])) {
                $roles = [];

                foreach ($this->m_paths['contact_role'] as $roleId => $rights) {
                    if (in_array($right, $rights)) {
                        $roles[] = $roleId;
                    }
                }

                $roleCondition = 'AND isys_catg_contact_list__isys_contact_tag__id ' . $dao->prepare_in_condition($roles);
            }

            $userObjectId = $dao->convert_sql_id(isys_application::instance()->container->get('session')->get_user_id());

            $sql = "SELECT isys_obj__id AS id, isys_obj__isys_obj_type__id AS typeId
                FROM isys_catg_contact_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_contact_list__isys_obj__id
                INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
                WHERE isys_connection__isys_obj__id = {$userObjectId}
                AND isys_obj__id = {$objectId}
                {$roleCondition};";

            return $this->m_object_cache[$objectId][$right] = (bool)$dao->retrieve($sql)
                ->get_row('id');
        }

        // Get some data for the upcoming checks and exceptions.
        $rightName = isys_auth::get_right_name($right);
        $objectData = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_object($objectId, false, 1)
            ->get_row();

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_LOCATION', [$rightName, $objectData['isys_obj__title']]));
    }

    /**
     * This method checks, if you are allowed to process an action for a category.
     *
     * @param   integer $p_right
     * @param   string  $p_id
     *
     * @throws  isys_exception_general
     * @throws  isys_exception_auth
     * @return  boolean
     */
    public function category($p_right, $p_id)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_constant = strtoupper(trim($p_id));

        if (!defined($l_constant)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__EXCEPTION__CONSTANT_COULD_NOT_BE_FOUND', $l_constant));
        }

        if (isset($this->m_paths['category'])) {
            // Check for wildchars.
            if (isset($this->m_paths['category'][isys_auth::WILDCHAR]) && in_array($p_right, $this->m_paths['category'][isys_auth::WILDCHAR])) {
                return true;
            }

            // Check for actual rights on the given OBJ-TYPE.
            if (isset($this->m_paths['category'][$p_id]) && in_array($p_right, $this->m_paths['category'][$p_id])) {
                return true;
            }
        }

        // Get some data for the upcoming checks and exceptions.
        $l_right_name = isys_auth::get_right_name($p_right);

        // We don't know, whether the category is global|specific|custom.
        $l_category_title = $l_constant;
        $l_category = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_cat_by_const($l_constant, ['title']);

        if ($l_category && is_array($l_category)) {
            [$l_category_title] = array_values($l_category);
        }

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                $l_right_name,
                isys_application::instance()->container->get('language')
                    ->get($l_category_title)
            ]));
    }

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function overwrite_user_list_config($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('overwrite_user_list_config', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    }

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function list_config($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('list_config', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    }

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function define_standard_list_config($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('define_standard_list_config', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_LIST_CONFIG')), $p_right);
    }

    /**
     * @param   integer $p_right
     *
     * @return  boolean
     * @throws  isys_exception_auth
     */
    public function multilist_config($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        return $this->generic_boolean('multilist_config', new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_MULTILIST_CONFIG')), $p_right);
    }

    /**
     * This method checks the rights for categories and checks the rights from the object.
     *
     * @param   integer $p_right
     * @param   integer $p_object_id
     * @param   string  $p_category_const
     *
     * @throws  isys_exception_auth
     * @return  boolean
     */
    public function check_rights_obj_and_category($p_right, $p_object_id, $p_category_const)  // If errors occur, change back to "$p_category_const = null".
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        if ($p_category_const !== null && isset($this->m_paths['category'])) {
            if ($this->is_allowed_to($p_right, 'CATEGORY/' . strtoupper($p_category_const))) {
                return true;
            }
        }

        if ($p_category_const !== null) {
            if ($this->is_allowed_to($p_right, 'OBJ_ID/' . $p_object_id) && (strtoupper($p_category_const) == 'C__CATG__OVERVIEW')) {
                return true;
            }

            if ($this->m_paths['category_in_own_object']) {
                try {
                    return $this->category_in_own_object($p_right, $p_category_const . '+' . $p_object_id);
                } catch (isys_exception_auth $e) {
                    // @see ID-11576 Do not write the log here, this method gets called to 'check' if buttons should be shown.
                    // We don't want to throw an error here.
                }
            }

            // We still got a few last chances to permit the user...
            if ($this->m_paths['category_in_obj_type']) {
                $l_obj_type = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
                    ->get_type_by_object_id($p_object_id)
                    ->get_row();

                try {
                    return $this->category_in_obj_type($p_right, $p_category_const . '+' . $l_obj_type['isys_obj_type__const']);
                } catch (isys_exception_auth $e) {
                    // @see ID-11576 Do not write the log here, this method gets called to 'check' if buttons should be shown.
                    // We don't want to throw an error here.
                }
            }

            if (count($this->m_categories_in_objects) > 0) {
                try {
                    return $this->category_in_object($p_right, $p_category_const . '+' . $p_object_id);
                } catch (isys_exception_auth $e) {
                    // @see ID-11576 Do not write the log here, this method gets called to 'check' if buttons should be shown.
                    // We don't want to throw an error here.
                }
            }

            if (count($this->m_categories_in_locations) > 0) {
                // No need to catch the exception, because this is the last check (for now).
                return $this->category_in_location($p_right, $p_category_const . '+' . $p_object_id);
            }

            $l_category_title = $p_category_const;
            $l_category = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
                ->get_cat_by_const($p_category_const, ['title']);

            if ($l_category && is_array($l_category)) {
                [$l_category_title] = array_values($l_category);
            }

            throw new isys_exception_auth(isys_application::instance()->container->get('language')
                ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_CATEGORY', [
                    $this->get_right_name($p_right),
                    isys_application::instance()->container->get('language')
                        ->get($l_category_title)
                ]));
        } else {
            // check rights in object
            if ($this->is_allowed_to($p_right, 'OBJ_ID/' . $p_object_id)) {
                return true;
            }
        }

        $l_obj_title = isys_cmdb_dao::factory(self::$m_dao->get_database_component())
            ->get_obj_name_by_id_as_string($p_object_id);

        throw new isys_exception_auth(isys_application::instance()->container->get('language')
            ->get('LC__AUTH__CMDB_EXCEPTION__MISSING_RIGHT_FOR_OBJ_ID', [
                $this->get_right_name($p_right),
                $l_obj_title
            ]));
    }

    /**
     * This method checks the rights for categories and checks the rights from the object. Without exception.
     *
     * @param   integer $p_right
     * @param   integer $p_object_id
     * @param   string  $p_category_const
     *
     * @return  bool
     */
    public function has_rights_in_obj_and_category($p_right, $p_object_id, $p_category_const) // If errors occur, change back to "$p_category_const = null".
    {
        try {
            return $this->check_rights_obj_and_category($p_right, $p_object_id, $p_category_const);
        } catch (isys_exception_auth $e) {
            return false;
        }
    }

    /**
     * Optional method for combining auth paths.
     *
     * @param   array &$p_paths
     *
     * @return  isys_auth_cmdb
     */
    public function combine_paths(array &$p_paths)
    {
        foreach ($p_paths as $l_method => $l_params) {
            switch ($l_method) {
                case 'obj_in_type':
                case 'obj_type':
                    $p_paths[$l_method] = isys_auth_cmdb_object_types::combine_object_types($l_params);
                    break;

                case 'category_in_location':
                case 'category_in_object':
                case 'category_in_obj_type':
                case 'category_in_contact_role':
                    $p_paths[$l_method] = isys_auth_cmdb_categories::combine_category_with_parameter($l_params);
                    break;
                    // @See ID-8686 we won´t combine these rights as the location browser only can handle one object
                case 'location':
                case 'location_excl':
                    break;
                default:
                    /**
                     * @see ID-8237  Try to combine all unspecific paths.
                     */
                    $p_paths[$l_method] = self::combine_simple_values($l_params);
                    break;
            }
        }

        return $this;
    }

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     */
    public function get_auth_methods()
    {
        return [
            'obj_id'                           => [
                'title'  => 'LC__AUTH_GUI__OBJ_ID_CONDITION',
                'type'   => 'object',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::ARCHIVE,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'obj_in_type'                      => [
                'title'  => 'LC__AUTH_GUI__OBJ_IN_TYPE_CONDITION',
                'type'   => 'object_type',
                'rights' => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::ARCHIVE,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'obj_type'                         => [
                'title'  => 'LC__AUTH_GUI__OBJ_TYPE_CONDITION',
                'type'   => 'object_type',
                'rights' => [
                    // isys_auth::CREATE, // Doesn't work yet.
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'obj_owner' => [
                'title'  => 'LC__AUTH_GUI__OBJ_OWNER_CONDITION',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::ARCHIVE,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'location'                         => [
                'title'  => 'LC__AUTH_GUI__LOCATION_CONDITION',
                'type'   => 'location',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    // @see ID-4272
                    // isys_auth::ARCHIVE,
                    // isys_auth::DELETE,
                    // isys_auth::SUPERVISOR
                ]
            ],
            'location_excl'                    => [
                'title'  => 'LC__AUTH_GUI__LOCATION_CONDITION_EXCLUSIVE',
                'type'   => 'location',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ],
            'logical_location'                 => [
                'title'  => 'LC__AUTH_GUI__LOGICAL_LOCATION_CONDITION',
                'type'   => 'object',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ],
            'logical_location_excl'            => [
                'title'  => 'LC__AUTH_GUI__LOGICAL_LOCATION_CONDITION_EXCLUSIVE',
                'type'   => 'object',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT
                ]
            ],
            'category'                         => [
                'title'   => 'LC__AUTH_GUI__CATEGORY_CONDITION',
                'type'    => 'category',
                'rights'  => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'category_in_obj_type'             => [
                'title'   => 'LC__AUTH_GUI__CATEGORY_IN_OBJ_TYPE_CONDITION',
                'type'    => 'category_in_obj_type',
                'rights'  => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'category_in_object'               => [
                'title'   => 'LC__AUTH_GUI__CATEGORY_IN_OBJECT_CONDITION',
                'type'    => 'category_in_object',
                'rights'  => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'category_in_location'             => [
                'title'   => 'LC__AUTH_GUI__CATEGORY_IN_LOCATION_CONDITION',
                'type'    => 'category_in_location',
                'rights'  => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'category_in_own_object'           => [
                'title'   => 'LC__AUTH_GUI__CATEGORY_IN_OWN_OBECTS',
                'type'    => 'category',
                'rights'  => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'list_config'                      => [
                'title'  => 'LC__AUTH_GUI__LIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::EXECUTE
                ]
            ],
            'overwrite_user_list_config'       => [
                'title'  => 'LC__AUTH_GUI__OVERWRITE_USER_LIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'define_standard_list_config'      => [
                'title'  => 'LC__AUTH_GUI__DEFINE_STANDARD_LIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'multilist_config'                 => [
                'title'  => 'LC__AUTH_GUI__MULTILIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::EXECUTE
                ]
            ],
            'overwrite_user_multilist_config'  => [
                'title'  => 'LC__AUTH_GUI__OVERWRITE_USER_MULTILIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'define_standard_multilist_config' => [
                'title'  => 'LC__AUTH_GUI__DEFINE_STANDARD_MULTILIST_CONFIG',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EXECUTE
                ]
            ],
            'explorer'                         => [
                'title'  => 'LC__AUTH_GUI__EXPLORER_CONDITION',
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ],
            'explorer_profiles'                => [
                'title'   => 'LC__AUTH_GUI__EXPLORER_PROFILE_CONDITION',
                'type'    => 'boolean',
                'rights'  => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ],
                'default' => [
                    isys_auth::VIEW
                ]
            ],
            'location_view'                    => [
                'title'  => 'LC__CMDB__MENU_TREE_VIEW',
                'type'   => 'boolean',
                'rights' => [isys_auth::VIEW]
            ],
            'object_browser_configuration'     => [
                'title'  => 'LC__CMDB__TREE__SYSTEM__CMDB_EXPLORER__CUSTOMIZATION',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE
                ]
            ],
            'contact_role' => [
                'title'  => 'LC__AUTH_GUI__BY_CONTACT_ROLE',
                'type'   => 'contact_role',
                'rights' => [
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::ARCHIVE,
                    isys_auth::DELETE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'category_in_contact_role' => [
                'title'  => 'LC__AUTH_GUI__CATEGORY_BY_CONTACT_ROLE',
                'type'   => 'category_in_contact_role',
                'rights' => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                    isys_auth::ARCHIVE,
                    isys_auth::EXECUTE,
                    isys_auth::SUPERVISOR
                ]
            ],
            'attribute_visibility'     => [
                'title'  => 'LC__CMDB__TREE__SYSTEM__ATTRIBUTE_SETTINGS',
                'type'   => 'boolean',
                'rights' => [
                    isys_auth::CREATE,
                    isys_auth::VIEW,
                    isys_auth::EDIT,
                    isys_auth::DELETE,
                ]
            ]
        ];
    }

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__CMDB');
    }

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return 'CMDB';
    }

    /**
     * @throws Exception
     * @throws \idoit\Exception\JsonException
     */
    protected function __construct()
    {
        parent::__construct();

        // After the constructor is called, we can prepare some of the loaded paths.
        if (isset($this->m_paths['category_in_object'])) {
            foreach ($this->m_paths['category_in_object'] as $l_param => $l_rights) {
                [$l_category, $l_ids] = explode('+', strtolower($l_param));

                if (is_numeric($l_ids)) {
                    $l_ids = [$l_ids];
                } elseif (isys_format_json::is_json_array($l_ids)) {
                    $l_ids = isys_format_json::decode($l_ids);
                } else {
                    // How about nope?
                    continue;
                }

                if (!isset($this->m_categories_in_objects[$l_category])) {
                    $this->m_categories_in_objects[$l_category] = [];
                }

                if (is_countable($l_ids) && count($l_ids) > 0) {
                    foreach ($l_ids as $l_id) {
                        $this->m_categories_in_objects[$l_category][$l_id] = array_merge((array)$this->m_categories_in_objects[$l_category][$l_id], $l_rights);
                    }
                }
            }
        }

        if (isset($this->m_paths['category_in_location'])) {
            foreach ($this->m_paths['category_in_location'] as $l_param => $l_rights) {
                [$l_category, $l_ids] = explode('+', strtolower($l_param));

                if (is_numeric($l_ids)) {
                    $l_ids = [$l_ids];
                } elseif (isys_format_json::is_json_array($l_ids)) {
                    $l_ids = isys_format_json::decode($l_ids);
                } else {
                    // How about nope?
                    continue;
                }

                if (!isset($this->m_categories_in_locations[$l_category])) {
                    $this->m_categories_in_locations[$l_category] = [];
                }

                if (is_countable($l_ids) && count($l_ids) > 0) {
                    foreach ($l_ids as $l_id) {
                        $this->m_categories_in_locations[$l_category][$l_id] = array_merge((array)$this->m_categories_in_locations[$l_category][$l_id], $l_rights);
                    }
                }
            }
        }

        if (isset($this->m_paths['category_in_contact_role'])) {
            $database = isys_application::instance()->container->get('database');
            $dao = isys_cmdb_dao_category_s_person_assigned_groups::instance($database);
            $userObjectId = (int)isys_application::instance()->container->get('session')->get_user_id();
            $roleToObjectCache = [];

            $objects = [$userObjectId];

            // @see ID-8906 Consider groups which the current user is a member of.
            $groupResult = $dao->get_data(null, $userObjectId);

            while ($row = $groupResult->get_row()) {
                $objects[] = (int)$row['isys_person_2_group__isys_obj__id__group'];
            }

            foreach ($this->m_paths['category_in_contact_role'] as $parameter => $rights) {
                [$categoryConstant, $roleId] = explode('+', strtolower($parameter));

                // We only work with singular roles here.
                if (is_numeric($roleId) && $roleId > 0) {
                    $roleId = (int)$roleId;
                } elseif ($roleId === isys_auth::WILDCHAR) {
                    // @see ID-8906 Consider the wildcard!
                    $roleId = -1;
                } else {
                    continue;
                }

                if (!isset($roleToObjectCache[$roleId])) {
                    $roleCondition = '';

                    if ($roleId > 0) {
                        $roleCondition = "AND isys_catg_contact_list__isys_contact_tag__id = {$roleId}";
                    }

                    $sql = "SELECT isys_catg_contact_list__isys_obj__id AS id
                        FROM isys_catg_contact_list
                        INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
                        WHERE isys_connection__isys_obj__id " . $dao->prepare_in_condition($objects) . "
                        {$roleCondition};";

                    $roleToObjectCache[$roleId] = [];
                    $result = $dao->retrieve($sql);

                    while ($row = $result->get_row()) {
                        $roleToObjectCache[$roleId][] = $row['id'];
                    }
                }

                foreach ($roleToObjectCache[$roleId] as $objectId) {
                    $this->m_categories_in_objects[$categoryConstant][$objectId] = array_merge(
                        (array)$this->m_categories_in_objects[$categoryConstant][$objectId],
                        $rights
                    );
                }
            }
        }
    }
}
