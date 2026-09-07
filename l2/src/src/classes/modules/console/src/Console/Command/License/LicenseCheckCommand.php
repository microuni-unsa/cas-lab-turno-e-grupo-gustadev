<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command\License;

use Exception;
use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\NumberOption;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\License\VerifyLicense;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LicenseCheckCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('license-check')
            ->setDescription('Checks if i-doit tenant is licensed')
            ->setHelp('This command checks if the given i-doit tenant is licensed properly. Use `--consider-buffer` to also check if i-doit is in the 30 days tolerance time and needs to be relicensed soon.');

        $this->addValue(new Option(
            'system.user',
            'i-doit Admin Username',
            'admin',
            new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'i-doit Admin username')
        ));
        $this->addValue(new PasswordOption(
            'system.password',
            'i-doit Admin Password',
            null,
            new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'i-doit Admin password'),
            false
        ));
        $this->addValue(new NumberOption(
            'tenant',
            'Tenant id',
            null,
            new InputOption('tenant', 'i', InputOption::VALUE_REQUIRED, 'Tenant id')
        ));
        $this->addValue(new NumberOption(
            'consider-buffer',
            'Consider buffer',
            null,
            new InputOption('consider-buffer', null, InputOption::VALUE_NONE, 'Consider 30 day buffer')
        ));

        $this->setHidden(!defined('C__MODULE__PRO') || !C__MODULE__PRO);
        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        global $g_license_token;
        return new CollectionStep('Check tenant license', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new VerifyLicense(
                isys_application::instance()->container->get('database_system'),
                (int)$this->getValue('tenant'),
                (bool)$this->getValue('consider-buffer'),
                LicenseServiceFactory::createDefaultLicenseService(
                    isys_application::instance()->container->get('database_system'),
                    $g_license_token
                )
            ),
        ], true, true, false);
    }

    /**
     * Process the command - ask user to provide values, create step and process it
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (defined('C__MODULE__PRO') && C__MODULE__PRO) {
            return parent::execute($input, $output) === Command::SUCCESS
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $output->writeln('<error>i-doit OPEN can not work with licenses.</error>');

        return Command::FAILURE;
    }
}
