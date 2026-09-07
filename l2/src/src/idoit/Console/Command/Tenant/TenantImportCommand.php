<?php

namespace idoit\Console\Command\Tenant;

use Exception;
use idoit\Console\Command\AbstractCommand;
use idoit\Module\License\Event\Tenant\TenantAddedEvent;
use idoit\Module\License\Event\Tenant\TenantBeforeAddedEvent;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use isys_component_dao_mandator;
use isys_component_database;
use isys_format_json;
use isys_settings;
use isys_tenantsettings;
use isys_update_files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class TenantImportCommand extends AbstractCommand
{
    const NAME = 'system:tenant-import';
    const AUTH_DOMAIN = self::AUTH_DOMAIN_SYSTEM;
    const REQUIRES_LOGIN = false;

    private InputInterface $input;
    private OutputInterface $output;
    private ?string $targetPath = null;
    private ?int $tenantId = null;
    private ?string $rootUser = null;
    private ?string $rootPassword = null;
    private ?Filesystem $filesystem = null;
    private ?string $file = null;
    private ?string $dbHost = null;
    private ?int $dbPort = null;
    private string $databaseVersion;
    private isys_component_database $systemDatabase;

    private const IGNORED_TENANT_SETTINGS = [
        'system.base.uri'
    ];

    /**
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Import your tenant data inluding uploaded files from a ZIP package generated from the system:tenant-export command.';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        global $g_db_system;

        $definition = new InputDefinition();
        $definition->addOption(new InputOption('file', 'f', InputOption::VALUE_REQUIRED, 'Source ZIP file'));
        $definition->addOption(new InputOption('tenant-database-name', 'd', InputOption::VALUE_REQUIRED, 'Tenant database name'));
        $definition->addOption(new InputOption('tenant-title', 't', InputOption::VALUE_REQUIRED, 'Tenant name'));
        $definition->addOption(new InputOption('with-system-settings', '', InputOption::VALUE_NONE, 'Merge system settings'));
        $definition->addOption(new InputOption('with-tenant-settings', '', InputOption::VALUE_NONE, 'Import tenant settings'));
        $definition->addOption(new InputOption('db-root-user', '', InputOption::VALUE_REQUIRED, 'Database root user'));
        $definition->addOption(new InputOption('db-root-pass', '', InputOption::VALUE_REQUIRED, 'Database root password'));
        $definition->addOption(new InputOption('db-host', '', InputOption::VALUE_REQUIRED, 'Database host', $g_db_system['host']));
        $definition->addOption(new InputOption('db-port', '', InputOption::VALUE_REQUIRED, 'Database port', $g_db_system['port']));

        return $definition;
    }

    /**
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \idoit\Exception\JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $g_db_system, $g_license_token;

        $importTime = date("Y-m-d_H-i-s", time());
        $this->input = $input;
        $this->output = $output;
        $this->filesystem = new Filesystem();
        $this->targetPath = BASE_DIR . "temp/idoit-tenant-export-" . $importTime;
        $this->rootUser = $input->getOption('db-root-user');
        $this->rootPassword = $input->getOption('db-root-pass');
        $this->dbHost = $input->getOption('db-host');
        $this->dbPort = $input->getOption('db-port');
        $io = new SymfonyStyle($input, $output);

        $this->systemDatabase = isys_application::instance()->container->get('database_system');

        $this->databaseVersion = $this->systemDatabase->query('SELECT VERSION() AS v;')->fetch_assoc()['v'];

        if (!$this->rootUser) {
            $this->rootUser = $io->ask("Please enter root database user");
        }

        if (!$this->rootPassword) {
            $this->rootPassword = $io->askHidden("Please enter root database password");
        }

        if (!$this->dbHost) {
            $this->dbHost = $g_db_system['host'];
        }

        if (!$this->dbPort) {
            $this->dbPort = $g_db_system['port'];
        }

        if (!$this->rootUser) {
            $io->writeln("<comment>Please pass the database credentials <error>db-root-user</error> and <error>db-root-pass</error>.</comment>");
            return Command::FAILURE;
        }

        $this->file = $input->getOption('file') ?? null;
        if (!$this->file) {
            $this->file = $io->ask("Please enter the location of the source Zip file");
        }

        if (!file_exists($this->file)) {
            $io->error("Please set the source zip file with option --file or confirm that the file exists or is correct.");
            return Command::FAILURE;
        }

        $rawData = file_get_contents('zip://' . $this->file .'#idoit.json', false);

        if (!isys_format_json::is_json_array($rawData)) {
            $io->error("Tenant import aborted. Malformed JSON in idoit.json file.");
            return Command::FAILURE;
        }

        $jsonData = isys_format_json::decode($rawData);

        $tenantDatabaseName = $input->getOption('tenant-database-name');

        if (!$tenantDatabaseName) {
            $tenantDatabaseName = $io->ask("Please enter the name of the new tenant database") ?? $jsonData['tenant']['database'] ?? '';
        }

        $currentVersion = isys_application::instance()->info['version'];

        if ($jsonData['version']['db'] !== $currentVersion || $jsonData['version']['file'] !== $currentVersion) {
            $io->error([
                "Tenant import aborted. Versions are not aligned.",
                "Installed Version: {$currentVersion}",
                "File Version: {$jsonData['version']['file']}",
                "File DB Version: {$jsonData['version']['db']}"
            ]);
            return Command::FAILURE;
        }

        try {
            $systemDao = new isys_component_dao_mandator($this->systemDatabase);
            $this->extractFile();
            $tenantTitle = $input->getOption('tenant-title') ?? $jsonData['tenant']['title'] ?? '';
            $tenantDbUser = $g_db_system['user'] ?? $jsonData['tenant']['user'];
            $tenantDbPassword = $g_db_system['pass'] ?? $jsonData['tenant']['pass'];
            $licenseService = null;

            if (class_exists(LicenseServiceFactory::class)) {
                $licenseService = LicenseServiceFactory::createDefaultLicenseService(
                    $systemDao->get_database_component(),
                    $g_license_token
                );
            }

            if (!$tenantDatabaseName || $systemDao->get_mandator_id_by_db_name($tenantDatabaseName)) {
                $io->error("Tenant import aborted. Tenant database name is not set or already exists.");
                return Command::FAILURE;
            }

            $licenseService?->getEventDispatcher()
                ->dispatch(new TenantBeforeAddedEvent(), TenantBeforeAddedEvent::NAME);

            $cryptoHash = $jsonData['config']['g_crypto_hash'];
            $this->createTenant($systemDao, $tenantTitle, $tenantDatabaseName, $tenantDbUser, $tenantDbPassword, $cryptoHash);
            $this->createTenantDb($systemDao);

            if ($this->tenantId > 0) {
                $licenseService?->getEventDispatcher()->dispatch(
                    new TenantAddedEvent($this->tenantId),
                    TenantAddedEvent::NAME
                );
            }

            if (!!$input->getOption('with-system-settings')) {
                $output->writeln(['', 'Apply <info>System settings</info> from system.settings.json ... ']);
                $this->applySystemSettings();
                $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);
            }

            if (!!$input->getOption('with-tenant-settings')) {
                $output->writeln(['', 'Apply <info>Tenant settings</info> from tenant.settings.json ... ']);
                $this->applyTenantSettings();
                $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);
            }

            $output->writeln(['', 'Copy <info>uploaded files</info> from file objects... ']);
            $this->copyObjectFiles();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Copy <info>import files</info>... ']);
            $this->copyImportFiles();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Copy <info>object images</info>... ']);
            $this->copyObjectImageFiles();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Copy <info>object type images</info>... ']);
            $this->copyObjectTypeImageFiles();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln([
                '',
                '<info>All done!</info>',
                "<comment>The Tenant has been successfully imported.</comment>"
            ]);

            $this->cleanup();

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln("<error>An error occured while importing the tenant. {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

    /**
     * Convenience method to retrieve the data from system.settings.json or tenant.settings.json
     *
     * @param string $identifier
     *
     * @return array|mixed
     */
    private function getSettingsFromJson(string $identifier)
    {
        $fileName = "$this->targetPath/{$identifier}.settings.json";
        if (!file_exists($fileName)) {
            return [];
        }

        if (!is_readable($fileName)) {
            $this->output->writeln("File {$fileName} is not readable. Skip applying {$identifier} settings.");
            return [];
        }

        $encodedSettings = file_get_contents($fileName);

        try {
            if (!isys_format_json::is_json_array($encodedSettings)) {
                $this->output->writeln("JSON output from {$fileName} is malformed. Skip applying {$identifier} settings.");
                return [];
            }
        } catch (Throwable $e) {
            $this->output->writeln(["Reading JSON content from {$fileName} is not possible with following error:", $e->getMessage()]);
            return [];
        }

        return json_decode($encodedSettings, true);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function applySystemSettings(): void
    {
        $settings = $this->getSettingsFromJson('system');

        if (empty($settings)) {
            return;
        }
        isys_settings::initialize($this->systemDatabase);
        foreach ($settings as $key => $value) {
            isys_settings::set($key, $value);
        }
        isys_settings::force_save();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function applyTenantSettings(): void
    {
        $settings = $this->getSettingsFromJson('tenant');

        if (empty($settings)) {
            return;
        }
        isys_tenantsettings::initialize($this->systemDatabase, $this->tenantId, true);
        foreach ($settings as $key => $value) {
            if (in_array($key, self::IGNORED_TENANT_SETTINGS)) {
                continue;
            }
            isys_tenantsettings::set($key, $value);
        }
        isys_tenantsettings::force_save();
    }

    /**
     * @return void
     */
    private function cleanup(): void
    {
        if (!$this->filesystem->exists($this->targetPath)) {
            return;
        }

        $this->output->writeln("<comment>- Delete export directory {$this->targetPath}</comment>", OutputInterface::VERBOSITY_DEBUG);
        $this->filesystem->remove($this->targetPath);
    }

    /**
     * @param isys_component_dao_mandator $dao
     *
     * @return void
     * @throws \isys_exception_database
     */
    private function connectTenantDb(isys_component_dao_mandator $dao): void
    {
        global $g_db_system, $g_crypto_hash;
        $tenantData = $dao->get_mandator($this->tenantId)->get_row();

        $g_crypto_hash = $tenantData['isys_mandator__crypto_hash'];

        $tenantDb = isys_component_database::get_database(
            $g_db_system["type"],
            $tenantData["isys_mandator__db_host"],
            $tenantData["isys_mandator__db_port"],
            $tenantData["isys_mandator__db_user"],
            isys_component_dao_mandator::getPassword($tenantData),
            $tenantData["isys_mandator__db_name"]
        );
        isys_application::instance()->container->get('database')->setDatabase($tenantDb);
    }

    /**
     * @param string $identifier
     * @param string $baseDir
     * @param string $folder
     *
     * @return void
     */
    private function mirrorHelper(string $identifier, string $baseDir, string $folder = '')
    {
        $sourcePath = "{$this->targetPath}/files/{$identifier}";

        if (!file_exists($sourcePath)) {
            return;
        }

        if (!is_readable($sourcePath)) {
            $this->output->writeln("Export folder {$sourcePath} is not readable. Skipping copying import files.");
            return;
        }

        $this->filesystem->mirror(
            $sourcePath,
            "{$baseDir}$folder"
        );
    }

    /**
     * @return void
     */
    private function copyObjectTypeImageFiles(): void
    {
        $baseDir = BASE_DIR . "upload/images/{$this->tenantId}/object-type/";

        $this->mirrorHelper('object-type-icons', $baseDir, 'icons');
        $this->mirrorHelper('object-type-images', $baseDir, 'images');
    }

    /**
     * @return void
     */
    private function copyObjectImageFiles(): void
    {
        $this->mirrorHelper('object-images', BASE_DIR . "upload/images/{$this->tenantId}/", 'object-images');
    }

    /**
     * @return void
     */
    private function copyImportFiles(): void
    {
        $this->mirrorHelper('imports', BASE_DIR . "imports/{$this->tenantId}", '');
    }

    /**
     * @return void
     */
    private function copyObjectFiles(): void
    {
        $cmdbDao = isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT
            isys_file_physical__filename AS filename,
            isys_file_physical__filename_original AS originalFilename,
            isys_file_version__isys_obj__id AS objectId
            FROM isys_file_physical
            INNER JOIN isys_file_version ON isys_file_version__isys_file_physical__id = isys_file_physical__id
            WHERE isys_file_physical__filename != ''
            AND isys_file_physical__filename IS NOT NULL;";

        $result = $cmdbDao->retrieve($query);

        $this->output->writeln(' > Reading and copying <info>' . count($result) . ' files</info>...');

        while ($row = $result->get_row()) {
            try {
                $filePath = \isys_application::instance()->getUploadFilePath($row['filename']);
                $fileName = basename($filePath);
                $exportPath = "{$this->targetPath}/files/files/{$fileName}";

                if ($this->filesystem->exists($exportPath)) {
                    $this->output->writeln("<comment> - Copy '{$row['originalFilename']}' from object #{$row['objectId']}.</comment>", OutputInterface::VERBOSITY_DEBUG);
                    $this->filesystem->copy($exportPath, $filePath);
                } else {
                    $this->output->writeln("<error> > Could not find '{$fileName}' (from object #{$row['objectId']}) in the export directory. Skipping.</error>");
                }
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * @param isys_component_dao_mandator $dao
     *
     * @return void
     * @throws Exception
     * @todo DB information from tenant is missing from export. If it is added than this method needs to be adjusted
     */
    private function createTenantDb(isys_component_dao_mandator $dao): void
    {
        $tenantData = $dao->get_mandator($this->tenantId)->get_row();
        $tenantDb = $tenantData['isys_mandator__db_name'];
        $tenantDbUser = $tenantData['isys_mandator__db_user'];

        $user = escapeshellarg($this->rootUser);
        $pass = escapeshellarg($this->rootPassword);
        $host = escapeshellarg($this->dbHost);
        $port = escapeshellarg($this->dbPort);

        $this->output->writeln("Creating tenant db with name <info>{$tenantDb}</info>. This may take a while.");

        $executable = system_which('mysql');

        if (str_contains(strtolower($this->databaseVersion), 'maria')) {
            $this->output->writeln(" > Looks like you are using <comment>MariaDB</comment>. <info>If you use MariaDB 11.0.1 or later, please go sure to make 'mariadb' executable available.</info>");
            $executable = system_which('mariadb', 'mysql');
        }

        $createDb = "CREATE DATABASE {$tenantDb} DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

        // @see ID-12310 Granting privileges (instead 'GRANT ALL') to tenant database.
        $grantDb = "GRANT ALL PRIVILEGES ON {$tenantDb}.* TO '{$tenantDbUser}'@{$host};";
        $dumpFilePath = $this->targetPath . '/dumps/idoit_data.sql';
        $flushPrivileges = "FLUSH PRIVILEGES;";

        $this->output->writeln("Create database <info>{$tenantDb}</info>...");
        exec("{$executable} --user={$user} --password={$pass} --host={$host} --port={$port} -e \"{$createDb}\"", $execOutput);

        $this->output->writeln("Grant rights to <info>'{$tenantDbUser}'@{$host}</info>...");
        exec("{$executable} --user={$user} --password={$pass} --host={$host} --port={$port} -e \"{$grantDb}\"", $execOutput);

        $this->output->writeln("Import tenant data...");
        exec("{$executable} --user={$user} --password={$pass} --host={$host} --port={$port} {$tenantDb} < {$dumpFilePath}", $execOutput);

        $this->output->writeln("Flush privileges...");
        exec("{$executable} --user={$user} --password={$pass} --host={$host} --port={$port} -e \"{$flushPrivileges}\"", $execOutput);

        if (count($execOutput)) {
            throw new Exception('It seems like something went wrong: ' . implode($execOutput));
        }

        $this->connectTenantDb($dao);
    }

    /**
     * @return void
     */
    private function extractFile(): void
    {
        $this->output->writeln("Extracting file...");
        $zipFile = $this->file;

        // Checking for zlib and the ZipArchive class to solve #4853
        if (!class_exists('ZipArchive') || !extension_loaded('zlib')) {
            throw new Exception('Error: Could not extract zip file. Please check if the zip and zlib PHP extensions are installed.');
        }

        if (!(new isys_update_files())->read_zip($zipFile, $this->targetPath, false, true)) {
            throw new Exception('Error: Could not read zip package.');
        }
        $this->output->writeln("Zip file <info>{$zipFile}</info> extracted to <info>{$this->targetPath}</info>.");
    }

    /**
     * @param isys_component_dao_mandator $dao
     * @param string                      $tenantTitle
     * @param string                      $tenantDatabaseName
     *
     * @return void
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @todo DB information from tenant is missing from export. If it is added than this method needs to be adjusted
     */
    private function createTenant(isys_component_dao_mandator $dao, string $tenantTitle, string $tenantDatabaseName, string $tenantDbUser, string $tenantDbPassword, string $cryptoHash): void
    {
        $this->output->writeln("Creating tenant entry into system database...");
        $db = $dao->get_database_component();
        $dao->add(
            $tenantTitle,
            '',
            null,
            'default',
            $this->dbHost,
            $this->dbPort,
            $tenantDbUser,
            $tenantDbPassword,
            $tenantDatabaseName,
            0,
            1,
            $cryptoHash
        );
        $this->tenantId = $dao->get_last_insert_id();

        $this->output->writeln("Tenant entry successfully created with id <info>{$this->tenantId}</info>.");
    }
}
