<?php

use idoit\Component\Helper\ArrayHelper;
use idoit\Module\Cmdb\Component\CategoryChanges\Changes;
use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Component\SyncMerger\Merger;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\AbstractCollection;
use idoit\Module\Cmdb\Model\Entry\Entry;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;

/**
 * i-doit
 *
 * CMDB Action Processor
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category
{
    /**
     * Instance of isys_component_dao_lock.
     *
     * @var  isys_component_dao_lock
     */
    protected $m_dao_lock;

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_dao_lock = new isys_component_dao_lock($g_comp_database);
    }

    /**
     * Callback function called by rank_records.
     *
     * @param   $p_object_id
     * @param   $p_category_const
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function check_right($p_object_id, $p_category_const)
    {
        return isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_object_id, $p_category_const);
    }

    /**
     * Checks if the object is locked. Returns true, if object is locked for the current user - false, if not.
     *
     * @return  boolean
     */
    protected function object_is_locked()
    {
        if ($this->m_dao_lock->check_lock($_GET[C__CMDB__GET__OBJECT])) {
            isys_component_template_infobox::instance()
                ->set_message("<b>ERROR! - " . isys_application::instance()->container->get('language')
                        ->get("LC__OBJECT_LOCKED") . " (Lock-Timeout: " . C__LOCK__TIMEOUT . "s)</b>", null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 3));

            return true;
        }

        return false;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $postData
     * @param int                    $objectId
     *
     * @return int|null
     * @throws isys_exception_database
     * @see ID-10890 Helper method to fetch correct entry IDs (mainly necessary for overview page).
     */
    private function fetchEntryId(isys_cmdb_dao_category $dao, array $postData, int $objectId): ?int
    {
        $entryId = $_GET[C__CMDB__GET__CATLEVEL] ?: $postData[C__GET__ID];

        if (is_array($entryId)) {
            $entryId = current($entryId);
        }

        // The overview page itself has no entry.
        if ($dao instanceof isys_cmdb_dao_category_g_overview) {
            return null;
        }

        // If we are NOT on the overview page, the found entry should be fine.
        // @see ID-10993 Fix error, in case '$entryId' does not contain a int.
        if (is_numeric($entryId) && $_GET[C__CMDB__GET__CATG] != C__CATG__OVERVIEW) {
            return (int)$entryId;
        }

        // For SV categories, it should be safe to fetch the ID from the database.
        if (!$dao->is_multivalued()) {
            return $dao->getEntryIdByObjectId($objectId);
        }

        // Special logic for some MV categories, shown on the overview page.
        switch (get_class($dao)) {
            case isys_cmdb_dao_category_g_ip::class:
                $entryId = $dao->get_primary_ip($objectId)->get_row_value('isys_catg_ip_list__id');
                break;
        }

        if (is_numeric($entryId) && $entryId > 0) {
            return (int)$entryId;
        }

        return null;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $postData
     *
     * @return bool
     * @see ID-10890 Helper method to skip certain categories on the overview page.
     */
    private function skipChanges(isys_cmdb_dao_category $dao, array $postData): bool
    {
        if ($_GET[C__CMDB__GET__CATG] != C__CATG__OVERVIEW) {
            return false;
        }

        // Skip 'contact' category, when being saved from the overview page.
        return $dao instanceof isys_cmdb_dao_category_g_contact && !isset($postData['C__CMDB__CATG__CONTACT__CONNECTED_OBJECT__HIDDEN']);
    }

    /**
     * Format the users changes by processing the _SM2_FORM data and compare them with the post array.
     *
     * @param array         $p_posts
     * @param isys_cmdb_dao $p_dao
     *
     * @return Changes
     * @throws Exception
     */
    public function format_changes($p_posts, $p_dao)
    {
        if (is_array($p_posts) && $p_dao instanceof \isys_cmdb_dao_category) {
            $objectId = (int)$_GET[C__CMDB__GET__OBJECT];
            $p_dao->set_object_id($objectId);
            $p_dao->set_object_type_id($p_dao->get_objTypeID($objectId));

            // @see ID-10890 Fetch the correct entry ID.
            $entryId = $this->fetchEntryId($p_dao, $p_posts, $objectId);

            // @see ID-10890 Skip certain categories.
            if ($this->skipChanges($p_dao, $p_posts)) {
                $changer = Changes::instance($p_dao, $objectId, $entryId);
                $changer->processChanges();

                return $changer;
            }

            $fakeEntry = [
                Config::CONFIG_DATA_ID => $entryId,
                Config::CONFIG_PROPERTIES => []
            ];

            $currentData = Merger::instance(Config::instance($p_dao, $objectId, $fakeEntry))->flattenSyncData();
            $changedData = $p_dao->parse_user_data();

            foreach ($currentData as $key => $value) {
                // @see ID-11288 'isset' won't work for some dialog fields because empty state (-1) will be changed to NULL.
                if (!array_key_exists($key, $changedData)) {
                    $changedData[$key] = $value;
                }
            }

            if ($p_dao instanceof ObjectBrowserAssignedEntries) {
                $entries = $p_dao->getAttachedEntries($objectId);
                $currentEntries = [];

                if ($entries instanceof AbstractCollection) {
                    $currentEntries = $entries->getIds();
                } else {
                    foreach ($entries->getEntries() as $entry) {
                        if ($entry instanceof ObjectEntry) {
                            $currentEntries[] = $entry->getObjectid();
                            continue;
                        }

                        if ($entry instanceof Entry) {
                            $currentEntries[] = $entry->getEntryid();
                            continue;
                        }
                    }
                }

                $currentData[$p_dao->get_object_browser_property()] = $currentEntries;

                if ($p_dao instanceof isys_cmdb_dao_category_g_contact && !empty($changedData[$p_dao->get_object_browser_property()])) {
                    $changedData[$p_dao->get_object_browser_property()] = isys_format_json::is_json_array($changedData[$p_dao->get_object_browser_property()])
                        ? isys_format_json::decode($changedData[$p_dao->get_object_browser_property()])
                        : (array)$changedData[$p_dao->get_object_browser_property()];

                    // Use 'array_values( array_unique( ... ) )' to get a clean list.
                    $changedData[$p_dao->get_object_browser_property()] = array_values(array_unique(ArrayHelper::concat($currentData[$p_dao->get_object_browser_property()], $changedData[$p_dao->get_object_browser_property()])));
                }

                // @see ID-10890 Use the same 'empty value' so that the Changer does not recognize a change.
                if (empty($changedData[$p_dao->get_object_browser_property()]) && empty($currentData[$p_dao->get_object_browser_property()])) {
                    $changedData[$p_dao->get_object_browser_property()] = null;
                    $currentData[$p_dao->get_object_browser_property()] = null;
                }
            }

            // Starting point to process the changes
            $changer = Changes::instance($p_dao, $objectId, $entryId, $currentData, $changedData);
            $changer->processChanges();

            return $changer;
        }

        throw new Exception('Changes could not be calculated!');
    }

    /**
     * @param isys_cmdb_dao_category     $categoryDao
     * @param isys_cmdb_action_processor $actionProcessor
     *
     * @return void
     * @throws isys_exception_database
     * @see ID-10557 Extract the validation logic
     */
    protected function notifyValidationError(isys_cmdb_dao_category $categoryDao, isys_cmdb_action_processor $actionProcessor): void
    {
        $language = isys_application::instance()->container->get('language');
        $notify = isys_application::instance()->container->get('notify');
        $template = isys_application::instance()->container->get('template');
        $objectId = $categoryDao->get_object_id();
        $objectTypeId = $categoryDao->get_object_type_id();

        // Overview error handler.
        if (method_exists($categoryDao, 'get_invalid_classes')) {
            $message = 'errors occured in: <strong>' . str_replace('isys_cmdb_dao_category_', '', $categoryDao->get_invalid_classes()) . '</strong>';
        } else {
            $message = isys_format_json::encode($categoryDao->get_additional_rules());
        }

        // Maybe we should process the gui now.
        isys_event_manager::getInstance()
            ->triggerCMDBEvent('C__LOGBOOK_EVENT__CATEGORY_CHANGED__NOT', $message, $objectId, $objectTypeId, $categoryDao->getCategoryTitle());

        // C__CMDB__ERROR__ACTION_CATEGORY_UPDATE for form error.
        $actionProcessor->result_push(-C__CMDB__ERROR__ACTION_CATEGORY_UPDATE);

        // If ever necessary, we can assign the complete property information to the template a few lines below :)
        $template->assign('validation_errors', $categoryDao->get_additional_rules());

        // Throw exception only if update is triggered via ajax
        if ($_GET[C__GET__AJAX] && isset($_GET[C__GET__AJAX_CALL])) {
            $messages = [];

            foreach ($categoryDao->get_additional_rules() as $attributeConstant => $data) {
                $propertyName = $data['title'];

                if (empty($propertyName)) {
                    $property = $categoryDao->get_property_by_ui_id($attributeConstant);

                    if (is_array($property)) {
                        $property = current($property);

                        $propertyName = $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
                    } else {
                        $propertyName = 'LC__CMDB__CATG__ATTRIBUTE';
                    }
                }

                $messages[] = "<br /><strong>{$language->get($propertyName)}</strong> - {$data['message']}";
            }

            // This will trigger the "Notify" box.
            throw new Exception($language->get('LC__VALIDATION_ERROR') . ' ' . implode('', $messages));
        }

        $notify->error($language->get('LC__VALIDATION_ERROR'));
    }
}
