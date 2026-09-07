<?php

namespace idoit\Component\Browser;

use isys_cmdb_dao;
use isys_component_database;

abstract class Filter implements FilterInterface
{
    /**
     * @var isys_component_database
     */
    protected $db;

    /**
     * @var isys_cmdb_dao
     */
    protected $dao;

    /**
     * @var mixed
     */
    protected $parameter;

    /**
     * Condition constructor.
     *
     * @param isys_component_database $db
     */
    public function __construct(\isys_component_database $db)
    {
        $this->db = $db;
        $this->dao = isys_cmdb_dao::instance($this->db);
    }

    /**
     * @inheritdoc
     */
    public function setParameter($parameter): static
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParameter(): mixed
    {
        return $this->parameter;
    }

    /**
     * @inheritdoc
     */
    public function getQueryCondition(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function hasVisitor(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function visit(array $objects): array
    {
        return [];
    }
}
