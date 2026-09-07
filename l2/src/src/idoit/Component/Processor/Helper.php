<?php

namespace idoit\Component\Processor;

use idoit\Component\Processor\Dto\Shape\CustomCategoryShape;
use idoit\Component\Processor\Dto\Shape\GlobalCategoryShape;
use idoit\Component\Processor\Dto\Shape\SpecificCategoryShape;
use idoit\Module\SyneticsFlows\Serialization\Serializer;
use Throwable;

/**
 * Helper class for different processors.
 */
class Helper
{
    /**
     * @param int $objectTypeId
     *
     * @return GlobalCategoryShape[]|CustomCategoryShape[]
     */
    public static function prepareAssignedCategories(int $objectTypeId): array
    {
        try {
            $language = \isys_application::instance()->container->get('language');
            $dao = \isys_application::instance()->container->get('cmdb_dao');

            $categoryQuery = "SELECT 'global' AS type,
                isysgui_catg__id AS id,
                isysgui_catg__title AS titleRaw,
                isysgui_catg__const AS constant
                FROM isys_obj_type_2_isysgui_catg
                INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id = {$objectTypeId}

                UNION

                SELECT 'custom' AS type,
                isysgui_catg_custom__id AS id,
                isysgui_catg_custom__title AS titleRaw,
                isysgui_catg_custom__const AS constant
                FROM isys_obj_type_2_isysgui_catg_custom
                INNER JOIN isysgui_catg_custom ON isysgui_catg_custom__id = isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
                WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = {$objectTypeId};";

            $categoryResult = $dao->retrieve($categoryQuery);

            while ($categoryRow = $categoryResult->get_row()) {
                $categoryRow['title'] = $language->get($categoryRow['titleRaw']);

                if ($categoryRow['type'] === 'global') {
                    $assignedCategories[] = Serializer::fromJson(GlobalCategoryShape::class, $categoryRow);
                } else {
                    $assignedCategories[] = Serializer::fromJson(CustomCategoryShape::class, $categoryRow);
                }
            }

            return $assignedCategories;
        } catch (Throwable $exception) {
            return [];
        }
    }

    /**
     * @param int $objectTypeId
     *
     * @return string[]
     */
    public static function prepareOverviewCategories(int $objectTypeId, string $objectTypeConstant): array
    {
        try {
            $language = \isys_application::instance()->container->get('language');
            $dao = \isys_application::instance()->container->get('cmdb_dao');

            $categoryQuery = "SELECT 'global' AS type,
                c.isysgui_catg__id AS id,
                c.isysgui_catg__title AS titleRaw,
                c.isysgui_catg__const AS constant,
                o.isys_obj_type_2_isysgui_catg_overview__sort AS sort
                FROM isys_obj_type_2_isysgui_catg_overview AS o
                INNER JOIN isysgui_catg AS c ON c.isysgui_catg__id = o.isysgui_catg__id
                WHERE o.isys_obj_type__id = {$objectTypeId}

                UNION

                SELECT 'custom' AS type,
                c.isysgui_catg_custom__id AS id,
                c.isysgui_catg_custom__title AS titleRaw,
                c.isysgui_catg_custom__const AS constant,
                o.isys_obj_type_2_isysgui_catg_custom_overview__sort AS sort
                FROM isys_obj_type_2_isysgui_catg_custom_overview AS o
                INNER JOIN isysgui_catg_custom AS c ON c.isysgui_catg_custom__id = o.isysgui_catg_custom__id
                WHERE o.isys_obj_type__id = {$objectTypeId};";

            $count = 0;
            $categoryResult = $dao->retrieve($categoryQuery);

            while ($categoryRow = $categoryResult->get_row()) {
                $categoryRow['title'] = $language->get($categoryRow['titleRaw']);
                $sortCounter = "{$categoryRow['sort']}.{$count}";

                if ($categoryRow['type'] === 'global') {
                    $assignedCategories[] = Serializer::fromJson(GlobalCategoryShape::class, $categoryRow);
                } else {
                    $assignedCategories[] = Serializer::fromJson(CustomCategoryShape::class, $categoryRow);
                }

                $count ++;
            }

            $specificCategoryPosition = \isys_tenantsettings::get("cmdb.objtype.{$objectTypeConstant}.specific-cat-position", null);

            if (\isys_format_json::is_json_array($specificCategoryPosition)) {
                $specificCategoryPosition = \isys_format_json::decode($specificCategoryPosition);
            }

            if (is_array($specificCategoryPosition) && count($specificCategoryPosition)) {
                foreach (\isys_format_json::decode($specificCategoryPosition) as $sort => $constant) {
                    if (!defined($constant)) {
                        continue;
                    }

                    $assignedCategories[$sort] = self::prepareSpecificCategoryShape(constant($constant));
                }
            }

            ksort($assignedCategories);

            return array_values($assignedCategories);
        } catch (Throwable $exception) {
            return [];
        }
    }

    /**
     * @param int $categoryId
     *
     * @return SpecificCategoryShape|null
     */
    private static function prepareSpecificCategoryShape(int $categoryId): SpecificCategoryShape|null
    {
        try {
            $language = \isys_application::instance()->container->get('language');
            $dao = \isys_application::instance()->container->get('cmdb_dao');

            $query = "SELECT isysgui_cats__id AS id,
                isysgui_cats__title AS titleRaw,
                isysgui_cats__const AS constant
                FROM isysgui_cats
                WHERE isysgui_cats__id = {$categoryId}
                LIMIT 1;";

            $result = $dao->retrieve($query)->get_row();

            if (empty($result)) {
                return null;
            }

            $result['title'] = $language->get($result['titleRaw']);

            return Serializer::fromJson(SpecificCategoryShape::class, $result);
        } catch (Throwable $exception) {
            return null;
        }
    }
}
