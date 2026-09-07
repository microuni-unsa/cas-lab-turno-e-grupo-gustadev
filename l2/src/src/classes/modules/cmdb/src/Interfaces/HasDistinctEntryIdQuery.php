<?php

namespace idoit\Module\Cmdb\Interfaces;

interface HasDistinctEntryIdQuery
{
    /**
     * This specific query might be necessary in order to fetch the correct entry ID.
     *
     * @param int $objectId
     *
     * @return string
     * @see ID-9959
     * @see ID-9943
     */
    public function getEntryIdQuery(int $objectId): string;
}
