<?php

use idoit\Component\Location\Coordinate;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Context\Context;

/**
 * i-doit
 *
 * DAO: global category for locations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_location extends isys_cmdb_dao_category_global
{
    /**
     * Location cache (including parent objects).
     *
     * @var  array
     */
    protected static $m_location_cache = [];

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'location';

    /**
     * @var  string
     */
    protected $m_connected_object_id_field = 'isys_catg_location_list__parentid';

    /**
     * @var  boolean
     */
    protected $m_has_relation = true;

    /**
     * @var  string
     */
    protected $m_object_id_field = 'isys_catg_location_list__isys_obj__id';

    /**
     * @var []
     */
    protected static $invalidateCache = [];

    /**
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Constructor.
     *
     * @param   isys_component_database &$p_db
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct(isys_component_database $p_db)
    {
        isys_component_signalcollection::get_instance()
            ->connect('mod.cmdb.beforeCategoryEntrySave', [$this, 'validate_before_save'])
            ->connect('mod.cmdb.beforeCreateCategoryEntry', [$this, 'validate_before_save'])
            ->connect('mod.cmdb.beforeUpdateLocationNode', [$this, 'update_location_node']);

        return parent::__construct($p_db);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        try {
            if (!empty(self::$invalidateCache)) {
                array_map(function ($l_cache) {
                    $l_cache->clear();
                }, self::$invalidateCache);
            }
        } catch (Exception $e) {
            isys_notify::warning(sprintf('Could not clear cache files for %sauth-* with message: ' . $e->getMessage(), isys_glob_get_temp_dir()));
        }
    }

    /**
     * Static method for checking if a given slot is free.
     *
     * @param array $usedSlots
     * @param int   $slot
     * @param int   $insertion
     * @return string|null
     */
    private function getPositionedObjectName(array $usedSlots, int $slot, int $insertion): ?string
    {
        $front = "{$slot}-" . C__INSERTION__FRONT;
        $rear = "{$slot}-" . C__INSERTION__REAR;
        $both = "{$slot}-" . C__INSERTION__BOTH;
        $inserted = "{$slot}-{$insertion}";

        if ($insertion === C__INSERTION__BOTH && array_key_exists($front, $usedSlots)) {
            return $usedSlots[$front] ?? 'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED';
        }

        if ($insertion === C__INSERTION__BOTH && array_key_exists($rear, $usedSlots)) {
            return $usedSlots[$rear] ?? 'LC__CATG__LOCATION__BACKSIDE_OCCUPIED';
        }

        if (array_key_exists($both, $usedSlots)) {
            return $usedSlots[$both] ?? 'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED';
        }

        if (array_key_exists($inserted, $usedSlots)) {
            return match ($insertion) {
                C__INSERTION__FRONT => $usedSlots[$inserted] ?? 'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED',
                C__INSERTION__REAR => $usedSlots[$inserted] ?? 'LC__CATG__LOCATION__BACKSIDE_OCCUPIED',
                C__INSERTION__BOTH => $usedSlots[$inserted] ?? 'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED',
            };
        }

        return null;
    }

    /**
     * Export Helper for property longitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_longitude($p_value, $p_row)
    {
        return $p_row['longitude'] ?: '';
    }

    /**
     * Export Helper for property latitude for global category location.
     *
     * @param  mixed $p_value
     * @param  array $p_row
     *
     * @return string
     */
    public function property_callback_latitude($p_value, $p_row = [])
    {
        return $p_row['latitude'] ?: '';
    }

    /**
     * Return complete location path.
     */
    public function dynamic_property_callback_location_path(array $row): string
    {
        if (!isset($row['isys_catg_location_list__parentid'])) {
            $l_parentid = $this->get_data(null, $row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        } else {
            $l_parentid = $row['isys_catg_location_list__parentid'];
        }

        $context = Context::instance();

        if ($l_parentid > 0) {
            $instance = isys_popup_browser_location::instance()
                ->set_format_exclude_self(false);

            // @see ID-10024 to prevent a cutting on the object title in the output
            if ($context->getOrigin() !== Context::ORIGIN_GUI ||
                $context->getGroup() === Context::CONTEXT_GROUP_REPORT_EXPORT
            ) {
                $instance->set_format_object_name_cut(1000000);
            }

            return $instance->format_selection($l_parentid);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Return complete location path without html.
     */
    public function dynamic_property_callback_location_path_raw(array $row): string
    {
        if (!isset($row['isys_catg_location_list__parentid'])) {
            $parentID = $this->get_data(null, $row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        } else {
            $parentID = $row['isys_catg_location_list__parentid'];
        }

        if ($parentID > 0) {
            // Strip all tags
            return strip_tags(
                preg_replace('#<script(.*?)>(.*?)</script>#is', '', isys_popup_browser_location::instance()
                    ->set_format_exclude_self(false)
                    ->format_selection($parentID, true))
            );
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Return the single location parent of the given object.
     *
     * @param   array $p_row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_location($p_row)
    {
        if (!isset($p_row['isys_catg_location_list__parentid'])) {
            $l_parentid = $this->get_data(null, $p_row["__id__"])
                ->get_row_value('isys_catg_location_list__parentid');
        } else {
            $l_parentid = $p_row['isys_catg_location_list__parentid'];
        }

        if ($l_parentid > 0) {
            $l_location_row = $this->get_object_by_id($l_parentid)
                ->get_row();

            return isys_factory::get_instance('isys_ajax_handler_quick_info')
                ->get_quick_info($l_location_row['isys_obj__id'], $l_location_row['isys_obj__title'], C__LINK__OBJECT);
        }

        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Callback method for the assembly option dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_assembly_options(isys_request $p_request)
    {
        $language = isys_application::instance()->container->get('language');
        // Preparing the assembly-options (horizontal/vertical).
        $l_options = [
            C__RACK_INSERTION__HORIZONTAL => $language->get('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL')
        ];

        $allowedContexts = [
            Context::CONTEXT_REPORT_SELECTION,
            Context::CONTEXT_REPORT_CONDITION,
            Context::CONTEXT_IMPORT_XML,
            Context::CONTEXT_EXPORT_XML,
            Context::CONTEXT_IMPORT_CSV, // @see ID-8899 Add CSV import as allowed context, the validation will happen later.
            Context::CONTEXT_MULTIEDIT, // @see ID-9070
        ];

        // Check if callback is coming from report @see ID-7701 and ID-7700
        if (in_array(Context::instance()->getContextTechnical(), $allowedContexts, true)) {
            return [
                C__RACK_INSERTION__HORIZONTAL => $language ->get('LC__CMDB__CATS__ENCLOSURE__HORIZONTAL'),
                C__RACK_INSERTION__VERTICAL => $language->get('LC__CMDB__CATS__ENCLOSURE__VERTICAL'),
            ];
        }

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_request->get_row('isys_catg_location_list__parentid'))
                ->get_row();

            if ($l_rack['isys_cats_enclosure_list__vertical_slots_rear'] > 0 || $l_rack['isys_cats_enclosure_list__vertical_slots_front'] > 0) {
                $l_options[C__RACK_INSERTION__VERTICAL] = $language->get('LC__CMDB__CATS__ENCLOSURE__VERTICAL');
            }
        }

        return $l_options;
    }

    /**
     * Callback function for dynamic property _pos
     *
     * @param array $p_row
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function retrievePositionInRack(array $p_row)
    {
        if (!isset($p_row['isys_catg_location_list__id'])) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $language = isys_application::instance()->container->get('language');

        $catDataCurrentObject = $this->get_data($p_row['isys_catg_location_list__id'])->get_row();
        $parentObjectId = $catDataCurrentObject['isys_catg_location_list__parentid'];
        $currentObjectId = $catDataCurrentObject['isys_catg_location_list__isys_obj__id'];
        $currentObjectPosition = $catDataCurrentObject['isys_catg_location_list__pos'];
        $currentObjectOption = $catDataCurrentObject['isys_catg_location_list__option'];
        $enclosureSorting = 'desc';

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $enclosureSorting = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $parentObjectId)
                ->get_row_value('isys_cats_enclosure_list__slot_sorting');
        }

        if ($currentObjectOption == C__RACK_INSERTION__VERTICAL) {
            if (!$currentObjectPosition && Context::instance()->getContextCustomer() === Context::CONTEXT_REPORT) {
                return '';
            }

            return 'Slot #' . $currentObjectPosition;
        }

        if ($currentObjectOption == C__RACK_INSERTION__HORIZONTAL) {
            $daoFormFactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component());
            $maxRackUnits = $daoFormFactor->get_rack_hu($parentObjectId);
            $deviceUnits = $daoFormFactor->get_rack_hu($currentObjectId);

            if ($deviceUnits > 0) {
                switch ($enclosureSorting) {
                    case 'asc':
                        $startPosition = $maxRackUnits - ($maxRackUnits - $currentObjectPosition);
                        $endPosition = $maxRackUnits - ($maxRackUnits - $currentObjectPosition - ($deviceUnits - 1));

                        break;
                    case 'desc':
                    default:
                        $currentObjectPosition--;
                        $startPosition = $maxRackUnits - $currentObjectPosition;
                        $endPosition = $startPosition - ($deviceUnits - 1);

                        break;
                }
                return $language->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $startPosition . ' &rarr; ' . $endPosition;
            } else {
                return $language->get('LC__CMDB__CATG__RACKUNITS_ABBR') . ' ' . $currentObjectPosition;
            }
        }
    }

    /**
     * Callback method for the position dialog-field.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_pos(isys_request $p_request)
    {
        $l_return = [];

        $objectHeight = 1;
        $daoFormfactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component());
        $l_by_ajax = $p_request->get_data('ajax', false);

        if ($l_by_ajax) {
            $rackObjectId = $p_request->get_object_id();
        } else {
            $row = $this->get_data($p_request->get_category_data_id())->get_row();
            $objectHeight = $daoFormfactor
                ->get_data(null, $row['isys_catg_location_list__isys_obj__id'])
                ->get_row_value('isys_catg_formfactor_list__rackunits') ?? 1;

            $rackObjectId = $row['isys_catg_location_list__parentid'];
        }

        $language = isys_application::instance()->container->get('language');

        $heightUnits = $rackHeight = $daoFormfactor->get_rack_hu($rackObjectId);

        $l_dao_loc = isys_cmdb_dao_category_g_location::instance($this->get_database_component());
        $l_res = $l_dao_loc->get_data(null, null, 'AND isys_catg_location_list__parentid = ' . $l_dao_loc->convert_sql_id($rackObjectId) .
            ' AND isys_catg_location_list__option = ' . $l_dao_loc->convert_sql_int(C__RACK_INSERTION__HORIZONTAL) .
            ' AND isys_catg_location_list__pos > 0' .
            ' ORDER BY isys_catg_location_list__pos ASC');

        $l_rack = [];

        while ($rackHeight !== null && $rackHeight >= 0) {
            if (empty($l_row)) {
                $l_row = $l_res->get_row();
            }

            if ($rackHeight == $heightUnits - $l_row['isys_catg_location_list__pos']) {
                $l_rack_length = $daoFormfactor->get_rack_hu($l_row['isys_catg_location_list__isys_obj__id']);

                $insertion = (int)$l_row['isys_catg_location_list__insertion'] ;

                $l_insertion = match ($insertion) {
                    C__INSERTION__REAR => C__INSERTION__REAR,
                    C__INSERTION__FRONT => C__INSERTION__FRONT,
                    C__INSERTION__BOTH => C__INSERTION__BOTH,
                    default => null,
                };

                $l_string = match ($insertion) {
                    C__INSERTION__REAR => $language->get('LC__CATG__LOCATION__BACKSIDE_OCCUPIED'),
                    C__INSERTION__FRONT => $language->get('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED'),
                    C__INSERTION__BOTH => $language->get('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED'),
                    default => '',
                };

                $l_start_from = $rackHeight - 1;
                $l_index_start = $l_row['isys_catg_location_list__pos'] + 1;
                while ($l_rack_length > 1) {
                    if ($l_by_ajax) {
                        $l_rack[] = [
                            'rack_index' => $l_index_start,
                            'rack_pos'   => $l_start_from + 1,
                            'value'      => $l_string,
                            'insertion'  => $l_insertion
                        ];
                    } else {
                        $l_rack[$l_start_from + 1] = $l_insertion;
                    }

                    $l_rack_length--;
                    $l_index_start++;
                    $l_start_from--;
                }

                if ($l_by_ajax) {
                    $l_rack[] = [
                        'rack_index' => $l_row['isys_catg_location_list__pos'],
                        'rack_pos'   => $rackHeight + 1,
                        'value'      => $l_string,
                        'insertion'  => $l_insertion
                    ];
                } else {
                    $l_rack[$rackHeight + 1] = $l_insertion;
                }

                $l_row = $l_res->get_row();
            }

            $rackHeight--;
        }

        if ($l_by_ajax) {
            return [
                'units'          => $heightUnits,
                'assigned_units' => $l_rack
            ];
        }

        $rackData = [];
        $enclosureExists = class_exists('isys_cmdb_dao_category_s_enclosure');

        if ($enclosureExists) {
            $rackData = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $rackObjectId)
                ->get_row();
        }

        $ascending = isset($rackData['isys_cats_enclosure_list__slot_sorting']) ?
            $rackData['isys_cats_enclosure_list__slot_sorting'] === 'asc' : true;
        $abbr = $language->get('LC__CMDB__CATG__RACKUNITS_ABBR');

        $isMultiedit = Context::instance()->getContextTechnical() === Context::CONTEXT_MULTIEDIT;
        $locationRow = $this->get_data($p_request->get_category_data_id(), $p_request->get_object_id())->get_row();

        // @see ID-9070 This whole IF is only necessary for multiedit in order to show taken 'vertical' slots on-load.
        if ($enclosureExists && $isMultiedit && is_array($locationRow) && $locationRow['isys_catg_location_list__option'] == C__RACK_INSERTION__VERTICAL) {
            $takenSlots = [];
            // Fetch all other vertically assigned objects of the same rack.
            $result = $l_dao_loc->get_data(null, null, 'AND isys_catg_location_list__parentid = ' . $l_dao_loc->convert_sql_id($locationRow['isys_catg_location_list__parentid']) .
                ' AND isys_catg_location_list__option = ' . $l_dao_loc->convert_sql_int(C__RACK_INSERTION__VERTICAL) .
                ' AND isys_catg_location_list__insertion = ' . $l_dao_loc->convert_sql_int($locationRow['isys_catg_location_list__insertion']) .
                ' AND isys_catg_location_list__pos > 0');

            $verticalSlots = $locationRow['isys_catg_location_list__insertion'] == C__INSERTION__FRONT
                ? $rackData['isys_cats_enclosure_list__vertical_slots_front']
                : $rackData['isys_cats_enclosure_list__vertical_slots_rear'];

            while ($otherObject = $result->get_row()) {
                $takenSlots[(int)$otherObject['isys_catg_location_list__pos']] = true;
            }

            for ($i = 1; $i <= $verticalSlots; $i++) {
                $infoString = '-';

                if (isset($takenSlots[$i])) {
                    $infoString = $locationRow['isys_catg_location_list__insertion'] == C__INSERTION__FRONT
                        ? $language->get('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED')
                        : $language->get('LC__CATG__LOCATION__BACKSIDE_OCCUPIED');
                }

                $l_return[$i] = "Slot #{$i}: {$infoString}";
            }
        } else {
            // Get all possible hu positions from rack.
            for ($i = $heightUnits; $i >= 1; $i--) {
                $from = $i;
                $to = $from - ($objectHeight - 1);

                if ($ascending) {
                    $from = ($heightUnits + 1) - $i;
                    $to = $from + ($objectHeight - 1);
                }

                $infoString = '-';

                if (array_key_exists($i, $l_rack)) {
                    $infoString = match ((int)$l_rack[$i]) {
                        C__INSERTION__FRONT => $language->get('LC__CATG__LOCATION__FRONTSIDE_OCCUPIED'),
                        C__INSERTION__REAR => $language->get('LC__CATG__LOCATION__BACKSIDE_OCCUPIED'),
                        C__INSERTION__BOTH => $language->get('LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED'),
                    };
                }

                if ($from !== $to) {
                    $l_return[$heightUnits - $i + 1] = "{$abbr} {$from} &rarr; {$to}: {$infoString}";
                } else {
                    $l_return[$heightUnits - $i + 1] = "{$abbr} {$from}: {$infoString}";
                }
            }
        }


        return $l_return;
    }

    /**
     * @param isys_request $request
     *
     * @return array
     * @throws Exception
     */
    public function callback_property_insertion(isys_request $request): array
    {
        $language = isys_application::instance()->container->get('language');

        // @see ID-9070 Only allow 'horizontal' option to get 'front and back'.
        $catData = $this
            ->get_data($request->get_category_data_id(), $request->get_object_id())
            ->get_row_value('isys_catg_location_list__option');

        if ($catData && $catData == C__RACK_INSERTION__VERTICAL) {
            return [
                C__INSERTION__FRONT => $language->get('LC__CMDB__CATG__LOCATION_FRONT'),
                C__INSERTION__REAR  => $language->get('LC__CMDB__CATG__LOCATION_BACK')
            ];
        }

        return [
            C__INSERTION__FRONT => $language->get('LC__CMDB__CATG__LOCATION_FRONT'),
            C__INSERTION__REAR  => $language->get('LC__CMDB__CATG__LOCATION_BACK'),
            C__INSERTION__BOTH  => $language->get('LC__CMDB__CATG__LOCATION_BOTH')
        ];
    }

    /**
     * Method for finding a free slot for a given object in a given rack.
     *
     * @param int  $p_rack_id
     * @param int  $p_insertion
     * @param int  $p_object_to_assign
     * @param int  $p_option
     * @param string|null $p_rackSort
     * @param bool $onlyFree
     * @return array
     * @throws isys_exception_database
     */
    public function get_free_rackslots(
        $p_rack_id,
        $p_insertion,
        $p_object_to_assign,
        $p_option,
        $p_rackSort = null,
        bool $onlyFree = false,
    ) {
        $l_return = $l_used_slots = [];

        // @see ID-9070 Don't return results if option or insertion are unset.
        if ($p_option == -1 || $p_insertion == -1) {
            return $l_return;
        }

        $p_insertion = (int)$p_insertion;

        $l_sort_asc = (isys_tenantsettings::get('cmdb.rack.slot-assignment-sort-direction', 'asc') === 'asc');
        $language = isys_application::instance()->container->get('language');

        if (class_exists('isys_cmdb_dao_category_s_enclosure')) {
            $l_rack = isys_cmdb_dao_category_s_enclosure::instance($this->get_database_component())
                ->get_data(null, $p_rack_id)
                ->get_row();
        }

        // @See ID-4676
        if ($p_rackSort !== null) {
            $l_rack['isys_cats_enclosure_list__slot_sorting'] = $p_rackSort;
        }

        $l_obj = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_object_to_assign)
            ->get_row();

        $l_positions = $this->get_positions_in_rack($p_rack_id);

        if ($p_option == C__RACK_INSERTION__HORIZONTAL) {
            $l_obj_height = (int)($l_obj['isys_catg_formfactor_list__rackunits'] ?: 1);

            $l_units = $l_positions['units'];
            $l_assigned_objects = $l_positions['assigned_units'];
            $l_chassis = [];

            if (is_array($l_assigned_objects)) {
                // Here we write the used slots in an array, so we can check it later with "in_array()".
                foreach ($l_assigned_objects as $l_slot) {
                    if ($l_slot['insertion'] === null || $l_slot['option'] == C__RACK_INSERTION__VERTICAL || $l_slot['option'] == null) {
                        continue;
                    }

                    $self = $l_slot['obj_id'] == $p_object_to_assign ? ' (LC__CATG__LOCATION__OCCUPIED_BY_SELF)' : '';

                    if ($l_slot['is_chassis']) {
                        for ($i = 0; $i < $l_slot['height']; $i++) {
                            $l_chassis[($l_slot['pos'] + $i) . '-' . $l_slot['insertion']] = [
                                'id'     => $l_slot['obj_id'],
                                'title'  => $l_slot['title'] . $self,
                                'height' => $l_slot['height'],
                            ];
                        }
                    }

                    if ($l_slot['height'] >= 1) {
                        for ($i = 0; $i < $l_slot['height']; $i++) {
                            $l_used_slots[($l_slot['pos'] + $i) . '-' . $l_slot['insertion']] = $l_slot['title'] . $self;
                        }
                    }
                }
            }

            for ($i = 1; $i <= $l_units; $i++) {
                if ($l_rack['isys_cats_enclosure_list__slot_sorting'] === 'desc') {
                    $l_num = ($l_units - $i) + 1;
                    $l_num_to = $l_num - $l_obj_height + 1;

                    if ($l_num_to < 1) {
                        continue;
                    }
                } else {
                    $l_num = $i;
                    $l_num_to = $i + $l_obj_height - 1;

                    if ($l_num_to > $l_units) {
                        continue;
                    }
                }

                if (isset($l_chassis["{$i}-{$p_insertion}"])) {
                    $chassisHeight = (int)($l_chassis["{$i}-{$p_insertion}"]['height'] ?? 1);
                    $chassisTitle = $l_chassis["{$i}-{$p_insertion}"]['title'];

                    $from = $l_num;
                    $to = ($l_num + $chassisHeight) - 1;
                    $key = "{$i};{$from};{$to}";

                    if ($chassisHeight === 1) {
                        $l_return[$key] = $language->get_in_text("LC__CMDB__CATG__RACKUNITS_ABBR {$from}: {$chassisTitle}");
                    } else {
                        if ($l_sort_asc) {
                            $l_return[$key] = $language->get_in_text("LC__CMDB__CATG__RACKUNITS_ABBR {$from} &rarr; {$to}: {$chassisTitle}");
                        } else {
                            $from = ($l_num + $chassisHeight) - 1;
                            $to = $l_num;
                            $key = "{$i};{$to};{$from}";

                            $l_return[$key] = $language->get_in_text("LC__CMDB__CATG__RACKUNITS_ABBR {$from} &rarr; {$to}: $chassisTitle");
                        }
                    }

                    $i += ($chassisHeight - 1);

                    continue;
                }

                // If the current row is in use, we don't need process the next lines.
                $occupied = $this->getPositionedObjectName($l_used_slots, $i, $p_insertion);

                $l_tmp_to = $i + $l_obj_height - 1;

                for ($l_tmp = $i; $l_tmp <= $l_tmp_to; $l_tmp++) {
                    $used = $this->getPositionedObjectName($l_used_slots, $l_tmp, $p_insertion);

                    if ($used !== null) {
                        $occupied = $used;
                    }
                }

                if ($onlyFree && !empty($occupied)) {
                    continue;
                }

                $occupied = $occupied ?? '-';

                if ($l_obj_height === 1) {
                    $l_return["{$i};{$l_num};{$l_num}"] = $language->get_in_text(
                        "LC__CMDB__CATG__RACKUNITS_ABBR {$l_num}: {$occupied}",
                    );
                } else {
                    if ($l_sort_asc) {
                        $l_return["{$i};{$l_num};{$l_num_to}"] = $language->get_in_text(
                            "LC__CMDB__CATG__RACKUNITS_ABBR {$l_num} &rarr; {$l_num_to}: {$occupied}",
                        );
                    } else {
                        $l_return["{$i};{$l_num};{$l_num_to}"] = $language->get_in_text(
                            "LC__CMDB__CATG__RACKUNITS_ABBR {$l_num_to} &rarr; {$l_num}: {$occupied}",
                        );
                    }
                }
            }
        } else {
            if (isset($l_positions['assigned_units']) && is_array($l_positions['assigned_units'])) {
                foreach ($l_positions['assigned_units'] as $l_slot) {
                    // Skip horizontal slots.
                    if ($l_slot['option'] == C__RACK_INSERTION__HORIZONTAL) {
                        continue;
                    }

                    // Skip other insertions.
                    if ($l_slot['insertion'] === null || $l_slot['insertion'] != $p_insertion) {
                        continue;
                    }

                    if ($l_slot['option'] == null) {
                        continue;
                    }

                    $self = $l_slot['obj_id'] == $p_object_to_assign ? ' (LC__CATG__LOCATION__OCCUPIED_BY_SELF)' : '';

                    $addition = match($p_insertion) {
                        C__INSERTION__FRONT => 'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED',
                        C__INSERTION__REAR => 'LC__CATG__LOCATION__BACKSIDE_OCCUPIED',
                        C__INSERTION__BOTH => 'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED',
                    };

                    $l_used_slots[$l_slot['pos']] = $language->get_in_text(($l_slot['title'] ?? $addition) . $self);
                }
            }

            $column = $p_insertion === C__INSERTION__REAR ? '_rear' : '_front';

            for ($i = 1; $i <= $l_rack['isys_cats_enclosure_list__vertical_slots' . $column]; $i++) {
                $addition = $l_used_slots[$i] ?? '-';

                $l_return["{$i};{$i}"] = "Slot #{$i}: {$addition}";
            }
        }

        return $l_return;
    }

    /**
     * Returns the options for the "position in rack" dialog.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_positions_in_rack($p_obj_id)
    {
        $l_formfactor = isys_cmdb_dao_category_g_formfactor::instance($this->get_database_component())
            ->get_data(null, $p_obj_id)
            ->get_row();

        $l_return = [
            'units'          => $l_formfactor['isys_catg_formfactor_list__rackunits'],
            'assigned_units' => []
        ];

        $l_res = isys_cmdb_dao_location::instance($this->get_database_component())
            ->get_location($p_obj_id, null);

        while ($l_row = $l_res->get_row()) {
            $l_return['assigned_units'][] = [
                'obj_id'     => $l_row['isys_obj__id'],
                'title'      => $l_row['isys_obj__title'],
                'height'     => $l_row['isys_catg_formfactor_list__rackunits'] ?: 1,
                'option'     => $l_row['isys_catg_location_list__option'],
                'pos'        => $l_row['isys_catg_location_list__pos'],
                'insertion'  => $l_row['isys_catg_location_list__insertion'],
                'is_chassis' => $this->objtype_is_cats_assigned($l_row['isys_obj_type__id'], defined_or_default('C__CATS__CHASSIS'))
            ];
        }

        return $l_return;
    }

    /**
     * Method for retrieving the location parent of the given object.
     *
     * @param   integer $p_obj_id
     *
     * @return  mixed
     * @throws  isys_exception_database
     */
    public function get_parent_id_by_object($p_obj_id)
    {
        if ($p_obj_id > 0) {
            if (isset(self::$m_location_cache[$p_obj_id])) {
                return self::$m_location_cache[$p_obj_id]['parent'];
            }

            $l_sql = 'SELECT isys_catg_location_list__parentid AS parent,
				isys_obj__title AS title
				FROM isys_obj
				LEFT JOIN isys_catg_location_list ON isys_catg_location_list__isys_obj__id = isys_obj__id
				WHERE isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            $l_parent = $this->retrieve($l_sql)
                ->get_row();

            if (is_array($l_parent)) {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => $l_parent['title'],
                    'parent' => $l_parent['parent'] ?: false
                ];
            } else {
                self::$m_location_cache[$p_obj_id] = [
                    'title'  => null,
                    'parent' => false
                ];
            }

            return self::$m_location_cache[$p_obj_id]['parent'];
        }

        return false;
    }

    /**
     * @param null $p_filter
     * @param int  $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_container_objects($p_filter = null, $p_status = C__RECORD_STATUS__NORMAL, $p_consider_rights = false)
    {
        $l_filter = '';

        if ($p_consider_rights) {
            $l_filter = isys_auth_cmdb_objects::instance()
                ->get_allowed_objects_condition();
        }

        $l_filter .= ' AND isys_obj__status = ' . $this->convert_sql_int($p_status);

        if ($p_filter !== null) {
            $l_filter .= ' AND isys_obj__title LIKE ' . $this->convert_sql_text('%' . $p_filter . '%');
        }

        return $this->get_data(null, null, 'AND isys_obj_type__container = 1' . $l_filter, null, $p_status);
    }

    /**
     * @param   integer $p_rack_obj_id
     * @param   boolean $p_front
     *
     * @return  isys_component_dao_result
     * @throws  Exception
     * @throws  isys_exception_database
     */
    public function get_rack_positions($p_rack_obj_id, $p_front = true)
    {
        $l_sql = 'SELECT * FROM isys_catg_location_list
			INNER JOIN isys_catg_global_list ON isys_catg_location_list__isys_obj__id = isys_catg_global_list__isys_obj__id
			WHERE isys_catg_location_list__pos > 0
			AND isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_rack_obj_id);

        if ($p_front) {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 1);';
        } else {
            $l_sql .= ' AND (isys_catg_location_list__insertion = 0);';
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Save global category location element
     *
     * @param   integer $p_cat_level        Level to save, default 0.
     * @param   integer &$p_intOldRecStatus __status of record before update.
     * @param   boolean $p_create           Decides whether to create or to save.
     *
     * @return  null
     * @author  Andre Woesten <awoesten@i-doit.org>
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        $l_catdata = $this->get_general_data();

        $p_intOldRecStatus = $l_catdata["isys_catg_location_list__status"];
        $l_oldParent = $l_catdata["isys_catg_location_list__parentid"];

        if (!empty($l_catdata["isys_catg_location_list__id"])) {
            $coord = null;
            if ($_POST["C__CATG__LOCATION_LATITUDE"] || $_POST["C__CATG__LOCATION_LONGITUDE"]) {
                $coord = new Coordinate([
                    str_replace(',', '.', $_POST["C__CATG__LOCATION_LATITUDE"]) ?: 0,
                    str_replace(',', '.', $_POST["C__CATG__LOCATION_LONGITUDE"]) ?: 0
                ]);
            }

            $l_return = $this->save(
                $l_catdata["isys_catg_location_list__id"],
                $l_catdata["isys_catg_location_list__isys_obj__id"],
                $_POST['C__CATG__LOCATION_PARENT__HIDDEN'],
                $l_oldParent,
                $_POST["C__CATG__LOCATION_POS"],
                $_POST["C__CATG__LOCATION_INSERTION"],
                $_POST["C__CATG__LOCATION_IMAGE"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $_POST['C__CATG__LOCATION_OPTION'],
                $coord,
                $_POST['C__CATG__LOCATION_SLOT__selected_values'],
                $_POST["C__CATG__LOCATION_SNMP_SYSLOCATION"]
            );

            return $l_return;
        }

        return null;
    }

    /**
     * Method for saving a object to
     *
     * @param integer $p_object_id
     * @param string  $p_slot
     *
     * @return bool
     */
    public function save_object_to_segment($p_object_id, $p_slot)
    {
        // Instead: assign this object to the segment object and assign the slot.
        $l_chassis_dao = isys_cmdb_dao_category_s_chassis::instance($this->m_db);
        $l_chassis_slot_dao = isys_cmdb_dao_category_s_chassis_slot::instance($this->m_db);

        $l_chassis_object = $l_chassis_slot_dao->get_data($p_slot)
            ->get_row();

        // First we assign the object to the chassis
        isys_cmdb_dao_location::instance($this->m_db)
            ->attach($p_object_id, $l_chassis_object['isys_obj__id']);

        $l_slots = [];
        $l_slot_raw = explode(',', $p_slot);

        foreach ($l_slot_raw as $l_slot) {
            $l_slots[] = ['id' => $l_slot];
        }

        // Delete all old connections between chassis and $p_object_id.
        $l_slots_result = $l_chassis_dao->get_slots_by_assiged_object($p_object_id);

        while ($l_slots_row = $l_slots_result->get_row()) {
            $l_chassis_dao->rank_record($l_slots_row['isys_cats_chassis_list__id'], C__CMDB__RANK__DIRECTION_DELETE, 'isys_cats_chassis_list', null, true);
        }

        $l_chassis_dao->sync([
            'properties' => [
                'assigned_device' => [C__DATA__VALUE => $p_object_id],
                'assigned_slots'  => [C__DATA__VALUE => $l_slots]
            ]
        ], $l_chassis_object['isys_obj__id'], isys_import_handler_cmdb::C__CREATE);

        // Let the parent and relation be re-set by the sync method.
        $this->sync([
            'properties' => [
                'parent' => [C__DATA__VALUE => $l_chassis_object['isys_obj__id']]
            ]
        ], $p_object_id, isys_import_handler_cmdb::C__UPDATE);

        $this->setCacheFilesToRemove();

        return true;
    }

    /**
     * Creates the category entry.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_parent_object_id
     * @param   integer    $p_posID
     * @param   integer    $p_insertion
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_status
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     * @param   string     $p_slot
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function save_category(
        $p_list_id,
        $p_parent_object_id,
        $p_posID,
        $p_insertion = null,
        $p_unused = null,
        $p_description = '',
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_option = null,
        $p_coord = null,
        $p_slot = null,
        $snmpSysLocation = null
    ) {
        $l_data = $this->get_data($p_list_id)
            ->get_row();

        // In case of "create" context, the "$l_data" variable might be empty.
        $l_parent = ($p_parent_object_id ?: $l_data['isys_catg_location_list__parentid']);

        // @see ID-9423 Do not allow overlapping objects in a rack.
        if (isset($l_data['isys_catg_location_list__isys_obj__id'], $l_parent, $p_option, $p_insertion, $p_posID)) {
            $props = [
                'object'    => $l_data['isys_catg_location_list__isys_obj__id'],
                'parent'    => $l_parent,
                'option'    => $p_option,
                'insertion' => $p_insertion,
                'posID'     => $p_posID
            ];

            $props = array_map('intval', array_filter($props, fn ($value) => is_numeric($value) && $value >= 0));

            if (count($props) === 5 && $this->isRack($props['parent']) && !$this->isRackPositionAvailable($props['object'], $props['parent'], $props['option'], $props['insertion'], $props['posID'])) {
                throw new Exception(isys_application::instance()->container->get('language')->get('LC__SETTINGS__CMDB__RACK_ASSIGNMENT_NOT_POSSIBLE'));
            }
        }

        $l_parent_row = $this->get_data(null, $l_parent)
            ->get_row();

        if (empty($p_parent_object_id) && $l_parent_row['isys_obj_type__const'] === isys_tenantsettings::get('cmdb.rack.segment-template-object-type', 'C__OBJTYPE__RACK_SEGMENT')) {
            $chassisDao = new isys_cmdb_dao_category_s_chassis($this->m_db);
            $objects = $chassisDao->get_assigned_objects($l_parent_row['isys_catg_location_list__isys_obj__id']);
            foreach ($objects as $object) {
                if ($object['isys_obj__id'] === $l_data['isys_obj__id']) {
                    $chassisDao->remove_slot_assignments($object['isys_cats_chassis_list__id']);
                    $chassisDao->relations_remove($object['isys_cats_chassis_list__id'], $object['isys_cats_chassis_list__isys_obj__id'], $object['isys_obj__id']);
                    $chassisDao->delete_entry($object['isys_cats_chassis_list__id'], 'isys_cats_chassis_list');
                }
            }
            $p_slot = null;
        }
        if (!empty($p_slot) && !empty($l_parent_row) && $l_parent_row['isys_obj_type__const'] === isys_tenantsettings::get('cmdb.rack.segment-template-object-type', 'C__OBJTYPE__RACK_SEGMENT')) {
            return $this->save_object_to_segment($l_data['isys_obj__id'], $p_slot);
        }

        // @see ID-8899 This might happen in CSV import context, because '0' might be handled wrong.
        if ($p_insertion === null && $p_posID >= 0 && $p_option > 0) {
            $p_insertion = C__INSERTION__REAR;
        }

        // Reset Insertion, position and option if parent object has not the rack category or is empty or if the object type of the current object can not be positioned in a rack
        if (($p_parent_object_id > 0 && $l_parent_row['isys_obj_type__isysgui_cats__id'] != defined_or_default('C__CATS__ENCLOSURE')) || empty($p_parent_object_id) ||
            !$l_data['isys_obj_type__show_in_rack']) {
            $p_insertion = null;
            $p_posID = null;
            $p_option = null;
        } elseif ($p_parent_object_id > 0 && $l_parent_row['isys_obj_type__isysgui_cats__id'] == defined_or_default('C__CATS__ENCLOSURE') && class_exists('isys_cmdb_dao_category_s_enclosure')) {
            // check if data in the specific category exists
            $rackDao = isys_cmdb_dao_category_s_enclosure::instance(isys_application::instance()->database);
            $parentRackData = $rackDao->get_data(null, $p_parent_object_id)
                ->get_row();
            if (!$parentRackData) {
                // Create default entry in specific category
                $rackDao->create_data([
                    'isys_obj__id'         => $p_parent_object_id,
                    'vertical_slots_front' => 0,
                    'vertical_slots_rear'  => 0,
                    'slot_sorting'         => 'asc',
                    'status' => C__RECORD_STATUS__NORMAL
                ]);
            }
        }

        if (!$p_coord) {
            $p_coord = null;
        }

        // @see ID-8899 Before saving the position in a rack, validate this data!
        if ($p_option == C__RACK_INSERTION__VERTICAL && $p_parent_object_id > 0 && $p_insertion !== null) {
            if ($this->hasVerticalSlots((int)$p_parent_object_id, (int)$p_insertion, (int)$p_posID)) {
                // The object will be assigned, clear any previously assigned object.
                $this->detachPreviouslyConnected((int)$l_data['isys_obj__id'], (int)$p_parent_object_id, (int)$p_insertion, (int)$p_posID);
            } else {
                // The vertical slot can not be used, remove the positioning data.
                $p_option = null;
                $p_insertion = 0;
                $p_posID = null;
            }
        }

        $l_strSQL = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__parentid = " . $this->convert_sql_id($p_parent_object_id) . ",
			isys_catg_location_list__pos = " . $this->convert_sql_id($p_posID) . ",
			isys_catg_location_list__insertion = " . ($p_insertion === null ? $this->convert_sql_id($p_insertion) : $this->convert_sql_int($p_insertion)) . ",
			isys_catg_location_list__gps = " . $this->convert_sql_point($p_coord) . ",
			isys_catg_location_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_location_list__status = " . $this->convert_sql_id($p_status) . ",
			isys_catg_location_list__option = " . $this->convert_sql_id($p_option) . ",
			isys_catg_location_list__snmp_syslocation = " . $this->convert_sql_text($snmpSysLocation) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_list_id) . ";";

        $this->m_strLogbookSQL = $l_strSQL;

        $l_bRet = $this->update($l_strSQL) && $this->apply_update();

        if ($l_bRet) {
            // Create implicit relation.
            try {
                if ($p_parent_object_id > 0) {
                    isys_cmdb_dao_category_g_relation::instance($this->m_db)
                        ->handle_relation(
                            $p_list_id,
                            "isys_catg_location_list",
                            defined_or_default('C__RELATION_TYPE__LOCATION'),
                            $l_data["isys_catg_location_list__isys_catg_relation_list__id"],
                            $p_parent_object_id,
                            $l_data["isys_catg_location_list__isys_obj__id"]
                        );
                }
            } catch (Exception $e) {
                throw $e;
            }
        }
        $this->setCacheFilesToRemove();

        return $l_bRet;
    }

    /**
     * Creates the category entry.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_object_id
     * @param   integer    $p_parent_object_id
     * @param   integer    $p_posID
     * @param   integer    $p_insertion
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_status
     * @param   Coordinate $p_coord
     * @param   integer    $p_option
     *
     * @throws  Exception
     * @throws  isys_exception_dao
     * @return  integer
     */
    public function create_category(
        $p_list_id,
        $p_object_id,
        $p_parent_object_id,
        $p_posID,
        $p_insertion,
        $p_unused = null,
        $p_description = '',
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_coord = null,
        $p_option = null,
        $snmpSysLocation = null
    ) {
        // @see ID-9351 Previously we'd set the coordinate to 'new Coordinate([0, 0])'.
        if (!$p_coord) {
            $p_coord = null;
        }

        // @see ID-8899 This might happen in CSV import context, because '0' might be handled wrong.
        if ($p_insertion === null && $p_posID >= 0 && $p_option > 0) {
            $p_insertion = C__INSERTION__REAR;
        }

        // @see ID-8899 Before saving the position in a rack, validate this data!
        if ($p_option == C__RACK_INSERTION__VERTICAL && $p_parent_object_id > 0 && $p_insertion !== null) {
            if ($this->hasVerticalSlots((int)$p_parent_object_id, (int)$p_insertion, (int)$p_posID)) {
                // The object will be assigned, clear any previously assigned object.
                $this->detachPreviouslyConnected((int)$p_object_id, (int)$p_parent_object_id, (int)$p_insertion, (int)$p_posID);
            } else {
                // The vertical slot can not be used, remove the positioning data.
                $p_option = 0;
                $p_insertion = null;
                $p_posID = null;
            }
        }

        // @see ID-9423 Do not allow overlapping objects in a rack.
        if (isset($p_object_id, $p_parent_object_id, $p_option, $p_insertion, $p_posID)) {
            $props = [
                'object'    => $p_object_id,
                'parent'    => $p_parent_object_id,
                'option'    => $p_option,
                'insertion' => $p_insertion,
                'posID'     => $p_posID
            ];

            $props = array_map('intval', array_filter($props, fn ($value) => is_numeric($value) && $value >= 0));

            if (count($props) === 5 && $this->isRack($props['parent']) && !$this->isRackPositionAvailable($props['object'], $props['parent'], $props['option'], $props['insertion'], $props['posID'])) {
                throw new Exception(isys_application::instance()->container->get('language')->get('LC__SETTINGS__CMDB__RACK_ASSIGNMENT_NOT_POSSIBLE'));
            }
        }

        $l_sql = 'INSERT IGNORE INTO isys_catg_location_list SET
			isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . ',
			isys_catg_location_list__parentid = ' . $this->convert_sql_id($p_parent_object_id) . ',
			isys_catg_location_list__gps = ' . $this->convert_sql_point($p_coord) . ',
			isys_catg_location_list__pos = ' . $this->convert_sql_id($p_posID) . ',
			isys_catg_location_list__option = ' . $this->convert_sql_int($p_option) . ',
			isys_catg_location_list__insertion = ' . ($p_insertion === null ? $this->convert_sql_id($p_insertion) : $this->convert_sql_int($p_insertion)) . ',
			isys_catg_location_list__description = ' . $this->convert_sql_text($p_description) . ',
			isys_catg_location_list__snmp_syslocation = ' . $this->convert_sql_text($snmpSysLocation) . ',
			isys_catg_location_list__status = ' . $this->convert_sql_int($p_status) . ';';

        $this->update($l_sql) && $this->apply_update();
        $this->m_strLogbookSQL .= $l_sql;
        $l_last_id = $this->get_last_insert_id();

        // Create implicit relation.
        try {
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);

            if (!empty($p_parent_object_id)) {
                $l_dao_relation->handle_relation($l_last_id, 'isys_catg_location_list', defined_or_default('C__RELATION_TYPE__LOCATION'), null, $p_parent_object_id, $p_object_id);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->setCacheFilesToRemove();

        return $l_last_id;
    }

    /**
     * @param int $rackObject
     * @param int $insertion
     * @param int $position
     *
     * @return void
     * @throws isys_exception_database
     */
    private function detachPreviouslyConnected(int $objectId, int $rackObject, int $insertion, int $position): void
    {
        $option = $this->convert_sql_id(C__RACK_INSERTION__VERTICAL);

        $query = "SELECT isys_catg_location_list__id AS id
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__parentid = {$rackObject}
            AND isys_catg_location_list__isys_obj__id != {$objectId}
            AND isys_catg_location_list__option = {$option}
            AND isys_catg_location_list__insertion = {$insertion}
            AND isys_catg_location_list__pos = {$position}
            LIMIT 1;";

        $entryId = $this->retrieve($query)->get_row_value('id');

        if ($entryId === null) {
            return;
        }

        $query = "UPDATE isys_catg_location_list
            SET isys_catg_location_list__option = null,
            isys_catg_location_list__insertion = null,
            isys_catg_location_list__pos = null
            WHERE isys_catg_location_list__id = {$this->convert_sql_id($entryId)}
            LIMIT 1;";

        $this->update($query) && $this->apply_update();
    }

    /**
     * This method checks if the given rack object can contain a given vertical slot.
     *
     * @param int $rackObject
     * @param int $insertion
     * @param int $position
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function hasVerticalSlots(int $rackObject, int $insertion, int $position): bool
    {
        $query = "SELECT isys_cats_enclosure_list__vertical_slots_front AS front, isys_cats_enclosure_list__vertical_slots_rear AS rear
            FROM isys_cats_enclosure_list
            WHERE isys_cats_enclosure_list__isys_obj__id = {$rackObject}
            LIMIT 1;";

        $row = $this->retrieve($query)->get_row();

        if ($row === null) {
            return false;
        }

        if ($insertion === C__INSERTION__FRONT && $position > $row['front']) {
            return false;
        }

        if ($insertion === C__INSERTION__REAR && $position > $row['rear']) {
            return false;
        }

        return true;
    }

    /**
     * More simple method for resetting a location.
     *
     * @param   integer $p_obj_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function reset_location($p_obj_id)
    {
        if ($p_obj_id > 0) {
            $l_sql = 'UPDATE isys_catg_location_list
                SET isys_catg_location_list__parentid = NULL
                WHERE isys_catg_location_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ';';

            $this->update($l_sql);
            $this->setCacheFilesToRemove();
        }
    }

    /**
     * Executes the operations to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer    $p_list_id
     * @param   integer    $p_objID
     * @param   integer    $p_parent_id
     * @param   integer    $p_oldParentID
     * @param   integer    $p_posID
     * @param   integer    $p_insertion
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     * @param   slot       $p_slot
     *
     * @return  boolean
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save(
        $p_list_id,
        $p_objID,
        $p_parent_id,
        $p_oldParentID = null,
        $p_posID = null,
        $p_insertion = null,
        $p_unused = null,
        $p_description = '',
        $p_option = null,
        $p_coord = null,
        $p_slot = null,
        $snmpSysLocation = null
    ) {
        if ($p_list_id > 0) {
            if ($p_parent_id != 'NULL' && $p_parent_id > 0) {
                if (!$this->obj_exists($p_parent_id)) {
                    throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parent_id));
                }

                if (empty($p_oldParentID)) {
                    $this->insert_node($p_list_id, $p_parent_id);
                } else {
                    $this->move_node($p_list_id, $p_parent_id);
                }
            } else {
                if ($p_oldParentID > 0) {
                    $this->delete_node($p_list_id);
                }
            }

            return $this->save_category(
                $p_list_id,
                $p_parent_id,
                $p_posID,
                $p_insertion,
                null,
                $p_description,
                C__RECORD_STATUS__NORMAL,
                $p_option,
                $p_coord,
                $p_slot,
                $snmpSysLocation
            );
        }

        return false;
    }

    /**
     * Executes the operations to create the category entry referenced by isys_obj__id $p_objID
     *
     * @param   integer    $p_objID
     * @param   integer    $p_parentID
     * @param   integer    $p_posID
     * @param   integer    $p_frontsideID
     * @param   null       $p_unused
     * @param   string     $p_description
     * @param   integer    $p_option
     * @param   Coordinate $p_coord
     *
     * @return  integer  The newly created ID or false
     * @throws  Exception
     * @throws  isys_exception_dao_cmdb
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function create(
        $p_objID,
        $p_parentID,
        $p_posID = null,
        $p_frontsideID = null,
        $p_unused = null,
        $p_description = '',
        $p_option = null,
        $p_coord = null,
        $snmpSysLocation = null
    ) {
        if ($p_parentID > 0) {
            if (!$this->obj_exists($p_parentID)) {
                throw new isys_exception_dao_cmdb(sprintf('Parent location with id %s does not exist.', $p_parentID));
            }
        }

        $l_insID = $this->create_category(
            null,
            $p_objID,
            $p_parentID,
            $p_posID,
            $p_frontsideID,
            null,
            $p_description,
            C__RECORD_STATUS__NORMAL,
            $p_coord,
            $p_option,
            $snmpSysLocation
        );

        if ($p_parentID != null) {
            $this->insert_node($l_insID, $p_parentID);
        }

        return $l_insID;
    }

    /**
     * Get whole location tree with one query.
     *
     * @return  isys_component_dao_result
     */
    public function get_location_tree()
    {
        $l_sql = 'SELECT isys_obj__id AS id, a.isys_catg_location_list__parentid AS parentid, isys_obj__title AS title, isys_obj_type__id AS object_type_id, isys_obj_type__title AS object_type, a.isys_catg_location_list__isys_catg_relation_list__id AS relation_id
			FROM isys_catg_location_list a
			INNER JOIN isys_catg_location_list b ON a.isys_catg_location_list__parentid = b.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj ON isys_obj__id = a.isys_catg_location_list__isys_obj__id
			INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			ORDER BY a.isys_catg_location_list__parentid ASC';

        return $this->retrieve($l_sql);
    }

    /**
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_cached_locations($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            return self::$m_location_cache[$p_obj_id];
        }

        return self::$m_location_cache;
    }

    /**
     * Returns the location path of the given object. Will throw an RuntimeException on recursion!
     *
     * @param   integer $p_obj
     *
     * @return  array
     * @throws  RuntimeException
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_location_path($p_obj)
    {
        $l_return = [];
        $l_parentid = $p_obj;

        while (($l_parentid = $this->get_parent_id_by_object($l_parentid)) !== false) {
            if (in_array($l_parentid, $l_return)) {
                throw new RuntimeException(isys_application::instance()->container->get('language')
                        ->get('LC__CATG__LOCATION__RECURSION_IN_OBJECT') . ' #' . $l_parentid . ' "' . $this->get_obj_name_by_id_as_string($l_parentid) . '"');
            }

            if (defined('C__OBJ__ROOT_LOCATION') && $l_parentid != C__OBJ__ROOT_LOCATION) {
                $l_return[] = $l_parentid;
            }
        }

        return $l_return;
    }

    /**
     * Default Event triggered on updating the location nodes.
     *
     * @param   int    $p_nodeID
     * @param   int    $p_parentNodeID
     * @param   string $p_updatetype
     *
     * @author  Dennis Stücken <dstuecken@i-doit.com>
     */
    public function update_location_node($p_nodeID, $p_parentNodeID, $p_updatetype)
    {
        // Invalidate cache. This is needed for cached location rights:
        isys_cache::keyvalue()
            ->flush();
    }

    /**
     * Creates a new node in the lft-rgt-tree.
     *
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @throws  Exception
     */
    public function insert_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'insert');

        $l_query = "SELECT isys_catg_location_list__rgt
	        FROM isys_catg_location_list
	        WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";

        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_rgt = (empty($l_row["isys_catg_location_list__rgt"])) ? 0 : ((int)$l_row["isys_catg_location_list__rgt"]) - 1;

        $l_update = "UPDATE isys_catg_location_list SET
	        isys_catg_location_list__rgt = isys_catg_location_list__rgt + 2
	        WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + 2
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = " . $this->convert_sql_id($l_rgt + 1) . ",
			isys_catg_location_list__rgt = " . $this->convert_sql_id($l_rgt + 2) . "
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }
        $this->setCacheFilesToRemove();
    }

    /**
     * Delete a node from the tree.
     *
     * @param   integer $p_nodeID
     *
     * @throws  Exception
     */
    public function delete_node($p_nodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, null, 'delete');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_lft = (int)$l_row["isys_catg_location_list__lft"];
        $l_rgt = (int)$l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        if ($l_lft > 0 && $l_rgt > 0 && $l_rgt > $l_lft) {
            // Delete relations
            $l_dao_relation = new isys_cmdb_dao_category_g_relation($this->m_db);
            $l_sql = "SELECT isys_catg_location_list__isys_catg_relation_list__id
				FROM isys_catg_location_list
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";

            $l_res = $this->retrieve($l_sql);
            if ($l_res->num_rows() > 0) {
                while ($l_row = $l_res->get_row()) {
                    $l_dao_relation->delete_relation($l_row["isys_catg_location_list__isys_catg_relation_list__id"]);
                }
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = NULL,
				isys_catg_location_list__rgt = NULL
				WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
				WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $l_update = "UPDATE isys_catg_location_list SET
				isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
				WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
            if (!$this->update($l_update)) {
                throw new Exception($this->get_database_component()
                    ->get_last_error_as_string());
            }

            $this->setCacheFilesToRemove();
        }
    }

    /**
     * @param   integer $p_nodeID
     * @param   integer $p_parentNodeID
     *
     * @return  boolean
     * @throws  Exception
     */
    public function move_node($p_nodeID, $p_parentNodeID)
    {
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.beforeUpdateLocationNode', $p_nodeID, $p_parentNodeID, 'move');

        $l_query = "SELECT isys_catg_location_list__lft, isys_catg_location_list__rgt, isys_catg_location_list__parentid
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__id = " . $this->convert_sql_id($p_nodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_lft = (int)$l_row["isys_catg_location_list__lft"];
        $l_rgt = (int)$l_row["isys_catg_location_list__rgt"];
        $l_diff = $l_rgt - $l_lft + 1;

        $l_subElements = [];
        $l_query = "SELECT isys_catg_location_list__id
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__lft BETWEEN " . $l_lft . " AND " . $l_rgt . ";";
        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row()) {
            if ($l_row['isys_catg_location_list__id'] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                continue;
            }

            $l_subElements[] = $l_row['isys_catg_location_list__id'];
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt - " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft - " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgt . ";";
        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_query = "SELECT isys_catg_location_list__rgt, isys_catg_location_list__lft
			FROM isys_catg_location_list
			WHERE isys_catg_location_list__isys_obj__id = " . $this->convert_sql_id($p_parentNodeID) . ";";
        $l_row = $this->retrieve($l_query)
            ->get_row();

        $l_rgtNew = ((int)$l_row["isys_catg_location_list__rgt"]) - 1;
        $l_lftNew = ((int)$l_row["isys_catg_location_list__lft"]) + 1;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + " . $l_diff . "
			WHERE isys_catg_location_list__rgt > " . $l_rgtNew;

        if (count($l_subElements) > 0) {
            $l_in_query = implode(',', $l_subElements);
            $l_in_query = rtrim($l_in_query, ',');
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + " . $l_diff . "
			WHERE isys_catg_location_list__lft > " . $l_rgtNew;

        if (count($l_subElements) > 0) {
            $l_update .= ' AND isys_catg_location_list__id NOT IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }

        // TODO: Insert subtree at the new position
        $l_rgtNew += $l_diff;
        $l_diff = $l_rgtNew - $l_rgt;

        $l_update = "UPDATE isys_catg_location_list SET
			isys_catg_location_list__lft = isys_catg_location_list__lft + (" . $l_diff . "),
			isys_catg_location_list__rgt = isys_catg_location_list__rgt + (" . $l_diff . ")
			WHERE FALSE";

        if (count($l_subElements) > 0) {
            $l_update .= ' OR isys_catg_location_list__id IN (' . $l_in_query . ')';
        }

        if (!$this->update($l_update)) {
            throw new Exception($this->get_database_component()
                ->get_last_error_as_string());
        }
        $this->setCacheFilesToRemove();

        return $this->apply_update();
    }

    /**
     * @param array $p_data
     * @param mixed $p_prepend_table_field
     *
     * @return array|mixed|true
     * @throws Exception
     * @see ID-9423 Check if the position(s) are taken.
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $parentValidation = parent::validate($p_data, $p_prepend_table_field);
        $language = isys_application::instance()->container->get('language');

        if (!is_array($parentValidation)) {
            $parentValidation = [];
        }

        $rackPositionProps = [
            'object' => $this->get_object_id(),
            'parent' => null,
            'option' => null,
            'insertion' => null,
            'pos' => null
        ];

        foreach ($rackPositionProps as $prop => &$value) {
            if (isset($p_data[$prop][C__DATA__VALUE]) && $p_data[$prop][C__DATA__VALUE] >= 0) {
                $value = $p_data[$prop][C__DATA__VALUE];
            } elseif (is_numeric($p_data[$prop]) && $p_data[$prop] >= 0) {
                $value = $p_data[$prop];
            }
        }

        $rackPositionProps = array_map('intval', array_filter($rackPositionProps, 'is_numeric'));

        // @see ID-10358 Check for location loops.
        if (isset($rackPositionProps['object'], $rackPositionProps['parent']) && $rackPositionProps['object'] && $rackPositionProps['parent']) {
            if ($this->isAboutToCreateRecursion($rackPositionProps['object'], $rackPositionProps['parent'])) {
                $parentValidation['parent'] = $language->get('LC__CATG__LOCATION__VALIDATION__RECURSION');
            }

            if ($this->isAboutToSelfAssign($rackPositionProps['object'], $rackPositionProps['parent'])) {
                $parentValidation['parent'] = $language->get('LC__CATG__LOCATION__VALIDATION__SELF_ASSIGNMENT');
            }
        }

        if (count($rackPositionProps) === 5 && $this->isRack($rackPositionProps['parent']) && !$this->isRackPositionAvailable($rackPositionProps['object'], $rackPositionProps['parent'], $rackPositionProps['option'], $rackPositionProps['insertion'], $rackPositionProps['pos'])) {
            $parentValidation['pos'] = $language->get('LC__SETTINGS__CMDB__RACK_ASSIGNMENT_NOT_POSSIBLE');
        }

        return empty($parentValidation) ? true : $parentValidation;
    }

    /**
     * @param int $object
     * @param int $parent
     *
     * @return bool
     * @throws isys_exception_database
     */
    private function isAboutToCreateRecursion(int $object, int $parent): bool
    {
        // Get the LFT and RGT values from the current object.
        $mpttQuery = "SELECT isys_catg_location_list__lft AS lft, isys_catg_location_list__rgt AS rgt
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__isys_obj__id = {$object}
            LIMIT 1";

        $mptt = $this->retrieve($mpttQuery)->get_row();

        if ($mptt === null || !isset($mptt['lft'], $mptt['rgt'])) {
            return false;
        }

        $lft = $this->convert_sql_int($mptt['lft']);
        $rgt = $this->convert_sql_int($mptt['rgt']);

        // Check if the current object CONTAINS the newly selected parent - this will create a recursion and is not allowed.
        $sql = "SELECT isys_catg_location_list__isys_obj__id AS id
            FROM isys_catg_location_list
            WHERE isys_catg_location_list__isys_obj__id = {$parent}
            AND isys_catg_location_list__lft > {$lft}
            AND isys_catg_location_list__rgt < {$rgt};";

        return count($this->retrieve($sql)) > 0;
    }

    /**
     * @param int $object
     * @param int $parent
     *
     * @return bool
     */
    private function isAboutToSelfAssign(int $object, int $parent): bool
    {
        return $object === $parent;
    }

    /**
     * Checks whether location is well-defined. This helps to avoid self-
     * referencing, non-location objects, and referencing loops.
     *
     * @param int $p_obj_id      Object identifier
     * @param int $p_location_id Location identifier
     *
     * @return bool|string Returns true on success, otherwise error message.
     */
    public function validate_parent($p_obj_id, $p_location_id)
    {
        assert(is_int($p_obj_id) && $p_obj_id > 0);
        assert(is_int($p_location_id) && $p_location_id > 0);

        // Avoid self-referencing:
        if ($p_obj_id === $p_location_id) {
            return isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        }

        $l_sql = 'SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__container = 1';
        $l_res = $this->retrieve($l_sql);
        while ($l_row = $l_res->get_row()) {
            $l_valid_location_object_types[] = $l_row['isys_obj_type__id'];
        }

        // Location must be a location object:
        $l_obj_type = intval($this->get_objTypeID($p_location_id));
        if (!in_array($l_obj_type, $l_valid_location_object_types)) {
            return isys_application::instance()->container->get('language')
                ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
        }

        $l_parent_id = $p_location_id;
        $l_location_objects = [];

        // Walk through the location tree:
        while ($l_parent_id !== false) {
            // Object itself isn't part of the tree:
            if (in_array($p_obj_id, $l_location_objects)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Root location has no parent.
            if ($l_parent_id == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                break;
            }

            // Location isn't part of the tree:
            if (in_array($l_parent_id, $l_location_objects)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Parent must be a location object:
            $l_obj_type = intval($this->get_objTypeID($l_parent_id));
            if (!in_array($l_obj_type, $l_valid_location_object_types)) {
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
            }

            // Keep parent in mind:
            $l_location_objects[] = $l_parent_id;

            // Next one...
            $l_parent_id = intval($this->get_parent_id_by_object($l_parent_id));
        }

        return true;
    }

    /**
     * This method gets called before "category save" or "category create" by signal-slot-system.
     *
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @see     ID-4974  When saving the category, we check if the location changes need to be handled.
     */
    public function validate_before_save()
    {
        if (isys_tenantsettings::get('cmdb.chassis.handle-location-changes', false)) {
            $db = isys_application::instance()->container->get('database');
            $objectId = (int)$_GET[C__CMDB__GET__OBJECT];
            $parent = isset($_POST['C__CATG__LOCATION_PARENT__HIDDEN']) ? (int)$_POST['C__CATG__LOCATION_PARENT__HIDDEN'] : null;

            if ($parent === null) {
                $parent = $this->get_parent_id_by_object($objectId);
            }

            $chassisDao = isys_cmdb_dao_category_s_chassis::instance($db);
            $result = $chassisDao->get_slots_by_assiged_object($objectId);

            while ($row = $result->get_row()) {
                if ($row['isys_cats_chassis_slot_list__isys_obj__id'] != $parent) {
                    $chassisDao->relations_remove($row['isys_cats_chassis_list__id'], null, $objectId);
                }
            }
        }
    }

    /**
     * Method for retrieving the dynamic properties, used by the new list component.
     *
     * @return DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    protected function dynamic_properties()
    {
        return [
            '_location_path' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_PATH',
                'isys_catg_location_list__parentid',
                'isys_catg_location_list',
                [
                    $this,
                    'dynamic_property_callback_location_path'
                ]
            ),
            '_location_path_raw' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_PATH_RAW',
                'isys_catg_location_list__parentid',
                'isys_catg_location_list',
                [
                    $this,
                    'dynamic_property_callback_location_path_raw'
                ]
            ),
            '_pos' => new DynamicProperty(
                'LC__CMDB__CATG__LOCATION_POS',
                'isys_catg_location_list__id',
                'isys_catg_location_list',
                [
                    $this,
                    'retrievePositionInRack'
                ]
            )
        ];
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *, ST_AsText(isys_catg_location_list__gps) AS isys_catg_location_list__gps, ST_X(isys_catg_location_list__gps) AS latitude, ST_Y(isys_catg_location_list__gps) AS longitude FROM isys_catg_location_list
			INNER JOIN isys_obj
			ON isys_catg_location_list__isys_obj__id = isys_obj__id
			INNER JOIN isys_obj_type
			ON isys_obj__isys_obj_type__id = isys_obj_type__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= " AND isys_catg_location_list__id = " . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_catg_location_list__status = " . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql . ";");
    }

    /**
     * Method which builds the location path as query
     *
     * @param int    $count
     * @param int    $maxLengthLocationObjects
     * @param string $where
     * @param bool   $withoutObjectIdPlaceholder
     * @param string $appendix
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function build_location_path_query($count = 1, $maxLengthLocationObjects = 16, $where = '', $withoutObjectIdPlaceholder = false, $appendix = '..')
    {
        $l_separator = isys_tenantsettings::get('gui.separator.location', ' > ');

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $l_separator = '<span style="direction: ltr;width: 15px;display: inline-block;text-align: center;">' . $l_separator . '</span>';
        }

        $count = max((int) $count, 1);
        $locationSelect = 'CONCAT_WS(\'' . $l_separator . '\', ';

        $htmlLengthAll = 0;
        $htmlLengthSingleElement = 0;

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $htmlLengthSingleElement = strlen('<style>td[data-property="isys_cmdb_dao_category_g_location__location_path"] span.overflowable {direction: rtl;}</style>');

            $locationSelect .= ' CONCAT(\'<style>td[data-property="isys_cmdb_dao_category_g_location__location_path"] span.overflowable {direction: rtl;}</style>\', CONCAT_WS(\'' .
                $l_separator . '\', ';
        }

        $locationPaths = [];

        // Locationpath
        for ($i = $count;$i>0;$i--) {
            $locationPaths[$i] = "(SELECT CONCAT(IF(isys_obj__id!=1 && LENGTH(isys_obj__title)>$maxLengthLocationObjects" .
                ",CONCAT(SUBSTRING(isys_obj__title,1," . max($maxLengthLocationObjects-strlen($appendix), 0) . "),'$appendix'),isys_obj__title)" .
                (!$withoutObjectIdPlaceholder ? ",' {',isys_obj__id,'}'" : "") . ")
                FROM isys_obj WHERE sub$i.isys_catg_location_list__isys_obj__id = isys_obj__id)";

            $htmlLengthAll += $htmlLengthSingleElement;
        }

        if (isys_tenantsettings::get('gui.location_path.direction.rtl', false)) {
            $locationPaths = array_reverse($locationPaths);
        }

        $locationSelect = $locationSelect . implode(',', $locationPaths) . (isys_tenantsettings::get('gui.location_path.direction.rtl', false) ? '))' : '') . ') AS title';

        // JOINS
        $previous = "main";
        for ($i = 1;$i <= $count;$i++) {
            $joins[] = " LEFT JOIN isys_catg_location_list AS sub$i ON sub$i.isys_catg_location_list__isys_obj__id = $previous.isys_catg_location_list__parentid";
            $previous = "sub$i";
        }

        $joins[] = ' LEFT JOIN isys_obj as objMain ON objMain.isys_obj__id = main.isys_catg_location_list__isys_obj__id';

        return "SELECT $locationSelect FROM isys_catg_location_list AS main" . implode($joins) . $where;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        $language = isys_application::instance()->container->get('language');
        return [
            'location_path'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_PATH',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location path'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__parentid',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_obj__id FROM isys_catg_location_list',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__IMPORT     => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__VIRTUAL    => true
                ]
            ]),
            'parent'           => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location',
                    C__PROPERTY__INFO__BACKWARD_PROPERTY => 'isys_cmdb_dao_category_g_virtual_object::assigned_object',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD            => 'isys_catg_location_list__parentid',
                    C__PROPERTY__DATA__RELATION_TYPE    => defined_or_default('C__RELATION_TYPE__LOCATION'),
                    C__PROPERTY__DATA__RELATION_HANDLER => new isys_callback([
                        'isys_cmdb_dao_category_g_location',
                        'callback_property_relation_handler'
                    ], [
                        'isys_cmdb_dao_category_g_location',
                        true
                    ]),
                    C__PROPERTY__DATA__SELECT           => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\') FROM isys_catg_location_list
                              INNER JOIN isys_obj ON isys_obj__id = isys_catg_location_list__parentid',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        null,
                        null,
                        'isys_catg_location_list__parentid'
                    ),
                    C__PROPERTY__DATA__JOIN             => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_location_list',
                            'LEFT',
                            'isys_catg_location_list__isys_obj__id',
                            'isys_obj__id'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID      => 'C__CATG__LOCATION_PARENT',
                    C__PROPERTY__UI__PARAMS  => [
                        'p_strPopupType'  => 'browser_location',
                        'callback_accept' => 'if($(\'C__CATG__LOCATION_PARENT__HIDDEN\')){$(\'C__CATG__LOCATION_PARENT__HIDDEN\').fire(\'locationObject:selected\');}',
                        'callback_detach' => 'if($(\'C__CATG__LOCATION_PARENT__HIDDEN\')){$(\'C__CATG__LOCATION_PARENT__HIDDEN\').fire(\'locationObject:selected\');}',
                        'containers_only' => true
                    ],
                    C__PROPERTY__UI__DEFAULT => '0'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__LIST   => true,
                    C__PROPERTY__PROVIDES__REPORT => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'location'
                    ]
                ]
            ]),
            'option'           => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_OPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assembly option'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__option',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_location_list__option = ' .
                        $this->convert_sql_id(C__RACK_INSERTION__HORIZONTAL) . ' THEN \'LC__CMDB__CATS__ENCLOSURE__HORIZONTAL\'
                            WHEN isys_catg_location_list__option = ' . $this->convert_sql_id(C__RACK_INSERTION__VERTICAL) . ' THEN \'LC__CMDB__CATS__ENCLOSURE__VERTICAL\'
                            ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END)
                            FROM isys_catg_location_list',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_OPTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_assembly_options'
                        ])
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true
                ]
            ]),
            'insertion'        => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_FRONTSIDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Insertion'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__insertion',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_location_list__insertion = ' .
                        $this->convert_sql_int(C__INSERTION__REAR) . ' THEN \'LC__CATG__LOCATION__BACKSIDE_OCCUPIED\'
                            WHEN isys_catg_location_list__insertion = ' . $this->convert_sql_int(C__INSERTION__FRONT) . ' THEN \'LC__CATG__LOCATION__FRONTSIDE_OCCUPIED\'
                            WHEN isys_catg_location_list__insertion = ' . $this->convert_sql_int(C__INSERTION__BOTH) . ' THEN \'LC__CATG__LOCATION__FRONT_AND_BACK_SIDES_OCCUPIED\'
                            ELSE ' . $this->convert_sql_text(isys_tenantsettings::get('gui.empty_value', '-')) . ' END) AS title
                            FROM isys_catg_location_list',
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id'),
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_INSERTION',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData'        => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_insertion'
                        ]),
                        'p_strSelectedID' => C__INSERTION__FRONT
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH    => false,
                    C__PROPERTY__PROVIDES__REPORT    => true,
                    C__PROPERTY__PROVIDES__LIST      => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT => true
                ]
            ]),
            'pos'              => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_POS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Position in the rack'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__pos',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        "SELECT CONCAT(
                            'option:', isys_catg_location_list__option,
                            ',position:', isys_catg_location_list__pos,
                            ',rack_ru:', f1.isys_catg_formfactor_list__rackunits,
                            ',rack_sorting:', isys_cats_enclosure_list__slot_sorting,
                            ',obj_ru:', (
                                CASE WHEN EXISTS (SELECT 1
                                    FROM isys_catg_formfactor_list f2
                                    WHERE f2.isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__isys_obj__id)
                                THEN f2.isys_catg_formfactor_list__rackunits
                                ELSE 1 END)
                            ) AS positionData
                            FROM isys_catg_location_list
                            INNER JOIN isys_catg_formfactor_list f1 ON f1.isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__parentid
                            INNER JOIN isys_cats_enclosure_list ON isys_cats_enclosure_list__isys_obj__id = isys_catg_location_list__parentid
                            LEFT JOIN isys_catg_formfactor_list f2 ON f2.isys_catg_formfactor_list__isys_obj__id = isys_catg_location_list__isys_obj__id",
                        'isys_catg_location_list',
                        'isys_catg_location_list__id',
                        'isys_catg_location_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory(['isys_catg_location_list__parentid > 0'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_formfactor_list',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_catg_formfactor_list__id',
                            '',
                            'f1',
                            'f1',
                            'isys_catg_location_list'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_enclosure_list',
                            'LEFT',
                            'isys_catg_location_list__parentid',
                            'isys_cats_enclosure_list__id',
                            '',
                            '',
                            '',
                            'isys_catg_location_list'
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_formfactor_list',
                            'LEFT',
                            'isys_catg_location_list__isys_obj__id',
                            'isys_catg_formfactor_list__isys_obj__id',
                            '',
                            'f2',
                            'f2',
                            'isys_catg_location_list'
                        )
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__LOCATION_POS',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => new isys_callback([
                            'isys_cmdb_dao_category_g_location',
                            'callback_property_pos'
                        ]),
                        'p_bSort'  => false
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'location_property_pos'
                    ]
                ]
            ]),
            'gps'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'GPS',
                    C__PROPERTY__INFO__DESCRIPTION => 'GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__gps'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => false,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => false,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__REPORT     => false,
                    C__PROPERTY__PROVIDES__LIST       => false,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_gps'
                    ]
                ]
            ]),
            'latitude'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LATITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Latitude of GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__gps',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT ST_X(isys_catg_location_list__gps)
                            FROM isys_catg_location_list', 'isys_catg_location_list', 'isys_catg_location_list__id', 'isys_catg_location_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__FIELD_FUNCTION => function ($field) {
                        return 'ST_X(' . $field . ')';
                    },
                ],
                C__PROPERTY__UI => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_LATITUDE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_latitude'
                    ]
                ]
            ]),
            'longitude'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION_LONGITUDE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Longitude of GPS Coordinate'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_location_list__gps',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT ST_Y(isys_catg_location_list__gps)
                            FROM isys_catg_location_list', 'isys_catg_location_list', 'isys_catg_location_list__id', 'isys_catg_location_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_location_list', 'LEFT', 'isys_catg_location_list__isys_obj__id', 'isys_obj__id')
                    ],
                    C__PROPERTY__DATA__FIELD_FUNCTION => function ($field) {
                        return 'ST_Y(' . $field . ')';
                    },
                ],
                C__PROPERTY__UI => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_LONGITUDE'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL    => true,
                    C__PROPERTY__PROVIDES__REPORT     => true,
                    C__PROPERTY__PROVIDES__IMPORT     => true,
                    C__PROPERTY__PROVIDES__EXPORT     => true,
                    C__PROPERTY__PROVIDES__VALIDATION => false,
                    C__PROPERTY__PROVIDES__SEARCH     => false,
                    C__PROPERTY__PROVIDES__LIST       => true,
                    C__PROPERTY__PROVIDES__MULTIEDIT  => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'property_callback_longitude'
                    ]
                ]
            ]),
            'snmp_syslocation' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__LOCATION__SNMP_SYSLOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'SNMP Syslocation'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__snmp_syslocation',
                    C__PROPERTY__DATA__INDEX => true
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATG__LOCATION_SNMP_SYSLOCATION'
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_location_list__description'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__LOCATION', 'C__CATG__LOCATION')
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__REPORT => true,
                    C__PROPERTY__PROVIDES__LIST   => true
                ]
            ])
        ];
    }

    /**
     * Sync method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  mixed
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_coord = null;

            if (isset($p_category_data['properties']['gps'][C__DATA__VALUE]) && is_array($p_category_data['properties']['gps'][C__DATA__VALUE]) &&
                ($p_category_data['properties']['gps'][C__DATA__VALUE][0] || $p_category_data['properties']['gps'][C__DATA__VALUE][1])) {
                $l_gps = $p_category_data['properties']['gps'][C__DATA__VALUE];

                if (is_numeric($l_gps[0]) && is_numeric($l_gps[1])) {
                    $l_coord = new Coordinate($l_gps);
                }
            } else {
                if ($p_category_data['properties']['latitude'][C__DATA__VALUE] || $p_category_data['properties']['longitude'][C__DATA__VALUE]) {
                    $l_coord = new Coordinate([
                        str_replace(',', '.', $p_category_data['properties']['latitude'][C__DATA__VALUE]) ?: 0,
                        str_replace(',', '.', $p_category_data['properties']['longitude'][C__DATA__VALUE]) ?: 0
                    ]);
                }
            }

            // @see ID-4543: If position is empty then property insertion and option has to be empty otherwise it won´t be available in the rack view as
            //               unassigned object
            if (empty($p_category_data['properties']['pos'][C__DATA__VALUE])) {
                $p_category_data['properties']['insertion'][C__DATA__VALUE] = null;
                $p_category_data['properties']['option'][C__DATA__VALUE] = null;
                $p_category_data['properties']['pos'][C__DATA__VALUE] = null;
            }

            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                return $this->create(
                    $p_object_id,
                    $p_category_data['properties']['parent'][C__DATA__VALUE],
                    $p_category_data['properties']['pos'][C__DATA__VALUE] ?: null,
                    $p_category_data['properties']['insertion'][C__DATA__VALUE] ?: null,
                    null,
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    $p_category_data['properties']['option'][C__DATA__VALUE] ?: null,
                    $l_coord,
                    $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE] ?: null
                );
            } elseif ($p_status === isys_import_handler_cmdb::C__UPDATE) {
                if ($p_category_data['data_id'] === null) {
                    $l_res = $this->retrieve('SELECT isys_catg_location_list__id FROM isys_catg_location_list WHERE isys_catg_location_list__isys_obj__id = ' .
                        $this->convert_sql_id($p_object_id) . ';');

                    if (is_countable($l_res) && count($l_res)) {
                        $p_category_data['data_id'] = $l_res->get_row_value('isys_catg_location_list__id');
                    } else {
                        return $this->create(
                            $p_object_id,
                            $p_category_data['properties']['parent'][C__DATA__VALUE],
                            $p_category_data['properties']['pos'][C__DATA__VALUE] ?: null,
                            $p_category_data['properties']['insertion'][C__DATA__VALUE] ?: null,
                            null,
                            $p_category_data['properties']['description'][C__DATA__VALUE],
                            $p_category_data['properties']['option'][C__DATA__VALUE] ?: null,
                            $l_coord,
                            $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE] ?: null
                        );
                    }
                }

                // Save category data:
                if ($p_category_data['data_id'] > 0) {
                    $this->save(
                        $p_category_data['data_id'],
                        $p_object_id,
                        $p_category_data['properties']['parent'][C__DATA__VALUE],
                        1,
                        $p_category_data['properties']['pos'][C__DATA__VALUE],
                        $p_category_data['properties']['insertion'][C__DATA__VALUE],
                        null,
                        $p_category_data['properties']['description'][C__DATA__VALUE],
                        $p_category_data['properties']['option'][C__DATA__VALUE],
                        $l_coord,
                        null,
                        $p_category_data['properties']['snmp_syslocation'][C__DATA__VALUE]
                    );

                    return $p_category_data['data_id'];
                }
            }
        }

        return false;
    }

    /**
     * Set cache files which have to be removed
     *
     * @return void
     */
    private function setCacheFilesToRemove()
    {
        if (empty(self::$invalidateCache)) {
            self::$invalidateCache = isys_caching::find('auth-*');
        }
    }

    /**
     * @param int    $categoryDataId
     * @param int    $direction
     * @param string $table
     * @param null   $checkMethod
     * @param false  $p_purge
     *
     * @return bool
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public function rank_record($categoryDataId, $direction, $table, $checkMethod = null, $p_purge = false)
    {
        $hasRight = true;

        if ($checkMethod !== null && is_callable($checkMethod)) {
            $hasRight = call_user_func($checkMethod, $direction);
        }

        if ($hasRight) {
            $this->delete_node($categoryDataId);
            return parent::rank_record($categoryDataId, $direction, $table, $checkMethod, $p_purge);
        }

        return false;
    }

    /**
     * @param int $objectId
     * @return bool
     * @throws isys_exception_database
     * @see ID-12182
     */
    public function isRack(int $objectId): bool
    {
        $query = "SELECT COUNT(1) AS cnt
            FROM isys_obj
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            INNER JOIN isysgui_cats ON isysgui_cats__id = isys_obj_type__isysgui_cats__id
            WHERE isys_obj__id = {$objectId}
            AND isysgui_cats__const = 'C__CATS__ENCLOSURE'
            LIMIT 1;";

        return (bool) $this->retrieve($query)->get_row_value('cnt');
    }

    /**
     * @param int $objectId
     * @param int $rackObjectId
     * @param int $option
     * @param int $insertion
     * @param int $position
     * @return bool
     * @see ID-9423
     */
    public function isRackPositionAvailable(int $objectId, int $rackObjectId, int $option, int $insertion, int $position): bool
    {
        $freeRackPositions = $this->get_free_rackslots($rackObjectId, $insertion, $objectId, $option, null, true);

        foreach ($freeRackPositions as $freePosition => $label) {
            [$pos, ] = explode(';', $freePosition);

            if ($pos == $position) {
                return true;
            }
        }

        return false;
    }
}
