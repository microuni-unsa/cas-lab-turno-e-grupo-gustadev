<?php

use idoit\Context\Context;
use idoit\Module\Cmdb\Component\CategoryChanges\Changes;

/**
 * i-doit
 *
 * CMDB Action: Category creation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category_create extends isys_cmdb_action_category implements isys_cmdb_action
{
    /**
     * Handle method.
     *
     * @param   isys_cmdb_dao $p_dao
     * @param   array         $p_data
     *
     * @throws  isys_exception_cmdb
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_DAO_CREATE)
            ->setGroup(Context::CONTEXT_GROUP_DAO)
            ->setContextCustomer(Context::CONTEXT_DAO_CREATE);

        /** @var  $l_actionproc  isys_cmdb_action_processor */
        $l_actionproc = $p_data["__ACTIONPROC"];

        /** @var  $l_dao  isys_cmdb_dao_category */
        $l_dao = $p_data[0];

        /** @var  $l_ui  isys_cmdb_ui_category */
        $l_ui = $p_data[1];

        // Check, if the user is allowed to create a new category entry.
        $this->check_right($_GET[C__CMDB__GET__OBJECT], $l_dao->get_category_const());

        if (!isset($l_dao) || !$l_dao) {
            throw new isys_exception_cmdb("Could not handle category update (DAO class not set)", C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        if (!isset($l_ui) || !$l_ui) {
            throw new isys_exception_cmdb("Could not handle category update (UI class not set)", C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        $l_newid = null;
        $l_default_template = null;
        $l_dao->set_strLogbookSQL('');
        $l_object_id = (int) $_GET[C__CMDB__GET__OBJECT];
        $changes = null;
        $reformattedChanges = [];

        // Get default template, if configured.
        if ($_POST['useTemplate'] == 1) {
            // Get template module.
            $l_template_module = new isys_module_templates();
            $l_default_template = $l_dao->get_default_template_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);
        }

        // Sanitize Data.
        $_POST = $l_dao->sanitize_post_data();

        // Reset the logbook SQL.
        $l_dao->set_strLogbookSQL('');

        // Set the object ID.
        $l_dao->set_object_id($l_object_id);
        $l_dao->set_object_type_id($_GET[C__CMDB__GET__OBJECTTYPE]);

        // @see ID-10557 Check if the validation throws any errors.
        // @see ID-10884 Do not validate MV categories, they use specific logic and will be validated while saving (= update procedure).
        if (!$l_dao->is_multivalued() && !$l_dao->validate_user_data()) {
            $this->notifyValidationError($l_dao, $l_actionproc);
            return null;
        }

        // If it is the global category or the object is locked, just cancel.
        if (($l_dao->get_category_id() === defined_or_default('C__CATG__GLOBAL') && $l_dao->get_category_type() === C__CMDB__CATEGORY__TYPE_GLOBAL) || $this->object_is_locked()) {
            if (!$this->object_is_locked() && $_POST['useTemplate'] == 1) {
                if (isset($l_template_module) && is_object($l_template_module)) {
                    $l_template_module->create_from_template(
                        [$l_default_template],
                        $_GET[C__CMDB__GET__OBJECTTYPE],
                        $_POST['C__CATG__GLOBAL_TITLE'],
                        $l_object_id,
                        false,
                        1,
                        ''
                    );
                }
            }

            $l_actionproc->result_push(null);

            return;
        } elseif ($l_dao->get_category_id() === defined_or_default('C__CATG__OVERVIEW') && $_POST['useTemplate'] == 1) {
            if (isset($l_template_module) && is_object($l_template_module)) {
                $l_template_module->create_from_template([$l_default_template], $_GET[C__CMDB__GET__OBJECTTYPE], $_POST['C__CATG__GLOBAL_TITLE'], $l_object_id, false, 1, '');
            }
        }

        if ($l_dao->get_object_browser_category() !== false) {
            // It is a category with only an object browser create changes
            $changes = $this->format_changes($_POST, $l_dao);
            $reformattedChanges = $changes->getCurrentReformatedChanges();
        }

        // Emit category signal (beforeCreateCategoryEntry).
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.beforeCreateCategoryEntry", $l_dao->get_category_id(), $l_object_id, $l_dao, $reformattedChanges);

        // Check if this is an ObjectBrowserReceiver
        if (is_a($l_dao, '\\idoit\\Module\\Cmdb\\Interfaces\\ObjectBrowserReceiver')) {
            if (isset($_POST[C__POST__POPUP_RECEIVER]) && !empty($_POST[C__POST__POPUP_RECEIVER])) {
                $l_post_key = C__POST__POPUP_RECEIVER;
            } else {
                $l_objBrowser_key = 'assigned_object';
                if ($l_dao->get_object_browser_category() === true && $l_dao->get_object_browser_property() !== '') {
                    // Retrieve property which is the object browser
                    $l_objBrowser_key = $l_dao->get_object_browser_property();
                }

                $l_post_key = ($l_dao->get_property_by_key($l_objBrowser_key)[C__PROPERTY__UI][C__PROPERTY__UI__ID] ?: '') . '__HIDDEN';

                if (!isset($_POST[$l_post_key])) {
                    foreach ($_POST as $k => $v) {
                        if ($k != 'savedCheckboxes' && !empty($v) && isys_format_json::is_json_array($v)) {
                            $l_post_key = $k;
                            break;
                        }
                    }
                }
            }

            if (isset($_POST[$l_post_key])) {
                if (isys_format_json::is_json_array($_POST[$l_post_key])) {
                    $l_data = isys_format_json::decode($_POST[$l_post_key]);
                } else {
                    $l_data = explode(',', $_POST[$l_post_key]);
                }

                /** @var $l_dao \idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver */
                $l_dao->attachObjects((int)$_GET[C__CMDB__GET__OBJECT], $l_data);

                $l_newid = true;
            }
        }

        if (empty($l_post_key) || $l_post_key === '__HIDDEN') {
            if (!$l_dao->is_multivalued()) {
                // Create the record!
                $l_newid = $l_dao->create_connector($l_dao->get_table(), $l_object_id);

                isys_component_signalcollection::get_instance()
                    ->emit('mod.cmdb.categoryEntryJustCreated', $l_dao, $l_newid, true, $l_object_id, $_POST, $changes?->getCurrentReformatedChanges());

                if (is_null($l_newid)) {
                    $l_newid = -1;
                }
            } else {
                $l_newid = -1;
            }
        }

        $l_dao->object_changed($_GET[C__CMDB__GET__OBJECT]);

        if ($l_dao->get_object_browser_category() !== false && $changes instanceof Changes && $changes->hasAnyChanges()) {
            // Set changes into the db
            $l_dao->logbook_update(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                $changes,
                $_POST['LogbookCommentary'] ?? null,
                $_POST['LogbookReason'] ?? null
            );

            isys_component_signalcollection::get_instance()
                ->emit('mod.cmdb.categoryEntryJustCreated', $l_dao, $l_newid, true, $l_object_id, $_POST, $changes?->getCurrentReformatedChanges());
        }

        // Emit category signal (afterCreateCategoryEntry).
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.afterCreateCategoryEntry", $l_dao->get_category_id(), $l_newid, ($l_newid > 0), $l_object_id, $l_dao, $reformattedChanges);

        // This was a standard save :-)
        $l_actionproc->result_push($l_newid);
    }
}
