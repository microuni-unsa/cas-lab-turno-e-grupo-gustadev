<?php

namespace idoit\Module\Report\SqlQuery\Structure;

use idoit\Component\Property\Property;
use idoit\Module\Report\SqlQuery\Structure\SelectType\AbstractSelectType;
use idoit\Module\Report\SqlQuery\Structure\SelectType\Custom\MultiDialogType;
use idoit\Module\Report\SqlQuery\Structure\SelectType\TypeInterface;

class ReportSelectBuilder
{
    private static $instance = null;

    /**
     * @var TypeInterface[]
     */
    private $selectTypes = [];

    /**
     * @var AbstractSelectType|null
     */
    private $selectBuilder = null;

    /**
     * @return ReportSelectBuilder
     */
    public static function instance()
    {
        if (self::$instance === null) {
            $reportSelectBuilder = new self();
            $reportSelectBuilder->addType(new MultiDialogType());
            self::$instance = $reportSelectBuilder;
        }
        return self::$instance;
    }

    /**
     * @param $type
     *
     * @return void
     */
    protected function addType($type)
    {
        $this->selectTypes[] = $type;
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        foreach ($this->selectTypes as $type) {
            if ($type instanceof AbstractSelectType && $type->isApplicable($property)) {
                $type->setProperty($property);
                $this->selectBuilder = $type;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $alias
     *
     * @return string
     */
    public function buildSelect($alias, $conditions = [])
    {
        $this->selectBuilder->setAlias($alias);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $this->selectBuilder->addCondition($condition);
            }
        }

        return $this->selectBuilder->buildSelect();
    }
}
