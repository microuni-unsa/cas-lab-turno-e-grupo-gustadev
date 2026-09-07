<?php

namespace idoit\Component\Processor\Category;

use Exception;
use idoit\Module\Cmdb\Component\CategoryChanges\Changes;
use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Component\SyncMerger\Merger;
use isys_application;
use isys_auth_cmdb;
use isys_cmdb_dao_category;
use isys_import_handler_cmdb;
use isys_module_cmdb;

/**
 * Category processor component to establish a unified internal API to apply CRUD operations on categories.
 */
class BaseCategoryProcessor implements CategoryProcessorInterface
{
    private isys_auth_cmdb $auth;

    protected isys_cmdb_dao_category $dao;

    public function __construct(isys_cmdb_dao_category $dao)
    {
        $this->auth = isys_module_cmdb::getAuth();
        $this->dao = $dao;
    }

    public function supports(string $categoryConstant): bool
    {
        return true;
    }

    protected function prepareIdValues(array $ids): string
    {
        return implode(', ', array_map('intval', array_filter($ids, fn ($id) => is_numeric($id))));
    }

    public function readById(int|array $id, int|array|null $status = null): array
    {
        $table = $this->dao->get_table();
        $idList = $this->prepareIdValues((array)$id);

        if (!str_ends_with($table, '_list')) {
            $table .= '_list';
        }

        $statusCondition = '';

        if ($status !== null) {
            $statusList = $this->prepareIdValues((array)$status);

            $statusCondition = "AND {$table}__status IN ({$statusList})";
        }

        $formatted = [];
        $result = $this->dao->retrieve("SELECT * FROM {$table} WHERE {$table}__id IN ({$idList}) {$statusCondition};");

        while ($row = $result->get_row()) {
            // @todo  Format the result!
            $formatted[] = $row;
        }

        return $formatted;
    }

    public function readByObject(int|array $id, int|array|null $status = null): array
    {
        $table = $this->dao->get_table();
        $idList = $this->prepareIdValues((array)$id);

        if (!str_ends_with($table, '_list')) {
            $table .= '_list';
        }

        $statusCondition = '';

        if ($status !== null) {
            $statusList = $this->prepareIdValues((array)$status);

            $statusCondition = "AND {$table}__status IN ({$statusList})";
        }

        $formatted = [];
        $result = $this->dao->retrieve("SELECT * FROM {$table} WHERE {$table}__isys_obj__id IN ({$idList}) {$statusCondition};");

        while ($row = $result->get_row()) {
            // @todo  Format the result!
            $formatted[] = $row;
        }

        return $formatted;
    }

    public function archive(int|array $id): void
    {
        if ($this->isSingleValue()) {
            throw new Exception('Single value category entries can not be archived!');
        }

        $this->auth->category($this->auth::ARCHIVE, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__ARCHIVED);
    }

    public function delete(int|array $id): void
    {
        if ($this->isSingleValue()) {
            throw new Exception('Single value category entries can not be deleted!');
        }

        $this->auth->category($this->auth::DELETE, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__DELETED);
    }

    public function purge(int|array $id): void
    {
        $this->auth->category($this->auth::SUPERVISOR, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__PURGE);
    }

    public function restore(int|array $id): void
    {
        if ($this->isSingleValue()) {
            throw new Exception('Single value category entries can not be restored!');
        }

        $this->auth->category($this->auth::EDIT, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__NORMAL);
    }

    public function isSingleValue(): bool
    {
        return !$this->dao->is_multivalued();
    }

    public function isMultiValue(): bool
    {
        return $this->dao->is_multivalued();
    }

    private function rank(array $ids, int $targetStatus): void
    {
        $table = $this->dao->get_table();

        foreach ($ids as $id) {
            $currentStatus = (int)$this->readById($id)->get_row_value('status');

            $rankIterations = $targetStatus - $currentStatus;
            $directionDelete = $rankIterations > 0;

            for ($i = 0; $i < abs($rankIterations); $i++) {
                if ($directionDelete > 0) {
                    $this->dao->rank_record($id, C__CMDB__RANK__DIRECTION_DELETE, $table, null, $targetStatus == C__RECORD_STATUS__PURGE);
                } else {
                    $this->dao->rank_record($id, C__CMDB__RANK__DIRECTION_RECYCLE, $table);
                }
            }
        }
    }

    public function save(int|null $id, int $objectId, array $data): int
    {
        $signal = isys_application::instance()->container->get('signals');

        // @todo Determine mode, depending on $id and SV / MV!
        $mode = isys_import_handler_cmdb::C__UPDATE || isys_import_handler_cmdb::C__CREATE;


        // 1. Validate data
        if ($id !== null) {
            $this->dao->set_list_id($id);
        }

        $this->dao->set_object_id($objectId);
        $this->dao->validate($data);




        // @todo  Prepare sync data!
        $syncData = $this->prepareSyncData($id, $objectId, $data);

        // 2. Dispatch "before save" signal
        $signal->emit(
            'mod.api.beforeCmdbCategorySync',
            $syncData,
            $objectId,
            $mode,
            $this->dao
        );

        // 3. Prepare changes (always prepare the 'changes' based on the current state in the DB + the given $data)
        $changes = $this->getCategoryChanges($this->dao, $objectId, $syncData, $id);

        // 4. Call "sync" of DAO
        $syncResult = $this->dao->sync($syncData, $objectId, $mode);

        // 5. Log changes
        if ($syncResult && $changes instanceof Changes) {
            $this->dao->logbook_update('C__LOGBOOK_EVENT__CATEGORY_CHANGED', $changes);
        }

        // 6. Dispatch "after save" signal
        $signal->emit(
            'mod.cmdb.afterCategoryEntrySave',
            $this->dao,
            $l_data['data_id'] ?? null,
            $syncResult,
            $objectId,
            $syncData,
            [] // @todo Check - previously this is used for outdated 'changes' format?
        );

        // 7. Return category entry ID
        if (!is_numeric($syncResult)) {
            // @todo PHPDoc says that 'true' will be returned if nothing changed.
        }

        return (int)$syncResult;
    }

    protected function prepareSyncData(int $id, int $objectId, array $data): array
    {
        $fakeEntry = [
            Config::CONFIG_DATA_ID => $id,
            Config::CONFIG_PROPERTIES => ['id' => $id]
        ];

        // @todo Process '$data'.

        return Merger::instance(Config::instance($this->dao, $objectId, $fakeEntry))->getDataForSync();
    }

    protected function getCategoryChanges(isys_cmdb_dao_category $p_category_dao, int $objectId, array $syncData = [], ?int $entryId = null): ?Changes
    {
        if (empty($syncData)) {
            return null;
        }

        $fakeEntry = [
            Config::CONFIG_DATA_ID => $entryId,
            Config::CONFIG_PROPERTIES => []
        ];

        $currentData = Merger::instance(Config::instance($p_category_dao, $objectId, $fakeEntry))->getDataForSync();

        if (isset($syncData[isys_import_handler_cmdb::C__PROPERTIES]) && isset($currentData[isys_import_handler_cmdb::C__PROPERTIES])) {
            $currentDataValues = $syncDataValues = [];

            foreach ($syncData[isys_import_handler_cmdb::C__PROPERTIES] as $index => $value) {
                // @todo  Verify 'empty()' or 'trim($val) === ""'?
                $syncDataValues[$index] = empty($value['value']) ? null : $value['value'];
            }

            foreach ($currentData[isys_import_handler_cmdb::C__PROPERTIES] as $index => $value) {
                $currentDataValues[$index] = $value['value'];
                if (!isset($syncData[isys_import_handler_cmdb::C__PROPERTIES][$index])) {
                    $syncDataValues[$index] = $value['value'];
                }
            }

            $changes = Changes::instance($p_category_dao, $objectId, $entryId, ($entryId === null ? $currentDataValues : []), $syncDataValues);
            $changes->processChanges();
            return $changes;
        }

        return null;
    }
}
