<?php

namespace idoit\Console\Command\Tenant;

use Exception;
use idoit\AddOn\Manager\ManageCandidates;
use idoit\Console\Command\AbstractCommand;
use isys_application;
use isys_settings;
use isys_tenantsettings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use ZipArchive;

class TenantExportCommand extends AbstractCommand
{
    const NAME = 'system:tenant-export';

    private string $tempDirectory;
    private InputInterface $input;
    private OutputInterface $output;
    private Filesystem $filesystem;
    private int $tenantId;
    private string $databaseVersion;

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
        return 'Export your tenant data inluding uploaded files in a ZIP package.';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition();
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
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->filesystem = new Filesystem();
        $this->tenantId = (int)$this->session->get_mandator_id();

        $this->tempDirectory = BASE_DIR . 'temp/tenant-export-' . substr(md5(microtime()), 0, 8) . '/';

        $targetZipFile = BASE_DIR . "temp/idoit-tenant-export-{$this->tenantId}.zip";

        try {
            if (!class_exists(ZipArchive::class)) {
                throw new Exception("PHP zip extension is not installed. Please install and try again.");
            }

            if ($this->filesystem->exists($targetZipFile)) {
                throw new Exception("Export file {$targetZipFile} already exists.");
            }

            $this->databaseVersion = \isys_application::instance()->container->get('database')->query('SELECT VERSION() AS v;')->fetch_assoc()['v'];

            if (str_contains(strtolower($this->databaseVersion), 'maria')) {
                if (!system_which('mysqldump') && !system_which('mariadb-dump')) {
                    throw new Exception('It seems like "mysqldump" and "mariadb-dump" are both not available on your machine, this is necessary to create database dumps.');
                }
            } else {
                if (!system_which('mysqldump')) {
                    throw new Exception('It seems like "mysqldump" is not available on your machine, this is necessary to create database dumps.');
                }
            }

            // Prepare the zip file.
            $zipArchive = new ZipArchive();

            $zipOpenResult = $zipArchive->open($targetZipFile, ZipArchive::CREATE);

            if ($zipOpenResult !== true) {
                $errorMessage = match ($zipOpenResult) {
                    ZipArchive::ER_EXISTS => "The file <comment>{$targetZipFile}</comment> already exists",
                    ZipArchive::ER_INCONS => 'The zip archiv seems inconsistent',
                    ZipArchive::ER_INVAL => 'Invalid argument',
                    ZipArchive::ER_MEMORY => 'Memory allocation failed',
                    ZipArchive::ER_NOENT => 'No such file',
                    ZipArchive::ER_NOZIP => 'No zip archive',
                    ZipArchive::ER_OPEN => 'File could not be opened',
                    ZipArchive::ER_READ => 'Error while reading',
                    ZipArchive::ER_SEEK => 'Positioning error',
                    default => 'Please try again',
                };

                throw new Exception('Zip file could not be created: ' . $errorMessage);
            }

            $this->output->writeln("<comment>- Prepare export directory underneath {$this->tempDirectory}</comment>", OutputInterface::VERBOSITY_DEBUG);
            $this->filesystem->mkdir($this->tempDirectory, 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'dumps', 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'files/files', 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'files/imports', 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'files/object-images', 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'files/object-type-icons', 0755);
            $this->filesystem->mkdir($this->tempDirectory . 'files/object-type-images', 0755);

            $output->writeln(['', 'Writing <info>idoit.json</info> file']);
            $this->createIdoitFile();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Writing <info>system.settings.json</info> file']);
            $this->createSystemSettingsFile();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Writing <info>tenant.settings.json</info> file']);
            $this->createTenantSettingsFile();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

            $output->writeln(['', 'Dump <info>tenant database</info>, this may take a moment... ']);
            $this->createTenantDumpFile();
            $output->writeln(' > Done', OutputInterface::VERBOSITY_VERBOSE);

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
                'All files collected!',
                'Create <info>' . basename($targetZipFile) . '</info> file, located in <info>' . BASE_DIR . 'temp/</info>.'
            ]);

            $addonPathLength = mb_strlen($this->tempDirectory);
            $fileIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->tempDirectory));

            foreach ($fileIterator as $file) {
                if (strncmp($file->getBasename(), '.', 1) === 0) {
                    continue;
                }

                $output->writeln('<comment>- add ./' . substr($file->getPathname(), $addonPathLength) . '</comment>', OutputInterface::VERBOSITY_DEBUG);
                $zipArchive->addFile($file->getPathname(), substr($file->getPathname(), $addonPathLength));
            }

            $zipArchive->close();
        } catch (Throwable $e) {
            $output->writeln([
                '<error>Something went wrong with the following message</error>',
                "<error>{$e->getMessage()}</error>"
            ]);

            $this->cleanup();

            return Command::FAILURE;
        }

        $output->writeln([
            '',
            'All done!',
            "The exported files have been zipped into <info>{$targetZipFile}</info>."
        ]);

        $this->cleanup();

        return Command::SUCCESS;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function createSystemSettingsFile(): void
    {
        isys_settings::initialize(isys_application::instance()->container->get('database_system'));

        $this->output->writeln('<comment> - Write system.settings.json file</comment>', OutputInterface::VERBOSITY_DEBUG);
        isys_file_put_contents($this->tempDirectory . 'system.settings.json', json_encode(isys_settings::get()));
    }

    /**
     * @return void
     * @throws Exception
     */
    private function createTenantSettingsFile(): void
    {
        isys_tenantsettings::initialize(isys_application::instance()->container->get('database'));

        $this->output->writeln('<comment> - Write tenant.settings.json file</comment>', OutputInterface::VERBOSITY_DEBUG);
        isys_file_put_contents($this->tempDirectory . 'tenant.settings.json', json_encode(isys_tenantsettings::get()));
    }

    /**
     * @return void
     */
    private function cleanup(): void
    {
        if (!$this->filesystem->exists($this->tempDirectory)) {
            return;
        }

        $this->output->writeln("<comment>- Delete export directory {$this->tempDirectory}</comment>", OutputInterface::VERBOSITY_DEBUG);
        $this->filesystem->remove($this->tempDirectory);
    }

    /**
     * @throws Exception
     */
    private function createIdoitFile(): void
    {
        global $g_db_system, $g_security, $g_admin_auth, $g_crypto_hash, $g_license_token, $g_product_info;

        $addons = [];

        $tenantData = $this->session->get_mandator_data();
        $tenantDbPassword = \isys_component_dao_mandator::getPassword($tenantData);
        $this->output->writeln('<comment> - Collecting add-on data</comment>', OutputInterface::VERBOSITY_DEBUG);
        $database = \isys_application::instance()->container->get('database');
        $addonResult = \isys_application::instance()->container->get('moduleManager')->get_modules();

        while ($addon = $addonResult->get_row()) {
            $addonInstance = new ManageCandidates($database, \isys_application::instance()->app_path . 'src/classes/modules/');
            $addonInstance->setIdentifier($addon['isys_module__identifier']);
            $packageJson = $addonInstance->getPackageJson();

            if (empty($addon['isys_module__identifier']) || $packageJson['type'] !== 'addon') {
                continue;
            }

            $addons[$addon['isys_module__identifier']] = $packageJson['version'] ?: '0.0';
        }

        $this->output->writeln('<comment> - Collect tenant version</comment>', OutputInterface::VERBOSITY_DEBUG);
        $versionQuery = "SELECT isys_db_init__value AS val FROM isys_db_init WHERE isys_db_init__key = 'version' LIMIT 1;";
        $tenantVersion = \isys_application::instance()->container->get('cmdb_dao')->retrieve($versionQuery)->get_row_value('val');

        $this->output->writeln('<comment> - Collecting remaining info from config.inc.php</comment>', OutputInterface::VERBOSITY_DEBUG);

        $data = [
            "version" => [
                "file" => $g_product_info['version'],
                "db"   => $tenantVersion
            ],
            "addons"  => $addons,
            "license" => [
                "type"          => "token|offline_license|legacy_license",
                "license_token" => "....."
            ],
            "config"  => [
                'g_db_system' => $g_db_system,
                'g_security' => $g_security,
                'g_admin_auth' => $g_admin_auth,
                'g_crypto_hash' => $g_crypto_hash,
                'g_license_token' => $g_license_token,
            ],
            "tenant" => [
                'title' => $tenantData['isys_mandator__title'],
                'database' => $tenantData['isys_mandator__db_name'],
                'user' => $tenantData['isys_mandator__db_user'],
                'pass' => $tenantDbPassword,
                'description' => $tenantData['isys_mandator__description'],
            ]
        ];

        $this->output->writeln('<comment> - Write idoit.json file</comment>', OutputInterface::VERBOSITY_DEBUG);
        isys_file_put_contents($this->tempDirectory . 'idoit.json', json_encode($data));
    }

    /**
     * @throws Exception
     */
    private function createTenantDumpFile(): void
    {
        $tenantData = $this->session->get_mandator_data();

        $executable = system_which('mysqldump');

        // @see ID-12074 Escape arguments properly.
        $user = escapeshellarg($tenantData['isys_mandator__db_user']);
        $pass = escapeshellarg(\isys_component_dao_mandator::getPassword($tenantData));
        $host = escapeshellarg($tenantData['isys_mandator__db_host']);
        $dumpFilePath = escapeshellarg($this->tempDirectory . 'dumps/idoit_data.sql');
        $database = escapeshellarg($tenantData['isys_mandator__db_name']);

        if (str_contains(strtolower($this->databaseVersion), 'maria')) {
            $this->output->writeln(" > Looks like you are using <comment>MariaDB</comment>. <info>If you use MariaDB 11.0.1 or later, please go sure to make 'mariadb-dump' executable available.</info>");
            $executable = system_which('mariadb-dump', 'mysqldump');
        }

        $this->output->writeln(" > Will use <comment>{$executable}</comment> to create tenant dump... ");

        $result = exec("{$executable} --user={$user} --password={$pass} --host={$host} --result-file={$dumpFilePath} {$database} 2>&1", $execOutput);

        // Specifically check the return value, because it will contain errors - the output itself might also contain other (non-error) stuff.
        if (!empty($result) && count($execOutput)) {
            throw new Exception('- ' . implode("\n- ", $execOutput));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function copyObjectFiles(): void
    {
        $cmdbDao = \isys_application::instance()->container->get('cmdb_dao');
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

                if ($this->filesystem->exists($filePath)) {
                    $this->output->writeln("<comment> - Copy '{$row['originalFilename']}' from object #{$row['objectId']}.</comment>", OutputInterface::VERBOSITY_DEBUG);
                    $this->filesystem->copy($filePath, $this->tempDirectory . 'files/files/' . basename($filePath));
                } else {
                    $this->output->writeln("<error> > Could not find '{$row['filename']}' (from object #{$row['objectId']}) on your filesystem. Skipping.</error>");
                }
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * @return void
     */
    private function copyImportFiles(): void
    {
        $this->mirrorFiles('imports', BASE_DIR . "imports/{$this->tenantId}");
    }

    /**
     * @return void
     */
    private function copyObjectImageFiles(): void
    {
        $this->mirrorFiles('object-images', BASE_DIR . "upload/images/{$this->tenantId}/", 'object-images');
    }

    /**
     * @return void
     */
    private function copyObjectTypeImageFiles(): void
    {
        $baseDir = BASE_DIR . "upload/images/{$this->tenantId}/object-type/";

        $this->mirrorFiles('object-type-icons', $baseDir, 'icons');
        $this->mirrorFiles('object-type-images', $baseDir, 'images');
    }

    /**
     * @param string $identifier
     * @param string $baseDir
     * @param string $folder
     *
     * @return void
     */
    private function mirrorFiles(string $identifier, string $baseDir, string $folder = '')
    {
        if (!$this->filesystem->exists("{$baseDir}{$folder}") || !is_readable("{$baseDir}{$folder}")) {
            $this->output->writeln("<error>Folder '{$baseDir}{$folder}' does not exist or is not readable. Skipping.</error>", OutputInterface::VERBOSITY_DEBUG);
            return;
        }

        $this->filesystem->mirror(
            "{$baseDir}{$folder}",
            "{$this->tempDirectory}files/{$identifier}"
        );
    }

}
