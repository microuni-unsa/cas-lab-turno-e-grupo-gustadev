<?php

namespace idoit\Module\System\Cleanup;

use Exception;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_custom_fields;
use isys_component_template_language_manager;
use Throwable;

/**
 * Class DeleteDuplicateSingleValueEntries
 *
 * @package idoit\Module\System\Cleanup
 * @see ID-10847
 */
class DeleteDuplicateSingleValueEntries extends AbstractCleanup
{
    private int $deletedEntries;
    private isys_cmdb_dao $dao;
    private isys_component_template_language_manager $language;

    /**
     * Method for starting the cleanup process.
     *
     * @throws \Exception
     */
    public function process()
    {
        \isys_module_system::getAuth()->check(\isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $this->deletedEntries = 0;
        $this->dao = $this->container->get('cmdb_dao');
        $this->language = $this->container->get('language');

        echo 'Deleting duplicate single-value categorie entries...<br />';

        $this->processGlobalCategories();

        $this->processSpecificCategories();

        $this->processCustomCategories();

        if ($this->deletedEntries) {
            echo "Found and deleted {$this->deletedEntries} duplicated single value entries!<br />";
        } else {
            echo 'No duplicate single value entries found.<br />';
        }
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function processGlobalCategories(): void
    {
        echo '<strong>Running checks for global categories...</strong><br />';
        $editType = $this->dao->convert_sql_int(isys_cmdb_dao_category::TYPE_EDIT);

        $query = "SELECT isysgui_catg__source_table AS sourceTable, isysgui_catg__class_name AS className, isysgui_catg__title AS title
            FROM isysgui_catg
            WHERE isysgui_catg__const NOT IN ('C__CATG__NETWORK_PORT', 'C__CATG__NETWORK_PORT_OVERVIEW', 'C__CATG__STORAGE', 'C__CATG__CUSTOM_FIELDS', 'C__CATG__OPERATING_SYSTEM')
            AND isysgui_catg__list_multi_value = 0
            AND isysgui_catg__type = {$editType};";

        $result = $this->dao->retrieve($query);

        while ($row = $result->get_row()) {
            $this->cleanUp(
                $row['sourceTable'] . '_list',
                $row['className'],
                $this->language->get($row['title'])
            );
        }
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function processSpecificCategories(): void
    {
        echo '<strong>Running checks for specific categories...</strong><br />';
        $editType = $this->dao->convert_sql_int(isys_cmdb_dao_category::TYPE_EDIT);

        $query = "SELECT isysgui_cats__source_table AS sourceTable, isysgui_cats__class_name AS className, isysgui_cats__title AS title
            FROM isysgui_cats
            WHERE isysgui_cats__const NOT IN ('C__CATS__NET_IP_ADDRESSES')
            AND isysgui_cats__list_multi_value = 0
            AND isysgui_cats__type = {$editType};";

        $result = $this->dao->retrieve($query);

        while ($row = $result->get_row()) {
            $this->cleanUp(
                $row['sourceTable'],
                $row['className'],
                $this->language->get($row['title'])
            );
        }
    }

    private function processCustomCategories(): void
    {
        echo '<strong>Running checks for custom categories...</strong><br />';
        $result = $this->dao->retrieve('SELECT * FROM isysgui_catg_custom WHERE isysgui_catg_custom__list_multi_value = 0;');

        while ($row = $result->get_row()) {
            $customCategoryId = (int)$row['isysgui_catg_custom__id'];

            if (!$customCategoryId) {
                continue;
            }

            $subQuery = "SELECT *
                FROM isys_catg_custom_fields_list
                WHERE isys_catg_custom_fields_list__isysgui_catg_custom__id = {$customCategoryId}
                GROUP BY isys_catg_custom_fields_list__data__id
                ORDER BY isys_catg_custom_fields_list__data__id ASC";

            $countEntryQuery = "SELECT *, COUNT(isys_catg_custom_fields_list__isys_obj__id) AS cnt, GROUP_CONCAT(isys_catg_custom_fields_list__data__id) AS ids
                FROM ({$subQuery}) AS customf
                GROUP BY isys_catg_custom_fields_list__isys_obj__id
                HAVING cnt > 1";

            $duplicatedResult = $this->dao->retrieve($countEntryQuery);

            if (!count($duplicatedResult)) {
                continue;
            }

            echo 'Deleting duplicate entries in custom category "' . $this->language->get($row['isysgui_catg_custom__title']) . '"... <br />';

            while ($duplicatedRow = $duplicatedResult->get_row()) {
                $entryIds = array_map('intval', explode(',', $duplicatedRow['ids']));

                // Sort the values (low to high).
                sort($entryIds);

                // Remove the first entry (lowest ID).
                array_shift($entryIds);

                $this->purgeRecords(
                    (int)$duplicatedRow['objectId'],
                    $entryIds,
                    'isys_catg_custom_fields_list',
                    (new isys_cmdb_dao_category_g_custom_fields($this->container->get('database')))->set_catg_custom_id($customCategoryId)
                );
            }
        }
    }

    /**
     * @param string $table
     * @param string $daoClassName
     * @param string $categoryTitle
     *
     * @return void
     * @throws \isys_exception_database
     */
    private function cleanUp(string $table, string $daoClassName, string $categoryTitle): void
    {
        if (!class_exists($daoClassName)) {
            return;
        }

        if (!is_a($daoClassName, isys_cmdb_dao_category::class, true)) {
            return;
        }

        if (!$this->dao->table_exists($table)) {
            return;
        }

        $categoryDao = $daoClassName::instance($this->container->get('database'));

        $countEntryQuery = "SELECT GROUP_CONCAT({$table}__id) as id, {$table}__isys_obj__id as objectId, count({$table}__isys_obj__id) as cnt
            FROM {$table}
            GROUP BY {$table}__isys_obj__id
            HAVING cnt > 1";

        $duplicatedResult = $this->dao->retrieve($countEntryQuery);
        $duplicatedEntries = count($duplicatedResult);

        if (!$duplicatedEntries) {
            return;
        }

        echo 'Found duplicated entries in category "' . $categoryTitle . '"!<br />';

        while ($duplicatedRow = $duplicatedResult->get_row()) {
            $entryIds = array_map('intval', explode(',', $duplicatedRow['id']));

            // Sort the values (low to high).
            sort($entryIds);

            // Remove the first entry (lowest ID).
            array_shift($entryIds);

            $this->purgeRecords((int)$duplicatedRow['objectId'], $entryIds, $table, $categoryDao);
        }
    }

    /**
     * @param int                    $objectId
     * @param array                  $entryIds
     * @param string                 $table
     * @param isys_cmdb_dao_category $dao
     *
     * @return void
     */
    private function purgeRecords(int $objectId, array $entryIds, string $table, isys_cmdb_dao_category $dao): void
    {
        try {
            foreach ($entryIds as $entryId) {
                if (!$dao->rank_record($entryId, C__CMDB__RANK__DIRECTION_DELETE, $table, null, true)) {
                    throw new Exception("An error occured while removing entry #{$entryId} for object #{$objectId}.");
                }
            }

            $entryCount = count($entryIds);

            $this->deletedEntries += $entryCount;

            echo "Cleaned up {$entryCount} entries for object #{$objectId}!<br />";
        } catch (Throwable $e) {
            echo $e->getMessage() . '<br />';
        }
    }
}
