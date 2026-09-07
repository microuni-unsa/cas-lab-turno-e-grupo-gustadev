<?php

use idoit\Component\Helper\Ip;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: specific category for DHCP
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_net_dhcp extends isys_cmdb_dao_category_specific
{
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'net_dhcp';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATS__NET_DHCP';
    }

    /**
     * Get the ip assignment for the specified type (IPV4)
     *
     * @param $p_type
     *
     * @return int
     */
    public static function get_category_ip_assignment($p_type): int
    {
        if ($p_type == defined_or_default('C__NET__DHCP_RESERVED')) {
            return defined_or_default('C__CATP__IP__ASSIGN__DHCP_RESERVED');
        }

        if ($p_type == defined_or_default('C__NET__DHCP_DYNAMIC')) {
            return defined_or_default('C__CATP__IP__ASSIGN__DHCP');
        }

        return defined_or_default('C__CATP__IP__ASSIGN__STATIC');
    }

    /**
     * Get the ip assignment for the specified type (IPV6)
     *
     * @param $p_type
     *
     * @return int
     */
    public static function get_category_ip6_assignment($p_type): int
    {
        if ($p_type == defined_or_default('C__NET__DHCPV6__SLAAC_AND_DHCPV6_RESERVED')) {
            return defined_or_default('C__CMDB__CATG__IP__SLAAC_AND_DHCPV6_RESERVED');
        }

        if ($p_type == defined_or_default('C__NET__DHCPV6__SLAAC_AND_DHCPV6')) {
            return defined_or_default('C__CMDB__CATG__IP__SLAAC_AND_DHCPV6');
        }

        if ($p_type == defined_or_default('C__NET__DHCP_RESERVED') || $p_type == defined_or_default('C__NET__DHCPV6__DHCPV6_RESERVED')) {
            return defined_or_default('C__CMDB__CATG__IP__DHCPV6_RESERVED');
        }

        if ($p_type == defined_or_default('C__NET__DHCP_DYNAMIC') || $p_type == defined_or_default('C__NET__DHCPV6__DHCPV6')) {
            return defined_or_default('C__CMDB__CATG__IP__DHCPV6');
        }

        return defined_or_default('C__CMDB__CATG__IP__STATIC');
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   integer $p_obj_id
     * @param   integer $p_type
     * @param   integer $p_typev6
     * @param   integer $p_ip_range_from
     * @param   integer $p_ip_range_to
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  integer
     */
    public function create(
        $p_obj_id = null,
        $p_type = null,
        $p_typev6 = null,
        $p_ip_range_from = null,
        $p_ip_range_to = null,
        $p_description = null,
        $p_status = C__RECORD_STATUS__NORMAL
    ) {
        if ($p_obj_id === null) {
            $p_obj_id = $_GET[C__CMDB__GET__OBJECT];
        }
        $l_ip_assignment = defined_or_default('C__CATP__IP__ASSIGN__STATIC');

        if (!Ip::validate_ip($p_ip_range_from)) {
            if (empty($p_typev6)) {
                $p_typev6 = defined_or_default('C__NET__DHCPV6__DHCPV6');
            }
            $l_ip_assignment = self::get_category_ip6_assignment($p_typev6);
        } elseif (Ip::validate_ip($p_ip_range_from)) {
            if (!empty($p_typev6)) {
                $p_typev6 = null;
            }
            $l_ip_assignment = self::get_category_ip_assignment($p_type);
        }

        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_db);
        $l_ip_dao->update_ip_assignment_by_ip_range($p_obj_id, $l_ip_assignment, $p_ip_range_from, $p_ip_range_to);

        $l_sql = 'INSERT INTO isys_cats_net_dhcp_list SET
            isys_cats_net_dhcp_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
            isys_cats_net_dhcp_list__isys_net_dhcp_type__id = ' . $this->convert_sql_id($p_type) . ',
            isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id = ' . $this->convert_sql_id($p_typev6) . ',
            isys_cats_net_dhcp_list__range_from = ' . $this->convert_sql_text($p_ip_range_from) . ',
            isys_cats_net_dhcp_list__range_from_long = ' . $this->convert_sql_text(Ip::ip2long($p_ip_range_from)) . ',
            isys_cats_net_dhcp_list__range_to = ' . $this->convert_sql_text($p_ip_range_to) . ',
            isys_cats_net_dhcp_list__range_to_long = ' . $this->convert_sql_text(Ip::ip2long($p_ip_range_to)) . ',
            isys_cats_net_dhcp_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_net_dhcp_list__status = ' . $this->convert_sql_int($p_status) . ';';

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Method for deleting DHCP ranges.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id)
    {
        $l_row = $this->get_data($p_id)
            ->get_row();

        // We have to assign the "ip assignment" to static, because we delete this dhcp range.
        isys_cmdb_dao_category_g_ip::instance($this->m_db)
            ->update_ip_assignment_by_ip_range(
                $l_row['isys_cats_net_dhcp_list__isys_obj__id'],
                defined_or_default('C__CATP__IP__ASSIGN__STATIC'),
                $l_row['isys_cats_net_dhcp_list__range_from'],
                $l_row['isys_cats_net_dhcp_list__range_to']
            );

        // And after that, we can happily delete our range.
        return $this->update('DELETE FROM isys_cats_net_dhcp_list WHERE isys_cats_net_dhcp_list__id = ' . $this->convert_sql_id($p_id) . ';') && $this->apply_update();
    }

    /**
     * With this method, we can find every DHCPv4 range conflict, before we actually start messing with the database.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function find_range_conflicts($p_obj_id, $p_from, $p_to, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!empty($p_from) && !empty($p_to)) {
            $from = $this->convert_sql_int(Ip::ip2long($p_from));
            $to = $this->convert_sql_int(Ip::ip2long($p_to));

            $l_sql = "SELECT *
                FROM isys_cats_net_dhcp_list
                WHERE (( isys_cats_net_dhcp_list__range_from_long BETWEEN {$from} AND {$to})
                    OR (isys_cats_net_dhcp_list__range_to_long BETWEEN {$from} AND {$to})
                    OR (isys_cats_net_dhcp_list__range_from_long <= {$from} AND isys_cats_net_dhcp_list__range_to_long >= {$to})
                    OR (isys_cats_net_dhcp_list__range_from_long >= {$from} AND isys_cats_net_dhcp_list__range_to_long <= {$to}))
                AND isys_cats_net_dhcp_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . "
                AND isys_cats_net_dhcp_list__status = " . $this->convert_sql_int($p_status) . "
                ORDER BY isys_cats_net_dhcp_list__range_from_long ASC;";

            return $this->retrieve($l_sql);
        }

        return null;
    }

    /**
     * With this method, we can find every DHCPv6 range conflict, before we actually start messing with the database.
     *
     * @param   integer $p_obj_id
     * @param   string  $p_from
     * @param   string  $p_to
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function find_ipv6_range_conflicts($p_obj_id, $p_from, $p_to = null, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!empty($p_from)) {
            if ($p_to === null) {
                $p_to = $p_from;
            }

            $l_from = $this->convert_sql_text(Ip::validate_ipv6($p_from));
            $l_to = $this->convert_sql_text(Ip::validate_ipv6($p_to));

            $l_sql = "SELECT * FROM isys_cats_net_dhcp_list
                WHERE (( isys_cats_net_dhcp_list__range_from BETWEEN {$l_from} AND {$l_to})
                    OR (isys_cats_net_dhcp_list__range_to BETWEEN {$l_from} AND {$l_to})
                    OR (isys_cats_net_dhcp_list__range_from <= {$l_from} AND isys_cats_net_dhcp_list__range_to >= {$l_to})
                    OR (isys_cats_net_dhcp_list__range_from >= {$l_from} AND isys_cats_net_dhcp_list__range_to <= {$l_to}))
                AND isys_cats_net_dhcp_list__isys_obj__id = " . $this->convert_sql_id($p_obj_id) . "
                AND isys_cats_net_dhcp_list__status = " . $this->convert_sql_int($p_status) . "
                ORDER BY isys_cats_net_dhcp_list__range_from_long ASC;";

            return $this->retrieve($l_sql);
        }

        return null;
    }

    /**
     * Method for merging a new DHCP range inside an existing.
     *
     * @param   integer $p_obj_id The object-ID of the layer3 net.
     * @param   integer $p_type   The assignment type from "isys_net_dhcp_type".
     * @param   string  $p_from   The starting IP-address.
     * @param   string  $p_to     The ending IP-address (if left empty this parameter will be set to the "from" value).
     *
     * @return  array
     */
    public function check_and_merge_new_dhcp_range_inside_existing($p_obj_id, $p_type, $p_from, $p_to = null)
    {
        if ($p_to === null) {
            $p_to = $p_from;
        }

        // With this method we check, if there will be any conflicts.
        $l_conflict_ids = $this->find_range_conflicts($p_obj_id, $p_from, $p_to);

        // Now we check, if the method retrieved any conflicted ID's for us.
        if ($l_conflict_ids->num_rows() > 0) {
            while ($l_row = $l_conflict_ids->get_row()) {
                // We save all entries.
                $l_dhcp_ranges[] = $l_row;

                // And delete them - So now we can start from scratch.
                $this->delete($l_row['isys_cats_net_dhcp_list__id']);
            }

            // We set the new FROM and TO values as default, but check this in the next IF-statements.
            $l_new_range_from = $p_from;
            $l_new_range_to = $p_to;

            // We get ourselves the first and last entry of the array.
            $l_first = current($l_dhcp_ranges);
            $l_last = end($l_dhcp_ranges);

            // Please note: We can only check this because it was ordered by the FROM range.
            if ($l_first['isys_cats_net_dhcp_list__range_from_long'] < Ip::ip2long($l_new_range_from)) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_first['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'] == $p_type) {
                    $l_new_range_from = $l_first['isys_cats_net_dhcp_list__range_from'];
                } else {
                    // If the types are not the same, we have to restore the first range.
                    $this->create(
                        $p_obj_id,
                        $l_first['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                        null,
                        $l_first['isys_cats_net_dhcp_list__range_from'],
                        Ip::long2ip(Ip::ip2long($l_new_range_from) - 1),
                        $l_first['isys_cats_net_dhcp_list__description'],
                        $l_first['isys_cats_net_dhcp_list__status']
                    );
                }
            }

            // In the last entry there has to be the most last range.
            if ($l_last['isys_cats_net_dhcp_list__range_to_long'] > Ip::ip2long($l_new_range_to)) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_last['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'] == $p_type) {
                    $l_new_range_to = $l_last['isys_cats_net_dhcp_list__range_to'];
                } else {
                    // If the types are not the same, we have to restore the range.
                    $this->create(
                        $p_obj_id,
                        $l_last['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                        null,
                        Ip::long2ip(Ip::ip2long($l_new_range_to) + 1),
                        $l_last['isys_cats_net_dhcp_list__range_to'],
                        $l_last['isys_cats_net_dhcp_list__description'],
                        $l_last['isys_cats_net_dhcp_list__status']
                    );
                }
            }

            $this->create($p_obj_id, $p_type, null, $l_new_range_from, $l_new_range_to, '', C__RECORD_STATUS__NORMAL);

            $l_return = ['result' => 'merged'];
        } else {
            // When the array is empty, we can blindly create a new DHCP range.
            $this->create($p_obj_id, $p_type, null, $p_from, $p_to, '', C__RECORD_STATUS__NORMAL);

            $l_return = ['result' => 'success'];
        }

        return $l_return;
    }

    /**
     * Method for merging a new DHCPv6 range inside an existing.
     *
     * @param   integer $p_obj_id The object-ID of the layer3 net.
     * @param   integer $p_type   The assignment type from "isys_net_dhcpv6_type".
     * @param   string  $p_from   The starting IP-address.
     * @param   string  $p_to     The ending IP-address (if left empty this parameter will be set to the "from" value).
     *
     * @return  array
     */
    public function check_and_merge_new_dhcpv6_range_inside_existing($p_obj_id, $p_type, $p_from, $p_to = null)
    {
        if ($p_to === null) {
            $p_to = $p_from;
        }

        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_db);

        // With this method we check, if there will be any conflicts.
        $l_conflict_ids = $this->find_ipv6_range_conflicts($p_obj_id, $p_from, $p_to);

        // Now we check, if the method retrieved any conflicted ID's for us.
        if ($l_conflict_ids->num_rows() > 0) {
            while ($l_row = $l_conflict_ids->get_row()) {
                // We save all entries.
                $l_dhcp_ranges[] = $l_row;

                // And delete them - So now we can start from scratch.
                $this->delete($l_row['isys_cats_net_dhcp_list__id']);
            }

            // We set the new FROM and TO values as default, but check this in the next IF-statements.
            $l_new_range_from = $p_from;
            $l_new_range_to = $p_to;

            // We get ourselves the first and last entry of the array.
            $l_first = current($l_dhcp_ranges);
            $l_last = end($l_dhcp_ranges);

            // Please note: We can only check this because it was ordered by the FROM range.
            if (strcmp($l_first['isys_cats_net_dhcp_list__range_from'], Ip::validate_ipv6($l_new_range_from)) < 0) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_first['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'] == $p_type) {
                    $l_new_range_from = $l_first['isys_cats_net_dhcp_list__range_from'];
                } else {
                    // If the types are not the same, we have to restore the first range.
                    $this->create(
                        $p_obj_id,
                        null,
                        $l_first['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                        $l_first['isys_cats_net_dhcp_list__range_from'],
                        Ip::calculate_prev_ipv6($l_new_range_from),
                        $l_first['isys_cats_net_dhcp_list__description'],
                        $l_first['isys_cats_net_dhcp_list__status']
                    );
                }
            }

            // In the last entry there has to be the most last range.
            if (strcmp($l_last['isys_cats_net_dhcp_list__range_to'], Ip::validate_ipv6($l_new_range_to)) > 0) {
                // Now we check for the type - If it's the same, we merge.
                if ($l_last['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'] == $p_type) {
                    $l_new_range_to = $l_last['isys_cats_net_dhcp_list__range_to'];
                } else {
                    // If the types are not the same, we have to restore the range.
                    $this->create(
                        $p_obj_id,
                        null,
                        $l_last['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                        Ip::calculate_next_ipv6($l_new_range_to),
                        $l_last['isys_cats_net_dhcp_list__range_to'],
                        $l_last['isys_cats_net_dhcp_list__description'],
                        $l_last['isys_cats_net_dhcp_list__status']
                    );
                }
            }

            $this->create($p_obj_id, null, $p_type, $l_new_range_from, $l_new_range_to, '', C__RECORD_STATUS__NORMAL);

            $l_return = ['result' => 'merged'];
        } else {
            // When the array is empty, we can blindly create a new DHCP range.
            $this->create($p_obj_id, null, $p_type, $p_from, $p_to, '', C__RECORD_STATUS__NORMAL);

            $l_return = ['result' => 'success'];
        }

        return $l_return;
    }

    /**
     * Return Category Data.
     *
     * @param   integer $p_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);
        $l_sql = 'SELECT * FROM ' . $this->m_table . ' ' . ' INNER JOIN isys_obj ON ' . $this->m_table . '__isys_obj__id = isys_obj__id ' .
            ' LEFT JOIN isys_net_dhcp_type ON ' . $this->m_table . '__isys_net_dhcp_type__id = isys_net_dhcp_type__id ' . ' LEFT JOIN isys_net_dhcpv6_type ON ' .
            $this->m_table . '__isys_net_dhcpv6_type__id = isys_net_dhcpv6_type__id ' . 'WHERE TRUE ' . $p_condition . ' ';

        if ($p_id !== null) {
            $l_sql .= 'AND isys_cats_net_dhcp_list__id = ' . $this->convert_sql_id($p_id) . ' ';
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= 'AND isys_cats_net_dhcp_list__status = ' . $this->convert_sql_int($p_status) . ' ';
        }

        $l_sql .= ' ORDER BY isys_cats_net_dhcp_list__range_from_long ASC;';

        return $this->retrieve($l_sql);
    }

    /**
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     * @author Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        $l_sql = '';

        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                $l_sql = ' AND (isys_cats_net_dhcp_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                $l_sql = ' AND (isys_cats_net_dhcp_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }

        return $l_sql;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type'        => (new DialogProperty(
                'C__CATS__NET_DHCP_TYPE',
                'LC__CMDB__CATS__NET__DHCPV4_TYPE',
                'isys_cats_net_dhcp_list__isys_net_dhcp_type__id',
                'isys_cats_net_dhcp_list',
                'isys_net_dhcp_type'
            ))->mergePropertyUiParams([
                'p_bDbFieldNN' => true
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => true,
            ]),
            'typev6'      => (new DialogProperty(
                'C__CATS__NET_DHCPV6_TYPE',
                'LC__CMDB__CATS__NET__DHCPV6_TYPE',
                'isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id',
                'isys_cats_net_dhcp_list',
                'isys_net_dhcpv6_type'
            ))->mergePropertyUiParams([
                'p_bDbFieldNN' => true
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => true,
            ]),
            'range_from'  => (new TextProperty(
                'C__CATS__NET_DHCP_RANGE_FROM',
                'LC__CMDB__CATS__NET__DHCP_RANGE_FROM',
                'isys_cats_net_dhcp_list__range_from',
                'isys_cats_net_dhcp_list'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'range_to'    => (new TextProperty(
                'C__CATS__NET_DHCP_RANGE_TO',
                'LC__CMDB__CATS__NET__DHCP_RANGE_TO',
                'isys_cats_net_dhcp_list__range_to',
                'isys_cats_net_dhcp_list'
            ))->mergePropertyUiParams([
                'p_strClass' => 'input-mini'
            ]),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__NET_DHCP', 'C__CATS__NET_DHCP'),
                'isys_cats_net_dhcp_list__description',
                'isys_cats_net_dhcp_list'
            )
        ];
    }

    /**
     * Validates the given IP address-range and sorts it from "low" to "high" (from -> to).
     *
     * @param   array $p_data
     * @param   mixed $p_prepend_table_field
     *
     * @return  mixed
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_net_type = isys_cmdb_dao_category_s_net::instance($this->m_db)
            ->get_data(null, $_GET[C__CMDB__GET__OBJECT], '', null, C__RECORD_STATUS__NORMAL)
            ->get_row_value('isys_cats_net_list__isys_net_type__id');

        // restructure $p_data
        foreach ($p_data as $key => &$value) {
            if (is_array($value) && array_key_exists(C__DATA__VALUE, $value)) {
                $value = $value[C__DATA__VALUE];
            }
        }

        if ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            // Special validation for IPv4 addresses.
            if (isset($p_data['range_from']) && isset($p_data['range_to'])) {
                if (!Ip::validate_ip($p_data['range_from'])) {
                    return [
                        'range_from' => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID')
                    ];
                }

                if (!Ip::validate_ip($p_data['range_to'])) {
                    return [
                        'range_to' => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID')
                    ];
                }

                if (Ip::ip2long($p_data['range_from']) > Ip::ip2long($p_data['range_to'])) {
                    return [
                        'range_from' => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID'),
                        'range_to'   => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID')
                    ];
                }
            }
        } elseif ($l_net_type == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
            // Special validation for IPv6 addresses.
            if (isset($p_data['range_from']) && isset($p_data['range_to'])) {
                if (!Ip::validate_ipv6($p_data['range_from'])) {
                    return [
                        'range_from' => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID')
                    ];
                }

                if (!Ip::validate_ipv6($p_data['range_to'])) {
                    return [
                        'range_to' => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS__NET_IP_ADDRESSES__IP_INVALID')
                    ];
                }

                // We are not able to compare two IPv6 addresses with eachother.
            }
        }

        return parent::validate($p_data);
    }

    /**
     * In this method we handle the archiving, deleting and purging of DHCP ranges.
     *
     * @param   integer $p_obj
     * @param   integer $p_direction
     *
     */
    public function pre_rank($p_obj, $p_direction)
    {
        // We will need the data of the entry which is beeing deleted or recycled.
        $l_old = $this->get_data($p_obj)
            ->get_row();

        $l_net_dao = new isys_cmdb_dao_category_s_net($this->get_database_component());
        $l_net_row = $l_net_dao->get_data(null, $l_old['isys_cats_net_dhcp_list__isys_obj__id'])
            ->get_row();

        // We only do all this stuff when we are inside a IPv4 NET.
        if ($l_net_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV4')) {
            if ($p_direction === C__CMDB__RANK__DIRECTION_DELETE) {
                // In this case, the entry is getting archived, deleted or purged - So we set the assignment to STATIC.
                isys_cmdb_dao_category_g_ip::instance($this->m_db)
                    ->update_ip_assignment_by_ip_range(
                        $l_old['isys_cats_net_dhcp_list__isys_obj__id'],
                        defined_or_default('C__CATP__IP__ASSIGN__STATIC'),
                        $l_old['isys_cats_net_dhcp_list__range_from'],
                        $l_old['isys_cats_net_dhcp_list__range_to']
                    );
            } elseif ($p_direction === C__CMDB__RANK__DIRECTION_RECYCLE) {
                // In this case we are recovering an entry.

                // With this method we check, if there will be any conflicts.
                $l_conflict_ids = $this->find_range_conflicts(
                    $l_old['isys_cats_net_dhcp_list__isys_obj__id'],
                    $l_old['isys_cats_net_dhcp_list__range_from'],
                    $l_old['isys_cats_net_dhcp_list__range_to']
                );

                // Now we check, if the method retrieved any conflicted ID's for us.
                if ($l_conflict_ids->num_rows() > 0) {
                    while ($l_row = $l_conflict_ids->get_row()) {
                        // We save all entries.
                        $l_dhcp_ranges[] = $l_row;

                        // And delete them - So now we can start from scratch.
                        $this->delete($l_row['isys_cats_net_dhcp_list__id']);
                    }

                    // We set the new FROM and TO values as default, but check this in the next IF-statements.
                    $l_new_range_from = $l_old['isys_cats_net_dhcp_list__range_from'];
                    $l_new_range_to = $l_old['isys_cats_net_dhcp_list__range_to'];

                    // We get ourselves the first and last entry of the array.
                    $l_first = current($l_dhcp_ranges);
                    $l_last = end($l_dhcp_ranges);

                    // Please note: We can only check this because it was ordered by the FROM range.
                    if ($l_first['isys_cats_net_dhcp_list__range_from_long'] < Ip::ip2long($l_new_range_from)) {
                        // Now we check for the type - If it's the same, we merge.
                        if ($l_first['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'] == $l_old['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']) {
                            $l_new_range_from = $l_first['isys_cats_net_dhcp_list__range_from'];
                        } else {
                            // If the types are not the same, we have to restore the first range.
                            $this->create(
                                $p_obj,
                                $l_first['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                                $l_first['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                                $l_first['isys_cats_net_dhcp_list__range_from'],
                                Ip::long2ip(Ip::ip2long($l_new_range_from) - 1),
                                $l_first['isys_cats_net_dhcp_list__description'],
                                $l_first['isys_cats_net_dhcp_list__status']
                            );
                        }
                    }

                    // In the last entry there has to be the most last range.
                    if ($l_last['isys_cats_net_dhcp_list__range_to_long'] > Ip::ip2long($l_new_range_to)) {
                        // Now we check for the type - If it's the same, we merge.
                        if ($l_last['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'] == $l_old['isys_cats_net_dhcp_list__isys_net_dhcp_type__id']) {
                            $l_new_range_to = $l_last['isys_cats_net_dhcp_list__range_to'];
                        } else {
                            // If the types are not the same, we have to restore the range.
                            $this->create(
                                $p_obj,
                                $l_last['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                                $l_last['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                                Ip::long2ip(Ip::ip2long($l_new_range_to) + 1),
                                $l_last['isys_cats_net_dhcp_list__range_to'],
                                $l_last['isys_cats_net_dhcp_list__description'],
                                $l_last['isys_cats_net_dhcp_list__status']
                            );
                        }
                    }

                    $this->save(
                        $l_old['isys_cats_net_dhcp_list__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_obj__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                        $l_new_range_from,
                        $l_new_range_to,
                        $l_old['isys_cats_net_dhcp_list__description'],
                        C__RECORD_STATUS__NORMAL
                    );
                } else {
                    // When the array is empty, we can blindly create a new DHCP range.
                    $this->save(
                        $l_old['isys_cats_net_dhcp_list__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_obj__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_net_dhcp_type__id'],
                        $l_old['isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id'],
                        $l_old['isys_cats_net_dhcp_list__range_from'],
                        $l_old['isys_cats_net_dhcp_list__range_to'],
                        $l_old['isys_cats_net_dhcp_list__description'],
                        C__RECORD_STATUS__NORMAL
                    );
                }
            }
        }
    }

    /**
     * Executes the query to save the category entry given by its ID $p_id.
     *
     * @param   integer $p_id
     * @param   integer $p_obj_id
     * @param   integer $p_type
     * @param   integer $p_typev6
     * @param   integer $p_ip_range_from
     * @param   integer $p_ip_range_to
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean true, if transaction executed successfully, else false
     */
    public function save(
        $p_id,
        $p_obj_id = null,
        $p_type = null,
        $p_typev6 = null,
        $p_ip_range_from = null,
        $p_ip_range_to = null,
        $p_description = null,
        $p_status = C__RECORD_STATUS__NORMAL
    ) {
        // We'll have to update the IP assignment for the old range to "static" and the new to whatever type this is.
        $l_old = $this->get_data($p_id)
            ->get_row();

        $l_ip_dao = new isys_cmdb_dao_category_g_ip($this->m_db);
        $l_ip_dao->update_ip_assignment_by_ip_range(
            $p_obj_id,
            ((Ip::validate_ip($l_old['isys_cats_net_dhcp_list__range_from'])) ? defined_or_default('C__CATP__IP__ASSIGN__STATIC '): defined_or_default('C__CMDB__CATG__IP__STATIC')),
            $l_old['isys_cats_net_dhcp_list__range_from'],
            $l_old['isys_cats_net_dhcp_list__range_to']
        );

        $l_ip_assignment = defined_or_default('C__CATP__IP__ASSIGN__STATIC');

        if (!Ip::validate_ip($p_ip_range_from)) {
            if (empty($p_typev6)) {
                $p_typev6 = defined_or_default('C__NET__DHCPV6__DHCPV6');
            }
            $l_ip_assignment = self::get_category_ip6_assignment($p_typev6);
        } elseif (Ip::validate_ip($p_ip_range_from)) {
            if (!empty($p_typev6)) {
                $p_typev6 = null;
            }
            $l_ip_assignment = self::get_category_ip_assignment($p_type);
        }

        $l_ip_dao->update_ip_assignment_by_ip_range($p_obj_id, $l_ip_assignment, $p_ip_range_from, $p_ip_range_to);

        $l_sql = 'UPDATE isys_cats_net_dhcp_list SET isys_cats_net_dhcp_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ',
            isys_cats_net_dhcp_list__isys_net_dhcp_type__id = ' . $this->convert_sql_id($p_type) . ',
            isys_cats_net_dhcp_list__isys_net_dhcpv6_type__id = ' . $this->convert_sql_id($p_typev6) . ',
            isys_cats_net_dhcp_list__range_from = ' . $this->convert_sql_text($p_ip_range_from) . ',
            isys_cats_net_dhcp_list__range_from_long = ' . $this->convert_sql_text(Ip::ip2long($p_ip_range_from)) . ',
            isys_cats_net_dhcp_list__range_to = ' . $this->convert_sql_text($p_ip_range_to) . ',
            isys_cats_net_dhcp_list__range_to_long = ' . $this->convert_sql_text(Ip::ip2long($p_ip_range_to)) . ',
            isys_cats_net_dhcp_list__description = ' . $this->convert_sql_text($p_description) . ',
            isys_cats_net_dhcp_list__status = ' . $this->convert_sql_int($p_status) . '
            WHERE isys_cats_net_dhcp_list__id = ' . $this->convert_sql_id($p_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * Save category entry.
     *
     * @param   integer $p_id
     * @param   integer $p_old_status
     * @param   boolean $p_create
     *
     * @return  integer
     */
    public function save_element($p_id, &$p_old_status, $p_create)
    {
        $l_return = false;

        $l_catdata = $this->get_general_data();
        $l_list_id = $l_catdata['isys_cats_net_dhcp_list__id'];

        if ($p_create) {
            $l_list_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CATS__NET_DHCP_TYPE'],
                $_POST['C__CATS__NET_DHCPV6_TYPE'],
                $_POST['C__CATS__NET_DHCP_RANGE_FROM'],
                $_POST['C__CATS__NET_DHCP_RANGE_TO'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_list_id > 0) {
                $l_return = true;
            }
        } else {
            $l_return = $this->save(
                $l_list_id,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CATS__NET_DHCP_TYPE'],
                $_POST['C__CATS__NET_DHCPV6_TYPE'],
                $_POST['C__CATS__NET_DHCP_RANGE_FROM'],
                $_POST['C__CATS__NET_DHCP_RANGE_TO'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return ($l_return == true) ? $l_list_id : -1;
    }

    /**
     * Method for updating the range only.
     *
     * @param   integer $p_id
     * @param   string  $p_from
     * @param   string  $p_to
     *
     * @return  boolean
     */
    public function update_ranges($p_id, $p_from = null, $p_to = null)
    {
        // We use this, so we don't have to play around setting the commas at the right places.
        $l_update = [];

        if ($p_from !== null) {
            $l_update[] = 'isys_cats_net_dhcp_list__range_from = ' . $this->convert_sql_text($p_from) . ', isys_cats_net_dhcp_list__range_from_long = ' . Ip::ip2long($p_from);
        }

        if ($p_to !== null) {
            $l_update[] = 'isys_cats_net_dhcp_list__range_to = ' . $this->convert_sql_text($p_to) . ', isys_cats_net_dhcp_list__range_to_long = ' . Ip::ip2long($p_to);
        }

        if (!empty($l_update)) {
            $l_sql = 'UPDATE isys_cats_net_dhcp_list SET ' . implode(' , ', $l_update) . ' WHERE isys_cats_net_dhcp_list__id = ' . $this->convert_sql_id($p_id) . ';';

            return ($this->update($l_sql) && $this->apply_update());
        }

        return false;
    }
}
