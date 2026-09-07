<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Application;

use idoit\Exception\JsonException;
use idoit\Module\Cmdb\Model\Ci\Category\AbstractProperty;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;
use isys_application;
use isys_cmdb_dao_category_g_application;
use isys_cmdb_dao_category_g_database_sa;
use isys_format_json;

class AssignedDatabases extends AbstractProperty implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_g_application';

    /**
     * @var string
     */
    protected static string $method = 'objectBrowserAssignedDatabases';

    /**
     * @param array|null $parameters
     *
     * @return string
     */
    public static function requestData(?array $parameters = []): string
    {
        $return = [];
        $db = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $objects = isys_cmdb_dao_category_g_database_sa::instance($db)
            ->get_data(null, $_GET[C__CMDB__GET__OBJECT] ?? $parameters[C__CMDB__GET__OBJECT] ?? null, '', null, C__RECORD_STATUS__NORMAL);

        if (count($objects)) {
            while ($row = $objects->get_row()) {
                $return[] = [
                    '__checkbox__' => $row["isys_catg_database_sa_list__id"],
                    $language->get('LC__CATG__DATABASE__TITLE')  => $row["isys_catg_database_sa_list__title"],
                    'DBMS' => $row["assigned_dbms"]
                ];
            }
        }

        return isys_format_json::encode($return);
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    public static function preparationData(?array $parameters = []): array
    {
        $return = [
            'category' => [],
            'first'    => [],
            'second'   => []
        ];

        $preselection = isys_format_json::is_json($parameters['preselection']) ?
            isys_format_json::decode($parameters['preselection']): $parameters['preselection'];

        if (!empty($preselection)) {
            $db = isys_application::instance()->container->get('database');
            $dao = isys_application::instance()->container->get('cmdb_dao');

            // Save a bit memory: Only select needed fields!
            $sql = "SELECT * FROM isys_catg_database_sa_list
                        WHERE isys_catg_database_sa_list__id " . $dao->prepare_in_condition($preselection);

            $res = $dao->retrieve($sql);

            if ($res->num_rows() > 0) {
                while ($row = $res->get_row()) {
                    // @see  ID-6220  Also return a 'first' selection.
                    $return['first'][] = (int)$row['isys_catg_database_sa_list__id'];

                    $return['second'][] = [
                        $row['isys_catg_database_sa_list__id'],
                        $row['isys_catg_database_sa_list__title'],
                    ];
                }
            }
        }

        return $return;
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    public static function preselectionData(?array $parameters = []): array
    {
        $preselection = [];
        $db = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $dao = isys_application::instance()->container->get('cmdb_dao');

        if (is_array($parameters['dataIds']) && count($parameters['dataIds'])) {
            $result = isys_cmdb_dao_category_g_database_sa::instance($db)
                ->get_data(null, null, ' AND isys_catg_database_sa_list__id IN (' . implode(',', $parameters['dataIds']). ')');

            while ($databaseData = $result->get_row()) {
                $preselection[] = [
                    $databaseData['isys_catg_database_sa_list__id'],
                    $databaseData['isys_catg_database_sa_list__title'],
                    $databaseData['isys_obj__title'],
                    $language->get($dao->get_objtype_name_by_id_as_string($databaseData['isys_obj__isys_obj_type__id']))
                ];
            }
        }

        return [
            'header' => [
                '__checkbox__',
                $language->get('LC__CATG__DATABASE__TITLE'),
                $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                $language->get('LC_UNIVERSAL__OBJECT_TYPE')
            ],
            'data'   => $preselection
        ];
    }
}
