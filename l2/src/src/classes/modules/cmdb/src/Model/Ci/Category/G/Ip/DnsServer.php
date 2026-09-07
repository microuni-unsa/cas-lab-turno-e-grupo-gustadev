<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Ip;

use idoit\Module\Cmdb\Model\Ci\Category\AbstractProperty;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;
use isys_application;
use isys_cmdb_dao_category_g_ip;
use isys_format_json;

class DnsServer extends AbstractProperty implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_g_ip';

    /**
     * @var string
     */
    protected static string $method = 'object_browser';

    /**
     * @param array|null $parameters
     *
     * @return string
     */
    public static function requestData(?array $parameters = []): string
    {
        // Handle Ajax-Request.
        $return = [];

        $dao = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->container->get('database'));
        $result = $dao->get_ips_by_obj_id($_GET[C__CMDB__GET__OBJECT]);
        $language = isys_application::instance()->container->get('language');

        if ($result->num_rows() > 0) {
            while ($row = $result->get_row()) {
                $return[] = [
                    '__checkbox__'                         => $row["isys_catg_ip_list__id"],
                    $language->get('LC__CATG__IP_ADDRESS') => $row["isys_cats_net_ip_addresses_list__title"]
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

        $preselection = $parameters['preselection'];
        $dao = isys_application::instance()->container->get('cmdb_dao');
        $language = isys_application::instance()->container->get('language');

        if (is_string($preselection) && isys_format_json::is_json_array($preselection)) {
            $preselection = isys_format_json::decode($preselection);
        }

        if (!empty($preselection) && is_array($preselection)) {
            $query = "SELECT isys_obj__id, isys_obj__isys_obj_type__id, isys_catg_ip_list__id, isys_cats_net_ip_addresses_list__title, isys_obj_type__title
                        FROM isys_catg_ip_list
                        INNER JOIN isys_cats_net_ip_addresses_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id
                        LEFT JOIN isys_obj ON isys_obj__id = isys_catg_ip_list__isys_obj__id
                        LEFT JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                        WHERE isys_catg_ip_list__id " . $dao->prepare_in_condition($preselection);

            $result = $dao->retrieve($query);

            while ($row = $result->get_row()) {
                $return['category'][] = $row['isys_obj__isys_obj_type__id'];

                // @see  ID-6220  Also return a 'first' selection.
                $return['first'][] = $row['isys_obj__id'];

                $return['second'][] = [
                    $row['isys_catg_ip_list__id'],
                    $row['isys_cats_net_ip_addresses_list__title'],
                    $language->get($row['isys_obj_type__title']),
                ];
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
        $language = isys_application::instance()->container->get('language');

        if (is_array($parameters['dataIds']) && count($parameters['dataIds'])) {
            $dao = isys_cmdb_dao_category_g_ip::instance(isys_application::instance()->container->get('database'));

            foreach ($parameters['dataIds'] as $dataId) {
                $categoryRow = $dao->get_data($dataId)->get_row();

                $preselection[] = [
                    $categoryRow['isys_catg_ip_list__id'],
                    $categoryRow['isys_cats_net_ip_addresses_list__title'],
                    $categoryRow['isys_obj__title'],
                    $language->get($categoryRow['isys_obj_type__title'])
                ];
            }
        }

        return [
            'header' => [
                '__checkbox__',
                $language->get('LC__CATG__IP_ADDRESS'),
                $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                $language->get('LC__UNIVERSAL__OBJECT_TYPE')
            ],
            'data'   => $preselection
        ];
    }
}
