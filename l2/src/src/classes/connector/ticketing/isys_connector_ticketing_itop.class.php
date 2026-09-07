<?php

/**
 * i-doit
 *
 * iTop ticketing connector
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_connector_ticketing_itop extends isys_connector_ticketing
{

    /**
     * HTTP Protocol
     *
     * @var isys_protocol_http
     */
    protected $m_protocol = null;

    /**
     * Response map
     * This mapping maps iTop response values to an internally defined i-doit format.
     *
     * @var array
     */
    protected $m_response_map = [
        'id'                      => parent::C__FIELD__ID,
        'Queue'                   => parent::C__FIELD__QUEUE,
        'caller_id_friendlyname'  => parent::C__FIELD__OWNER,
        'caller_id_friendlyname2' => parent::C__FIELD__CREATOR,
        'status'                  => parent::C__FIELD__STATUS,
        'title'                   => parent::C__FIELD__SUBJECT,
        'priority'                => parent::C__FIELD__PRIORITY,
        'priority2'               => parent::C__FIELD__INITIALPRIORITY,
        'priority3'               => parent::C__FIELD__FINALPRIORITY,
        'Requestors'              => parent::C__FIELD__REQUESTORS,
        'Cc'                      => parent::C__FIELD__CC,
        'AdminCc'                 => parent::C__FIELD__ADMINCC,
        'start_date'              => parent::C__FIELD__CREATED,
        'start_date2'             => parent::C__FIELD__STARTS,
        'start_date3'             => parent::C__FIELD__STARTED,
        'Due'                     => parent::C__FIELD__DUE,
        'Resolved'                => parent::C__FIELD__RESOLVED,
        'Told'                    => parent::C__FIELD__TOLD,
        'last_update'             => parent::C__FIELD__LASTUPDATED,
        'TimeEstimated'           => parent::C__FIELD__TIMEESTIMATED,
        'TimeWorked'              => parent::C__FIELD__TIMEWORKED,
        'TimeLeft'                => parent::C__FIELD__TIMELEFT
    ];

    /**
     * @var string
     */
    private $m_rt_base_url = null;

    /**
     * @var string
     */
    private $m_ticket_url_pattern = '/pages/UI.php?operation=details&class=UserRequest&id=%s&c[org_id]=1&c[menu]=UserRequest';

    /**
     * Base URL of the Rest interface
     *
     * @var string
     */
    private $m_url_base = '/webservices/rest.php?version=1.0';

    public function create_new_ticket_url($p_object_id)
    {
        return [
            'url' => $this->m_rt_base_url . '/pages/UI.php?operation=new&class=UserRequest&c%5Borg_id%5D=1&c%5Bmenu%5D=NewUserRequest'
        ];
    }

    public function get_attachment_content($p_attachment_id)
    {
        return [];
    }

    public function get_attachments($p_ticket_id)
    {
        return [];
    }

    public function get_comments($p_ticket_id)
    {
        return [];
    }

    public function get_history($p_ticket_id, $p_history_type = null)
    {
        if (!empty($p_ticket_id)) {
            return $this->generic_request('ticket/' . $p_ticket_id . '/history?format=l');
        }

        return [];
    }

    public function get_queue($p_queue_id = null)
    {
        return [];
    }

    public function get_ticket($p_ticket_id)
    {
        /** @todo Needed for workstations */
        return [];
    }

    /**
     * @param $p_ticketID
     *
     * @return string
     */
    public function get_ticket_url($p_ticketID)
    {
        $port = (int) $this->m_protocol->get_port();
        $urlPort = '';

        if ($port && $port !== 80) {
            $urlPort = ':' . $port;
        }

        return $this->m_protocol->get_host() . $urlPort . sprintf($this->m_ticket_url_pattern, $p_ticketID);
    }

    public function get_tickets($p_tickets)
    {
        return [];
    }

    /**
     * Fetches tickets by CMDB object identifier
     *
     * @param int $p_object_id Object identifier
     *
     * @return array
     */
    public function get_tickets_by_cmdb_object($p_object_id)
    {
        // Build parameter array
        $l_params = [
            'operation' => 'core/get',
            'class'     => 'UserRequest',
            'key'       => 'SELECT UserRequest AS u JOIN lnkFunctionalCIToTicket AS l ON l.ticket_id = u.id JOIN FunctionalCI AS f ON l.functionalci_id = f.id WHERE f.idoit_id = ' .
                $p_object_id
        ];

        try {
            // Perform a generic request and get the desired tickets for the ci
            return $this->generic_request($l_params);
        } catch (Exception $e) {
            // @see  ID-8284  Keep the overview page from crashing.
            isys_application::instance()->container->get('notify')->error($e->getMessage(), ['life' => 10]);
        }

        return [];
    }

    /**
     * Get ticket(s) by status
     *
     * @param mixed $p_status
     *
     * @return string
     */
    public function get_tickets_by_status($p_status)
    {
        return [];
    }

    public function get_users($p_user_id = null)
    {
        return [];
    }

    public function login($p_user, $p_pass)
    {
        return true;
    }

    public function logout()
    {
        return true;
    }

    public function search($p_params)
    {
        return [];
    }

    public function get_tickets_by_custom_field($p_custom_fields, $p_order_by = null)
    {
        return [];
    }

    public function get_ticket_links($p_ticket_id)
    {
        // Not used in code
    }

    private function request($p_params = [])
    {
        return $this->m_protocol->post(null, [
            'version'   => '1.0',
            'auth_user' => $this->m_user,
            'auth_pwd'  => $this->m_pass,
            'json_data' => isys_format_json::encode($p_params)
        ]);
    }

    /**
     * Formats an RT result
     *
     * @param string $p_raw_data
     *
     * @return array
     * @throws isys_exception_general
     */
    private function format_response($p_raw_data)
    {
        $l_result = [];

        // Check for a valid json response first
        if (!isys_format_json::is_json($p_raw_data)) {
            throw new isys_exception_general('iTop API response is not valid.');
        }

        // Encode charset and notation
        $p_raw_data = isys_format_json::decode($p_raw_data);

        // Check for returned code
        if (!is_array($p_raw_data) || !isset($p_raw_data['code']) || $p_raw_data['code'] != 0) {
            throw new isys_exception_general('An error occured while querying the api: ' . $p_raw_data['message']);
        }

        // Check for filled resultset
        if (isset($p_raw_data['objects']) && is_array($p_raw_data['objects'])) {
            // Map the response
            foreach ($p_raw_data['objects'] as $l_ticket) {
                // Some defaulting stuff and manipulations
                $l_ticket['fields']['id'] = $l_ticket['key'];
                $l_ticket['fields']['caller_id_friendlyname2'] = $l_ticket['fields']['caller_id_friendlyname'];
                $l_ticket['fields']['priority2'] = $l_ticket['fields']['priority3'] = $l_ticket['fields']['priority'];
                $l_ticket['fields']['start_date2'] = $l_ticket['fields']['start_date3'] = $l_ticket['fields']['start_date'];

                // Build array
                $l_result[$l_ticket['key']] = $this->map_response($l_ticket['fields']);
            }
        }

        return $l_result;
    }

    /**
     * Prepare a generic post request
     *
     * @param $p_params
     *
     * @return array
     * @throws isys_exception_general
     */
    private function generic_request($p_params)
    {
        $l_raw_data = $this->request($p_params);

        if ($l_raw_data) {
            return $this->format_response($l_raw_data);
        }

        return [];
    }

    /**
     * Injects the used protocol and sets RT's base url.
     *
     * @param $p_protocol
     *
     * @throws isys_exception_general
     */
    public function __construct($p_protocol)
    {
        parent::__construct($p_protocol);

        $this->m_ticket_url_pattern = $this->m_protocol->get_base_url() . $this->m_ticket_url_pattern;
        $this->m_rt_base_url = $this->m_protocol->get_host() . $this->m_protocol->get_base_url();
        $this->m_protocol->attach_base_url($this->m_url_base);
    }
}
