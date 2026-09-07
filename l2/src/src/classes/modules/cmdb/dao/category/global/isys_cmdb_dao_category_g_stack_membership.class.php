<?php

use idoit\Component\Property\Type\VirtualProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;

/**
 * i-doit
 *
 * DAO: specific category for stack membership.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_stack_membership extends isys_cmdb_dao_category_g_virtual implements ObjectBrowserAssignedEntries
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'stack_membership';

    /**
     * @return array
     */
    protected function properties()
    {
        return [
            'assigned_object' => (new VirtualProperty(
                'C__CATG__STACK_MEMBERSHIP__STACK',
                'LC__CATG__STACK_MEMBERSHIP__STACK',
                'isys_catg_stack_member_list__isys_obj__id',
                'isys_catg_stack_member_list'
            ))
        ];
    }

    /**
     * @param int|int[] $ids
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws isys_exception_database
     */
    public function getAttachedEntries($ids, $tag = '', $asId = false): CollectionInterface
    {
        $collection = new ObjectCollection();

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return $collection;
        }

        $query = 'SELECT
            isys_catg_stack_member_list__id as id,
            isys_catg_stack_member_list__isys_obj__id as objId,
            isys_obj__title as title,
            isys_obj__sysid as sysid,
            isys_obj_type__title as objType
            FROM isys_catg_stack_member_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_stack_member_list__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_stack_member_list__stack_member IN (' . implode(',', array_map(function ($id) {
            return $this->convert_sql_id($id);
        }, $ids)). ')';

        $result = $this->retrieve($query);

        while ($row = $result->get_row()) {
            $collection->addEntry(ObjectEntry::factory(
                $row['objId'],
                $row['title'],
                $row['sysid'],
                isys_application::instance()->container->get('language')->get($row['objType']),
                $row
            ));
        }

        return $collection;
    }
}
