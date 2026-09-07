<?php

namespace idoit\Module\Cmdb\Controller;

use idoit\Component\Provider\DiInjectable;
use idoit\Controller\Responseable;
use InvalidArgumentException;
use isys_application;
use isys_controller;
use isys_format_json as JSON;
use isys_register;
use Throwable;

/**
 * i-doit Setting controller.
 *
 * @package     idoit\Module\Cmdb\Controller
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.11.1
 */
class Setting extends Main implements isys_controller, Responseable
{
    use DiInjectable;

    private $response;

    private const AVAILABLE_TYPES = [
        'user' => 'settingsUser',
        'tenant' => 'settingsTenant',
        'system' => 'settingsSystem'
    ];

    /**
     * Pre method gets called by the framework.
     *
     * @return void
     */
    public function pre()
    {
        header('Content-Type: application/json');

        $this->response = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];
    }

    /**
     * Method for getting a system, tenant or user setting.
     *
     * @param isys_register $request
     *
     * @return void
     */
    public function get(isys_register $request)
    {
        try {
            $postData = (array)$request->get('GET');

            if (!isset($postData['type']) || !isset(self::AVAILABLE_TYPES[$postData['type']])) {
                throw new InvalidArgumentException('The setting "type" is either missing or should be one of these: ' . implode(', ', array_keys(self::AVAILABLE_TYPES)));
            }

            if (!isset($postData['key']) || empty($postData['key'])) {
                throw new InvalidArgumentException('The setting "key" needs to be passed!');
            }

            $this->response['data'] = isys_application::instance()->container
                ->get(self::AVAILABLE_TYPES[$postData['type']])
                ->get($postData['key'], $postData['default'] ?? null);
        } catch (Throwable $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    /**
     * Method for setting a system, tenant or user setting.
     *
     * @param isys_register $request
     *
     * @return void
     */
    public function set(isys_register $request)
    {
        try {
            $postData = (array)$request->get('POST');

            if (!isset($postData['type']) || !isset(self::AVAILABLE_TYPES[$postData['type']])) {
                throw new InvalidArgumentException('The setting "type" is either missing or should be one of these: ' . implode(', ', array_keys(self::AVAILABLE_TYPES)));
            }

            if (!isset($postData['key']) || empty($postData['key'])) {
                throw new InvalidArgumentException('The setting "key" needs to be passed!');
            }

            if (!isset($postData['value'])) {
                throw new InvalidArgumentException('The setting "value" needs to be passed!');
            }

            $this->response['data'] = isys_application::instance()->container
                ->get(self::AVAILABLE_TYPES[$postData['type']])
                ->set($postData['key'], $postData['value']);
        } catch (Throwable $e) {
            $this->response['success'] = false;
            $this->response['message'] = $e->getMessage();
        }
    }

    /**
     * Return the JSON and die.
     *
     * @return void
     */
    public function post()
    {
        echo JSON::encode($this->response);
        die;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
