<?php

namespace idoit\Component\Download;

/**
 * Class DownloadType
 *
 * @package idoit\Component\Download
 */
class DownloadType
{
    /** @var callable */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }
}
