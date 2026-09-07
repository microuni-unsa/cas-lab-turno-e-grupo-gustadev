<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Processors;

use Exception;
use isys_application;
use isys_cmdb_dao_category;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProcessor
{
    /**
     * @var string
     */
    protected static $categoryConst = '';

    /**
     * @var string[]
     */
    protected static $categoryDependency = [];

    /**
     * @var isys_cmdb_dao_category
     */
    protected $dao;

    /**
     * @var array
     */
    protected $dependentSyncData = [];

    /**
     * @param isys_cmdb_dao_category $dao
     */
    public function __construct(isys_cmdb_dao_category $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return static
     * @throws Exception
     */
    public static function factory()
    {
        $db = isys_application::instance()->container->get('database');
        $categoryConst = static::$categoryConst;

        $result = self::getByCategory([$categoryConst]);

        if (count($result)) {
            $className = $result[0]['className'];

            if (!class_exists($className)) {
                throw new Exception("The category dao for '{$categoryConst}' does not exist.", Response::HTTP_NOT_FOUND);
            }

            return new static(new $className($db));
        }

        throw new Exception("Processor for category const {$categoryConst} could not be loaded!", Response::HTTP_NOT_FOUND);
    }

    /**
     * @param array $categories
     *
     * @return array
     * @throws Exception
     */
    public static function getByCategory(array $categories): array
    {
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $categoryData = [];
        $recordStatusNormal = $dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
        $categoryConstants = implode(',', array_map(fn ($class) => $dao->convert_sql_text($class), $categories));

        $sql = "SELECT
              isysgui_catg__id AS id,
              isysgui_catg__class_name AS className,
              isysgui_catg__const AS const,
              '" . C__CMDB__CATEGORY__TYPE_GLOBAL . "' as type
            FROM isysgui_catg
            WHERE isysgui_catg__const IN ({$categoryConstants})
            AND isysgui_catg__status = {$recordStatusNormal}

            UNION

            SELECT
              isysgui_catg_custom__id AS id,
              'isys_cmdb_dao_category_g_custom_fields' AS className,
              isysgui_catg_custom__const AS const,
              '" . C__CMDB__CATEGORY__TYPE_CUSTOM . "' as type
            FROM isysgui_catg_custom
            WHERE isysgui_catg_custom__const IN ({$categoryConstants})
            AND isysgui_catg_custom__status = {$recordStatusNormal}

            UNION

            SELECT
              isysgui_cats__id AS id,
              isysgui_cats__class_name AS className,
              isysgui_cats__const AS const,
              '" . C__CMDB__CATEGORY__TYPE_SPECIFIC . "' as type
            FROM isysgui_cats
            WHERE isysgui_cats__const IN ({$categoryConstants})
            AND isysgui_cats__status = {$recordStatusNormal};";

        $result = $dao->retrieve($sql);

        while ($row = $result->get_row()) {
            $categoryData[] = $row;
        }

        return $categoryData;
    }

    /**
     * @return mixed
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @return string
     */
    public static function getCategoryConst()
    {
        return static::$categoryConst;
    }

    /**
     * @param array $syncData
     */
    public function setDependentSyncData(array $syncData)
    {
        if (empty(static::$categoryDependency)) {
            return;
        }

        foreach (static::$categoryDependency as $categoryConst) {
            $this->dependentSyncData[$categoryConst] = $syncData[$categoryConst];
        }
    }
}
