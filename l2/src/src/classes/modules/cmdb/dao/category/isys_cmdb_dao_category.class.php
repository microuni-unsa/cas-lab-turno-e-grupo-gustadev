<?php

use idoit\Component\Logger;
use idoit\Component\PlaceholderReplacer\Config as ReplacerConfig;
use idoit\Component\Property\Property;
use idoit\Context\Context;
use idoit\Module\Cmdb\Component\CategoryChanges\Changes;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SinglePropertyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Component\SyncMerger\Merger;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\Entry;
use idoit\Module\Cmdb\Model\Entry\EntryCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;
use idoit\Module\Pro\Model\AttributeSettings;

/**
 * i-doit
 *
 * DAO: CMDB Category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_dao_category extends isys_cmdb_dao
{
    const C__LOAD = 1;
    const C__SAVE = 2;

    /**
     * Will be used to limit the display of how many other objects contain the same data (in context of validation).
     */
    const UNIQUE_VALIDATION_OBJECT_COUNT = 10;

    /**
     * Category type for view only categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_VIEW = 1;

    /**
     * Category type for regular view and edit categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_EDIT = 2;

    /**
     * Category type for rear categories; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_REAR = 3;

    /**
     * Category type for assignment categories (Object browser on "New"); used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_ASSIGN = 4;

    /**
     * Category type for folders; used in isysgui_catx__type
     *
     * @var integer
     */
    const TYPE_FOLDER = 10;

    /**
     * @var array
     */
    protected array $m_additional_tom_rules = [];

    /**
     * @var array
     */
    protected $m_arrLogbookEntries = [];

    /**
     * @deprecated Will be removed in i-doit 40!
     */
    protected $m_bCasesensitiv;

    /**
     * @deprecated Will be removed in i-doit 40!
     */
    protected $m_bWordsonly;

    /**
     * Cached properties
     *
     * @var array
     */
    protected $m_cached_properties = [];

    /**
     * Category type.
     *
     * @var  integer
     */
    protected $m_cat_type = null;

    /**
     * Category identifier
     *
     * @var string
     */
    protected $m_category = null;

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = null;

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const;

    /**
     * Category's identifier.
     *
     * @var  integer
     */
    protected $m_category_id;

    /**
     * Category type's abbrevation.
     *
     * @var  string
     */
    protected $m_category_type_abbr = '';

    /**
     * Category type's constant.
     *
     * @var  string
     */
    protected $m_category_type_const = '';

    /**
     * Field which holds the connected object id field if defined
     *
     * @var string
     */
    protected $m_connected_object_id_field = null;

    /**
     * DAO result with category data.
     *
     * @var  isys_component_dao_result
     */
    protected $m_daores;

    /**
     * Category's data - this NEEDS to be "unset" by default, because there are some checks later on...
     *
     * @var  array
     */
    protected $m_data;

    /**
     * Name of property which should be used as identifier
     *
     * @var string
     */
    protected $m_entry_identifier = 'title';

    /**
     * Should we generically handle a relation creation via property C__PROPERTY__DATA__RELATION_TYPE.
     *
     * @var  boolean
     */
    protected $m_has_relation = false;

    /**
     * Field for singlevalue categories which determines if the entry is purgable or not
     *
     * @var  boolean
     */
    protected $m_is_purgable = false;

    /**
     * Category's list DAO.
     *
     * @var  string
     */
    protected $m_list;

    /**
     * @var integer
     */
    protected $m_list_id;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_multivalued = false;

    /**
     * Defines if the category only consists of an object browser
     *
     * @var  boolean
     */
    protected $m_object_browser_category = false;

    /**
     * Property of the object browser
     *
     * @var string
     */
    protected $m_object_browser_property = '';

    /**
     * @var integer
     */
    protected $m_object_id;

    /**
     * Field for the object id. This variable is needed for multiedit (for example global category guest systems or it service).
     *
     * @var  string
     */
    protected $m_object_id_field = 'isys_obj__id';

    /**
     * @var integer
     */
    protected $m_object_type_id;

    /**
     * @deprecated Will be removed in i-doit 40!
     */
    protected $m_prepared_properties;

    /**
     * @var boolean
     */
    protected $m_process_validated;

    /**
     * Information about manipulating, filtering, importing, exporting, and transforming data.
     *
     * @var  array
     */
    protected $m_properties = [];

    /**
     * New variable to determine if the current category is a reverse category of another one.
     *
     * @var  string
     */
    protected $m_reverse_category_of = null;

    /**
     * @var string
     */
    protected $m_source_table;

    /**
     * @deprecated Will be removed in i-doit 40!
     */
    protected $m_strLogbookDesc;

    /**
     * @var string
     */
    protected $m_strLogbookSQL;

    /**
     * @var array
     */
    protected $m_sync_catg_data;

    /**
     * Main table where properties are stored persistently.
     *
     * @var  string
     */
    protected $m_table;

    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl;

    /**
     * Category's user interface.
     *
     * @var  string
     */
    protected $m_ui;

    /**
     * Creates the distribution connector entry and returns its id.
     * If obj_id is null, the method takes it from $_GET parameter.
     *
     * @param string $p_table
     * @param int|null $p_obj_id
     * @return int|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        if ($p_obj_id === null) {
            $p_obj_id = $_GET[C__CMDB__GET__OBJECT];
        }

        if (!$this->is_multivalued()) {
            $l_sql = 'SELECT ' . $p_table . '__id FROM ' . $p_table . ' WHERE ' . $p_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';
            if ($l_id = $this->retrieve($l_sql)
                ->get_row_value($p_table . '__id')) {
                return $l_id;
            }
        }

        $l_sql = 'INSERT IGNORE INTO ' . $p_table . ' SET ' . $p_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return null;
    }

    /**
     * Method for returning an object as "Objectype > Object" with quicklink.
     *
     * @static
     *
     * @param   integer $l_obj_id
     *
     * @return  string
     */
    public static function dynamic_property_callback_object($l_obj_id)
    {
        if ($l_obj_id > 0) {
            global $g_comp_database;

            $l_quick_info = new isys_ajax_handler_quick_info();

            $l_row = isys_cmdb_dao::instance($g_comp_database)
                ->get_object_by_id($l_obj_id)
                ->get_row();

            return $l_quick_info->get_quick_info($l_row['isys_obj__id'], isys_application::instance()->container->get('language')
                    ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__OBJECT);
        }

        return '';
    }

    /**
     * Build a category based on the specified parameters.
     *
     * @param   isys_cmdb_dao $p_dao_cmdb
     * @param   integer       $p_obj_id
     * @param   integer       $p_cat_type
     * @param   integer       $p_cat_const
     * @param   array         $p_isysgui
     * @param   string        $p_str_cat_type
     * @param   integer       $p_cat_list_id
     *
     * @throws  isys_exception_cmdb
     * @return  isys_cmdb_dao_category&
     */
    public static function &manufacture(isys_cmdb_dao $p_dao_cmdb, $p_obj_id, $p_cat_type, $p_cat_const, $p_isysgui, $p_str_cat_type, $p_cat_list_id = null)
    {
        if ($p_dao_cmdb->obj_exists($p_obj_id)) {
            $l_cat_srctable = $p_isysgui["isysgui_cat{$p_str_cat_type}__source_table"];
            $l_cat_class = $p_isysgui["isysgui_cat{$p_str_cat_type}__class_name"];

            $l_q = "";

            if (class_exists($l_cat_class)) {
                $l_cat_db = $p_dao_cmdb->get_database_component();
                /**
                 * @var $l_cat_obj isys_cmdb_dao_category
                 */
                $l_cat_obj = new $l_cat_class($l_cat_db);

                // Set some parameters.
                $l_cat_obj->set_object_id($p_obj_id);
                $l_cat_obj->set_list_id($p_cat_list_id);
                $l_cat_obj->set_source_table($l_cat_srctable);
                $l_cat_obj->set_category_type($p_cat_type);

                if (is_numeric($p_cat_const)) {
                    $l_cat_obj->set_category_id($p_cat_const);
                }
            } else {
                return null;
            }

            /*
             * Wenn $p_cat_list_id NULL ist wird bei Neuanlage von Kategorieeintrügen immer der erste Datensatz aus get_data angezeigt, anstatt eine leere Kategorie.
             * Daher diese Kondition:
             */
            if (is_null($p_cat_list_id) && $l_cat_obj->is_multivalued()) {
                $p_cat_list_id = 'FALSE';
            }

            // Get category data.
            $l_dao_res = $l_cat_obj->get_data($p_cat_list_id, $p_obj_id);

            if ($l_dao_res == null) {
                throw new isys_exception_cmdb("Could not retrieve full distributor object record ($l_q)", C__CMDB__ERROR__DISTRIBUTOR);
            }

            if ($l_cat_obj != null) {
                if ($l_cat_obj->init($l_dao_res) == true) {
                    return $l_cat_obj;
                } else {
                    throw new isys_exception_cmdb("Could not initialize: '" . get_class($l_cat_obj) . "'", C__CMDB__ERROR__CATEGORY_BUILDER);
                }
            } else {
                throw new isys_exception_cmdb("Cannot instantiate category-dao: '{$l_cat_class}'.", C__CMDB__ERROR__CATEGORY_BUILDER);
            }
        }
    }

    /**
     * Sets the object browser property
     *
     * @param $p_value
     *
     */
    public function set_object_browser_property($p_value)
    {
        $this->m_object_browser_property = $p_value;
    }

    /**
     * Gets the object browser property
     *
     * @return string
     */
    public function get_object_browser_property()
    {
        return $this->m_object_browser_property;
    }

    /**
     * Get entry identifier
     *
     * @param   array $p_entry_data
     *
     * @return  string
     */
    public function get_entry_identifier($p_entry_data)
    {
        try {
            return $this->get_gui_value_for_property($this->m_entry_identifier, is_array($p_entry_data) ? $p_entry_data : []);
        } catch (isys_exception_cmdb $e) {
            return '';
        }
    }

    /**
     * Returns a GUI representation for the current property. No matter if this is a reference, dialog, chosen, or whatever.
     *
     * @param string $p_property
     * @param array  $p_row_data
     *
     * @return string
     */
    public function get_gui_value_for_property($p_property, array $p_row_data)
    {
        $l_return = '';
        $l_db_field = null;

        $l_property = $this->property($p_property);

        if (is_countable($l_property) && count($l_property) === 0) {
            $l_property = $this->dynamic_property($p_property);
        }

        if (is_countable($l_property) && count($l_property) > 0) {
            if (!isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) && !isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                return '';
            } else {
                if (!isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK])) {
                    // No helper class.
                    if ($this->get_category_type() != C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                            $l_db_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                        } else {
                            if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                                $l_db_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                            }
                        }
                    } else {
                        if ($p_property === 'description') {
                            $l_db_field = 'commentary_' . $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                        } else {
                            $l_db_field = $p_property;
                        }
                    }

                    if ($l_db_field && isset($p_row_data[$l_db_field])) {
                        // set return value
                        $l_return = $p_row_data[$l_db_field];
                    }
                } else {
                    if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK]) && isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                        // Check if helper class exists.
                        if (class_exists($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                            // Create new instance of the helper class:
                            $l_helper = new $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                $p_row_data,
                                $this->m_db,
                                $l_property[C__PROPERTY__DATA],
                                $l_property[C__PROPERTY__FORMAT],
                                $l_property[C__PROPERTY__UI]
                            );

                            if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]) && !empty($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                                $l_unit_properties = $this->property($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]);

                                if (method_exists($l_helper, 'set_unit_const')) {
                                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                                        $l_const = $p_row_data[$l_unit_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                                    } else {
                                        $l_const = $p_row_data[$l_unit_properties[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__const'];
                                    }

                                    $l_helper->set_unit_const($l_const);
                                }
                            }

                            // Call the helper's method:
                            if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                if (method_exists($l_helper, $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]) &&
                                        array_key_exists($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS], $p_row_data)) {
                                        $l_data = $p_row_data[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]];
                                    } else {
                                        $l_data = $p_row_data[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                                    }

                                    $l_return = '';
                                    if ($l_data) {
                                        $l_return = call_user_func([
                                            $l_helper,
                                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                        ], $l_data);
                                        // Check result before using it as array
                                        if ($l_return instanceof isys_export_data) {
                                            $l_return = $l_return->get_data();
                                        }
                                        if (!is_scalar($l_return)) {
                                            if (isset($l_return['title'])) {
                                                $l_return = $l_return['title'];
                                            } elseif (isset($l_return['title_lang'])) {
                                                $l_return = $l_return['title_lang'];
                                            } elseif (isset($l_return['value'])) {
                                                $l_return = $l_return['value'];
                                            } else {
                                                $l_return = '';
                                            }
                                        }
                                    }
                                } else {
                                    throw new isys_exception_cmdb(sprintf(
                                        'Method %s in helper class %s does not exist.',
                                        $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1],
                                        $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]
                                    ));
                                }
                            }

                            unset($l_helper);
                        }
                    }
                }
            }
        }

        return $l_return;
    }

    /**
     * Sets flag $m_object_browser_category.
     *
     * @param  $p_value
     * @deprecated Will be removed in i-doit 40!
     */
    public function set_object_browser_category($p_value)
    {
        $this->m_object_browser_category = $p_value;
    }

    /**
     * Gets member variable $m_object_browser_category.
     *
     * @return  boolean
     */
    public function get_object_browser_category()
    {
        return $this->m_object_browser_category;
    }

    /**
     * Is it possible to purge the entry for the current single value category?
     *
     * @return  boolean
     */
    public function category_entries_purgable()
    {
        return (bool)$this->m_is_purgable;
    }

    /**
     * Set the possibilty to purge the entry
     *
     * @param $p_purgable
     *
     * @return $this
     */
    public function set_category_entries_purgable($p_purgable)
    {
        $this->m_is_purgable = (bool)$p_purgable;

        return $this;
    }

    /**
     * Sets conditionless query string into cache for generic function get_data.
     *
     * @param   $p_query
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_conditionless_query($p_query)
    {
        isys_caching::factory('getdataconditionless')
            ->set($this->get_category_const(), addslashes($p_query));

        return $this;
    }

    /**
     * Gets conditionless query string from cache.
     *
     * @return  string
     */
    public function get_conditionless_query()
    {
        return stripslashes(isys_caching::factory('getdataconditionless')
            ->get($this->get_category_const()));
    }

    /**
     * Retrieves a single property by it's UI ID.
     *
     * @param   string  $p_const
     * @param   integer $p_get_with
     *
     * @return  mixed
     */
    public function get_property_by_ui_id($p_const, $p_get_with = null)
    {
        foreach ($this->get_properties($p_get_with) as $l_key => $l_property) {
            if ($p_const == $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID]) {
                return [$l_key => $l_property];
            }
        }

        return false;
    }

    /**
     * Gets name of category's list DAO.
     *
     * @return  string
     */
    public function get_category_list()
    {
        return $this->m_list;
    }

    /**
     * Gets user interface.
     *
     * @return  isys_cmdb_ui_category
     * @throws  isys_exception_ui
     */
    public function &get_ui()
    {
        global $index_includes;

        if (class_exists($this->m_ui)) {
            /**
             * @var isys_cmdb_ui_category
             */
            $l_ui = new $this->m_ui(isys_application::instance()->template);

            unset($index_includes['contentbottomcontentaddition']);
            unset($index_includes['contentbottomcontentadditionbefore']);

            return $l_ui->set_template($this->m_tpl);
        } else {
            throw new isys_exception_ui('UI for ' . get_class($this) . ' does not exist.');
        }
    }

    /**
     * Is category multi-valued?
     *
     * @return  boolean
     */
    public function is_multivalued()
    {
        return (bool)$this->m_multivalued;
    }

    /**
     * Returns the object id field.
     *
     * @return  string
     */
    public function get_object_id_field()
    {
        return $this->m_object_id_field;
    }

    /**
     * Returns the object id field.
     *
     * @return  string
     */
    public function get_connected_object_id_field()
    {
        return $this->m_connected_object_id_field;
    }

    /**
     * Gets potential filter rows.
     *
     * @return array
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_filter()
    {
        $l_info = $this->get_properties();

        if (!is_countable($l_info) || count($l_info) == 0) {
            return [];
        }

        $l_data = [];

        // Iterate through properties:
        foreach ($l_info as $l_key => $l_property) {
            // Skip properties that shouldn't be included:
            if (isset($l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH]) && $l_property[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === false) {
                continue;
            }

            $l_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            // Field alias are not used for custom categories
            $l_field_alias = ($this->m_cat_type === C__CMDB__CATEGORY__TYPE_CUSTOM) ? null : $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
            $l_references = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES];

            if (!empty($l_field_alias)) {
                $l_ref = $l_field_alias;
            } else {
                if (!empty($l_references) && is_array($l_references) && (!is_int(strpos($l_references[0], '_2_')) && $l_references[0] != 'isys_connection')) {
                    $l_ref = $l_references[0] . '__title';
                } else {
                    $l_ref = $l_field;
                }
            }

            $l_data[$l_ref . '::' . $l_key] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
        }

        return $l_data;
    }

    /**
     * Method for setting the source table.
     *
     * @param   string $p_source_table
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_source_table($p_source_table)
    {
        $this->m_source_table = $p_source_table;

        return $this;
    }

    /**
     * Set current list id (category entry id).
     *
     * @param   integer $p_list_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_list_id($p_list_id)
    {
        $this->m_list_id = (int)$p_list_id;

        return $this;
    }

    /**
     * @param $p_list
     *
     * @return $this
     */
    public function set_list($p_list)
    {
        $this->m_list = $p_list;

        return $this;
    }

    /**
     * Returns the current category entry id.
     *
     * @return  integer
     */
    public function get_list_id()
    {
        return (int)$this->m_list_id;
    }

    /**
     * Set current object id.
     *
     * @param   integer $p_object_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_object_id($p_object_id)
    {
        $this->m_object_id = (int)$p_object_id;

        return $this;
    }

    /**
     * Set the current object type ID.
     *
     * @param   integer $p_object_type_id
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_object_type_id($p_object_type_id)
    {
        $this->m_object_type_id = (int)$p_object_type_id;

        return $this;
    }

    /**
     * Set the category type.
     *
     * @param   integer $p_setValue
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_category_type($p_setValue)
    {
        $this->m_cat_type = (int)$p_setValue;

        return $this;
    }

    /**
     * Gets category's type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return (int)$this->m_cat_type;
    }

    /**
     * Gets category's name.
     *
     * @return  string
     */
    public function get_category()
    {
        return $this->m_category;
    }

    /**
     * Gets category's constant as string.
     *
     * @return  string
     */
    public function get_category_const()
    {
        return $this->m_category_const;
    }

    /**
     * Setter method for m_category_const
     *
     * @param $categoryConst
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_catgory_const($categoryConst)
    {
        $this->m_category_const = $categoryConst;
    }

    /**
     * Gets category's type as constant.
     *
     * @return  string
     */
    public function get_category_type_const()
    {
        return $this->m_category_type_const;
    }

    /**
     * Gets abbreviated category's type.
     *
     * @return  string
     */
    public function get_category_type_abbr()
    {
        return $this->m_category_type_abbr;
    }

    /**
     * Base initialization of category-DAO
     *
     * @param   isys_component_dao_result $p_daores
     *
     * @return  boolean
     * @throws  isys_exception_dao_cmdb
     */
    public function init(isys_component_dao_result &$p_daores)
    {
        if (is_object($p_daores)) {
            $this->m_daores = $p_daores;

            return true;
        }

        throw new isys_exception_dao_cmdb("Initialization of category failed, expecting object of type isys_component_dao_result.\n", self::class, 0);
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed    Returns category data identifier (int) on success, true (bool) if nothing has to be done, otherwise false.
     * @throws  isys_exception_validation
     */
    public function sync($p_category_data, $p_object_id, $p_status)
    {
        // There is nothing to import
        if (!is_countable($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]) || count($p_category_data[isys_import_handler_cmdb::C__PROPERTIES]) == 0) {
            return true;
        }

        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        // Assign object identifier and default record status:
        $l_data = [
            'isys_obj__id' => $p_object_id,
            'status'       => C__RECORD_STATUS__NORMAL
        ];

        // @see ID-10373 replace all placeholders before validating the data
        $objectData = $this->get_object($p_object_id, true, 1)->get_row();
        $config = ReplacerConfig::factory($p_object_id, $objectData['isys_obj_type__id'], $objectData['isys_obj__title'], $objectData['isys_obj__sysid'], $this->get_source_table() ?? $this->get_table() ?? '');
        // Build data array which can be handled by save() and create():
        foreach ($p_category_data['properties'] as $l_key => $l_value) {
            if (!is_string($l_value[C__DATA__VALUE])) {
                $l_data[$l_key] = $l_value[C__DATA__VALUE];
                continue;
            }
            $l_data[$l_key] = $value = isys_application::instance()->container->get('idoit.component.placeholder-replacer')
                ->replacePlaceholder($l_value[C__DATA__VALUE], $config);
        }

        $l_validation = $this->validate($l_data);

        if (is_array($l_validation) &&
            Context::instance()->getContextTechnical() === Context::CONTEXT_IMPORT_XML &&
            isys_tenantsettings::get('import.validation.empty-attribute-on-error', 1)
        ) {
            foreach ($l_validation as $propertyKey => $unused) {
                $l_data[$propertyKey] = null;
            }
            $l_validation = true;
        }

        if ($l_validation !== true) {
            throw new isys_exception_validation(isys_application::instance()->container->get('language')
                ->get('LC__VALIDATION_ERROR'), $l_validation, $p_category_data['data_id']);
        }

        $l_multivalued = $this->is_multivalued();

        if (!$l_multivalued) {
            if ($this->get_data_by_object($p_object_id)
                    ->count() === 0) {
                $p_status = isys_import_handler_cmdb::C__CREATE;
            }
        }

        if ($p_status == isys_import_handler_cmdb::C__CREATE) {
            return $this->create_data($l_data);
        }

        if ($p_status == isys_import_handler_cmdb::C__UPDATE && $p_category_data['data_id'] > 0) {
            if ($l_multivalued) {
                if ($this->save_data($p_category_data['data_id'], $l_data)) {
                    return $p_category_data['data_id'];
                }

                return false;
            }

            if ($this->save_single_value($l_data['isys_obj__id'], $l_data, true)) {
                return $p_category_data['data_id'];
            }

            return false;
        }

        return true;
    }

    /**
     * Sets or RE-sets the internal dao-result.
     * Only use this if you're really know what you're doing!
     *
     * @param   isys_component_dao_result $p_daores
     *
     * @return  isys_cmdb_dao_category
     * @deprecated Will be removed in i-doit 40!
     */
    public function set_dao_result(isys_component_dao_result &$p_daores)
    {
        $this->m_daores = $p_daores;

        return $this;
    }

    /**
     * Prepare sort statement
     *
     * @param string $p_sort_by
     * @param string $p_direction
     *
     * @return string
     */
    public function sort($p_sort_by, $p_direction)
    {
        $l_sql = '';

        if (is_string($p_sort_by) && !empty($p_sort_by)) {
            $l_sql .= ' ORDER BY ' . $p_sort_by;

            switch ($p_direction) {
                case 'DESC':
                    $l_sql .= ' DESC';
                    break;
                default:
                case 'ASC':
                    $l_sql .= ' ASC';
                    break;
            }
        }

        return $l_sql;
    }

    /**
     * Returns the object id by its corresponding category id (catlevel).
     *    Attention: If $p_source_table is null, $this->m_source_table is used. This does only work if this category dao was instantiated by isys_cmdb_dao_distributor!!
     *
     * @author Dennis Stücken 10-2010
     *
     * @param int $p_id
     * @param int $p_source_table
     *
     * @uses   $this->m_source_table
     * @return int
     */
    public function get_object_id_by_category_id($p_id, $p_source_table = null)
    {
        if (is_null($p_source_table)) {
            $p_source_table = $this->m_table;
        }

        return $this->retrieve("SELECT {$p_source_table}__isys_obj__id as id FROM {$p_source_table} WHERE {$p_source_table}__id = " . $this->convert_sql_id($p_id) . ';')
            ->get_row_value('id');
    }

    /**
     * Retrieves general data. Query runs every time you run this method. And it will always return the first row from the result set. Returns null on error.
     *
     * @throws  Exception
     * @return  array
     */
    public function get_general_data()
    {
        if (is_object($this->m_daores)) {
            $l_daores = $this->retrieve($this->m_daores->get_query());

            if (is_object($l_daores)) {
                $l_daodata = $l_daores->get_row();

                if (is_array($l_daodata)) {
                    $this->m_daores = $l_daores;
                    $this->m_daores->reset_pointer();

                    return $l_daodata;
                }
            }

            return null;
        } else {
            throw new isys_exception_cmdb(get_class($this) . " :: get_general_data failed. DAO-Result empty. (In " . __FILE__ . ":" . __LINE__ . ")");
        }
    }

    /**
     * Returns the associated DAO result to this category.
     *
     * @return  isys_component_dao_result
     */
    public function get_result()
    {
        return $this->m_daores;
    }

    /**
     * Return translated category name by constant string.
     *
     * @param string $p_catconst
     * @return string
     * @throws Exception
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_category_by_const_as_string($p_catconst)
    {
        // @see ID-2736
        if (empty($p_catconst)) {
            return false;
        }

        if (str_starts_with($p_catconst, 'C__CATG')) {
            $title = $this->get_catg_by_const($p_catconst)->get_row_value('isysgui_catg__title');

            return isys_application::instance()->container->get('language')->get($title);
        }

        if (str_starts_with($p_catconst, 'C__CATS')) {
            $title = $this->get_cats_by_const($p_catconst)->get_row_value('isysgui_cats__title');

            return isys_application::instance()->container->get('language')->get($title);
        }

        return false;
    }

    /**
     * Gets the categories source-table name.
     *
     * @return  string
     */
    public function get_source_table()
    {
        return $this->m_source_table;
    }

    /**
     * Gets the categories table name.
     *
     * @return  string
     */
    public function get_table()
    {
        return $this->m_table;
    }

    /**
     * Returns NULL and store the _status for a records from a global category to the second parameter by reference OR return the (integer) ErrorCode.
     *
     * @param int $p_cat_level
     * @param int &$p_intRecStatus
     * @return null
     * @throws Exception
     * @todo AW: Still necessary? Only in for compatibility to old CMDB-module
     */
    public function get_rec_status($p_cat_level, &$p_intRecStatus)
    {
        $l_catdata = $this->get_general_data();
        if (($l_table_name = $this->get_source_table())) {
            if ($p_cat_level == 0) {
                if ($this->get_category_type() == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
                    $p_intRecStatus = $l_catdata[$l_table_name . '__status'];
                } else {
                    $p_intRecStatus = $l_catdata[$l_table_name . '_list__status'];
                }
            }
        }

        return null;
    }

    /**
     * Set the validation.
     *
     * @param bool
     * @return isys_cmdb_dao_category
     */
    public function set_validation($p_bStatus)
    {
        $this->m_process_validated = $p_bStatus;

        return $this;
    }

    /**
     * Find out if the validation has been set.
     *
     * @return bool
     */
    public function get_validation()
    {
        return $this->m_process_validated;
    }

    /**
     * Setter for additional rules for TOM. Used in methode validate_post_data to add field related information or error text.
     *
     * @param array $additionalTomRules
     * @return isys_cmdb_dao_category
     */
    public function set_additional_rules(array $additionalTomRules)
    {
        $this->m_additional_tom_rules = $additionalTomRules;

        return $this;
    }

    /**
     * Return the additional TOM rules.
     *
     * @return  array
     */
    public function get_additional_rules(): array
    {
        return $this->m_additional_tom_rules;
    }

    /**
     * Validates property data.
     *
     * @param   array $p_data                Associative array of property tags as keys and their values as values.
     * @param   mixed $p_prepend_table_field This can be used to prepend a table field alias (use boolean "true" for the default category table).
     *
     * @return  mixed  Returns true on a successful validation, otherwise an associative array with property tags as keys and error messages as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $tablePrefix = '';
        $l_result = [];
        $l_properties = $this->get_properties(C__PROPERTY__WITH__VALIDATION);

        $language = isys_application::instance()->container->get('language');

        if ($p_prepend_table_field !== false) {
            if ($p_prepend_table_field === true) {
                $tablePrefix = $this->m_table . '.';
            } else {
                $tablePrefix = $p_prepend_table_field . '.';
            }
        }

        if (is_array($p_data)) {
            foreach ($p_data as $l_key => $l_value) {
                // If the property could not be found or Checks are not set, we don't want to waste time.
                if (!isset($l_properties[$l_key]) && !isset($l_properties[$l_key][C__PROPERTY__CHECK])) {
                    continue;
                }
                // don't validate virtual properties
                if (isset($l_properties[$l_key][C__PROPERTY__PROVIDES], $l_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL]) &&
                    $l_properties[$l_key][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VIRTUAL]) {
                    continue;
                }

                if (is_array($l_value) && array_key_exists(C__DATA__VALUE, $l_value)) {
                    $l_value = $l_value[C__DATA__VALUE];
                }

                // Mandatory field is empty.
                if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) {
                    // Check, if we got an empty string.
                    if (trim($l_value . '') === '') {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }

                    // Now to check for Dialog fields.
                    if ($l_value == -1 && $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG) {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }

                    // Now to check for Dialog+ and Object-Browser fields.
                    if (($l_value == -1 || $l_value == 'NULL' || $l_value == '0' || (is_array($l_value) && count($l_value) === 0)) &&
                        $l_properties[$l_key][C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP) {
                        $l_result[$l_key] = $language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                        continue;
                    }
                }

                // Value is empty, but it's not a mandatory field.
                if (trim($l_value . '') === '') {
                    continue;
                } else {
                    $l_res = false;
                    $l_id = null;
                    $field = $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] ?: $l_properties[$l_key][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                    $isCustomCategory = $this instanceof isys_cmdb_dao_category_g_custom_fields;
                    $sqlObjectType = $this->convert_sql_id($this->m_object_type_id);
                    $customFieldIdentifier = '';

                    // @see ID-1871 Special treatment for custom categories.
                    if ($isCustomCategory) {
                        $field = 'isys_catg_custom_fields_list__field_content';
                        [, $key] = isys_cmdb_dao_category_g_custom_fields::getTypeAndKey($l_key) ?? [null, null];

                        // @see ID-11499 Validate the exact field.
                        if ($key !== null) {
                            $customFieldIdentifier = 'AND isys_catg_custom_fields_list__field_key = ' . $this->convert_sql_text($key);
                        }

                        $l_id = 0;
                    }

                    try {
                        $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_GLOBAL';

                        if (!isset($l_properties[$l_key][C__PROPERTY__CHECK])) {
                            continue;
                        }

                        // @see ID-11499 Custom fields might provide array / array-like data,
                        if ($isCustomCategory && (is_array($l_value) || isys_format_json::is_json_array($l_value))) {
                            $formattedValues = array_map(
                                fn ($value) => is_numeric($value) ? $this->convert_sql_id($value) : $this->convert_sql_text($value),
                                is_array($l_value) ? $l_value : isys_format_json::decode($l_value)
                            );

                            $conditions = implode(
                                ' OR ',
                                array_map(
                                    fn ($sqlValue) => "BINARY {$tablePrefix}{$field} = {$sqlValue}",
                                    $formattedValues
                                )
                            );

                            $valueCondition = " {$customFieldIdentifier} AND ({$conditions})";
                        } else {
                            $sqlValue = $this->convert_sql_text($l_value);
                            $valueCondition = " {$customFieldIdentifier} AND BINARY {$tablePrefix}{$field} = {$sqlValue}";
                        }

                        if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL]) {
                            // Check Unique in Global context
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_GLOBAL';

                            $l_res = $this->get_data(
                                $l_id,
                                null,
                                $valueCondition,
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        } elseif (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE]) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] && $this->m_object_type_id > 0) {
                            // Check Unique in Object type context
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJTYPE';
                            $l_res = $this->get_data(
                                $l_id,
                                null,
                                "AND isys_obj__isys_obj_type__id = {$sqlObjectType} {$valueCondition}",
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        } elseif (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] && $this->m_object_id > 0) {
                            // Check for unique field in Object context.
                            $l_message = 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_OBJ';
                            $l_res = $this->get_data(
                                $l_id,
                                $this->m_object_id,
                                $valueCondition,
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        }

                        if ($l_res !== false && is_countable($l_res) && count($l_res) > 0) {
                            $l_objects = [];

                            while ($l_row = $l_res->get_row()) {
                                if (isset($l_row['isys_obj__status']) && $l_row['isys_obj__status'] != C__RECORD_STATUS__NORMAL) {
                                    continue;
                                }

                                if ($l_row['isys_obj__id'] != $this->m_object_id || $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) {
                                    $l_objects[] = '<span>' . $language->get($l_row['isys_obj_type__title']) . ' » ' . $l_row['isys_obj__title'] . '</span>';
                                }

                                // This is necessary to not count the current table entry.
                                if (isset($l_row[$this->get_table() . '__id']) && $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ]) {
                                    // @see  ID-8333  Custom categories use their own 'data id' instead of the table entry ID.
                                    $targetField = $isCustomCategory ? 'isys_catg_custom_fields_list__data__id' : $this->get_table() . '__id';

                                    // We simply remove the last inserted item.
                                    if ($this->get_list_id() > 0 && $l_row[$targetField] == $this->get_list_id()) {
                                        array_pop($l_objects);
                                    }
                                }
                            }

                            // Remove duplicates
                            $l_objects = array_unique($l_objects);

                            if ($l_object_count = count($l_objects)) {
                                if ($l_object_count > self::UNIQUE_VALIDATION_OBJECT_COUNT) {
                                    $l_objects = array_slice($l_objects, 0, self::UNIQUE_VALIDATION_OBJECT_COUNT);
                                    $l_objects[] = $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__UNIQUE_AND_MORE', ($l_object_count - self::UNIQUE_VALIDATION_OBJECT_COUNT));
                                }

                                $l_result[$l_key] = $language->get($l_message) . '<ul class="m0 mt10 list-style-none"><li>' . implode('</li><li>', $l_objects) . '</li></ul>';

                                continue;
                            }
                        }
                    } catch (isys_exception_database $e) {
                        $e->write_log();

                        isys_notify::warning($language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__FIELD_NOT_FOUND_IN_TABLE', [
                                    $this->m_table,
                                    $l_field
                                ]), ['sticky' => true]);
                    }
                }

                // Validate.
                if (is_array($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION])) {
                    if (isset($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0])) {
                        if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] > 0) {
                            $l_filter = $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0];

                            if (is_string($l_filter) && defined($l_filter)) {
                                $l_filter = constant($l_filter);
                            }
                        } else {
                            if (defined($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0])) {
                                $l_filter = constant($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0]);
                            } else {
                                if ($l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0] == 'VALIDATE_BY_TEXTFIELD') {
                                    // This case requires special treatment, because "filter_var" can not handle it!
                                    $l_strings = explode("\n", $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]['value']);

                                    if (!in_array($l_value, $l_strings)) {
                                        $l_result[$l_key] = $language->get('LC__SETTINGS__CMDB__VALIDATION__BY_TEXTFIELD_ERROR');
                                    }

                                    continue;
                                }
                            }
                        }

                        $l_options = $l_properties[$l_key][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1] ?? 0;

                        // Check, if the regular expression has delimiter.
                        if (isset($l_options['options']['regexp']) && substr($l_options['options']['regexp'], 0, 1) != substr($l_options['options']['regexp'], -1, 1)) {
                            $l_options['options']['regexp'] = '~' . $l_options['options']['regexp'] . '~';
                        }

                        if (isset($l_filter)) {
                            if ($l_filter == FILTER_VALIDATE_FLOAT) {
                                // ID-2717 If we want to validate floats, always replace the comma with a dot. This will also happen before saving.
                                $l_value = str_replace(',', '.', $l_value);
                            }

                            // @see ID-9195 ID-9203 Fix PHP 8 error when $l_filter contains a string ("VALIDATE_BY_TEXTFIELD").
                            if ($l_filter === 'VALIDATE_BY_TEXTFIELD') {
                                if (!in_array($l_value, explode("\n", $l_options['value']))) {
                                    $l_result[$l_key] = $language->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
                                }

                                continue;
                            }

                            // @see ID-12291 Verify that we pass integer values (no values like '2.0').
                            if ($l_filter == FILTER_VALIDATE_INT) {
                                $intCastedValue = (string)(int)$l_value;
                                $floatCastedValue = (string)$l_value;

                                if (!is_numeric($l_value) || $intCastedValue !== $floatCastedValue || strlen($intCastedValue) !== strlen($floatCastedValue)) {
                                    $l_result[$l_key] = $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_INTEGER');
                                }

                                continue;
                            }

                            if (filter_var($l_value, (int)$l_filter, $l_options) === false) {
                                $l_message = match ($l_filter) {
                                    FILTER_VALIDATE_FLOAT => 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_FLOAT',
                                    FILTER_VALIDATE_REGEXP => $language->get('LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_REGEX', $l_options['options']['regexp']),
                                    FILTER_VALIDATE_EMAIL => 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_EMAIL',
                                    FILTER_VALIDATE_URL => 'LC__SETTINGS__CMDB__VALIDATION_MESSAGE__NEEDS_TO_BE_URL',
                                    default => 'LC__UNIVERSAL__FIELD_VALUE_IS_INVALID',
                                };

                                $l_result[$l_key] = $language->get($l_message);
                            }
                        }
                    }
                }
            }
        }

        if (is_countable($l_result) && count($l_result) === 0) {
            $l_result = true;
        }

        return $l_result;
    }

    /**
     * Validates user data and calls the template system on error.
     *
     * @return  boolean  Result of validation
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function validate_user_data()
    {
        $l_result = true;

        // Get property information.
        $l_properties = $this->get_properties();

        // Get user data.
        $l_data = $this->parse_user_data();

        // Validate properties.
        $l_validation = $this->validate($l_data);

        if ($l_validation !== true) {
            $l_result = false;

            foreach ($l_validation as $l_property => $l_error) {
                // This may be necessary for custom categories.
                if (is_array($l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID]) || $this instanceof isys_cmdb_dao_category_g_custom_fields) {
                    $l_formtag = 'C__CATG__CUSTOM__' . $l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID];
                } else {
                    $l_formtag = $l_properties[$l_property][C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }

                $l_rules[$l_formtag] = [
                    'message'  => $l_error,
                    'property' => $l_property,
                    'title'    => isys_application::instance()->container->get('language')
                        ->get($l_properties[$l_property][C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]),
                ];
            }

            $this->set_additional_rules($l_rules);
        }

        $this->set_validation($l_result);

        return $l_result;
    }

    /**
     * Builds a generic query from array by using:
     *     array key    => as the database table name
     *     array value  => as its new value
     *       id = NULL on C__DB_GENERAL__INSERT
     *
     * Array example:
     *     array(
     *         "title" => $_POST["DATA_TITLE"],
     *         "description" => $_POST["DATA_DESCRIPTION"]
     *     );
     *
     * @param   string  $p_category
     * @param   array   $p_data
     * @param   integer $p_id
     * @param   integer $p_method
     *
     * @return  string
     */
    public function build_query($p_category, $p_data, $p_id, $p_method = C__DB_GENERAL__UPDATE)
    {
        switch ($p_method) {
            default:
            case C__DB_GENERAL__UPDATE:
                $l_sql = "UPDATE " . $p_category . " SET ";
                break;
            case C__DB_GENERAL__INSERT:
                $l_sql = "INSERT INTO " . $p_category . " SET ";
                break;
            case C__DB_GENERAL__REPLACE:
                $l_sql = "REPLACE INTO " . $p_category . " SET ";
                break;
        }

        // Determine the maximum key of p_data.
        $l_assignment = [];

        // Irerate through array and start building the sql.
        foreach ($p_data as $l_key => $l_value) {
            // @see ID-11830 Simplify the value transformation.
            if (is_null($l_value)) {
                $l_value = "NULL";
            } elseif (is_string($l_value) && strtolower($l_value) === "now()") {
                $l_value = "NOW()";
            } elseif (is_float($l_value)) {
                $l_value = "'" . $l_value . "'";
            } elseif (is_int($l_value)) {
                $l_value = $this->convert_sql_id($l_value);
            } else {
                $l_value = $this->convert_sql_text($l_value);
            }

            // If $l_value is -1, we need to convert this to NULL.
            if ($l_value == -1 || $l_value == "'-1'") {
                $l_value = "NULL";
            }

            $l_assignment[] = $p_category . "__" . $l_key . " = " . $l_value;
        }

        $l_sql .= implode(', ', $l_assignment);

        if (!is_null($p_id)) {
            $l_sql .= " WHERE " . $p_category . "__id = " . $this->convert_sql_id($p_id);
        }

        $l_sql .= ";";

        $this->m_strLogbookSQL = $l_sql;

        return $l_sql;
    }

    /**
     * Creates new entity.
     *
     * @param array $data
     * @return bool|int Returns created entity's identifier (int) or false (bool).
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function create_data($data)
    {
        $properties = $this->get_properties();

        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        $preparedData = $this->prepare_data($data);

        if ($preparedData === false) {
            return false;
        }

        $preparedQuery = $this->prepare_query($preparedData);

        if (empty($preparedQuery)) {
            return false;
        }

        $query = "INSERT INTO {$this->m_table} SET {$preparedQuery};";

        if ($this->update($query) && $this->apply_update()) {
            $entryId = $this->get_last_insert_id();

            if ($this->m_has_relation) {
                $this->handle_relation_generic($entryId, $preparedData);
            }

            return $entryId;
        }

        return false;
    }

    /**
     * Updates existing entity.
     *
     * @param int   $entryId
     * @param array $data
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($entryId, $data)
    {
        $properties = $this->get_properties();

        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        $preparedData = $this->prepare_data($data);

        if ($preparedData === false) {
            return false;
        }

        $preparedQuery = $this->prepare_query($preparedData);

        if (empty($preparedQuery)) {
            return false;
        }

        $entryId = (int)$entryId;
        $query = "UPDATE {$this->m_table}
            SET {$preparedQuery}
            WHERE {$this->m_table}__id = {$entryId};";

        if ($this->update($query) && $this->apply_update()) {
            if ($this->m_has_relation) {
                $this->handle_relation_generic($entryId, $preparedData);
            }

            return true;
        }

        return false;
    }

    /**
     * Updates existing single value category by object id instead of category id or creates new single value entry if no one exists.
     *
     * @param int   $objectId
     * @param array $data
     * @param bool  $autocreate Specifies if a new entry should be created when category in $objectId is empty
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function save_single_value($objectId, $data, $autocreate = true)
    {
        $objectId = (int)$objectId;
        $query = "SELECT {$this->m_table}__id AS id
            FROM {$this->m_table}
            WHERE {$this->m_table}__isys_obj__id = {$objectId}
            LIMIT 1;";

        $id = $this->retrieve($query)->get_row_value('id');

        if ($id > 0) {
            return $this->save_data($id, $data);
        } else {
            if ($autocreate) {
                // Extend data with object id.
                $data['isys_obj__id'] = $objectId;

                return $this->create_data($data);
            }
        }

        return false;
    }

    /**
     * Updates existing entity given by user via HTTP GET and POST.
     *
     * @param   bool $p_create Create data (or update it)?
     *
     * @return  mixed Category data's identifier (int) or false (bool), otherwise null if nothing is created/saved
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function save_user_data($p_create)
    {
        $l_object_id = intval($_GET[C__CMDB__GET__OBJECT]);

        $properties = $this->get_properties();
        // There is nothing to to:
        if (!is_countable($properties) || count($properties) === 0) {
            return true;
        }

        // Parse user's category data:
        $this->set_object_id($l_object_id);
        $l_data = $this->parse_user_data();

        // Continue if one or more properties are given:
        if (!is_countable($l_data) || count($l_data) === 0) {
            // Nothing to do...
            return null;
        }

        // Mandatory fields (may be overwritten):
        $l_data['isys_obj__id'] = $l_object_id;
        $l_data['status'] = C__RECORD_STATUS__NORMAL;

        $l_category_data_id = null;

        $l_create = false;

        if ($this->m_multivalued === true) {
            // In overview's category a new entity will be always created:
            if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW')) {
                $p_create = true;
            }

            if ($p_create === true) {
                $l_create = true;
            } else {
                if (isset($_GET[C__CMDB__GET__CATLEVEL]) && $_GET[C__CMDB__GET__CATLEVEL] > 0) {
                    $l_category_data_id = intval($_GET[C__CMDB__GET__CATLEVEL]);
                } else {
                    $l_category_data_id = intval($_POST[$this->m_category_const]);
                }
            }
        } else {
            $l_category_data_id = intval($_POST[$this->m_category_const]);

            // Get existing category data:
            if (!isset($this->m_data)) {
                $this->m_data = $this->get_data_by_object($l_object_id)
                    ->__to_array();
            }

            if (is_countable($this->m_data) && count($this->m_data) > 0) {
                $l_category_data_id = $this->m_data[$this->m_table . '__id'];
            } else {
                $l_create = true;
            }
        } // if multi-valued

        // Create or update category data?
        if ($l_create) {
            // Create new entity:
            $l_category_data_id = $this->create_data($l_data);
        } else {
            // Update existing entity:
            if ($this->save_data($l_category_data_id, $l_data) === false) {
                return false;
            }
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_category_data_id;
    }

    /**
     * Parses user data.
     *
     * @return  array  Associative array of property tags as keys and their values as values.
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    public function parse_user_data()
    {
        if (!isset($_POST) || !count($_POST)) {
            return [];
        }

        // Get category's properties:
        $l_properties = $this->get_properties();

        // @see ID-9246 Check if we are in 'frontend' context.
        $context = Context::instance();
        $isGuiContext = $context->getOrigin() === Context::ORIGIN_GUI && $context->getGroup() === Context::CONTEXT_GROUP_DAO;

        $l_data = [];
        $objectBrowserProperty = null;
        $popupReceiver = null;

        if ($this instanceof ObjectBrowserAssignedEntries) {
            $objectBrowserProperty = $this->get_object_browser_property();
        }

        if (!empty($_POST[C__POST__POPUP_RECEIVER])) {
            $popupReceiver = $_POST[C__POST__POPUP_RECEIVER];
        }

        // @see ID-10373 replace all placeholders before validating the data
        $objectData = $this->get_object($this->get_object_id() ?? null, true, 1)->get_row();

        // Iterate through properties:
        if (is_array($l_properties)) {
            foreach ($l_properties as $l_key => $l_value) {
                // @see ID-2736
                if (empty($l_key) || empty($l_value) || !isset($l_value[C__PROPERTY__UI])) {
                    continue;
                }

                if ($objectBrowserProperty === $l_key) {
                    $l_data[$l_key] = $popupReceiver ?? $_POST[$l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN'] ?? $_POST[$l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID]];
                    continue;
                }

                // @see ID-9246 This request comes from the frontend, so we should be able to assume that every key should exist.
                if ($isGuiContext && $this->initPropertyWithEmptyValue($l_value)) {
                    $l_data[$l_key] = '';
                }

                if ($this->get_category_type() == C__CMDB__CATEGORY__TYPE_CUSTOM &&
                    $l_value[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] != C__PROPERTY__INFO__TYPE__COMMENTARY) {
                    $l_post_key = 'C__CATG__CUSTOM__' . $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                } else {
                    $l_post_key = $l_value[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                }

                // Try to fetch the hidden fields if possible:
                switch ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) {
                    case C__PROPERTY__UI__TYPE__DIALOG:
                        $l_data[$l_key] = ($_POST[$l_post_key] === '-1') ? null : $_POST[$l_post_key];
                        break;

                    case C__PROPERTY__UI__TYPE__DATE:
                        $l_date = null;
                        if (isset($_POST[$l_post_key . '__HIDDEN']) && $_POST[$l_post_key . '__HIDDEN'] !== '-') {
                            $l_date .= $_POST[$l_post_key . '__HIDDEN'];
                        }

                        if (isset($l_date)) {
                            $l_data[$l_key] = $l_date;
                        }
                        break;

                    case C__PROPERTY__UI__TYPE__DATETIME:
                        $l_date = null;

                        if (isset($_POST[$l_post_key . '__VIEW']) && $_POST[$l_post_key . '__VIEW'] !== '-') {
                            $l_date = $_POST[$l_post_key . '__VIEW'];
                        } elseif (isset($_POST[$l_post_key]) && isset($_POST[$l_post_key . '__TIME'])) {
                            $l_date = $_POST[$l_post_key];
                        }

                        if ($l_date !== null && isset($_POST[$l_post_key . '__TIME']) && $_POST[$l_post_key . '__TIME'] !== '-') {
                            $l_date .= ' ' . $_POST[$l_post_key . '__TIME'];
                        }

                        if ($l_date === null && isset($_POST[$l_post_key])) {
                            $l_date = $_POST[$l_post_key];
                        }

                        // ID-3062 Bugfix
                        if ($l_date === null && isset($_POST['C__CATG__CUSTOM__' . $l_post_key . '__HIDDEN'])) {
                            $l_date = $_POST['C__CATG__CUSTOM__' . $l_post_key . '__HIDDEN'];
                        }

                        $l_data[$l_key] = $l_date;
                        break;

                    case C__PROPERTY__UI__TYPE__DIALOG_LIST:
                        // We should save data, even if its empty. This is important to detect cleared data.
                        $l_data[$l_key] = $_POST[$l_post_key . '__selected_values'];

                        // This is new, since we use "chosen" JS script as dialog_list.
                        if (empty($l_data[$l_key]) && is_array($_POST[$l_post_key . '__selected_box'])) {
                            $l_data[$l_key] = implode(',', $_POST[$l_post_key . '__selected_box']);
                        }
                        break;

                    default:
                        if (!empty($_POST[$l_post_key . '__HIDDEN']) || (isset($_POST[$l_post_key . '__HIDDEN']) && isset($_POST[$l_post_key]))) {
                            $l_data[$l_key] = $_POST[$l_post_key . '__HIDDEN'];
                        } else {
                            if (!empty($_POST[$l_post_key . '__selected_values'])) {
                                $l_data[$l_key] = $_POST[$l_post_key . '__selected_values'];
                            } else {
                                if (isset($l_post_key)) {
                                    $l_custom_key = 'C__CATG__CUSTOM__' . $l_post_key;

                                    // Custom field
                                    $l_post_key_hidden = $l_post_key . '__HIDDEN';
                                    if (isset($_POST[$l_custom_key])) {
                                        // standard values in custom categories
                                        $l_data[$l_key] = $_POST[$l_custom_key];
                                    } elseif (isset($_POST[$l_post_key_hidden])) {
                                        // hidden values in custom categories
                                        $l_data[$l_key] = $_POST[$l_post_key_hidden];
                                    } else {
                                        // default for standard categories
                                        if (isset($_POST[$l_post_key])) {
                                            $l_data[$l_key] = $_POST[$l_post_key];
                                        }

                                        if ($l_value[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__TIMEPERIOD) {
                                            $toKey = substr($l_post_key, 0, -4) . 'TO';

                                            if (isset($_POST[$toKey])) {
                                                $l_data[$l_key] = isys_format_json::encode([
                                                    'from' => $_POST[$l_post_key],
                                                    'to' => $_POST[$toKey]
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        break;
                }
            }

            if ($objectData['isys_obj__status'] === C__RECORD_STATUS__NORMAL) {
                $config = ReplacerConfig::factory(
                    $objectData['isys_obj__id'],
                    $objectData['isys_obj_type__id'],
                    $objectData['isys_obj__title'],
                    $objectData['isys_obj__sysid'],
                    $this->get_source_table() ?? $this->get_table() ?? ''
                );
                // Build data array which can be handled by save() and create():
                foreach ($l_data as $l_key => $l_value) {
                    if (!is_string($l_value)) {
                        continue;
                    }

                    $l_data[$l_key] = $value = isys_application::instance()->container->get('idoit.component.placeholder-replacer')
                        ->replacePlaceholder($l_value, $config);
                }
            }
        }

        return $l_data;
    }

    /**
     * We only want to init "chosen" properties with empty values.
     *
     * @param array|Property $property
     *
     * @return bool
     */
    private function initPropertyWithEmptyValue($property): bool
    {
        $infoType = $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE];
        $applicableInfoTypes = [C__PROPERTY__INFO__TYPE__DIALOG_LIST, C__PROPERTY__INFO__TYPE__MULTISELECT];

        if (in_array($infoType, $applicableInfoTypes, true)) {
            return true;
        }

        $dialogInfoTypes = [C__PROPERTY__INFO__TYPE__DIALOG, C__PROPERTY__INFO__TYPE__DIALOG_PLUS];
        $isChosen = $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_multiple'] || $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['chosen'];

        return (in_array($infoType, $dialogInfoTypes) && $isChosen);
    }

    /**
     * Return Category Data.
     *
     * @param int|null       $entryId
     * @param int|array|null $objectId
     * @param string         $condition
     * @param string|null    $filter
     * @param int|null       $status
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($entryId = null, $objectId = null, $condition = '', $filter = null, $status = null)
    {
        $l_properties = $this->get_properties();
        $selection = [
            $this->m_table . '.*',
            'mainObject.*',
            'isys_obj_type.*'
        ];
        $joins = [
            'INNER JOIN ' . $this->m_table . ' ON ' . $this->m_table . '__isys_obj__id = mainObject.isys_obj__id',
            'INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id'
        ];
        $l_already_joined = [];

        $query = $this->get_conditionless_query();

        // Always fetch additional data for 'connection', dialog (plus)' and 'autotext' fields:
        if (empty($query)) {
            foreach ($l_properties as $l_property) {
                // @todo check if specific modules are installed  (example: category application nagios module)
                if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES]) && is_array($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                    if (strpos($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] ?? '', '_2_')) {
                        continue;
                    }

                    $l_join_it = false;
                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) {
                        if (!isset($l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]])) {
                            if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_obj') {
                                $selection[] = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.isys_obj__title as ' .
                                    $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '_title';
                            } else {
                                if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === $this->m_table) {
                                    continue;
                                }

                                $selection[] = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.*';
                            }
                            $l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]] = true;
                            $l_join_it = true;
                        }
                    } else {
                        if (!isset($l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]])) {
                            if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === $this->m_table) {
                                continue;
                            }

                            $selection[] = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '.*';
                            $l_already_joined[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]] = true;
                            $l_join_it = true;
                        }
                    }

                    if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                        $selection[] = ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                '.' : '')) . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1] . ' AS ' .
                            $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];

                        if ($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] === 'isys_connection' ||
                            $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'connection'
                        ) {
                            $selection[] = ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                    '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                    '.' : '')) . 'isys_connection__isys_obj__id AS ' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '__object';
                        }
                    }

                    if ($l_join_it && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) {
                        $joins[] = 'LEFT OUTER JOIN ' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                            ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? ' AS ' .
                                $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] : '') . ' ON ' . $this->m_table . '.' .
                            $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' .
                            ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] .
                                '.' : ((isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0])) ? $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                '.' : '')) . '' . $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][1];
                    }
                }
            }

            $selection = array_filter($selection, fn ($item) => $item !== '.*');

            // Conditionless query
            $query = sprintf('SELECT %s FROM isys_obj as mainObject %s WHERE TRUE', implode(',', $selection), implode(' ', $joins));

            $this->set_conditionless_query($query);
        }

        // Filter data:
        if (isset($filter)) {
            $query .= $this->prepare_filter($filter);
        }

        // Reduce data by object identifier:
        if (!empty($objectId)) {
            $query .= $this->get_object_condition($objectId, 'mainObject');
        }

        // Reduce data by category data identifier:
        // @fixme Misbehavior detected by some code (could be 'FALSE' or a negative integer)
        $l_go_on = true;
        if ($this->is_multivalued() === false && $entryId == 'FALSE') {
            $l_go_on = false;
        }

        if (isset($entryId) && $l_go_on) {
            $query .= ' AND ' . $this->m_table . '.' . $this->m_table . '__id = ' . $this->convert_sql_id($entryId);
        }

        // Reduce data by record status:
        if (isset($status)) {
            $query .= ' AND ' . $this->m_table . '.' . $this->m_table . '__status = ' . $this->convert_sql_id($status);
        }

        // Condition:
        if (isset($condition)) {
            // LF: Do NOT remove the whitespaces!
            $query .= ' ' . $condition . ' ';
        }

        unset($l_properties, $selection, $joins, $l_already_joined);

        // Return result set:
        return $this->retrieve($query);
    }

    /**
     * Simple wrapper of get_data()
     *
     * @param null   $p_category_data_id
     * @param null   $p_obj_id
     * @param string $p_condition
     * @param null   $p_filter
     * @param null   $p_status
     *
     * @return array Category result as array
     */
    public function get_data_as_array($p_category_data_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        return $this->get_data($p_category_data_id, $p_obj_id, $p_condition, $p_filter, $p_status)
            ->__as_array();
    }

    /**
     * @param $p_obj_id
     * @param $p_condition
     * @param $p_status
     * @return isys_component_dao_result
     * @deprecated Please use 'get_data()' instead. Will be removed in i-doit 40!
     */
    public function get_data_by_object($p_obj_id, $p_condition = null, $p_status = null)
    {
        return $this->get_data(null, $p_obj_id, $p_condition, null, $p_status);
    }

    /**
     * @desc   return data object for current category by id
     * @author Dennis Stücken <dstuecken@synetics.de>
     *
     * @param int    $p_list_id
     * @param string $p_condition
     * @param int    $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data_by_id($p_list_id, $p_condition = null, $p_status = null)
    {
        if (is_null($p_list_id)) {
            $p_list_id = -1;
        }

        return $this->get_data($p_list_id, null, $p_condition, null, $p_status);
    }

    /**
     * Gets category's identifier.
     *
     * @return int
     */
    public function get_category_id()
    {
        return $this->m_category_id;
    }

    /**
     * Method for retrieving the name of a category.
     *
     * @return string
     * @throws isys_exception_database
     */
    public function getCategoryTitle()
    {
        if ($this->categoryTitle === null) {
            // Check for GLOBAL or SPECIFIC category. Custom categories implement their own method.
            try {
                if ($this->m_cat_type == C__CMDB__CATEGORY__TYPE_GLOBAL) {
                    $result = $this->retrieve('SELECT isysgui_catg__title AS title
                    FROM isysgui_catg
                    WHERE isysgui_catg__id = ' . $this->convert_sql_id($this->m_category_id) . ';');
                } else {
                    $result = $this->retrieve('SELECT isysgui_cats__title AS title
                    FROM isysgui_cats
                    WHERE isysgui_cats__id = ' . $this->convert_sql_id($this->m_category_id) . ';');
                }

                $this->categoryTitle = $result->get_row_value('title');
            } catch (\Exception $exception) {
                $this->categoryTitle = null;
            }
        }

        return $this->categoryTitle;
    }

    /**
     * Sets category's identifier if necessary
     *
     * @param $p_value
     *
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_category_id($p_value)
    {
        $this->m_category_id = $p_value;
    }

    /**
     * Return a specific property
     *
     * @param $p_key
     *
     * @return array
     */
    public function get_property_by_key($p_key)
    {
        if (isset($this->m_properties[$p_key])) {
            return $this->m_properties[$p_key];
        } else {
            return $this->get_properties()[$p_key] ?: null;
        }
    }

    /**
     * Gets information about manipulating, filtering, importing, exporting, and
     * transforming data. Properties will be completed with additional information.
     *
     * @param   integer $p_get_with This parameter defines, if we want the properties merged with several extra data.
     *
     * @return  array
     */
    public function get_properties($p_get_with = 0)
    {
        // Returned cached property array, but only rely on it if "$p_get_with" is null.
        if ($this->m_cached_properties && isset($this->m_cached_properties[$p_get_with])) {
            return $this->m_cached_properties[$p_get_with];
        }

        // ID-2997  Changed from "!isset($this->m_properties)" to "empty()" because the variable will always be set (as empty array).
        if (empty($this->m_properties)) {
            $this->m_properties = $this->properties();
        }

        $this->m_cached_properties[$p_get_with] = is_array($this->m_properties) ? $this->m_properties : [];

        // Connect general properties with custom ones
        $this->m_cached_properties[$p_get_with] += $this->get_custom_properties();

        $l_extended_properties = isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendProperties", $this->get_category_id(), $this->get_category_type(), $this);

        if (is_array($l_extended_properties)) {
            $l_extended_properties = array_filter($l_extended_properties);
            $l_extended_properties = array_shift($l_extended_properties);

            if (!empty($l_extended_properties) && is_array($l_extended_properties)) {
                foreach ($this->m_properties as $key => $property) {
                    if (!isset($l_extended_properties[$key])) {
                        continue;
                    }

                    // @see ID-8957 After merging either the 'Property' instance or the array, unset the extended property.
                    if ($property instanceof Property) {
                        $this->m_cached_properties[$p_get_with][$key] = $property->merge($l_extended_properties[$key]);
                    } else {
                        $this->m_cached_properties[$p_get_with][$key] = array_merge_recursive($property, $l_extended_properties[$key]);
                    }

                    unset($l_extended_properties[$key]);
                }


                // @see  ID-8651  First of all: skip the overview page!
                if (!empty($l_extended_properties) && !($this instanceof isys_cmdb_dao_category_g_overview)) {
                    array_walk($l_extended_properties, function ($item, $key) use ($p_get_with) {
                        // @see  ID-8651  We only add complete 'Property' instances to prevent the addition of "incomplete" definitions.
                        if ($item instanceof Property) {
                            $this->m_cached_properties[$p_get_with][$key] = $item;
                        } elseif (is_array($item)) {
                            // @see  ID-8651  Try to create a 'Property' instance from the given array, if it fails - skip it.
                            try {
                                $this->m_cached_properties[$p_get_with][$key] = Property::createInstanceFromArray($item);
                            } catch (Throwable $e) {
                                // Write a log.
                                Logger::factory('exception', BASE_DIR . '/log/exception.log')
                                    ->error((string)$e);
                            }
                        }
                    });
                }
            }
        }

        if ($p_get_with === 0) {
            return $this->m_cached_properties[$p_get_with];
        }

        if ($p_get_with & C__PROPERTY__WITH__VALIDATION && class_exists(AttributeSettings::class)) {
            $categoryConstant = $this->get_category_const();

            if ($this->m_cat_type == C__CMDB__CATEGORY__TYPE_CUSTOM || $this instanceof \isys_cmdb_dao_category_g_custom_fields) {
                /** @var isys_cmdb_dao_category_g_custom_fields $this */
                $categoryConstant = $this->get_catg_custom_const();
            }

            // @see ID-9755 Use the new 'attribute-settings' configuration.
            $result = AttributeSettings::instance($this->m_db)->getByCategoryConstant($categoryConstant);

            while ($row = $result->get_row()) {
                [, $propertyKey] = explode('::', $row['propertyReference']);

                if ($this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION]) {
                    // Merge user specific validation rules with dao
                    $this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION] = $row['validation']
                        ? [$row['validation'], isys_format_json::decode($row['validationOption'])]
                        : null;
                }

                $this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] = (bool)$row['mandatory'];
                $this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJ] = (bool)$row['uniqueObject'];
                $this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_OBJTYPE] = (bool)$row['uniqueObjectType'];
                $this->m_cached_properties[$p_get_with][$propertyKey][C__PROPERTY__CHECK][C__PROPERTY__CHECK__UNIQUE_GLOBAL] = (bool)$row['uniqueGlobal'];
            }
        }

        return $this->m_cached_properties[$p_get_with];
    }

    /**
     * @param string $p_categoryConst
     * @return int|null
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 40!
     */
    public function getCategorySpecificIdByConst(string $p_categoryConst) : ?int
    {
        $sql = "SELECT isysgui_cats__id as id
                FROM isysgui_cats
                WHERE isysgui_cats__const = " . $this->convert_sql_text($p_categoryConst) .
                " LIMIT 1";
        $id = $this->retrieve($sql)->get_row_value('id');
        return $id;
    }

    /**
     * @param string $p_categoryConst
     * @return int|null
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 40!
     */
    public function getCategoryGlobalIdByConst(string $p_categoryConst) : ?int
    {
        $sql = "SELECT isysgui_catg__id as id
                FROM isysgui_catg
                WHERE isysgui_catg__const = " . $this->convert_sql_text($p_categoryConst) .
            " LIMIT 1";
        $id = $this->retrieve($sql)->get_row_value('id');
        return $id;
    }

    /**
     * @param string $p_categoryConst
     * @return int|null
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 40!
     */
    public function getCategoryIdByConst(string $p_categoryConst) : ?int
    {
        $id = $this->getCategoryGlobalIdByConst($p_categoryConst);
        if (!$id) {
            $id = $this->getCategorySpecificIdByConst($p_categoryConst);
        }
        return $id;
    }

    /**
     * Retrieve custom properties
     *
     * @param bool $p_configured Get only configured properties
     *
     * @return array
     * @throws \Exception
     */
    public function get_custom_properties($p_configured = false)
    {
        // Get custom properties
        $l_properties = [];

        // Are there any custom properties
        if (method_exists($this, 'custom_properties')) {
            $l_properties = $this->custom_properties();

            if (is_array($l_properties)) {
                $l_dao_custom_properties = new isys_cmdb_dao_custom_property($this->m_db);

                foreach ($l_properties as $l_property_key => $l_property_data) {
                    // Get custom data for property from DB
                    $l_custom_data = $l_dao_custom_properties->get_data(null, $this->get_category_id(), $this->get_category_type_abbr(), $l_property_key);

                    if ($l_custom_data->num_rows()) {
                        $l_custom_data = $l_custom_data->get_row_value('isys_custom_properties__data');

                        if (!empty($l_custom_data)) {
                            // Decode custom data
                            $l_custom_data = isys_format_json::decode($l_custom_data);

                            if (is_array($l_custom_data)) {
                                // Merge custom data with property master
                                $l_properties[$l_property_key] = array_replace_recursive($l_property_data, $l_custom_data);
                            } else {
                                if ($p_configured) {
                                    unset($l_properties[$l_property_key]);
                                }
                            }
                        } else {
                            if ($p_configured) {
                                unset($l_properties[$l_property_key]);
                            }
                        }
                    } else {
                        if ($p_configured) {
                            unset($l_properties[$l_property_key]);
                        }
                    }
                }
            }
        }

        // We will allways return an array
        if (!is_array($l_properties)) {
            $l_properties = [];
        }

        return $l_properties;
    }

    /**
     * Generic save method for custom properties.
     *
     * @param   integer $p_id
     * @param   array   $p_data
     *
     * @return  bool
     * @throws  isys_exception_dao
     */
    public function save_custom_properties($p_id, $p_data)
    {
        if (!empty($p_id)) {
            $l_custom_properties = $this->get_custom_properties();

            if (is_countable($l_custom_properties) && count($l_custom_properties)) {
                // Prepare statement
                $l_sql = 'UPDATE ' . $this->get_source_table() . ' SET %s WHERE ' . $this->get_source_table() . '__id = ' . $this->convert_sql_id($p_id) . ';';

                $l_values = [];

                // Collect values
                foreach ($l_custom_properties as $l_property_key => $l_property_data) {
                    if (isset($l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) && !is_null($l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                        $l_values[] = ' ' . $l_property_data[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' .
                            $this->convert_sql_text($p_data[$l_property_data[C__PROPERTY__UI][C__PROPERTY__UI__ID]]);
                    }
                }

                if (count($l_values)) {
                    $l_values = implode(',', $l_values);

                    $l_sql = sprintf($l_sql, $l_values);

                    return ($this->update($l_sql) && $this->apply_update());
                }
            }
        }

        return true;
    }

    /**
     * Method for retrieving the dynamic properties which are being used to display special information inside the generic list-component.
     *
     * @return  array
     */
    public function get_dynamic_properties()
    {
        return $this->dynamic_properties();
    }

    /**
     * Wrapper method for "get_properties".
     *
     * @param integer $p_get_with
     * @return  array
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_properties_ng($p_get_with = null)
    {
        return $this->get_properties($p_get_with);
    }

    /**
     * Retrieves the number of saved category-entries to the given object.
     *
     * @param   integer $objectId
     *
     * @return  integer
     */
    public function get_count($objectId = null)
    {
        $table = false;

        if ($objectId === null || $objectId <= 0) {
            $objectId = $this->m_object_id;
        }

        // @see ID-2736
        if (!empty($this->m_source_table) || !empty($this->m_table)) {
            $table = $this->m_source_table ?? $this->m_table;

            if (!str_ends_with($table, '_list') && !str_contains($table, '_2_')) {
                $table .= '_list';
            }
        }

        if ($table && $objectId > 0) {
            $sql = 'SELECT COUNT(' . $table . '__id) as count
				FROM ' . $table . '
				WHERE ' . $table . '__status ' . $this->prepare_in_condition([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__TEMPLATE]) . '
				AND ' . $table . '__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

            return (int) $this->retrieve($sql)->get_row_value('count');
        }

        return false;
    }

    /**
     *
     * @return  string
     */
    public function get_strLogbookSQL()
    {
        return $this->m_strLogbookSQL;
    }

    /**
     *
     * @param   string $p_value
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_strLogbookSQL($p_value)
    {
        $this->m_strLogbookSQL = $p_value;

        return $this;
    }

    /**
     *
     * @return  array
     */
    public function get_arrLogbookEntries()
    {
        return $this->m_arrLogbookEntries;
    }

    /**
     *
     * @param   mixed $p_value
     *
     * @return  isys_cmdb_dao_category
     */
    public function set_arrLogbookEntries($p_value)
    {
        $this->m_arrLogbookEntries[] = $p_value;

        return $this;
    }

    /**
     * Creates the condition to the object table.
     *
     * @param   integer $p_obj_id May be an integer or an array of integers.
     * @param   string  $p_alias
     *
     * @return  string
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if ($p_obj_id !== null) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (' . $p_alias . '.isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ')';
            } else {
                $l_sql = ' AND (' . $p_alias . '.isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ')';
            }
        }

        return $l_sql;
    }

    /**
     * Create logbook entry on category update.
     *
     * @param string      $constant
     * @param Changes     $changer
     * @param string|null $commentary
     * @param int|null    $reasonId
     * @param int|null    $importEntryId
     *
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function logbook_update(string $constant, Changes $changer, ?string $commentary = null, ?int $reasonId = null, ?int $importEntryId = null)
    {
        $currentChangesCollection = $changer->getCurrentChangesCollection();
        $fromChangesCollection = $changer->getFromChangesCollection();
        $toChangesCollection = $changer->getToChangesCollection();

        if (!empty($currentChangesCollection->getReformatedData())) {
            $categoryTitle = $currentChangesCollection->getDao()->getCategoryTitle();

            // @see ID-9003 Escape possible HTML input.
            if ($currentChangesCollection->getDao() instanceof isys_cmdb_dao_category_g_custom_fields) {
                $categoryTitle = isys_glob_htmlentities($categoryTitle);
            }

            $entryIdentifier = null;
            $categoryDao = $currentChangesCollection->getDao();

            // @see ID-10040 Try to fetch an entry identifier for multi value categories.
            if ($categoryDao->is_multivalued()) {
                if (method_exists($categoryDao, 'get_entry_identifier') && $categoryDao->get_list_id()) {
                    $entryIdentifier = $categoryDao->get_entry_identifier($categoryDao->get_data($categoryDao->get_list_id())->get_row());
                }

                // If no entry identifier was found we'll use the ID.
                if ($entryIdentifier === null && $categoryDao->get_list_id()) {
                    $entryIdentifier = '#' . $categoryDao->get_list_id();
                }
            }

            isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    $constant,
                    $this->get_strLogbookSQL(),
                    $currentChangesCollection->getObjectId(),
                    $categoryDao->get_objTypeID($currentChangesCollection->getObjectId()),
                    $categoryTitle,
                    serialize($currentChangesCollection->getReformatedData()),
                    $commentary,
                    $reasonId,
                    '',
                    $entryIdentifier
                );

            if ($importEntryId) {
                isys_component_dao_logbook::instance(isys_application::instance()->container->get('database'))
                    ->set_import_entry($importEntryId);
            }
        }

        if (!empty($fromChangesCollection->getReformatedData())) {
            $categoryDao = $fromChangesCollection->getDao() ?? $currentChangesCollection->getDao();

            if ($fromChangesCollection->isMultipleObjects()) {
                /**
                 * @var ChangesData $changesData
                 */
                foreach ($fromChangesCollection->getData() as $changesData) {
                    $change = current($changesData->getData());
                    if ($change[TypeInterface::CHANGES_FROM] === '' && $change[TypeInterface::CHANGES_TO] === '') {
                        continue;
                    }

                    $property = $changesData->getSinglePropertyData();
                    if ($property instanceof SinglePropertyData) {
                        $categoryDao = $property->getDao();
                    }
                    $categoryTitle = $categoryDao->getCategoryTitle();

                    // @see ID-9003 Escape possible HTML input.
                    if ($categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
                        $categoryTitle = isys_glob_htmlentities($categoryTitle);
                    }

                    $objectId = $changesData->getObjectId();

                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            $constant,
                            $this->get_strLogbookSQL(),
                            $objectId,
                            $categoryDao->get_objTypeID($objectId),
                            $categoryTitle,
                            serialize($changesData->getData()),
                            $commentary,
                            $reasonId
                        );

                    if ($importEntryId) {
                        isys_component_dao_logbook::instance(isys_application::instance()->container->get('database'))
                            ->set_import_entry($importEntryId);
                    }
                }
            } else {
                $categoryTitle = $categoryDao->getCategoryTitle();

                // @see ID-9003 Escape possible HTML input.
                if ($fromChangesCollection->getDao() instanceof isys_cmdb_dao_category_g_custom_fields) {
                    $categoryTitle = isys_glob_htmlentities($categoryTitle);
                }

                isys_event_manager::getInstance()
                    ->triggerCMDBEvent(
                        $constant,
                        $this->get_strLogbookSQL(),
                        $fromChangesCollection->getObjectId(),
                        $categoryDao->get_objTypeID($fromChangesCollection->getObjectId()),
                        $categoryTitle,
                        serialize($fromChangesCollection->getReformatedData()),
                        $commentary,
                        $reasonId
                    );

                if ($importEntryId) {
                    isys_component_dao_logbook::instance(isys_application::instance()->container->get('database'))
                        ->set_import_entry($importEntryId);
                }
            }
        }

        if (!empty($toChangesCollection->getReformatedData())) {
            $categoryDao = $toChangesCollection->getDao() ?? $currentChangesCollection->getDao();

            if ($toChangesCollection->isMultipleObjects()) {
                /**
                 * @var ChangesData $changesData
                 */
                foreach ($toChangesCollection->getData() as $changesData) {
                    $change = current($changesData->getData());
                    if ($change[TypeInterface::CHANGES_FROM] === '' && $change[TypeInterface::CHANGES_TO] === '') {
                        continue;
                    }

                    $property = $changesData->getSinglePropertyData();

                    if ($property instanceof SinglePropertyData) {
                        $categoryDao = $property->getDao();
                    }

                    $categoryTitle = $categoryDao->getCategoryTitle();
                    $objectId = $changesData->getObjectId();

                    // @see ID-9003 Escape possible HTML input.
                    if ($categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
                        $categoryTitle = isys_glob_htmlentities($categoryTitle);
                    }

                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            $constant,
                            $this->get_strLogbookSQL(),
                            $objectId,
                            $categoryDao->get_objTypeID($objectId),
                            $categoryTitle,
                            serialize($changesData->getData()),
                            $commentary,
                            $reasonId
                        );

                    if ($importEntryId) {
                        isys_component_dao_logbook::instance(isys_application::instance()->container->get('database'))
                            ->set_import_entry($importEntryId);
                    }
                }
            } else {
                $categoryTitle = $categoryDao->getCategoryTitle();

                // @see ID-9003 Escape possible HTML input.
                if ($toChangesCollection->getDao() instanceof isys_cmdb_dao_category_g_custom_fields) {
                    $categoryTitle = isys_glob_htmlentities($categoryTitle);
                }

                isys_event_manager::getInstance()
                    ->triggerCMDBEvent(
                        $constant,
                        $this->get_strLogbookSQL(),
                        $toChangesCollection->getObjectId(),
                        $categoryDao->get_objTypeID($toChangesCollection->getObjectId()),
                        $categoryTitle,
                        serialize($toChangesCollection->getReformatedData()),
                        $commentary,
                        $reasonId
                    );

                if ($importEntryId) {
                    isys_component_dao_logbook::instance(isys_application::instance()->container->get('database'))
                        ->set_import_entry($importEntryId);
                }
            }
        }
    }

    /**
     * Create logbook entry on creating category entries.
     *
     * @param  string $p_strConst
     * @param  string $p_lc_category
     */
    public function logbook_create($p_strConst, $p_lc_category)
    {
        $this->logbook_rank($_GET[C__CMDB__GET__OBJECT], $p_strConst, $this->get_strLogbookSQL(), $p_lc_category);
    }

    /**
     * Sanitizes Post Data.
     *
     * @return  mixed
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function sanitize_post_data()
    {
        // ID-2997  Changed from "!isset($this->m_properties)" to "empty()" because the variable will always be set (as empty array).
        if (empty($this->m_properties)) {
            $l_properties = $this->get_properties();
        } else {
            $l_properties = $this->m_properties;
        }

        // Get the original POST data from the symfony request object.
        $l_raw_post = isys_application::instance()->container->get('request')->request;

        // @see ID-11156 The raw post data CAN contain arrays, we need to use 'all' instead of 'get' (change in symfony 6).
        $rawPostValues = $l_raw_post->all();

        if (isset($l_properties)) {
            foreach ($l_properties as $l_prop_key => $l_prop) {
                // @see ID-2736
                if (empty($l_prop_key)) {
                    continue;
                }

                if (is_array($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]) || $this->get_category_type() == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                    // @see ID-10666 Fix post key usage.
                    [, $name] = isys_cmdb_dao_category_g_custom_fields::getTypeAndKey($l_prop_key);

                    $l_post_key = "C__CATG__CUSTOM__{$name}";
                    $l_post_key_hidden = "{$l_post_key}__HIDDEN";

                    if (isset($_POST[$l_post_key_hidden])) {
                        $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID] = $l_post_key_hidden;

                        if (!isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                            continue;
                        }
                    } elseif (isset($_POST[$l_post_key])) {
                        $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID] = $l_post_key;

                        if (!isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])) {
                            continue;
                        }
                    }
                }

                if (isset($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID]) && array_key_exists($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID], $_POST)) {
                    $propertyId = $l_prop[C__PROPERTY__UI][C__PROPERTY__UI__ID];

                    // @see  ID-5379  Skip empty values with no default.
                    if (is_scalar($_POST[$propertyId]) && mb_strlen($_POST[$propertyId]) === 0) {
                        continue;
                    }

                    if (is_scalar($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) && mb_strlen($l_prop[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) === 0) {
                        continue;
                    }

                    $hasSanitization = isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])
                        && is_array($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION])
                        && isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0]);

                    if ($hasSanitization) {
                        if ($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0] > 0) {
                            $l_filter = $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0];
                        } else {
                            $l_filter = constant($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][0]);
                        }

                        if (isset($l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1])) {
                            $l_options = $l_prop[C__PROPERTY__CHECK][C__PROPERTY__CHECK__SANITIZATION][1];
                        } else {
                            $l_options = null;
                        }

                        $_POST[$propertyId] = @filter_var($_POST[$propertyId], $l_filter, $l_options);

                        // @see ID-3784 We want to notify the user, if his data has been modified by the sanitation.
                        // @see ID-11222 Only process if the data is 'not empty'.
                        if (array_key_exists($propertyId, $rawPostValues) && !empty($_POST[$propertyId]) && !empty($rawPostValues[$propertyId]) && $_POST[$propertyId] != $rawPostValues[$propertyId]) {
                            isys_notify::warning(isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__SANITATION__CHANGED_VALUE', [
                                    isys_glob_htmlentities($rawPostValues[$propertyId]),
                                    isys_glob_htmlentities($_POST[$propertyId])
                                ]), ['sticky' => true]);
                        }
                    }
                }
            }
        }

        return $_POST;
    }

    /**
     * Gets current object id.
     *
     * @return int
     */
    public function get_object_id(): int
    {
        return (int)$this->m_object_id;
    }

    /**
     * Gets current object type id.
     *
     * @return int
     */
    public function get_object_type_id(): int
    {
        return (int)$this->m_object_type_id;
    }

    /**
     * @param int $entryId
     * @param int $status
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function update_catlevel(int $entryId, int $status): bool
    {
        $table = $this->get_table();

        $l_sql = "UPDATE {$table}
            SET {$table}__status = {$status}
            WHERE {$table}__id = {$entryId}
            LIMIT 1;";

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * Method for deleting all entries by a given object id.
     *
     * @param int $objectId
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function delete_entries_by_obj_id(int $objectId): bool
    {
        if ($objectId > 0) {
            $table = $this->get_table();

            $query = "DELETE FROM {$table} WHERE {$table}__isys_obj__id = {$objectId};";

            return $this->update($query) && $this->apply_update();
        }

        return false;
    }

    /**
     * This method unsets the properties. This function is import for exporting custom categories.
     */
    public function unset_properties()
    {
        $this->m_cached_properties = $this->m_properties = [];
    }

    /**
     *
     * $p_provides syntax:
     *
     * $p_provides = [C__PROPERTY__PROVIDES__REPORT, C__PROPERTY__PROVIDES__LIST]
     *
     * @param   isys_array $p_array_reference
     * @param   integer    $p_record_status
     * @param   array      $p_provides Return property values only if theses flags are set to TRUE. Set to empty array to return all properties.
     *
     * @return  isys_array
     * @throws  isys_exception_general
     */
    public function category_data(&$p_array_reference, $p_record_status = C__RECORD_STATUS__NORMAL, array $p_provides = [])
    {
        if (!$this->get_object_id()) {
            return new isys_array();
        }

        $l_properties = $this->get_properties();
        $l_dynamic_properties = $this->get_dynamic_properties();
        $i = 0;

        /**
         * Retrieve category data
         */
        $l_catdata = $this->get_data_by_object($this->get_object_id(), null, $p_record_status);

        /* Format category result */
        while ($l_row = $l_catdata->get_row()) {
            $l_current_row = new isys_array([], ArrayObject::ARRAY_AS_PROPS);

            foreach ($l_properties as $l_key => $l_propdata) {
                if (is_string($l_key)) {
                    // Only load properties with special provides flags
                    $l_provides_stop = count($p_provides) > 0;
                    foreach ($p_provides as $l_provides) {
                        // If one of the provides flags is true, go further
                        if (isset($l_propdata[C__PROPERTY__PROVIDES][$l_provides]) && $l_propdata[C__PROPERTY__PROVIDES][$l_provides] === true) {
                            $l_provides_stop = false;
                            continue;
                        }
                    }
                    if ($l_provides_stop) {
                        continue;
                    }

                    if (isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                        isset($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                        /* Call helper object to retrieve more information */
                        if (class_exists($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0])) {
                            $l_helper = new $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0](
                                $l_row,
                                $this->get_database_component(),
                                $l_propdata[C__PROPERTY__DATA],
                                $l_propdata[C__PROPERTY__FORMAT],
                                $l_propdata[C__PROPERTY__UI]
                            );

                            /* Set the Unit constant for the convert-helper */
                            if ($l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'convert') {
                                if (method_exists($l_helper, 'set_unit_const')) {
                                    $l_row_unit = $l_properties[$l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                    $l_helper->set_unit_const($l_row[$l_row_unit]);
                                }
                            }

                            try {
                                $l_helper_data = call_user_func([
                                    $l_helper,
                                    $l_propdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]
                                ], $l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]);
                            } catch (isys_exception_general $e) {
                                throw new isys_exception_general($e->getMessage() . '. Problem occurred for property ' .
                                    $l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' in ' . $this->get_category_const());
                            }

                            $l_current_row[$l_key] = $this->category_data_extract_helper_data($l_helper_data);

                            unset($l_helper_data);
                        }
                    } else {
                        $l_current_row[$l_key] = new isys_cmdb_dao_category_data_value($l_row[$l_propdata[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]);
                    }

                    unset($l_helper);
                }
            }

            // Dynamic properties.
            foreach ($l_dynamic_properties as $l_dkey => $l_dpropdata) {
                if (isset($l_dpropdata[C__PROPERTY__FORMAT])) {
                    if (isset($l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]) &&
                        isset($l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                        $l_cat_dao = $l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];
                        $l_method = $l_dpropdata[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];

                        // @see There was a problem with the dynamic prop in 'isys_cmdb_dao_category_s_application_assigned_obj'.
                        if (is_string($l_cat_dao) && is_a($l_cat_dao, isys_component_dao::class, true)) {
                            $l_cat_dao = $l_cat_dao::instance($this->get_database_component());
                        }

                        if (method_exists($l_cat_dao, $l_method)) {
                            $l_current_row[$l_dkey] = new isys_cmdb_dao_category_data_value($l_cat_dao->$l_method($l_row, $l_row));
                        }
                    }
                }
            }

            if ($l_current_row->count() > 0) {
                if (isset($l_row[$this->m_table . '__id'])) {
                    $p_array_reference[$l_row[$this->m_table . '__id']] = $l_current_row;
                } else {
                    $p_array_reference[$i] = $l_current_row;
                }

                unset($l_current_row);
            }
        }

        // Free memory
        $l_catdata->free_result();

        unset($l_properties, $l_dynamic_properties);

        return $p_array_reference;
    }

    /**
     * @param array $p_data
     *
     * @return isys_cmdb_dao_category_data_reference|isys_cmdb_dao_category_data_value
     */
    public function category_data_extract_helper_subdata(array $p_data)
    {
        if (isset($p_data['id'])) {
            return new isys_cmdb_dao_category_data_reference((isset($p_data['ref_title'])) ? $p_data['ref_title'] : @$p_data['title'], $p_data['id'], $p_data);
        } else {
            return new isys_cmdb_dao_category_data_value((isset($p_data['ref_title'])) ? $p_data['ref_title'] : @$p_data['title'], $p_data);
        }
    }

    /**
     * Callback method which returns the master and slave object for the relation
     *
     * @param isys_request $p_request
     *
     * @return array
     * @throws isys_exception_general
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function callback_property_relation_handler(isys_request $p_request, $p_parameters = [])
    {
        [$l_class, $l_switch_fields] = $p_parameters;
        $l_return = [];

        if (class_exists($l_class)) {
            $l_dao = call_user_func([
                $l_class,
                'instance'
            ], isys_application::instance()->database);

            $l_data = $l_dao->get_data_by_id($p_request->get_category_data_id())
                ->get_row();

            if (isset($l_data[$l_dao->m_object_id_field])) {
                if ($l_switch_fields === true) {
                    $l_return[C__RELATION_OBJECT__MASTER] = $l_data[$l_dao->m_connected_object_id_field];
                    $l_return[C__RELATION_OBJECT__SLAVE] = $l_data[$l_dao->m_object_id_field];
                } else {
                    $l_return[C__RELATION_OBJECT__MASTER] = $l_data[$l_dao->m_object_id_field];
                    $l_return[C__RELATION_OBJECT__SLAVE] = $l_data[$l_dao->m_connected_object_id_field];
                }
            }
        }

        return $l_return;
    }

    /**
     * Method which retrieves only the specified property data
     *
     * @param null   $p_cat_data_id
     * @param null   $p_obj_id
     * @param string $p_property
     *
     * @return bool|isys_component_dao_result
     * @throws Exception
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_data_by_property($p_cat_data_id = null, $p_obj_id = null, $p_property = '')
    {
        $l_properties = $this->get_properties();
        if (isset($l_properties[$p_property])) {
            if (isset($l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD])) {
                $l_table = $this->get_table();
                $l_data_field = $l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                $l_join = '';
                try {
                    if ($l_properties[$p_property][C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] == 'isys_connection') {
                        $l_join = ' INNER JOIN isys_connection ON isys_connection__id = ' . $l_data_field;
                        $l_data_field = 'isys_connection__isys_obj__id';
                    }

                    $l_sql = 'SELECT ' . $l_data_field . ' AS ' . $p_property . ' FROM ' . $l_table . $l_join . ' WHERE TRUE ';

                    if ($p_cat_data_id !== null) {
                        $l_sql .= ' AND ' . $l_table . '__id = ' . $this->convert_sql_id($p_cat_data_id);
                    }

                    if ($p_obj_id !== null) {
                        $l_sql .= ' AND ' . $l_table . '__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
                    }

                    return $this->retrieve($l_sql);
                } catch (Exception $e) {
                    throw new Exception('Could not retrieve data with error: ' . $e->getMessage());
                }
            }
        }

        return false;
    }

    /**
     * Setter for setting category data into m_data
     *
     * @param $p_data
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function set_category_data($p_data)
    {
        $this->m_data = $p_data;
    }

    /**
     * Getter for retrieving category data from m_data
     *
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_category_data()
    {
        return $this->m_data;
    }

    /**
     * Method for retrieving all category properties.
     *
     * @return  array
     */
    protected function properties()
    {
        ;
    }

    /**
     * Get specific property
     *
     * @param string $key
     * @return array|Property
     */
    protected function property(string $key): array|Property
    {
        $properties = $this->properties();

        return $properties[$key] ?? [];
    }

    /**
     * Get specific property
     *
     * @param string $key
     * @return array|Property
     */
    protected function dynamic_property(string $key): array|Property
    {
        $properties = $this->dynamic_properties();

        return $properties[$key] ?? [];
    }

    /**
     * Abstract method for retrieving the dynamic properties of every category dao.
     *
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @return  array
     */
    protected function dynamic_properties()
    {
        return [];
    }

    /**
     * @param $p_category_id
     * @param $p_connected_object_id
     *
     * @return bool|mixed
     * @throws isys_exception_cmdb
     */
    protected function handle_connection($p_category_id, $p_connected_object_id)
    {
        $l_connection = new isys_cmdb_dao_connection($this->get_database_component());
        $l_connection_id = $l_connection->retrieve_connection($this->get_table(), $p_category_id);

        if (!$l_connection_id) {
            $l_connection_id = $l_connection->attach_connection($this->get_table(), $p_category_id, null, $this->get_table() . '__isys_connection__id');
        }

        $l_connection->update_connection($l_connection_id, $p_connected_object_id);

        return $l_connection_id;
    }

    /**
     * Generic relation handling
     *
     *  To enable generic handling, set your property to
     *    [C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE] => C__RELATION_TYPE__XYZ
     *
     * @param $p_list_id
     * @param $p_data
     */
    protected function handle_relation_generic($p_list_id, $p_data)
    {
        if ($p_list_id > 0 && is_array($p_data)) {
            $l_properties = $this->get_properties();
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->m_db);

            foreach ($l_properties as $l_property) {
                if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE]) && isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER])) {
                    $l_handler = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_HANDLER];
                    if (is_object($l_handler) && method_exists($l_handler, 'execute')) {
                        $l_relation_data = $l_handler->execute(new isys_request($p_data));

                        if (is_array($l_relation_data) && count($l_relation_data) > 1) {
                            $l_data = $this->get_data_by_id($p_list_id)
                                ->get_row();
                            $l_relation_id = @$l_data[$this->m_table . '__isys_catg_relation_list__id'] ? $l_data[$this->m_table . '__isys_catg_relation_list__id'] : null;

                            $l_relation_dao->handle_relation(
                                $p_list_id, // category id
                                $this->m_table, // table
                                $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__RELATION_TYPE], // relation type
                                $l_relation_id, // relation id
                                $l_relation_data[0], // master
                                $l_relation_data[1] // slave
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Prepares category's data before creating new category data or updating existing ones.
     *
     * @param   array $p_data
     *
     * @return  mixed Returns data which should be handled (array), otherwise false (bool)
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function prepare_data($p_data)
    {
        $registeredCalculations = $l_data = [];

        // Special fields:
        if (isset($p_data['id'])) {
            $l_data['id'] = $p_data['id'];
        }

        if (isset($p_data['isys_obj__id'])) {
            $l_data['isys_obj__id'] = $p_data['isys_obj__id'];
        }

        if (isset($p_data['status'])) {
            $l_data['status'] = $p_data['status'];
        }

        // Get category's properties:
        $l_properties = $this->get_properties();

        // Iterate through properties:
        foreach ($l_properties as $l_key => $l_value) {
            if (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__CALCULATE_VALUE])
                && is_callable($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__CALCULATE_VALUE])
            ) {
                $registeredCalculations[] = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__CALCULATE_VALUE];
            }

            if (!array_key_exists($l_key, $p_data)) {
                continue;
            }

            switch ($l_value[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) {
                case 'contact':

                    $l_dao_ref = new isys_contact_dao_reference($this->m_db);

                    // @todo $p_data[$l_key] can be an json string or contact id
                    if (is_numeric($p_data[$l_key])) {
                        // case contact id
                        $l_data[$l_key] = $p_data[$l_key];
                    } else {
                        // case json string
                        $l_existing_id = null;

                        $l_contact_id = $l_dao_ref->ref_contact($p_data[$l_key], $l_existing_id);
                        unset($l_dao_ref);

                        if ($l_contact_id === false) {
                            $l_data[$l_key] = '';
                        } else {
                            $l_data[$l_key] = intval($l_contact_id);
                        }
                    }
                    break;
                case 'date':
                    // Workaround:
                    if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] == C__TYPE__INT && is_numeric($p_data[$l_key])) {
                        $p_data[$l_key] = date('c', $p_data[$l_key]);
                    }

                    if (isset($p_data[$l_key]) && is_string($p_data[$l_key])) {
                        // @see  ID-5796  We set the date field to "" because non-scalar values will be skipped when creating the SQL.
                        if (empty($p_data[$l_key])) {
                            $l_data[$l_key] = '';
                            continue 2;
                        }

                        if (strpos($p_data[$l_key], ' - ')) {
                            $p_data[$l_key] = str_replace(' - ', ' ', $p_data[$l_key]);
                        }

                        $l_date = strtotime($p_data[$l_key]);
                    } else {
                        $l_date = false;
                    }

                    if ($l_date === false || $l_date < 0) {
                        $l_data[$l_key] = null;
                        continue 2;
                    }

                    $l_data[$l_key] = $l_date;

                    // One of MySQL's date types is used:
                    if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE] !== C__TYPE__INT) {
                        $l_format = null;
                        switch ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                            case 'date':
                                $l_format = 'Y-m-d';
                                break;
                            case 'datetime':
                            case 'timestamp':
                                $l_format = 'Y-m-d H:i:s';
                                break;
                            case 'time':
                                $l_format = 'H:i:s';
                                break;
                            case 'year':
                                $l_format = 'Y';
                                break;
                        }
                        $l_data[$l_key] = date($l_format, $l_data[$l_key]);
                    }
                    break;
                default:
                    if (!$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT] && !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__LIST] &&
                        !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__MULTIEDIT] && !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] &&
                        !$l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__VALIDATION]) {
                        continue 2;
                    }

                    $l_data[$l_key] = $p_data[$l_key];
                    break;
            }
        }

        if (!empty($registeredCalculations)) {
            foreach ($registeredCalculations as $calculation) {
                $l_data = call_user_func($calculation, $l_data);
            }
        }

        return $l_data;
    }

    /**
     * Prepares SQL query to create or update an entity.
     *
     * @todo    Default values
     *
     * @param   array $p_data Properties in a associative array with tags as keys and their corresponding values as values.
     *
     * @throws  isys_exception_dao_cmdb
     * @return  string  Properties' part of the sql query
     * @author  Benjamin Heisig <bheisig@synetics.de>
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    protected function prepare_query(array $p_data)
    {
        $l_result = [];

        // Add special fields if available:
        $l_specials = [
            'id',           // Category's data identifier
            'isys_obj__id', // Related object's identifier
            'status'        // Record status
        ];

        foreach ($l_specials as $l_special) {
            if (array_key_exists($l_special, $p_data)) {
                $l_result[] = $this->m_table . '__' . $l_special . ' = ' . $this->convert_sql_id($p_data[$l_special]);
            }
        }

        $l_properties = $this->get_properties();
        $l_already_specified = [];

        // Iterate through properties:
        foreach ($l_properties as $l_key => $l_value) {
            // @see ID-10764 Allow 'NULL' values
            if (!array_key_exists($l_key, $p_data) || (!is_scalar($p_data[$l_key]) && $p_data[$l_key] !== null)) {
                // Skip property
                continue;
            }

            $needsConversion = true;

            // @see  ID-5378  Do not use conversion, if the defaults are explicitly set to NULL or "".
            if (mb_strlen($p_data[$l_key] ?? '') === 0) {
                if ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT] === null) {
                    $needsConversion = false;
                    $p_data[$l_key] = 'NULL';
                } elseif (mb_strlen($l_value[C__PROPERTY__UI][C__PROPERTY__UI__DEFAULT]) === 0) {
                    $needsConversion = false;
                    $p_data[$l_key] = "''";
                }
            }

            if ($needsConversion) {
                switch ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]) {
                    case C__TYPE__TEXT:
                    case C__TYPE__TIME:
                    case C__TYPE__TEXT_AREA:
                        $p_data[$l_key] = $this->convert_sql_text($p_data[$l_key]);
                        break;
                    case C__TYPE__DATE:
                        // @todo No special convert yet!
                        if (empty($p_data[$l_key])) {
                            $p_data[$l_key] = 'NULL';
                        } else {
                            $p_data[$l_key] = $this->convert_sql_text($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__DATE_TIME:
                        if (empty($p_data[$l_key])) {
                            $p_data[$l_key] = 'NULL';
                        } else {
                            $p_data[$l_key] = $this->convert_sql_datetime($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__INT:
                        // We check for popups and dialogs with filled "p_strTable" parameter.
                        if ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__POPUP ||
                            ($l_value[C__PROPERTY__UI][C__PROPERTY__UI__TYPE] == C__PROPERTY__UI__TYPE__DIALOG &&
                                !empty($l_value[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']))) {
                            $p_data[$l_key] = $this->convert_sql_id($p_data[$l_key]);
                        } else {
                            $p_data[$l_key] = $this->convert_sql_int($p_data[$l_key]);
                        }
                        break;
                    case C__TYPE__FLOAT:
                    case C__TYPE__DOUBLE:
                        $p_data[$l_key] = $this->convert_sql_float($p_data[$l_key]);
                        break;
                        // @todo Never used:
                    case 'boolean':
                        $p_data[$l_key] = $this->convert_sql_boolean($p_data[$l_key]);
                        break;
                    default:
                        throw new isys_exception_dao_cmdb(sprintf(
                            'Category %s: Cannot prepare entity because of unknown type "%s".',
                            $this->get_category_const(),
                            $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TYPE]
                        ), get_class($this));
                }
            }

            if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] && !isset($l_already_specified[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]]) &&
                $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] != $this->m_table . '__id') {
                $l_result[] = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] . ' = ' . $p_data[$l_key];
                $l_already_specified[$l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]] = true;
            }
        }

        return implode(', ', $l_result);
    }

    /**
     * Returns a sql condition which filters by status of table $p_table
     *
     * @param array|int $p_status
     * @param string $p_table
     *
     * @return string
     */
    protected function get_status_condition($p_status, $p_table = 'isys_obj')
    {
        if (!is_null($p_status)) {
            if (is_array($p_status)) {
                return ' AND ' . $p_table . '__status ' . $this->prepare_in_condition($p_status) . ' ';
            } else {
                return ' AND ' . $p_table . '__status = ' . $p_status . ' ';
            }
        }

        return '';
    }

    /**
     * Creates and SQL specific filter for use in a WHERE statement
     *
     * @param array || string $p_filter
     *
     * @return string
     *
     */
    protected function prepare_filter($p_filter)
    {
        if ($p_filter === null) {
            return '';
        }

        $l_info = $this->get_properties();

        if (is_countable($l_info) && count($l_info) == 0) {
            return '';
        }

        // New behavior:
        $l_condition = '';
        $l_table = false;

        // @see ID-2736
        if (!empty($this->m_table)) {
            $l_table = (strpos($this->m_table, '_list') !== false) ? $this->m_table : ((is_int(strpos($this->m_table, '_2_')) ? $this->m_table : $this->m_table . '_list'));
        }

        if ($l_table && is_string($p_filter) && strlen($p_filter) >= (int)isys_tenantsettings::get('maxlength.search.filter', 3)) {
            $i = 0;

            foreach ($l_info as $l_value) {
                // Skip properties that shouldn't be included:
                if (isset($l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH]) && $l_value[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__SEARCH] === false) {
                    continue;
                }
                if ($i == 0) {
                    $l_condition .= 'AND (';
                }
                if ($i++ > 0) {
                    $l_condition .= 'OR ';
                }

                if (isset($l_value['description'])) {
                    $l_ref = $l_table . '.' . $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                } else {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                }

                if (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                } elseif (isset($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title';
                }

                if ($l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS]) {
                    $l_ref = $l_value[C__PROPERTY__DATA][C__PROPERTY__DATA__TABLE_ALIAS] . '.' . $l_ref;
                }

                $l_condition .= '(' . $l_ref . ' LIKE \'%' . addslashes($p_filter) . '%\') ';
            }
            if ($i > 0) {
                $l_condition .= ')';
            }

            return $l_condition;
        } else {
            return '';
        }
    }

    /**
     * Updates, applies update and fetches last inserted identifier.
     *
     * @param string $p_query SQL statement
     * @return mixed Last inserted identifier (int) or false (bool)
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @deprecated Will be removed in i-doit 40!
     */
    protected function do_update($p_query)
    {
        if ($this->update($p_query) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return false;
    }

    /**
     * Method for retrieving the value of a property.
     *
     * @param string $propertyKey
     * @return mixed
     */
    protected function get_property(string $propertyKey): mixed
    {
        return $this->m_sync_catg_data['properties'][$propertyKey][C__DATA__VALUE] ?? null;
    }

    /**
     * Get ON DUPLICATE condition for insert statement
     *
     * @param $p_object_id
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    protected function on_duplicate($p_object_id)
    {
        return ' ON DUPLICATE KEY UPDATE ' . $this->m_table . '__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ' ';
    }

    /**
     * Determines if the category has a relation field or not
     *
     * @return bool
     */
    public function has_relation()
    {
        return $this->m_has_relation;
    }

    /**
     * @param   mixed $p_data
     *
     * @return  mixed
     */
    private function category_data_extract_helper_data($p_data)
    {
        if ($p_data instanceof isys_export_data) {
            $p_data = $p_data->get_data();
        }

        if (is_array($p_data)) {
            if (isset($p_data[0])) {
                $values = [];

                foreach ($p_data as $l_subdata) {
                    if (is_array($l_subdata)) {
                        $values[] = $this->category_data_extract_helper_subdata($l_subdata);
                    }
                }

                return new isys_cmdb_dao_category_data_multivalue($values);
            } else {
                return $this->category_data_extract_helper_subdata($p_data);
            }
        }

        return new isys_cmdb_dao_category_data_value($p_data);
    }

    /**
     * Constructor.
     *
     * @param  isys_component_database &$p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        // Initiate important member variables.
        if ($this->m_category) {
            if (!isset($this->m_category_const)) {
                $this->m_category_const = strtoupper('C__' . $this->m_category_type_abbr . '__' . $this->m_category);
            }

            if (defined($this->m_category_const)) {
                if (constant($this->m_category_const) == defined_or_default('C__CATG__CUSTOM_FIELDS') && isset($_GET[C__CMDB__GET__CATG_CUSTOM])) {
                    $this->m_category_id = $_GET[C__CMDB__GET__CATG_CUSTOM];
                } else {
                    $this->m_category_id = constant($this->m_category_const);
                }
            }

            if (!isset($this->m_table)) {
                $this->m_table = 'isys_' . $this->m_category_type_abbr . '_' . $this->m_category . '_list';
            }

            if (!isset($this->m_ui)) {
                $this->m_ui = 'isys_cmdb_ui_category_' . substr($this->m_category_type_abbr, -1) . '_' . $this->m_category;
            }

            if (!isset($this->m_list) && $this->m_multivalued === true) {
                $this->m_list = 'isys_cmdb_dao_list_' . $this->m_category_type_abbr . '_' . $this->m_category;
            }

            if (!isset($this->m_tpl)) {
                $this->m_tpl = $this->m_category_type_abbr . '__' . $this->m_category . '.tpl';
            }
        }

        $this->set_validation(true);

        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $this->set_object_id($_GET[C__CMDB__GET__OBJECT]);
        }

        if (isset($_GET[C__CMDB__GET__OBJECTTYPE])) {
            $this->set_object_type_id($_GET[C__CMDB__GET__OBJECTTYPE]);
        }
    }

    /**
     * Wrap field with MySQL DATE_FORMAT() function with the user configured date format.
     *
     * @param string $field
     * @param bool $shortFormat
     * @return string
     * @throws Exception
     */
    public static function build_query_date_format(string $field, bool $shortFormat = false): string
    {
        $locales = isys_application::instance()->container->get('locales');

        // @see ID-12258 React to 'german' or 'not german' date format. The following code needs to be cleaned up in the future.
        $dateFormat = $locales->resolve_language_by_constant($locales->get_setting(LC_TIME)) === 'de' ? '%d.%m.%Y' : '%Y-%m-%d';
        $format = $dateFormat . ($shortFormat ? ' %H:%i:%s' : '');

        return "DATE_FORMAT({$field}, '{$format}')";
    }

    /**
     * Monetary formatter for dynamic properties
     *
     * @param array      $data
     * @param string     $columnName
     * @param int|string $objectId
     *
     * @return mixed|string
     * @throws Exception
     */
    public function dynamicMonetaryFormatter($data, $columnName, $objectId)
    {
        $value = $data[$columnName];

        if (!empty($value) || !empty($objectId)) {
            if (!isset($data[$columnName])) {
                $l_dao = static::instance(isys_application::instance()->container->get('database'));
                $value = $l_dao->get_data(null, $objectId)
                    ->get_row_value($columnName);
            }

            // Decimal seperator from the user configuration.
            $monetaryValues = explode(" ", isys_application::instance()->container->get('locales')
                ->fmt_monetary($value));

            return $monetaryValues[0] . ' ' . $monetaryValues[1];
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Check whether category is called for an object in birth
     *
     * @return bool
     */
    public function isCreationInOverview()
    {
        return  isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 &&
                isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
                isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE;
    }

    /**
     * @param int|int[] $id
     * @param string $tag
     * @param false  $asId
     *
     * @return CollectionInterface
     * @throws Exception
     */
    public function getAttachedEntries($id, $tag = '', $asId = false): CollectionInterface
    {
        return isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'))
            ->getConnectedObjects($this->get_table(), $id);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getCurrentEntry($id, $objectId)
    {
        $fakeEntry = [
            Config::CONFIG_DATA_ID => $id,
            Config::CONFIG_PROPERTIES => ['id' => $id]
        ];

        return Merger::instance(Config::instance($this, $objectId, $fakeEntry))->getDataForSync();
    }

    /**
     * This is a general method which is being used for callbacks from isys_callback.
     * Mostly it is being used for isys_popup_browser_object_ng::C__DATARETRIEVAL
     *
     * @param isys_request $request
     *
     * @return array
     */
    public function getEntriesByRequestObject(isys_request $request)
    {
        $entryId = $request->get_category_data_id() ?? null;
        $objectId = $request->get_object_id() ?? null;

        if ($this->get_object_browser_category() === true) {
            $entryId = null;
        }

        $result = $this->get_data($entryId, $objectId);
        $fields = isys_popup_browser_object_ng::C__DATARETRIEVAL_DEFAULT_FIELDS;
        $return = [];
        while ($row = $result->get_row()) {
            $dataSet = [];
            foreach ($fields as $field) {
                $dataSet[$field] = $row[$field] ?? null;
            }

            $return[] = $dataSet;
        }

        return $return;
    }

    /**
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    public static function memoryCondition(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        $multiplier = [1024, 1048576, 1073741824, 1099511627776, 1125899906842624];
        $column = $dao->get_database_component()
            ->escapeColumnName($name);
        $value = (int)$value;
        $conditions = [];
        foreach ($multiplier as $unitMultiplier) {
            $conditions[] = "{$column} LIKE '%," . $value * $unitMultiplier . ",%'";
        }

        $dao->add_additional_having_conditions(implode(' OR ', $conditions));

        return true;
    }

    /**
     * @param isys_cmdb_dao_list_objects $dao
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    public static function speedCondition(isys_cmdb_dao_list_objects $dao, $name, $value)
    {
        $column = $dao->get_database_component()
            ->escapeColumnName($name);
        $value = (int)$value;
        $dao->add_additional_having_conditions("{$column} LIKE '%<li>{$value} %'");

        return true;
    }

    /**
     * @param int   $objectId
     * @param array $newEntries
     * @return array
     * @throws Exception
     */
    public function filterUnassignedEntries(int $objectId, array $newEntries): array
    {
        $collection = $this->getAttachedEntries($objectId);

        if ($collection instanceof ObjectCollection) {
            // @see ID-11523 Use object ID.
            $assignedEntries = array_map(
                fn (ObjectEntry $entry) => $entry->getObjectid(),
                $collection->getEntries()
            );
        } elseif ($collection instanceof EntryCollection) {
            // @see ID-11523 Use entry ID.
            $assignedEntries = array_map(
                fn (Entry $entry) => $entry->getEntryid(),
                $collection->getEntries()
            );
        } else {
            // Keep this in case of other collection types...
            $assignedEntries = array_keys($collection->getEntries());
        }

        return array_filter(
            $newEntries,
            fn ($id) => !in_array((int)$id, $assignedEntries)
        );
    }

    /**
     * @return string
     */
    public function getCategoryJoinQuery(): string
    {
        return '';
    }

    /**
     * @param array       $syncData
     * @param int|null    $objectId
     * @param int|null    $objectTypeId
     * @param string|null $objectTitle
     * @param string|null $sysid
     *
     * @return array
     * @throws Exception
     */
    public function replacePlaceholdersWithSyncData(array $syncData, ?int $objectId = null, ?int $objectTypeId = null, ?string $objectTitle = null, ?string $sysid = null)
    {
        $table = $this->get_source_table() ?? $this->get_table() ?? '';
        $config = \idoit\Component\PlaceholderReplacer\Config::factory($objectId, $objectTypeId, $objectTitle, $sysid, $table);
        $properties = $syncData[isys_import_handler_cmdb::C__PROPERTIES];

        foreach ($properties as $propertyKey => &$content) {
            if (!is_array($content) || !isset($content[C__DATA__VALUE]) || !is_string($content[C__DATA__VALUE])) {
                continue;
            }
            // Replace default values
            $value = isys_application::instance()->container->get('idoit.component.placeholder-replacer')
                ->replacePlaceholder($content[C__DATA__VALUE] ?? '', $config);
            $content[C__DATA__VALUE] = $value;
        }
        $syncData[isys_import_handler_cmdb::C__PROPERTIES] = $properties;

        return $syncData;
    }

    /**
     * This method should only be used for single value categories as there is always one entry for each object
     *
     * @param int         $objectId
     * @param string|null $table
     *
     * @return int|null
     * @throws isys_exception_database
     */
    public function getEntryIdByObjectId(int $objectId, ?string $table = null): ?int
    {
        $table = (string)$table;

        if (!$this->table_exists($table)) {
            $table = $this->get_source_table();
        }

        if (!$this->table_exists($table)) {
            $table = $this->get_table();
        }

        if (!$this->table_exists($table)) {
            return null;
        }

        $id = $this
            ->retrieve("SELECT {$table}__id as id FROM {$table} WHERE {$table}__isys_obj__id = {$this->convert_sql_id($objectId)} limit 1;")
            ->get_row_value('id');

        if ($id !== null && $id > 0) {
            return (int)$id;
        }

        return null;
    }
}
