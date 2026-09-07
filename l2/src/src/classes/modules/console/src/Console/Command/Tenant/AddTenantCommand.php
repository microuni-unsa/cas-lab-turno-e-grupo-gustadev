<?php

namespace idoit\Module\Console\Console\Command\Tenant;

use idoit\Component\Settings\Environment;
use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\Addon\BundleInstall;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\ConstantStep;
use idoit\Module\Console\Steps\Dao\TenantAdd;
use idoit\Module\Console\Steps\Dao\TenantExist;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Sql\CreateDatabase;
use idoit\Module\Console\Steps\Sql\CreateDatabaseUser;
use idoit\Module\Console\Steps\Sql\DatabaseExist;
use idoit\Module\Console\Steps\Sql\GrantUserOnDatabase;
use idoit\Module\Console\Steps\Sql\ImportDatabaseFromDump;
use idoit\Module\Console\Steps\Sql\InsertPersons;
use idoit\Module\Console\Steps\Sql\UserCredentialsCorrect;
use idoit\Module\Console\Steps\Sql\UserExist;
use idoit\Module\License\LicenseServiceFactory;
use idoit\Module\Manager\Addon\BundleInstaller;
use isys_application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddTenantCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();
        global $g_absdir;
        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('tenant-create')
            ->setDescription('Create tenant in i-doit')
            ->setHelp(
                <<<TEXT
This command creates the i-doit tenant with given options
TEXT
            );
        // DB connection
        $this->addValue(new Option(
            'db.root.user',
            'Username of privileged DB User',
            'root',
            new InputOption('root-user', 'u', InputOption::VALUE_REQUIRED, 'Username of privileged DB User')
        ));
        $this->addValue(new PasswordOption(
            'db.root.password',
            'Password of privileged DB User',
            null,
            new InputOption('root-password', 'p', InputOption::VALUE_OPTIONAL, 'Password of privileged DB User'),
            false
        ));

        $this->addValue(new Option(
            'db.tenant.user',
            'Username of DB for new tenant',
            'idoit',
            new InputOption('user', 'U', InputOption::VALUE_REQUIRED, 'Username of DB for new tenant')
        ));
        $this->addValue(new PasswordOption(
            'db.tenant.password',
            'Password of DB for new tenant',
            null,
            new InputOption('password', 'P', InputOption::VALUE_OPTIONAL, 'Password of DB for new tenant'),
            false
        ));
        $this->addValue(new Option(
            'db.tenant.database',
            'DB name for new tenant',
            'idoit_data',
            new InputOption('database', 'd', InputOption::VALUE_REQUIRED, 'DB name for new tenant')
        ));
        // Tenant properties
        $this->addValue(new Option(
            'tenant.name',
            'Name of the new tenant',
            'Your company name',
            new InputOption('title', 't', InputOption::VALUE_REQUIRED, 'Name of the new tenant')
        ));

        // Addon bundle
        if (class_exists(BundleInstaller::class) && BundleInstaller::hasBundle() && ($addons = BundleInstaller::readAddonBundle())) {
            $this->addValue(new Option(
                'tenant.addons',
                'Addons for this tenant',
                implode(', ', $addons),
                new InputOption('addons', 'r', InputOption::VALUE_OPTIONAL, 'Addons for this tenant')
            ));
        }

        // @see ID-10785 Implement additional login user and password.
        $this->addValue(new Option(
            'db.tenant.login.user',
            'Username of i-doit user',
            null,
            new InputOption('login-user', null, InputOption::VALUE_OPTIONAL, 'Username of i-doit user')
        ));
        $this->addValue(new PasswordOption(
            'db.tenant.login.password',
            'Password of the i-doit user',
            null,
            new InputOption('login-password', null, InputOption::VALUE_OPTIONAL, 'Password of the i-doit user'),
            false
        ));

        $this->addValue(new Option(
            'db.tenant.login.recovery-email',
            'Recovery e-mail of the i-doit user',
            null,
            new InputOption('login-recovery-email', null, InputOption::VALUE_OPTIONAL, 'Recovery e-mail of the i-doit user'),
            false
        ));
        $this->addValue(new Option(
            'db.tenant.login.send-email',
            'Send e-mail for initial password definition',
            null,
            new InputOption('send-email', null, InputOption::VALUE_NONE, 'Send e-mail for initial password definition'),
            false
        ));

        $this->setHidden(!defined('C__MODULE__PRO') || !C__MODULE__PRO);

        // Configuration options
        $this->addValue(new Option('db.tenant.dump', 'I-doit Tenant Dump path', $g_absdir . '/setup/sql/idoit_data.sql'));
        $this->addValue(new Option('db.host', 'Hostname for DB connection', $g_db_system['host']));
        $this->addValue(new Option('db.port', 'Port for DB connection', $g_db_system['port']));
        $this->addValue(new Option('db.system.user', 'Username of system DB User', $g_db_system['user']));
        $this->addValue(new Option('db.system.password', 'Password of system DB User', $g_db_system['pass']));
        $this->addValue(new Option('db.system.database', 'i-doit System Database name', $g_db_system['name']));
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!defined('C__MODULE__PRO') || !C__MODULE__PRO) {
            $dao = \isys_component_dao_mandator::instance(isys_application::instance()->container->get('database_system'));
            if ($dao->get_mandator()->count() >= 1) {
                $output->writeln('<error>i-doit open does not include multi tenancy feature and supports only a single tenant.</error>');

                return self::FAILURE;
            }
        }

        $recoveryEmail = $input->getOption('login-recovery-email');
        $isRecoveryMailValid = $recoveryEmail ? filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL) !== false : true;
        $sendPasswordEmail = $input->getOption('send-email');
        $isSmtpConfigured = \isys_settings::get('system.email.smtp-host');
        if ($recoveryEmail && !$isRecoveryMailValid) {
            throw new \Exception('Option --login-recovery-email is invalid.');
        }

        if ($sendPasswordEmail && (!$recoveryEmail || !$isRecoveryMailValid)) {
            throw new \Exception('Option --login-recovery-email is not set or invalid.');
        }

        if ($sendPasswordEmail && !$isSmtpConfigured) {
            throw new \Exception('System-level configuration for smtp is missing. Please remove option --send-email or configure smtp:

./console.php system-set-settings -u {ADMIN_USER} -p {ADMIN_PASSWORD} -s "{ \
    \"system.email.smtp-host\": \"smtp.myhost.com\", \
    \"system.email.port\": \"25\", \
    \"system.email.smtp-auto-tls\": \"0\", \
    \"system.email.username\": \"smtp-user\", \
    \"system.email.password\": \"smtp-password\" \
}" -f');
        }

        if ($sendPasswordEmail && !Environment::get('IDOIT_APP_URL')) {
            throw new \Exception('URL for link generation is missing. Please remove option --send-email or configure URL:

./console.php idoit:set-env-var \
            -u {ADMIN_USER} -p {ADMIN_PASSWORD} \
            -k "IDOIT_APP_URL=https://url-to-idoit.com"
            ');
        }

        return parent::execute($input, $output);
    }

    protected function createStep()
    {
        global $g_license_token;

        $licenseService = null;

        if (class_exists(LicenseServiceFactory::class)) {
            $licenseService = LicenseServiceFactory::createDefaultLicenseService(
                isys_application::instance()->container->get('database_system'),
                $g_license_token
            );
        }

        $steps = [
            new CollectionStep('DB', [
                new IfCheck(
                    'User exists',
                    new UserExist(
                        $this->getValue('db.host'),
                        $this->getValue('db.root.user'),
                        $this->getValue('db.root.password'),
                        $this->getValue('db.port'),
                        $this->getValue('db.tenant.user'),
                    ),
                    new UserCredentialsCorrect(
                        $this->getValue('db.host'),
                        $this->getValue('db.tenant.user'),
                        $this->getValue('db.tenant.password'),
                        '',
                        $this->getValue('db.port')
                    ),
                    new CreateDatabaseUser(
                        $this->getValue('db.tenant.user'),
                        $this->getValue('db.tenant.password'),
                        $this->getValue('db.host'),
                        $this->getValue('db.root.user'),
                        $this->getValue('db.root.password'),
                        $this->getValue('db.port')
                    ),
                ),
                new IfCheck(
                    'DB Exist',
                    new DatabaseExist(
                        $this->getValue('db.host'),
                        $this->getValue('db.root.user'),
                        $this->getValue('db.root.password'),
                        $this->getValue('db.tenant.database'),
                        $this->getValue('db.port')
                    ),
                    null,
                    new CollectionStep('Create DB', [
                        new CreateDatabase(
                            $this->getValue('db.host'),
                            $this->getValue('db.root.user'),
                            $this->getValue('db.root.password'),
                            $this->getValue('db.tenant.database'),
                            $this->getValue('db.port')
                        ),
                        new ImportDatabaseFromDump(
                            $this->getValue('db.tenant.dump'),
                            $this->getValue('db.host'),
                            $this->getValue('db.root.user'),
                            $this->getValue('db.root.password'),
                            $this->getValue('db.tenant.database'),
                            $this->getValue('db.port')
                        )
                    ])
                ),
                new GrantUserOnDatabase(
                    $this->getValue('db.system.user'),
                    $this->getValue('db.system.password'),
                    $this->getValue('db.tenant.database'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
                new GrantUserOnDatabase(
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.tenant.database'),
                    $this->getValue('db.host'),
                    $this->getValue('db.root.user'),
                    $this->getValue('db.root.password'),
                    $this->getValue('db.port')
                ),
            ]),
            new IfCheck(
                'Create Tenant',
                new TenantExist(
                    isys_application::instance()->container->get('database_system'),
                    $this->getValue('tenant.name'),
                    $this->getValue('db.tenant.database')
                ),
                new ConstantStep('Tenant already exists', false),
                new TenantAdd(
                    isys_application::instance()->container->get('database_system'),
                    $licenseService,
                    $this->getValue('tenant.name'),
                    $this->getValue('tenant.description'),
                    $this->getValue('db.host'),
                    $this->getValue('db.port'),
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.tenant.database')
                )
            ),
            new CollectionStep('Adding persons', [
                new InsertPersons(
                    $this->getValue('db.host'),
                    $this->getValue('db.tenant.user'),
                    $this->getValue('db.tenant.password'),
                    $this->getValue('db.tenant.database'),
                    $this->getValue('db.port'),
                    $this->getValue('db.tenant.login.user'),
                    $this->getValue('db.tenant.login.password'),
                    $this->getValue('db.tenant.login.recovery-email'),
                    $this->getValue('db.tenant.login.send-email'),
                )
            ])
        ];

        if (class_exists(BundleInstaller::class) && BundleInstaller::hasBundle()) {
            $steps[] = new CollectionStep(
                'Install addons',
                [
                    new BundleInstall(
                        isys_application::instance()->container->get('database_system'),
                        $this->getValue('tenant.addons'),
                        $this->getValue('db.tenant.database')
                    )
                ]
            );
        }

        return new CollectionStep('Create Tenant', $steps);
    }
}
