<?php

/**
 * i-doit
 *
 * Monitoring livestatus connector class.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_monitoring_livestatus
{
    /**
     * The query resulsts will only be cached for a certain time - Default 10 minutes.
     *
     * @var  integer
     */
    const CACHE_TIME = 600;

    /**
     * Configuration array.
     */
    private static array $m_config = [];

    /**
     * Singleton instances.
     */
    private static array $m_instances = [];

    /**
     * The socket or stream connection.
     * This will be a resource of type 'socket' for UNIX/plain TCP, or 'stream' for SSL TCP.
     *
     * @var resource|null
     */
    private $m_connection = null;

    /**
     * Flag to indicate if the current connection is an SSL stream.
     */
    private bool $m_is_ssl_stream = false;

    /**
     * This variable indicates the currently used host.
     */
    private int $m_host;

    /**
     * @param string $str
     *
     * @return string
     * @see ID-10860 In PHP 8.3 'utf8_encode' is deprecated and should no longer be used.
     */
    private function encode(string $str): string
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
        }

        if (PHP_VERSION_ID < 80300) {
            return utf8_encode($str);
        }

        return $str;
    }

    /**
     * Static factory method.
     *
     * @static
     * @param integer $p_host
     * @return isys_monitoring_livestatus
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    public static function factory($p_host): isys_monitoring_livestatus
    {
        if (empty($p_host)) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__NO_CONFIG'), 0, false);
        }

        if (isset(self::$m_instances[$p_host])) {
            return self::$m_instances[$p_host];
        }

        return self::$m_instances[$p_host] = new self((int) $p_host);
    }

    /**
     * Destructor for disconnecting the socket.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Method for connecting to the configured socket.
     *
     * @return  isys_monitoring_livestatus
     * @throws  isys_exception_general
     */
    public function connect()
    {
        $this->m_is_ssl_stream = false;

        try {
            if (!$this->createConnection()) {
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__NO_CONFIG', 'Invalid connection type configured.'), 0, false);
            }

            if (!$this->m_is_ssl_stream && self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__TCP) {
                // Disable Nagle's Algorithm.
                if (defined('TCP_NODELAY')) {
                    @socket_set_option($this->m_connection, SOL_TCP, TCP_NODELAY, 1);
                } else {
                    // See http://bugs.php.net/bug.php?id=46360
                    @socket_set_option($this->m_connection, SOL_TCP, 1, 1);
                }
            }

        } catch (ErrorException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Connect with socket or stream defined in config.
     *
     * @return bool
     * @throws isys_exception_general
     */
    private function createConnection(): bool
    {
        $useSSL = self::$m_config[$this->m_host]['isys_monitoring_hosts__ssl'];
        $timeout_sec = isys_tenantsettings::get('monitoring.status.max-execution-time', 30);

        // Check for required extensions
        if (!extension_loaded("sockets")) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__PHP_EXTENSION_MISSING', 'sockets'), 0);
        }
        if ($useSSL && !extension_loaded("openssl")) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__PHP_EXTENSION_MISSING', 'openssl'), 0);
        }

        // UNIX socket connection
        if (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__UNIX) {
            $this->m_connection = @socket_create(AF_UNIX, SOCK_STREAM, 0);
            if ($this->m_connection === false) {
                $this->m_connection = null;
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'), 0);
            }
            // Set timeout for socket_connect
            @socket_set_option($this->m_connection, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout_sec, 'usec' => 0]);
            @socket_set_option($this->m_connection, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout_sec, 'usec' => 0]);

            return @socket_connect($this->m_connection, self::$m_config[$this->m_host]['isys_monitoring_hosts__path']);
        }

        // TCP socket connection (with or without SSL)
        if (self::$m_config[$this->m_host]['isys_monitoring_hosts__connection'] == C__MONITORING__LIVESTATUS_TYPE__TCP) {
            $address = self::$m_config[$this->m_host]['isys_monitoring_hosts__address'];
            $port = self::$m_config[$this->m_host]['isys_monitoring_hosts__port'];

            if ($useSSL) {
                $this->m_is_ssl_stream = true; // This connection will be handled as a stream
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                        'crypto_method'     => STREAM_CRYPTO_METHOD_TLS_CLIENT,
                    ],
                ]);

                $socket_address = 'tls://' . $address . ':' . $port;

                // Use stream_socket_client for SSL connections directly
                $this->m_connection = @stream_socket_client($socket_address, $errno, $errstr, $timeout_sec, // Timeout for connect operation
                    STREAM_CLIENT_CONNECT, $context);

                if ($this->m_connection === false) {
                    $this->m_connection = null;
                    throw new isys_exception_general(isys_application::instance()->container->get('language')
                            ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CONNECT_LIVESTATUS') . " ($errstr, Error No: $errno)", 0, false);
                }

                stream_set_timeout($this->m_connection, $timeout_sec);

                return true;

            }

            // Non-SSL TCP connection, use sockets extension
            $this->m_connection = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($this->m_connection === false) {
                $this->m_connection = null;
                throw new isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_CREATE_SOCKET'), 0);
            }
            // Set timeout for socket_connect
            @socket_set_option($this->m_connection, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout_sec, 'usec' => 0]);
            @socket_set_option($this->m_connection, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout_sec, 'usec' => 0]);

            return @socket_connect($this->m_connection, $address, $port);

        }

        return false;
    }

    /**
     * Disconnect method.
     *
     * @return  isys_monitoring_livestatus
     */
    public function disconnect()
    {
        if (is_resource($this->m_connection)) {
            if ($this->m_is_ssl_stream) {
                @fclose($this->m_connection);
            } else {
                @socket_close($this->m_connection);
            }
        }
        $this->m_connection = null;
        $this->m_is_ssl_stream = false;

        return $this;
    }

    /**
     * Query method.
     *
     * @param   array   $p_query
     * @param   boolean $p_force Enabling "force" will skip the cache-data.
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function query(array $p_query, $p_force = false)
    {
        $p_query = array_filter($p_query);

        if (count($p_query) == 0) {
            return [];
        }

        // First we look inside our cache, if this specific query has been retrieved in the past...
        $l_query_hash = md5(implode(',', $p_query));
        $l_cache = isys_caching::factory('monitoring', self::CACHE_TIME);

        if (!$p_force && ($l_cached = $l_cache->get('query:' . $l_query_hash))) {
            return $l_cached;
        }

        // Check if connection is active before writing.
        if (!is_resource($this->m_connection)) {
            $this->connect(); // Reconnect if necessary
        }

        $query_string = implode("\n", $p_query) . "\nOutputFormat:json\nResponseHeader: fixed16\nKeepAlive: on\n\n";

        // Write to socket or stream based on connection type
        if ($this->m_is_ssl_stream) {
            $l_write_result = @fwrite($this->m_connection, $query_string);
        } else {
            $l_write_result = @socket_write($this->m_connection, $query_string, strlen($query_string));
        }

        if ($l_write_result === false || $l_write_result === 0) { // Check for false AND 0 bytes written
            $error_msg = '';
            if (!$this->m_is_ssl_stream) {
                $error_msg = socket_strerror(socket_last_error($this->m_connection));
            } else {
                // For streams, check stream_get_meta_data for more info, or rely on higher level error.
                $meta = stream_get_meta_data($this->m_connection);
                if ($meta['timed_out']) {
                    $error_msg = 'Stream write timed out';
                } elseif ($meta['eof']) {
                    $error_msg = 'Stream connection closed unexpectedly during write';
                } else {
                    $error_msg = 'Stream write error (unknown)';
                }
            }
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_WRITE_TO_SOCKET', $error_msg), false);
        }

        // Read 16 bytes to get the status code and body size.
        $l_read = $this->read_socket(16);

        if ($l_read === false) {
            if (!$this->m_is_ssl_stream) {
                $error_msg = socket_strerror(socket_last_error($this->m_connection));
            } else {
                $meta = stream_get_meta_data($this->m_connection);
                if ($meta['timed_out']) {
                    $error_msg = 'Stream read timed out';
                } elseif ($meta['eof']) {
                    $error_msg = 'Stream connection closed unexpectedly during read';
                } else {
                    $error_msg = 'Stream read error (unknown)';
                }
            }

            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', $error_msg), false);
        }

        // Extract status code.
        $l_status = substr($l_read, 0, 3);

        // Extract content length.
        $l_length = intval(trim(substr($l_read, 4, 11)));

        // Read socket until end of data.
        $l_read = $this->read_socket($l_length);

        if ($l_read === false) {
            if (!$this->m_is_ssl_stream) {
                $error_msg = socket_strerror(socket_last_error($this->m_connection));
            } else {
                $meta = stream_get_meta_data($this->m_connection);
                if ($meta['timed_out']) {
                    $error_msg = 'Stream read timed out';
                } elseif ($meta['eof']) {
                    $error_msg = 'Stream connection closed unexpectedly during read';
                } else {
                    $error_msg = 'Stream read error (unknown)';
                }
            }
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', $error_msg), false);
        }

        // Catch errors (Like HTTP 200 is OK).
        if ($l_status != "200") {
            $error_msg = $l_read ?: 'It seems like the connection could not be established properly. Please verify that your configuration is correct.';

            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', $error_msg));
        }

        // Catch problems occured while reading? 104: Connection reset by peer
        // This is primarily for socket connections. Stream errors are handled by stream_get_meta_data and error handling on fwrite/fread.
        if (!$this->m_is_ssl_stream && socket_last_error($this->m_connection) == 104) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__COULD_NOT_READ_FROM_SOCKET', socket_strerror(socket_last_error($this->m_connection))), false);
        }

        // Decode the json response
        $l_return = json_decode($this->encode($l_read));

        // json_decode returns null on syntax problems
        if ($l_return === null) {
            throw new isys_exception_general(isys_application::instance()->container->get('language')
                ->get('LC__MONITORING__LIVESTATUS_EXCEPTION__INVALID_FORMAT'), 0, false);
        }

        // Cache the current query response.
        $l_cache->set('query:' . $l_query_hash, $l_return);

        return $l_return;
    }

    /**
     * Method for reading data from the open socket/stream.
     *
     * @param   integer $p_length
     *
     * @return  mixed  Will return a string with data, or boolean false on failure.
     */
    private function read_socket($p_length)
    {
        $l_offset = 0;
        $l_data_buffer = '';

        while ($l_offset < $p_length) {
            if ($this->m_is_ssl_stream) {
                // Read from stream. fread returns false on error, 0 on EOF.
                $l_data = @fread($this->m_connection, $p_length - $l_offset);
                if ($l_data === false) {
                    return false; // Error reading from stream
                }
            } else {
                // Read from socket. socket_read returns false on error, empty string on EOF.
                $l_data = @socket_read($this->m_connection, $p_length - $l_offset, PHP_BINARY_READ);
                if ($l_data === false) {
                    return false; // Error reading from socket
                }
            }

            $l_dataLen = strlen($l_data);
            $l_offset += $l_dataLen;
            $l_data_buffer .= $l_data;

            // If no data was read but we still need more, and not at EOF, then something is wrong
            // or connection was closed prematurely.
            if ($l_dataLen == 0 && $l_offset < $p_length) {
                if ($this->m_is_ssl_stream) {
                    $meta = stream_get_meta_data($this->m_connection);
                    if ($meta['eof'] || $meta['timed_out']) {
                        // Connection closed or timed out, return what we have (might be partial or empty)
                        break;
                    }
                } else {
                    // For sockets, empty string from socket_read typically means EOF.
                    // If socket_last_error is 0 and data is empty, it's EOF.
                    if (socket_last_error($this->m_connection) == 0) {
                        break; // EOF
                    }

                    // Otherwise, an error occurred.
                    return false;
                }
            }
        }

        return $l_data_buffer;
    }

    /**
     * Private clone method - Singleton!
     */
    private function __clone()
    {
    }

    /**
     * Private constructor method - Singleton!
     *
     * @param int $p_host
     * @throws isys_exception_database
     * @throws isys_exception_general
     */
    private function __construct(int $p_host)
    {
        global $g_comp_database;

        $this->m_host = $p_host;

        self::$m_config[$this->m_host] = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_data($this->m_host, C__MONITORING__TYPE_LIVESTATUS)
            ->get_row();

        $this->connect();
    }
}
