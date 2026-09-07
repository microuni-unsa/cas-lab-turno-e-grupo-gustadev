<?php

namespace idoit\Component\Tree;

use Exception;

/**
 * Class to register and fetch tree filters.
 *
 * @package idoit\Component\Tree
 */
class Filter
{
    private array $filters = [];

    /**
     * @param string              $identifier
     * @param TreeFilterInterface $treeFilter
     *
     * @return $this
     */
    public function register(string $identifier, TreeFilterInterface $treeFilter): static
    {
        $this->filters[$identifier] = $treeFilter;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function exists(string $identifier): bool
    {
        return isset($this->filters[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return TreeFilterInterface
     * @throws Exception
     */
    public function get(string $identifier): TreeFilterInterface
    {
        if (!$this->exists($identifier)) {
            throw new Exception("The requested upload type '{$identifier}' does not exist!");
        }

        return $this->filters[$identifier];
    }
}
