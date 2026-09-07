<?php

namespace idoit\Component\Table\Pagerfanta\Adapter;

/**
 * i-doit ListDaoAdapter for Pagerfanta.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ListDaoAdapter extends DaoAdapter
{
    /**
     * @param int $offset
     * @param int $length
     *
     * @return iterable
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->dao->load($offset, $length);
    }
}
