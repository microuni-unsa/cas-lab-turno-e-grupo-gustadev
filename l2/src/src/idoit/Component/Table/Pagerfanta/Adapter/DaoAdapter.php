<?php

namespace idoit\Component\Table\Pagerfanta\Adapter;

use idoit\Exception\Exception;
use isys_cmdb_dao_list_objects;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * i-doit DaoAdapter for Pagerfanta.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DaoAdapter implements AdapterInterface
{
    /**
     * @var isys_cmdb_dao_list_objects
     */
    protected $dao;

    /**
     * @var null
     */
    protected $nbResults = null;

    /**
     * @param \isys_cmdb_dao_list_objects $dao
     */
    public function __construct(isys_cmdb_dao_list_objects $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return int
     */
    public function getNbResults(): int
    {
        if ($this->nbResults === null) {
            $this->nbResults = $this->dao->get_object_count();
        }

        return $this->nbResults;
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     * @throws \isys_exception_database
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $l_query = $this->dao->get_table_query($offset, $length);

        if (empty($l_query)) {
            return [];
        }

        return $this->dao->retrieve($l_query)->__as_array();
    }
}
