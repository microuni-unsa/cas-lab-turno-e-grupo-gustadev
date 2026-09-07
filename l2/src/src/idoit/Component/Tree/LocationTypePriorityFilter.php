<?php

namespace idoit\Component\Tree;

use idoit\Module\Cmdb\Model\Tree;
use isys_cmdb_dao;

/**
 * Tree filter to prioritize either physical or logical location types.
 *
 * @package idoit\Component\Tree
 */
class LocationTypePriorityFilter implements TreeFilterInterface
{
    private isys_cmdb_dao $dao;
    private bool $prioritizePhysical;
    private bool $prioritizeLogical;

    public function __construct(isys_cmdb_dao $dao, bool $prioritizePhysical, bool $prioritizeLogical)
    {
        if ($prioritizePhysical === $prioritizeLogical) {
            throw new \Exception('You can not prioritize both location types.');
        }

        $this->dao = $dao;
        $this->prioritizePhysical = $prioritizePhysical;
        $this->prioritizeLogical = $prioritizeLogical;
    }

    /**
     * @param int|null    $parentObjectId
     * @param string $mode
     * @param int    $objectId
     *
     * @return bool
     */
    public function shouldSkip(?int $parentObjectId, string $mode, int $objectId): bool
    {
        $statusNormal = $this->dao->convert_sql_int(C__RECORD_STATUS__NORMAL);

        // Skip the (logically assigned) object, if it has a physical location.
        if ($this->prioritizePhysical && $mode === Tree::MODE_LOGICAL) {
            $query = "SELECT COUNT(1) AS cnt
                FROM isys_catg_location_list
                WHERE isys_catg_location_list__isys_obj__id = {$objectId}
                AND isys_catg_location_list__parentid > 1
                AND isys_catg_location_list__status = {$statusNormal}";

            if ($this->dao->retrieve($query)->get_row_value('cnt')) {
                // Object has a physical location.
                return true;
            }
        }

        // Skip the (physically assigned) object, if it has a logical location.
        if ($this->prioritizeLogical && $mode === Tree::MODE_PHSYICAL) {
            $query = "SELECT COUNT(1) AS cnt
                FROM isys_catg_logical_unit_list
                WHERE isys_catg_logical_unit_list__isys_obj__id = {$objectId}
                AND isys_catg_logical_unit_list__isys_obj__id__parent > 1
                AND isys_catg_logical_unit_list__status = {$statusNormal}";

            if ($this->dao->retrieve($query)->get_row_value('cnt')) {
                // Object has a logical location.
                return true;
            }
        }

        return false;
    }
}
