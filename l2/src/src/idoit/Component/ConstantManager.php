<?php

namespace idoit\Component;

use Exception;
use isys_component_database;
use isys_component_session;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class ConstantManager
{
    private const CACHE_FILE = 'constants.php';

    private static self $instance;

    private isys_component_database $database;
    private isys_component_database $systemDatabase;
    private isys_component_session $session;
    private Filesystem $fs;

    private array $systemCaches = [
        'isys_const_system' => 'System constants',
        'isys_language'     => 'Language cache',
    ];

    private array $tenantCaches = [
        'isys_ac_air_quantity_unit'           => 'AC units',
        'isys_ac_refrigerating_capacity_unit' => 'AC refrigerating units',
        'isys_backup_cycle'                   => 'Backup cycle',
        'isys_backup_type'                    => 'Backup type',
        'isys_catg_application_priority'      => 'Application types',
        'isys_catg_application_type'          => 'Application types',
        'isys_catg_global_category'           => 'Category for global categories',
        'isys_catg_identifier_type'           => 'Custom identifier types',
        'isys_cats_prt_emulation'             => 'Prt emulation',
        'isys_cats_prt_type'                  => 'Prt type',
        'isys_client_type'                    => 'Client types',
        'isys_cluster_type'                   => 'Cluster types',
        'isys_cmdb_status'                    => 'CMDB Status',
        'isys_connection_type'                => 'Connector types',
        'isys_contact_tag'                    => 'Contact Tags',
        'isys_contract_notice_period_type'    => 'Contract notice period type',
        'isys_contract_payment_period'        => 'Time intervals',
        'isys_controller_type'                => 'Controller types',
        'isys_currency'                       => 'Currencies',
        // @see ID-8082 adding database object types
        'isys_database_objects'               => 'Database object types',
        'isys_depth_unit'                     => 'Depth units',
        'isys_frequency_unit'                 => 'Frequency units',
        'isys_guarantee_period_unit'          => 'Guarantee period unit (for global category)',
        'isys_interface'                      => 'Connector interface',
        'isys_interval'                       => 'Time intervals',
        'isys_ip_assignment'                  => 'IP Assignment types',
        'isys_ipv6_assignment'                => 'IPv6 Assignments',
        'isys_ipv6_scope'                     => 'IPv6 scopes',
        'isys_layer2_net_subtype'             => 'Network subtypes',
        'isys_layer2_net_type'                => 'Layer-2 net types',
        'isys_logbook_event'                  => 'Logbook events',
        'isys_logbook_level'                  => 'Logbook Level',
        'isys_logbook_source'                 => 'Logbook sources',
        'isys_memory_unit'                    => 'Memory units',
        'isys_model_manufacturer'             => 'Model manufacturer',
        'isys_module'                         => 'Modules',
        'isys_net_dhcp_type'                  => 'DHCP Types',
        'isys_net_dhcpv6_type'                => 'DHCPv6 Types',
        'isys_net_type'                       => 'Net types',
        'isys_obj'                            => 'Objects',
        'isys_obj_type'                       => 'Object types',
        'isys_obj_type_group'                 => 'Object type groups',
        'isys_pobj_type'                      => 'Power object types',
        'isys_port_duplex'                    => 'Port duplex',
        'isys_port_mode'                      => 'Port modes',
        'isys_port_negotiation'               => 'Port negotiation',
        'isys_port_speed'                     => 'Port Speeds',
        'isys_port_type'                      => 'Port types',
        'isys_power_fuse_type'                => 'Power fuse types',
        'isys_raid_type'                      => 'Raid types',
        'isys_relation_type'                  => 'Relation types',
        'isys_report_category'                => 'Report categories with constants',
        'isys_snmp_community'                 => 'SNMP communities',
        'isys_stor_raid_level'                => 'Raid Levels',
        'isys_stor_type'                      => 'Storage device types',
        'isys_switch_role'                    => 'Switch roles',
        'isys_switch_spanning_tree'           => 'Spanning tree',
        // @see  WORKFLOW-32  Fixes the bug by adding the constants to the cache.
        'isys_task_event'                     => 'Task Events',
        'isys_tts_type'                       => 'TTS Types',
        'isys_unit_of_time'                   => 'Time units',
        'isys_virtual_network_type'           => 'Virtual network types',
        'isys_virtual_storage_type'           => 'Virtual storage types',
        'isys_vlan_management_protocol'       => 'VLAN Management',
        'isys_volume_unit'                    => 'Volume units',
        'isys_wan_capacity_unit'              => 'WAN Speed units',
        'isys_weight_unit'                    => 'Weight units',
        'isys_weighting'                      => 'Relation weighting',
        'isys_wlan_channel'                   => 'Wlan / access point channel',
        'isys_wlan_function'                  => 'Wlan / access point function',
        'isys_wlan_standard'                  => 'Wlan / access point standard',
        'isys_workflow_action_type'           => 'Workflow Actiontypes',
        'isys_workflow_status'                => 'Workflow Status',
        'isys_workflow_type'                  => 'Workflow Types',
        'isysgui_catg'                        => 'Global categories',
        'isysgui_catg_custom'                 => 'Custom categories',
        'isysgui_cats'                        => 'Specific categories',
    ];

    public static function instance(isys_component_database $databaseSystem, isys_component_database $database, isys_component_session $session): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($databaseSystem, $database, $session);
        }

        return self::$instance;
    }

    private function __construct(isys_component_database $databaseSystem, isys_component_database $database, isys_component_session $session)
    {
        $this->database = $database;
        $this->systemDatabase = $databaseSystem;
        $this->session = $session;
        $this->fs = new Filesystem();
    }

    private function getSystemCacheFilePath(): string
    {
        return BASE_DIR . 'temp/' . self::CACHE_FILE;
    }

    private function getTenantCacheFilePath(?string $overwriteDirectoryName = null): ?string
    {
        // Overwriting can be necessary in CLI context.
        $tenantDirectory = $this->session->get_tenant_cache_dir() ?: $overwriteDirectoryName;

        if (trim($tenantDirectory) === '') {
            return null;
        }

        return BASE_DIR . 'temp/' . $tenantDirectory . '/' . self::CACHE_FILE;
    }

    /**
     * Clear tenant cache.
     *
     * @return void
     */
    public function deleteTenantCacheFile(): void
    {
        $filePath = $this->getTenantCacheFilePath();

        // Tenant cache is not known yet.
        if ($filePath === null) {
            return;
        }

        $this->fs->remove($filePath);
    }

    /**
     * Clear system cache.
     *
     * @return void
     */
    public function deleteSystemCacheFile(): void
    {
        $this->fs->remove($this->getSystemCacheFilePath());
    }

    /**
     * Creates the dynamic constants mandator cache-file.
     * Fetches all records matching the given criteria, creates a PHP file with const definition for including.
     *
     * @param string|null $overwriteDirectoryName
     * @return $this
     */
    public function createTenantCacheFile(?string $overwriteDirectoryName = null): self
    {
        $filePath = $this->getTenantCacheFilePath($overwriteDirectoryName);

        // Tenant cache is not known yet.
        if ($filePath === null) {
            return $this;
        }

        $content = '';

        foreach ($this->tenantCaches as $table => $headline) {
            $content .= $this->readTableContents($table, $headline, $this->database);
        }

        if (trim($content) !== '') {
            $currentDateTime = date('Y-m-d H:i:s');

            $content = <<<PHP
<?php
// Here we store tenant constants.
// Current state was created {$currentDateTime}.
{$content};
PHP;

            $this->fs->dumpFile($filePath, $content);
            $this->fs->chmod($filePath, 0664);
        }

        return $this;
    }

    /**
     * Creates the constant cache file for the system database.
     * Fetches all records matching the given criteria, creates a PHP file with const definition for including.
     *
     * @return $this
     */
    public function createSystemCacheFile(): self
    {
        global $g_config;

        $content = '';

        foreach ($this->systemCaches as $table => $headline) {
            $content .= $this->readTableContents($table, $headline, $this->systemDatabase);
        }

        if (trim($content) !== '') {
            $currentDateTime = date('Y-m-d H:i:s');
            $httpsEnabled = isset($_SERVER['HTTPS']) ? 'true' : 'false';
            $timeZoneTime = date('H:i:s', 0);

            $content = <<<PHP
<?php
// Here we store system constants.
// Current state was created {$currentDateTime}.

// Address cache for i-doit handlers, which are started by php-cli mode (there isn't any apache variable available then).
define('C__HTTP_HOST', '{$_SERVER['HTTP_HOST']}');
define('C__HTTPS_ENABLED', {$httpsEnabled});
define('C__WWW_DIR', '{$g_config['www_dir']}');
define('C__DOCUMENT_ROOT', '{$_SERVER['DOCUMENT_ROOT']}');
define('C__SERVER_ADDR', '{$_SERVER['SERVER_ADDR']}');
define('C__SERVER_PORT', '{$_SERVER['SERVER_PORT']}');
define('C__SERVER_NAME', '{$_SERVER['SERVER_NAME']}');

// Fallback date for empty or not-set dates (start of unix timestamp, based on timezone).
define('C__FALLBACK_EMPTY_DATE', '1970-01-01');
define('C__FALLBACK_EMPTY_DATETIME', '1970-01-01 00:00:00');
define('C__FALLBACK_EMPTY_DATETIME_TZ', '1970-01-01 {$timeZoneTime}');
{$content}
PHP;

            $filePath = $this->getSystemCacheFilePath();

            $this->fs->dumpFile($filePath, $content);
            $this->fs->chmod($filePath, 0664);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function includeSystemCache(): self
    {
        $filePath = $this->getSystemCacheFilePath();

        try {
            if (!file_exists($filePath)) {
                $this->createSystemCacheFile();
            }

            if (file_exists($filePath)) {
                include_once $filePath;
            } else {
                throw new Exception('Cache file exists but is not readable: ' . $filePath);
            }
        } catch (Throwable $e) {
            $this->fallbackCache($this->systemCaches, $this->systemDatabase);

            // @todo send message to system log saying "Tenant constant file (...) is not accessible due to write permission problems in (...)".
        }

        return $this;
    }

    /**
     * @param string|null $overwriteDirectoryName
     * @return $this
     * @throws Exception
     */
    public function includeTenantCache(?string $overwriteDirectoryName = null): self
    {
        $filePath = $this->getTenantCacheFilePath($overwriteDirectoryName);

        if ($filePath === null) {
            throw new Exception('Tenant cache directory is unknown.');
        }

        try {
            if (!file_exists($filePath)) {
                $this->createTenantCacheFile($overwriteDirectoryName);
            }

            if (file_exists($filePath)) {
                include_once $filePath;
            } else {
                throw new Exception('Cache file exists but is not readable: ' . $filePath);
            }
        } catch (Throwable $e) {
            $this->fallbackCache($this->tenantCaches, $this->database);

            // @todo send message to system log saying "Temp directory $tenantSubdirectory is not writable"
        }

        return $this;
    }

    /**
     * Fetches all records matching the "criteria" to create a PHP file with constant definitions.
     *
     * @param string                  $table
     * @param string                  $headline
     * @param isys_component_database $database
     * @return string|null
     */
    private function readTableContents(string $table, string $headline, isys_component_database $database): ?string
    {
        $result = $database->query("SHOW TABLES LIKE '{$table}';");

        if ($database->num_rows($result) == 0) {
            return null;
        }

        try {
            $result = $database->query("SELECT * FROM {$table} WHERE !ISNULL({$table}__const);");

            // File header (placed at this position to remember empty tables by viewing the const_cache.
            $code = PHP_EOL . "// {$headline} from table '{$table}' (in '{$database->get_db_name()}')." . PHP_EOL;

            if ($database->num_rows($result)) {
                while ($row = $database->fetch_array($result)) {
                    $identifier = $row[$table . '__const'];
                    $value = $row[$table . '__value'] ?? $row[$table . '__id'];

                    if (trim($identifier) === '') {
                        continue;
                    }

                    $code .= "if (!defined('{$identifier}')) {\n    define('{$identifier}', {$value});\n}\n";
                }
            }
        } catch (Throwable $e) {
            return null;
        }

        return $code;
    }

    /**
     * Fallback mode for non-accessible cache file.
     * - Retrieves all constants as PHP define statements
     * - Evaluates them with eval()
     */
    private function fallbackCache(array $cacheTables, isys_component_database $database): void
    {
        $code = '';

        foreach ($cacheTables as $table => $headline) {
            $code .= $this->readTableContents($table, $headline, $database);
        }

        if (trim($code) !== '') {
            eval($code);
        }
    }
}
