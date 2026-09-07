<?php

namespace idoit\Module\Report\SqlQuery\Structure;

/**
 * Selection of a Select
 *
 * @package     idoit\Module\Report\SqlQuery\Structure
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8
 */
class SelectSubSelect
{
    /**
     * @var string
     */
    private $selectQuery = '';

    /**
     * @var string
     */
    private $selectAlias = '';

    /**
     * @var string
     */
    private $selectTable = '';

    /**
     * @var \idoit\Module\Report\SqlQuery\Structure\SelectCondition
     */
    private $selectCondition;

    /**
     * @var \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy
     */
    private $selectGroupBy;

    /**
     * @var string
     */
    private $selectPrimaryKey = '';

    /**
     * @var string
     */
    private $selectReferenceKey = '';

    /**
     * @var string
     */
    private $selectConditionField = '';

    /**
     * @var int
     */
    private $selectLimit = null;

    /**
     * @var int
     */
    private $selectStatus = C__RECORD_STATUS__NORMAL;

    /**
     * @var bool
     */
    private bool $asSubselect = true;

    /**
     * @return int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectStatus()
    {
        return $this->selectStatus;
    }

    /**
     * @param int $selectStatus
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectStatus($selectStatus)
    {
        $this->selectStatus = $selectStatus;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectLimit()
    {
        return $this->selectLimit;
    }

    /**
     * @param string $selectLimit
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectLimit($selectLimit)
    {
        $this->selectLimit = $selectLimit;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectConditionField()
    {
        return $this->selectConditionField;
    }

    /**
     * @param string $selectConditionField
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectConditionField($selectConditionField)
    {
        $this->selectConditionField = $selectConditionField;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectReferenceKey()
    {
        return $this->selectReferenceKey;
    }

    /**
     * @param string $selectReferenceKey
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectReferenceKey($selectReferenceKey)
    {
        $this->selectReferenceKey = $selectReferenceKey;

        return $this;
    }

    /**
     * @var string
     */
    private $selectFieldObjectID = '';

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectPrimaryKey()
    {
        return $this->selectPrimaryKey;
    }

    /**
     * @param string $selectPrimaryKey
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectPrimaryKey($selectPrimaryKey)
    {
        $this->selectPrimaryKey = $selectPrimaryKey;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectFieldObjectID()
    {
        return $this->selectFieldObjectID;
    }

    /**
     * @param string $selectFieldObjectID
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectFieldObjectID($selectFieldObjectID)
    {
        $this->selectFieldObjectID = $selectFieldObjectID;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectTable()
    {
        return $this->selectTable;
    }

    /**
     * @param string $selectTable
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectTable($selectTable)
    {
        $this->selectTable = $selectTable;

        return $this;
    }

    /**
     * @return \idoit\Module\Report\SqlQuery\Structure\SelectCondition
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectCondition()
    {
        return $this->selectCondition;
    }

    /**
     * @param $selectCondition \idoit\Module\Report\SqlQuery\Structure\SelectCondition
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectCondition($selectCondition)
    {
        $this->selectCondition = $selectCondition;

        return $this;
    }

    /**
     * @return \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectGroupBy()
    {
        return $this->selectGroupBy;
    }

    /**
     * @param $selectGroupBy \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectGroupBy($selectGroupBy)
    {
        $this->selectGroupBy = $selectGroupBy;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getSelectAlias()
    {
        return $this->selectAlias;
    }

    /**
     * @param string $selectAlias
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setSelectAlias($selectAlias)
    {
        $this->selectAlias = $selectAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectQuery()
    {
        return $this->selectQuery;
    }

    /**
     * @param $selectQuery
     */
    public function setSelectQuery($selectQuery)
    {
        $this->selectQuery = $selectQuery;

        return $this;
    }

    /**
     * @param bool $asSubselect
     *
     * @return $this
     */
    public function setAsSubselect(bool $asSubselect): SelectSubSelect
    {
        $this->asSubselect = $asSubselect;

        return $this;
    }

    /**
     * Get select body substring
     *
     * @return string
     */
    public function getSelection()
    {
        $selectPos = strrpos($this->getSelectQuery(), 'SELECT');
        $fromPos = strrpos($this->getSelectQuery(), 'FROM');
        return $selectPos !== false && $fromPos ? trim(substr(
            $this->getSelectQuery(),
            $selectPos + 6,
            ((substr_count($this->getSelectQuery(), 'FROM') > 1) ? $fromPos : $fromPos) - 6
        )) : '';
    }

    /**
     * @param string $selection
     *
     * @return string
     */
    public function replaceSelection(string $selection)
    {
        $selectPos = strrpos($this->getSelectQuery(), 'SELECT');
        $fromPos = strrpos($this->getSelectQuery(), 'FROM');
        return $selectPos !== false && $fromPos ? trim(substr_replace(
            $this->getSelectQuery(),
            $selection,
            $selectPos + 7,
            ((substr_count($this->getSelectQuery(), 'FROM') > 1) ? $fromPos : $fromPos) - 8
        )) : $this->getSelectQuery();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $groupBy = $this->getSelectGroupBy();
        $condition = $this->getSelectCondition();
        $limit = ($this->getSelectLimit() !== null ? 'LIMIT ' . $this->getSelectLimit() : '');
        $alias = ($this->getSelectAlias() ?: '');
        $select = $this->getSelectQuery();
        if ($groupBy instanceof SelectGroupBy && $groupBy->getGroupConcatSelection() !== '') {
            $select = $this->replaceSelection($groupBy->getGroupConcatSelection());
        }

        if ($this->asSubselect) {
            return "({$select} {$condition} {$groupBy} {$limit}) {$alias}";
        }

        return "{$select} {$condition} {$groupBy} {$limit}";
    }

    /**
     * @param                                                         $select
     * @param string                                                  $selectTable
     * @param string                                                  $selectPrimaryKey
     * @param string                                                  $selectFieldObjectID
     * @param string                                                  $selectReferenceKey
     * @param string                                                  $selectAlias
     * @param \idoit\Module\Report\SqlQuery\Structure\SelectCondition $selectCondition
     * @param \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy   $selectGroupBy
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function factory(
        $select,
        $selectTable = '',
        $selectPrimaryKey = '',
        $selectFieldObjectID = '',
        $selectReferenceKey = '',
        $selectAlias = '',
        $selectCondition = null,
        $selectGroupBy = null,
        $selectConditionField = '',
        $selectLimit = null,
        $selectStatus = C__RECORD_STATUS__NORMAL,
        $asSubselect = true,
    ) {
        $selectSubselect = new SelectSubSelect($select);

        if ($selectCondition === null) {
            $selectCondition = \idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]);
        }

        if ($selectGroupBy === null) {
            $selectGroupBy = \idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory([]);
        }

        return $selectSubselect->setSelectConditionField($selectConditionField)
            ->setSelectCondition($selectCondition)
            ->setSelectTable($selectTable)
            ->setSelectAlias($selectAlias)
            ->setSelectPrimaryKey($selectPrimaryKey)
            ->setSelectFieldObjectID($selectFieldObjectID)
            ->setSelectReferenceKey($selectReferenceKey)
            ->setSelectGroupBy($selectGroupBy)
            ->setSelectStatus($selectStatus)
            ->setSelectLimit($selectLimit);
    }

    /**
     * Selection constructor.
     *
     * @param $selectQuery
     */
    public function __construct($selectQuery)
    {
        $this->setSelectQuery($selectQuery);
    }

    /**
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function __clone()
    {
        $selectSubselect = new SelectSubSelect($this->getSelectQuery());

        return $selectSubselect->setSelectConditionField($this->getSelectConditionField())
            ->setSelectCondition($this->getSelectCondition())
            ->setSelectTable($this->getSelectTable())
            ->setSelectAlias($this->getSelectAlias())
            ->setSelectPrimaryKey($this->getSelectPrimaryKey())
            ->setSelectFieldObjectID($this->getSelectFieldObjectID())
            ->setSelectReferenceKey($this->getSelectReferenceKey())
            ->setSelectGroupBy($this->getSelectGroupBy())
            ->setSelectStatus($this->getSelectStatus())
            ->setSelectLimit($this->getSelectLimit());
    }
}
