<?php

namespace idoit\Component\Processor\Category;

use Exception;
use isys_auth_cmdb;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_global;
use isys_module_cmdb;

/**
 * Assignment category processor component to establish a unified internal API to apply CRUD operations on categories.
 */
class BaseAssignmentCategoryProcessor implements AssignmentCategoryProcessorInterface
{
    private isys_auth_cmdb $auth;

    private isys_cmdb_dao_category $dao;

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

        return $this->dao->retrieve("SELECT * FROM {$table} WHERE {$table}__id IN ({$idList}) {$statusCondition};");
    }

    public function readByObject(int|array $id, int|array|null $status = null): array
    {
        $table = $this->dao->get_table();
        $idList = $this->prepareIdValues((array)$id);

        if ($this->dao instanceof isys_cmdb_dao_category_global) {
            $table .= '_list';
        }

        $statusCondition = '';

        if ($status !== null) {
            $statusList = $this->prepareIdValues((array)$status);

            $statusCondition = "AND {$table}__status IN ({$statusList})";
        }

        return $this->dao->retrieve("SELECT * FROM {$table} WHERE {$table}__isys_obj__id IN ({$idList}) {$statusCondition};");
    }

    public function archive(int|array $id): void
    {
        if ($this->canOnlyPurge()) {
            throw new Exception('The entries of this category can not be archived!');
        }

        $this->auth->category($this->auth::ARCHIVE, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__ARCHIVED);
    }

    public function delete(int|array $id): void
    {
        if ($this->canOnlyPurge()) {
            throw new Exception('The entries of this category can not be deleted!');
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
        if ($this->canOnlyPurge()) {
            throw new Exception('The entries of this category can not be restored!');
        }

        $this->auth->category($this->auth::EDIT, $this->dao->get_category_const());

        $this->rank((array)$id, (int)C__RECORD_STATUS__NORMAL);
    }

    public function isSingleValue(): bool
    {
        return false;
    }

    public function isMultiValue(): bool
    {
        return true;
    }

    private function canOnlyPurge(): bool
    {
        // These categories can only attach / detach.
        $purgeableCategories = [
            'C__CATG__ASSIGNED_LOGICAL_UNIT',
            'C__CATG__ASSIGNED_SIM_CARDS',
            'C__CATG__GUEST_SYSTEMS',
            'C__CATG__QINQ_CE',
            'C__CATG__OBJECT',
            'C__CATS__ORGANIZATION_PERSONS',
            'C__CATS__PERSON_ASSIGNED_GROUPS',
            'C__CATS__PERSON_GROUP_MEMBERS',
        ];

        return in_array($this->dao->get_category_const(), $purgeableCategories, true);
    }

    private function rank(array $ids, int $targetStatus): void
    {
        $table = $this->dao->get_table();

        foreach ($ids as $id) {
            $currentStatus = (int)$this->readById($id)->get_row_value('status');

            $rankIterations = $targetStatus - $currentStatus;
            $directionDelete = $rankIterations > 0;

            if ($rankIterations === 0) {
                // @todo Discuss if we should throw an exception in this case or simply continue (see ID-10704).
            }

            for ($i = 0; $i < abs($rankIterations); $i++) {
                if ($directionDelete > 0) {
                    $this->dao->rank_record($id, C__CMDB__RANK__DIRECTION_DELETE, $table, null, $targetStatus == C__RECORD_STATUS__PURGE);
                } else {
                    $this->dao->rank_record($id, C__CMDB__RANK__DIRECTION_RECYCLE, $table);
                }
            }
        }
    }

    public function set(array $ids, int $objectId): void
    {
        // TODO: Implement set() method.
    }

    public function attach(array $ids, int $objectId): void
    {
        // TODO: Implement attach() method.
    }

    public function detach(array $ids, int $objectId): void
    {
        // TODO: Implement detach() method.
    }

    public function save(?int $id, int $objectId, array $data): int
    {
        // TODO: Implement save() method.
    }
}
