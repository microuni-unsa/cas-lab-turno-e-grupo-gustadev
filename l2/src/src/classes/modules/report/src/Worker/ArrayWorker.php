<?php

namespace idoit\Module\Report\Worker;

use idoit\Module\Report\Protocol\Worker;

/**
 * Report Array Worker
 *
 * @package     idoit\Module\Report\Export
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ArrayWorker implements Worker
{
    /** @var array */
    private array $data = [];

    /**
     * @param array $row
     *
     * @return void
     */
    public function work(array $row): void
    {
        $this->data[] = $row;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return $this->data;
    }
}
