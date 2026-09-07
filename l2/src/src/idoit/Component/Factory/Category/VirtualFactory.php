<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Component\Processor\Category\VirtualCategoryProcessor;
use isys_cmdb_dao;
use isys_cmdb_dao_category;

/**
 * Virtual factory to create virtual category processors.
 */
class VirtualFactory implements CategoryProcessorFactoryInterface
{
    private isys_cmdb_dao $dao;

    /**
     * @param isys_cmdb_dao           $dao
     */
    public function __construct(isys_cmdb_dao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param string $categoryConstant
     *
     * @return bool
     * @throws \isys_exception_database
     */
    public function supports(string $categoryConstant): bool
    {
        $viewTypeParameter = $this->dao->convert_sql_int(isys_cmdb_dao_category::TYPE_VIEW);
        $queryParameter = $this->dao->convert_sql_text($categoryConstant);

        $query = "SELECT isysgui_catg__id AS id
            FROM isysgui_catg
            WHERE isysgui_catg__const = {$queryParameter}
            AND isysgui_catg__source_table IN ('isys_catg_virtual', 'isys_catg_virtual_list')
            AND isysgui_catg__type & {$viewTypeParameter}

            UNION

            SELECT isysgui_cats__id AS id
            FROM isysgui_cats
            WHERE isysgui_cats__const = {$queryParameter}
            AND isysgui_cats__source_table IN ('isys_cats_virtual', 'isys_cats_virtual_list')
            AND isysgui_cats__type & {$viewTypeParameter};";

        return $this->dao->retrieve($query)->get_row_value('id') !== null;
    }

    /**
     * @param string $categoryConstant
     *
     * @return CategoryProcessorInterface
     */
    public function create(string $categoryConstant): CategoryProcessorInterface
    {
        return new VirtualCategoryProcessor();
    }
}
