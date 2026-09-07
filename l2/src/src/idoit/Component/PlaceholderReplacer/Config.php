<?php

namespace idoit\Component\PlaceholderReplacer;

use isys_application;

class Config
{
    /**
     * @var int|null
     */
    private ?int $objectId = null;

    /**
     * @var int|null
     */
    private ?int $objectTypeId = null;

    /**
     * @var string|null
     */
    private ?string $objectTitle = null;

    /**
     * @var string|null
     */
    private ?string $sysId = null;

    /**
     * @var string|null
     */
    private ?string $table = null;

    /**
     * @var int
     */
    private int $tableInitialCount = 0;

    /**
     * @var int
     */
    private int $objectTypeInitialCount = 0;

    /**
     * @var bool
     */
    private bool $preview = false;

    /**
     * @param int|null    $objectId
     * @param int|null    $objectTypeId
     * @param string|null $objectTitle
     * @param string|null $sysId
     * @param string|null $table
     */
    public function __construct(?int $objectId = null, ?int $objectTypeId = null, ?string $objectTitle = '', ?string $sysId = '', ?string $table = 'isys_catg_accounting_list', ?bool $preview = false)
    {
        $this->objectId = $objectId;
        $this->objectTypeId = $objectTypeId;
        $this->objectTitle = $objectTitle;
        $this->sysId = $sysId;
        $this->table = $table;
        $this->preview = $preview;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function getObjectTypeId(): ?int
    {
        return $this->objectTypeId;
    }

    public function getObjectTitle(): ?string
    {
        return $this->objectTitle;
    }

    public function getSysId(): ?string
    {
        return $this->sysId;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @return int
     */
    public function getTableInitialCount(): int
    {
        return $this->tableInitialCount;
    }

    /**
     * @param int $tableInitialCount
     *
     * @return $this
     */
    public function setTableInitialCount(int $tableInitialCount): static
    {
        $this->tableInitialCount = $tableInitialCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectTypeInitialCount(): int
    {
        return $this->objectTypeInitialCount;
    }

    /**
     * @param int $objectTypeInitialCount
     *
     * @return $this
     */
    public function setObjectTypeInitialCount(int $objectTypeInitialCount): static
    {
        $this->objectTypeInitialCount = $objectTypeInitialCount;

        return $this;
    }

    /**
     * @see ID-10763 On duplication we do not update the counter for a preview
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->preview;
    }

    /**
     * @param int|null    $objectId
     * @param int|null    $objectTypeId
     * @param string|null $objectTitle
     * @param string|null $sysId
     * @param string|null $table
     *
     * @return Config
     * @throws \Exception
     */
    public static function factory(?int $objectId = null, ?int $objectTypeId = null, ?string $objectTitle = '', ?string $sysId = '', ?string $table = 'isys_catg_accounting_list', ?bool $preview = false): Config
    {
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $objectTypeInitialCount = 0;

        $config = new static($objectId, $objectTypeId, $objectTitle ?? '', $sysId ?? '', $table, $preview);

        if ($table !== '') {
            if (!$dao->table_exists($table)) {
                $table .= '_list';
            }

            // @see ID-10714 Check if the table exists, before continuing.
            if ($dao->table_exists($table)) {
                $tableIdCountQuery = "SELECT MAX({$table}__id) AS cnt FROM {$table};";
                $tableInitialCount = $dao->retrieve($tableIdCountQuery)->get_row_value('cnt') ?? 0;
                $config->setTableInitialCount($tableInitialCount);
            }
        }

        if ($objectTypeId) {
            $objectTypeCountQuery = sprintf("SELECT COUNT(1) AS cnt FROM isys_obj
            WHERE isys_obj__isys_obj_type__id = %d;", $dao->convert_sql_id($objectTypeId));
            $objectTypeInitialCount = $dao->retrieve($objectTypeCountQuery)->get_row_value('cnt') ?? 0;
            $config->setObjectTypeInitialCount($objectTypeInitialCount);
        }

        return $config;
    }
}
