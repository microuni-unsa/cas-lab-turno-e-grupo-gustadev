<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\BaseCategoryProcessor;
use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Exception\Exception;
use isys_cmdb_dao;
use isys_cmdb_dao_category_g_custom_fields;
use isys_component_database;

/**
 * Custom factory specifically to create custom category processors.
 */
class CustomFactory implements CategoryProcessorFactoryInterface
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

        $query = "SELECT isysgui_catg_custom__id AS id
            FROM isysgui_catg_custom
            WHERE isysgui_catg_custom__const = {$queryParameter}";

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

        $query = "SELECT isysgui_catg_custom__id AS id
            FROM isysgui_catg_custom
            WHERE isysgui_catg_custom__const = {$queryParameter}";

        $categoryData = $this->dao->retrieve($query)->get_row();

        if (!$categoryData) {
            throw new Exception("Category with constant '{$categoryConstant}' could not be found.");
        }

        return new BaseCategoryProcessor((new isys_cmdb_dao_category_g_custom_fields($this->database))->set_catg_custom_id((int)$categoryData['id']));
    }
}
