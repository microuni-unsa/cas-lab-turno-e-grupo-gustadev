<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: specific category router.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_router extends isys_cmdb_dao_category_specific
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'router';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATS__ROUTER';
        $this->m_entry_identifier = 'routing_protocol';
    }

    /**
     * @param isys_request $p_request
     *
     * @return array
     */
    public function callback_property_gateway_address(isys_request $p_request): array
    {
        $ipDao = isys_cmdb_dao_category_g_ip::instance($this->get_database_component());
        $netDao = isys_cmdb_dao_category_s_net::instance($this->get_database_component());

        $return = [];
        $selectedIp = [];
        $alreadyAdded = [];
        $emptyAddressName = isys_application::instance()->container->get('language')->get('LC__IP__EMPTY_ADDRESS');

        $selectedIpResult = $ipDao->get_ips_for_router_list_by_obj_id($p_request->get_object_id(), $p_request->get_category_data_id());

        while ($selectedIpRow = $selectedIpResult->get_row()) {
            $selectedIp[] = $selectedIpRow['isys_catg_ip_list__id'];
        }

        $routerIpResult = $ipDao->get_data(null, $p_request->get_object_id());

        while ($routerIpRow = $routerIpResult->get_row()) {
            if (isset($alreadyAdded[$routerIpRow['isys_cats_net_ip_addresses_list__isys_obj__id']])) {
                continue;
            }

            $netData = $netDao->get_data(null, $routerIpRow['isys_cats_net_ip_addresses_list__isys_obj__id'])->get_row();

            $address = empty($netData['isys_cats_net_list__address'])
                ? $emptyAddressName
                : $netData['isys_cats_net_list__address'];

            $return[] = [
                'id'   => $routerIpRow['isys_catg_ip_list__id'],
                'val'  => "{$routerIpRow['isys_catg_ip_list__hostname']} ({$address}) - {$netData['isys_obj__title']}",
                'sel'  => in_array($routerIpRow['isys_catg_ip_list__id'], $selectedIp),
                'link' => ''
            ];

            $alreadyAdded[$routerIpRow['isys_cats_net_ip_addresses_list__isys_obj__id']] = true;
        }

        return $return;
    }

    /**
     * Method for returning the properties.
     *
     * @return array
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'routing_protocol' => new DialogPlusProperty(
                'C__CATS__ROUTER__ROUTING_PROTOCOL',
                'LC__CMDB__CATS__ROUTER__ROUTING_PROTOCOL',
                'isys_cats_router_list__routing_protocol',
                'isys_cats_router_list',
                'isys_routing_protocol'
            ),
            'gateway_address'  => (new DialogDataProperty(
                'C__CATS__ROUTER__GATEWAY_ADDRESS',
                'LC__CMDB__CATS__ROUTER__GATEWAY_ADDRESS',
                'isys_cats_router_list__id',
                'isys_cats_router_list',
                new isys_callback([
                    'isys_cmdb_dao_category_s_router',
                    'callback_property_gateway_address'
                ]),
                false,
                ['isys_export_helper', 'routing_gateway']
            ))->mergePropertyInfo([
                'type' => Property::C__PROPERTY__INFO__TYPE__DIALOG_LIST
            ])
            ->mergePropertyUi([
                'type' => Property::C__PROPERTY__UI__TYPE__DIALOG_LIST
            ])->mergePropertyUiParams([
                'multiselection' => true
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => false
            ]),
            'description'      => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__ROUTER', 'C__CATS__ROUTER'),
                'isys_cats_router_list__description',
                'isys_cats_router_list'
            )
        ];
    }

    /**
     * @param array $data
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function create_data($data)
    {
        $entryId = parent::create_data($data);

        if (isset($data['gateway_address'])) {
            $hostaddressIds = $data['gateway_address'];

            if (is_string($hostaddressIds)) {
                $hostaddressIds = array_unique(array_filter(explode(',', $hostaddressIds)));
            }

            if (is_array($hostaddressIds)) {
                $this->processIpConnection($entryId, array_values($hostaddressIds));
            }
        }

        return $entryId;
    }

    /**
     * @param int   $entryId
     * @param array $data
     *
     * @return bool|int
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($entryId, $data)
    {
        parent::save_data($entryId, $data);

        if (isset($data['gateway_address'])) {
            $hostaddressIds = $data['gateway_address'];

            if (is_string($hostaddressIds)) {
                $hostaddressIds = array_unique(array_filter(explode(',', $hostaddressIds)));
            }

            if (is_array($hostaddressIds)) {
                $this->processIpConnection($entryId, array_values($hostaddressIds));
            }
        }

        return $entryId;
    }

    /**
     * @param int   $entryId
     * @param array $hostaddressIds
     *
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function processIpConnection(int $entryId, array $hostaddressIds): void
    {
        $this->clearIpAttachments($entryId);

        foreach ($hostaddressIds as $hostaddressId) {
            if ($hostaddressId > 0) {
                // Then add the ones from our POST array.
                $this->attachIp($entryId, (int)$hostaddressId);
            }
        }
    }

    /**
     * Clears all ip attachments.
     *
     * @param int $entryId
     *
     * @return void
     * @throws isys_exception_dao
     */
    private function clearIpAttachments(int $entryId): void
    {
        $query = "DELETE FROM isys_catg_ip_list_2_isys_cats_router_list WHERE isys_cats_router_list__id = {$entryId};";

        $this->update($query);
        $this->apply_update();
    }

    /**
     * Attaches an IP address to a port.
     *
     * @param int $entryId
     * @param int $hostaddressEntryId
     *
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function attachIp(int $entryId, int $hostaddressEntryId): void
    {
        if ($entryId > 0 && $hostaddressEntryId > 0) {
            $query = "SELECT * FROM isys_catg_ip_list_2_isys_cats_router_list
				WHERE isys_catg_ip_list__id = {$hostaddressEntryId}
				AND isys_cats_router_list__id = {$entryId};";

            $res = $this->retrieve($query);

            if (count($res) === 0) {
                $insertQuery = "INSERT INTO isys_catg_ip_list_2_isys_cats_router_list SET
					isys_catg_ip_list__id = {$hostaddressEntryId},
					isys_cats_router_list__id = {$entryId};";

                $this->update($insertQuery);
                $this->apply_update();
            }
        }
    }
}
