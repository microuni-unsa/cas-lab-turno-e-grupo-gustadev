<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command\License;

use Exception;
use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\License\InstallWebLicense;
use idoit\Module\Console\Steps\Step;
use isys_application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebLicenseImportCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        global $g_absdir;
        global $g_db_system;
        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('license-import')
            ->setDescription('Import licenses from the i-doit server')
            ->setHelp(
                <<<TEXT
This command imports license keys from the i-doit license server
TEXT
            );

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
        $this->addValue(new Option(
            'license.server',
            'Path for the i-doit license server',
            'https://lizenzen.i-doit.com',
            new InputOption('license-server', 'l', InputOption::VALUE_REQUIRED, 'Path for the i-doit license server')
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
        $db = isys_application::instance()->container->get('database_system');
        global $g_license_token;
        $steps = [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new InstallWebLicense(
                $db->get_host(),
                $db->get_user(),
                $db->get_pass(),
                $db->get_db_name(),
                $db->get_port(),
                $this->getValue('license.server'),
                $g_license_token
            ),
        ];
        return new CollectionStep('Imports licenses from the i-doit server', $steps);
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
            return parent::execute($input, $output);
        }

        $output->writeln('<error>i-doit OPEN can not work with licenses.</error>');

        return Command::FAILURE;
    }
}
