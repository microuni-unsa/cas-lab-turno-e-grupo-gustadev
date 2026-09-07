<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\BaseAssignmentCategoryProcessor;
use idoit\Component\Processor\Category\BaseCategoryProcessor;
use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Exception\Exception;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_component_database;

/**
 * Generic factory to create "global" and "specific" category processors.
 */
class GenericFactory implements CategoryProcessorFactoryInterface
{
    private isys_cmdb_dao $dao;

    private isys_component_database $database;

    /**
     * @param isys_cmdb_dao           $dao
     * @param isys_component_database $database
     */
    public function __construct(isys_cmdb_dao $dao, isys_component_database $database)
    {
        $this->dao = $dao;
        $this->database = $database;

    }

    /**
     * @param string $categoryConstant
     *
     * @return bool
     * @throws \isys_exception_database
     */
    public function supports(string $categoryConstant): bool
    {
        $queryParameter = $this->dao->convert_sql_text($categoryConstant);

        $query = "SELECT isysgui_catg__id AS id
            FROM isysgui_catg
            WHERE isysgui_catg__const = {$queryParameter}

            UNION

            SELECT isysgui_cats__id AS id
            FROM isysgui_cats
            WHERE isysgui_cats__const = {$queryParameter};";

        return $this->dao->retrieve($query)->get_row_value('id') !== null;
    }

    /**
     * @param string $categoryConstant
     *
     * @return CategoryProcessorInterface
     * @throws Exception
     * @throws \isys_exception_database
     */
    public function create(string $categoryConstant): CategoryProcessorInterface
    {
        $queryParameter = $this->dao->convert_sql_text($categoryConstant);

        $query = "SELECT isysgui_catg__class_name AS className
            FROM isysgui_catg
            WHERE isysgui_catg__const = {$queryParameter}

            UNION

            SELECT isysgui_cats__class_name AS className
            FROM isysgui_cats
            WHERE isysgui_cats__const = {$queryParameter};";

        $categoryData = $this->dao->retrieve($query)
            ->get_row();

        if (!$categoryData) {
            throw new Exception("Category with constant '{$categoryConstant}' could not be found.");
        }

        $categoryClass = $categoryData['className'];

        if (!class_exists($categoryClass)) {
            throw new Exception("Category DAO for '{$categoryConstant}' could not be found.");
        }

        if (!is_a($categoryData['className'], isys_cmdb_dao_category::class, true)) {
            throw new Exception("Category DAO for '{$categoryConstant}' needs to extend 'isys_cmdb_dao_category'.");
        }

        $dao = $categoryClass::instance($this->database);

        // Check if we have a 'assignment' category.
        if (in_array(ObjectBrowserReceiver::class, class_implements($dao), true)) {
            return new BaseAssignmentCategoryProcessor($dao);
        }

        return new BaseCategoryProcessor($dao);
    }
}
