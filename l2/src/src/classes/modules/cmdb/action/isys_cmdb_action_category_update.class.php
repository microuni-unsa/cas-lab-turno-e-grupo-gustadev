<?php

use idoit\Component\Helper\Unserialize;
use idoit\Context\Context;
use idoit\Module\Cmdb\Component\CategoryChanges\Changes;

/**
 * Action: category creation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category_update extends isys_cmdb_action_category implements isys_cmdb_action
{
    /**
     * Cache the property infos
     *
     * @var array
     */
    private static $infoCache = [];

    /**
     * @var array
     */
    private static $popupInstance = [];

    /**
     * Categories where the title is generated with the plugin isys_smarty_plugin_f_title_suffix_counter
     *
     * @var array
     */
    const CATEGORIES_WITH_SUFFIX = [
        'isys_cmdb_dao_category_g_network_port'      => [
            'C__CATG__PORT__TITLE',
            'C__CATG__PORT'
        ],
        'isys_cmdb_dao_category_g_connector'         => [
            'C__UNIVERSAL__TITLE',
            'C__CATG__CONNECTOR'
        ],
        'isys_cmdb_dao_category_g_controller_fcport' => [
            'C__CATG__CONTROLLER_FC_PORT_TITLE',
            'C__CATG__FC_PORT'
        ],
        'isys_cmdb_dao_category_s_chassis_slot'      => [
            'C__CMDB__CATS__CHASSIS_SLOT__TITLE',
            'C__CMDB__CATS__CHASSIS_SLOT'
        ]
    ];

    const SM2_SKIP = [
        'main',
        'filter',
        'C__CATG__TITLE',
        'C__CATG__SYSID',
        'C__CATG__LOCATION',
        'C__CATG__PURPOSE',
        'C__CATG__CONTACT',
        'C__CATG__RELATIONS',
        'C__CATG__ACCESS',
        'C__UNIVERSAL__BUTTON_SAVE_QUICK',
        'C__UNIVERSAL__BUTTON_CANCEL',
        'commentary',
        'LogbookReason',
    ];

    /**
     * Check if a change occurred by comparing the post value (string) and the complete
     * sm2 array. Return value is an array with the changes.
     *
     * @param   string|int $newValue
     * @param   array  $smartyMetaMap
     *
     * @return  array
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     */
    public static function get_change($newValue, $smartyMetaMap)
    {
        $language = isys_application::instance()->container->get('language');
        $oldValue = '';

        if ($smartyMetaMap['p_strPopupType'] === 'browser_cable_connection_ng') {
            $oldValue = $smartyMetaMap['p_strValue'];
            if ($oldValue == '') {
                $oldValue = $language->get('LC__UNIVERSAL__CONNECTION_DETACHED');
            } elseif (is_numeric($oldValue)) {
                $oldValue = isys_cmdb_dao_category_g_connector::getConnectorNameByConnectorId($oldValue);
            }
        } elseif (isset($smartyMetaMap['p_strValue']) && !empty($smartyMetaMap['p_strValue'])) {
            $oldValue = $smartyMetaMap['p_strValue'];
        } elseif (isset($smartyMetaMap['p_strSelectedID']) && !empty($smartyMetaMap['p_strSelectedID'])) {
            $oldValue = $smartyMetaMap['p_strSelectedID'];
            if ($smartyMetaMap['p_strTable'] === 'isys_dialog_plus_custom') {
                $oldValuesArray = explode(',', $oldValue);
                $catgCustomDao = isys_cmdb_dao_category_g_custom_fields::instance(isys_application::instance()->container->get('database'));
                $oldValuesTitlesArray = $catgCustomDao->getDialogTitleById($oldValuesArray);
                $oldValue = implode(", ", $oldValuesTitlesArray);
            }
        } elseif (isset($smartyMetaMap['p_arData']) && !empty($smartyMetaMap['p_arData'])) {
            if ($smartyMetaMap['type'] === 'f_dialog_list') {
                $l_arData = Unserialize::toArray($smartyMetaMap['p_arData']);
                if (is_array($l_arData)) {
                    $l_from = [];
                    foreach ($l_arData as $l_data) {
                        if ($l_data['sel']) {
                            $l_from[] = $l_data['val'];
                        }
                    }
                    ksort($l_from);
                    $oldValue = implode(',', $l_from);
                }
            }
        }

        $oldValue = trim($oldValue);
        $newValue = trim($newValue);

        if (($oldValueJSON = isys_format_json::is_json_array($oldValue))) {
            $oldValue = str_replace(["'", '"'], '', $oldValue);
        }

        if (($l_post_json = isys_format_json::is_json_array($newValue))) {
            $newValue = str_replace(["'", '"' ], '', $newValue);
        }

        $selectableFieldTypes = class_exists('isys_custom_fields_dao', true) ? isys_custom_fields_dao::getMultipleSelectableFieldTypes() : ['dialog_plus'];
        // @todo LF: Check, if "$smartyMetaMap["p_strPopupType"] != 'dialog_plus'" doesn't break anything! It should work just fine but you'll never know.
        if (isset($smartyMetaMap['p_strPopupType']) && in_array($smartyMetaMap['p_strPopupType'], $selectableFieldTypes)) {
            $popupClass = 'isys_popup_' . $smartyMetaMap['p_strPopupType'] === 'checkboxes'? 'dialog_plus': $smartyMetaMap['p_strPopupType'];

            if (class_exists($popupClass)) {
                /** @var  $popupInstance  isys_component_popup */
                if (isset(self::$popupInstance[$smartyMetaMap['p_strPopupType']])) {
                    $popupInstance = self::$popupInstance[$smartyMetaMap['p_strPopupType']];
                } else {
                    $popupInstance = new $popupClass();
                }

                if ($popupInstance instanceof isys_popup_browser_object_ng) {
                    // We don´t need the quickinfo for the logbook changes otherwise the comparison would always be false
                    $popupInstance->set_format_quick_info(false);
                } elseif ($popupInstance instanceof isys_popup_browser_location) {
                    // @see ID-3386 popup browser location has a different method for deactivating the quickinfo
                    $popupInstance->set_format_as_text(true);
                }

                if (is_numeric($oldValue) || is_numeric($newValue)) {
                    $oldValue = (is_numeric($oldValue) ? $popupInstance->format_selection((int)$oldValue, true) : $oldValue);
                    $newValue = (is_numeric($newValue) ? $popupInstance->format_selection((int)$newValue, true) : $newValue);
                } else {
                    // This case only occurs in create mode if we have an object browser with multiselection
                    $l_dao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database'));
                    if ($l_post_json) {
                        $newValue = implode(', ', array_map(function ($l_item) use ($l_dao) {
                            if (is_numeric($l_item)) {
                                return trim($l_dao->get_obj_name_by_id_as_string($l_item));
                            }

                            return trim($l_item);
                        }, isys_format_json::decode($newValue)));
                    }

                    if ($oldValueJSON) {
                        $oldValue = implode(', ', array_map(function ($l_item) use ($l_dao) {
                            if (is_numeric($l_item)) {
                                return trim($l_dao->get_obj_name_by_id_as_string($l_item));
                            }

                            return trim($l_item);
                        }, isys_format_json::decode($oldValue)));
                    }
                }

                // Do NOT compare `$oldValue` and `$newValue` strict (!==).
                if ($oldValue != $newValue) {
                    return [
                        'from' => strip_tags($oldValue),
                        'to'   => strip_tags($newValue)
                    ];
                }

                return null;
            }
        }

        /**
         * @see  ID-2460  Format a possible "," notated number to the english "." format, to not track 1,76 and 1.76 as a change.
         * @see  ID-3115  Format a 'no-break space' (&nbsp;) and a UTF8 encoded 'no-break space' (chr(194) + chr(160)) with a " " (this can occur in WYSIWYG fields).
         */

        $l_search = [',', '&nbsp;', chr(194) . chr(160)];
        $l_replace = ['.', ' ', ' '];

        // @see  ID-6741  Additionally checking for the string length.
        $cleanedFrom = str_replace($l_search, $l_replace, $oldValue);
        $cleanedTo = str_replace($l_search, $l_replace, $newValue);

        // Do NOT compare `$cleanedFrom` and `$cleanedTo` strict (!==).
        if ($cleanedFrom != $cleanedTo || mb_strlen($cleanedFrom) !== mb_strlen($cleanedTo)) {
            if ($oldValue === null || $oldValue === '') {
                $oldValue = null;
            }

            return [
                'from' => $oldValue,
                'to'   => $newValue
            ];
        }

        return null;
    }

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao $p_dao
     * @param   array         $p_data
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception
     * @return  mixed
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_DAO_UPDATE)
            ->setGroup(Context::CONTEXT_GROUP_DAO)
            ->setContextCustomer(Context::CONTEXT_DAO_UPDATE);

        global $g_catlevel, $g_navmode;

        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_changed_compressed = null;
        $l_changed = [];
        $l_saveval = 0;

        $l_gets = isys_module_request::get_instance()->get_gets();
        $l_posts = isys_module_request::get_instance()->get_posts();
        $language = isys_application::instance()->container->get('language');
        $notify = isys_application::instance()->container->get('notify');

        /** @var  $l_actproc isys_cmdb_action_processor */
        $l_actproc = &$p_data['__ACTIONPROC'];

        /** @var  $l_dao isys_cmdb_dao_category */
        $l_dao = $p_data[0];
        $justCreated = false;

        if (!$l_dao instanceof isys_cmdb_dao_category_g_overview) {
            $data = $l_dao->retrieve($l_dao->get_result()->get_query())->get_row();
            $justCreated = empty($data);
        }


        /** @var  isys_cmdb_ui_category */
        $l_ui = $p_data[1];

        // Class name.
        $l_class = get_class($l_dao);

        // New auth-check.
        $this->check_right($_GET[C__CMDB__GET__OBJECT], $l_dao->get_category_const());

        // Check classes.
        if (!isset($l_dao) || !$l_dao) {
            throw new isys_exception_cmdb('Could not handle category update (DAO class not set)', C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        if (!isset($l_ui) || !$l_ui) {
            throw new isys_exception_cmdb('Could not handle category update (UI class not set)', C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        // Check Locking.
        if ($this->object_is_locked()) {
            $l_actproc->result_push(null);

            return null;
        }

        $l_recstatus = null;
        $l_newlevel = 0;

        if (isset($_GET[C__CMDB__GET__CATG_CUSTOM]) && $l_dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $l_category = $l_dao->get_cat_custom_name_by_id_as_string($_GET[C__CMDB__GET__CATG_CUSTOM]);
            $l_strConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';

            $l_dao->set_catg_custom_id($_GET[C__CMDB__GET__CATG_CUSTOM]);
        } elseif ($_GET[C__CMDB__GET__CATG]) {
            $l_category = $l_dao->get_catg_name_by_id_as_string($_GET[C__CMDB__GET__CATG]);
            $l_strConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';
        } elseif ($_GET[C__CMDB__GET__CATS]) {
            $l_category = $l_dao->get_cats_name_by_id_as_string($_GET[C__CMDB__GET__CATS]);
            $l_strConstEvent = 'C__LOGBOOK_EVENT__CATEGORY_CHANGED';
        } else {
            $l_strConstEvent = 'C__LOGBOOK_EVENT__OBJECT_CHANGED';
            $l_category = '';
        }

        $l_category_with_suffix = false;

        // Sanitize Data
        $_POST = $l_dao->sanitize_post_data();

        $l_dao->set_strLogbookSQL('');

        // Check if sent POST data is OK.
        $l_dao->set_object_id($_GET[C__CMDB__GET__OBJECT]);
        $l_dao->set_object_type_id($_GET[C__CMDB__GET__OBJECTTYPE]);

        // @see ID-10557 Check if the validation throws any errors.
        if (!$l_dao->validate_user_data()) {
            $this->notifyValidationError($l_dao, $l_actproc);
            return null;
        }

        try {
            /**
             * -----------------------------------------------------------------------------------
             * Call logbook change management
             * -----------------------------------------------------------------------------------
             */
            $l_changed = [];
            $l_changed_compressed = '';
            $l_generated_titles = null;
            $completeChanges = null;
            $l_suffix_info = null;

            if (array_key_exists($l_class, self::CATEGORIES_WITH_SUFFIX)) {
                $l_suffix_info = self::CATEGORIES_WITH_SUFFIX[$l_class];
                $l_generated_titles = isys_smarty_plugin_f_title_suffix_counter::generate_title_as_array($l_posts, $l_suffix_info[1], $l_suffix_info[0]);
                $l_category_with_suffix = true;
                if (is_array($l_generated_titles)) {
                    $l_changed_compressed = [];

                    foreach ($l_generated_titles as $l_title) {
                        $l_posts[$l_suffix_info[0]] = $l_title;
                        $l_changed = $this->format_changes($l_posts, $l_dao);
                        $completeChanges[] = $l_changed;
                        $l_changed->resetInstance($l_dao);

                        // Emit category signal (beforeCategoryEntrySave) for each title like Connector01, Connector02 etc.
                        isys_component_signalcollection::get_instance()
                            ->emit('mod.cmdb.beforeCategoryEntrySave', $l_dao, $_GET[C__CMDB__GET__CATLEVEL], $_GET[C__CMDB__GET__OBJECT], $l_posts, $l_changed->getCurrentReformatedChanges());
                    }
                }
            } else {
                $l_changed = $this->format_changes($l_posts, $l_dao);
                $completeChanges = $l_changed;

                // Emit category signal (beforeCategoryEntrySave).
                isys_component_signalcollection::get_instance()
                    ->emit('mod.cmdb.beforeCategoryEntrySave', $l_dao, $_GET[C__CMDB__GET__CATLEVEL], $_GET[C__CMDB__GET__OBJECT], $_POST, $completeChanges->getCurrentReformatedChanges());
            }

            // Save category.
            if (method_exists($l_dao, 'save_element')) {
                $l_saveval = $l_dao->save_element($l_newlevel, $l_recstatus, empty($_GET[C__CMDB__GET__CATLEVEL]));
            } else {
                $l_saveval = $l_dao->save_user_data(empty($_GET[C__CMDB__GET__CATLEVEL]));
            }

            // @see ID-9307 Only update the 'changed' date if there were actual changes.
            if ($l_dao instanceof isys_cmdb_dao_category_g_overview) {
                if ($l_dao->getSavedCategories()) {
                    $l_dao->object_changed($_GET[C__CMDB__GET__OBJECT]);
                }
            } else {
                $l_dao->object_changed($_GET[C__CMDB__GET__OBJECT]);
            }

            /*
             * Dennis Stücken:
             * Send category id to UI, if a new category was created by save_element.
             * This functions requests, that a create() inside save_element returns the created id, of course!
             */
            if (is_numeric($l_saveval)) {
                $_GET[C__CMDB__GET__CATLEVEL] = $l_saveval;
            }

            if ($l_newlevel) {
                $g_catlevel = $l_newlevel;
            }
        } catch (isys_exception $e) {
            $notify->error($e->getMessage());
        }

        $l_dao->get_result()->requery();

        if ($l_saveval > 0) {
            // Save_element() has set a new level->ID assignment.
            $l_actproc->result_push([
                $l_newlevel,
                $l_saveval
            ]);
        } else {
            if ($l_saveval < 0) {
                // Errors found.
                throw new isys_exception_cmdb(
                    "Could not save category entry ({$l_class}->save_element()) - return code is {$l_saveval}",
                    C__CMDB__ERROR__ACTION_PROCESSOR
                );
            }

            // Standard save.
            $l_actproc->result_push(null);
        }

        if ((is_countable($completeChanges) || $completeChanges instanceof Changes) && $_GET[C__CMDB__GET__CATG] != defined_or_default('C__CATG__LOGBOOK')) {
            // Removes lock of the dataset.
            if ($this->m_dao_lock) {
                $this->m_dao_lock->delete_by_object_id($l_gets[C__CMDB__GET__OBJECT]);
            }

            if ($_GET[C__CMDB__GET__CATG] != defined_or_default('C__CATG__OVERVIEW')) {
                /**
                 * -----------------------------------------------------------------------------------
                 * Create the logbook entry after object change
                 * -----------------------------------------------------------------------------------
                 */
                if ($l_category_with_suffix && is_array($completeChanges)) {
                    foreach ($completeChanges as $changes) {
                        $l_dao->logbook_update(
                            $l_strConstEvent,
                            $changes,
                            $_POST['LogbookCommentary'] ?? null,
                            $_POST['LogbookReason'] ?? null
                        );
                    }
                } else {
                    $l_dao->logbook_update(
                        $l_strConstEvent,
                        $completeChanges,
                        $_POST['LogbookCommentary'] ?? null,
                        $_POST['LogbookReason'] ?? null
                    );
                }
            }
        }

        if ($l_suffix_info !== null && $l_category_with_suffix && is_array($l_generated_titles) && is_array($completeChanges)) {
            foreach ($l_generated_titles as $index => $l_title) {
                $l_posts[$l_suffix_info[0]] = $l_title;
                // Emit category signal (afterCategoryEntrySave) for each title like Connector01, Connector02 etc.
                isys_component_signalcollection::get_instance()
                    ->emit('mod.cmdb.afterCategoryEntrySave', $l_dao, $_GET[C__CMDB__GET__CATLEVEL], $l_saveval, $_GET[C__CMDB__GET__OBJECT], $_POST, $completeChanges[$index]->getCurrentReformatedChanges());
            }
            return true;
        }

        if ($justCreated) {
            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.categoryEntryJustCreated', $l_dao, $_GET[C__CMDB__GET__CATLEVEL], $l_saveval, $_GET[C__CMDB__GET__OBJECT], $_POST, $completeChanges->getCurrentReformatedChanges());
        }

        // Emit category signal (afterCategoryEntrySave).
        isys_component_signalcollection::get_instance()
            ->emit('mod.cmdb.afterCategoryEntrySave', $l_dao, $_GET[C__CMDB__GET__CATLEVEL], $l_saveval, $_GET[C__CMDB__GET__OBJECT], $_POST, $completeChanges->getCurrentReformatedChanges());

        return true;
    }
}
