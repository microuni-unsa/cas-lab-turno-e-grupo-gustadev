<?php

namespace idoit\Module\Console\Console\Command\idoit;

use idoit\Module\Console\Console\Command\AbstractConfigurableCommand;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Option\PasswordOption;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\SetSettingsStep;
use idoit\Module\Console\Steps\Step;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetSettingCommand extends AbstractConfigurableCommand
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        global $g_db_system;

        if (!is_array($g_db_system)) {
            die("config.inc.php is not loaded! Please, install the i-doit first!\n");
        }

        $this->setName('system-set-settings')
            ->setDescription('Set system settings')
            ->setHelp('This command enables you to set system related settings by providing a settings list based on json');

        $this->addValue(new Option('system.user', 'i-doit Admin Username', 'admin', new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'i-doit Admin username')));
        $this->addValue(new PasswordOption(
            'system.password',
            'i-doit Admin Password',
            null,
            new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'i-doit Admin password'),
            false
        ));
        $this->addValue(new Option('setting.map', 'Map of settings to be set as json like {"proxy.active": 1, [...]}', null, new InputOption('settings', 's', InputOption::VALUE_REQUIRED, 'Map of settings to be set as json like {"proxy.active": 1, [...]}')));
        $this->addValue(new Option('setting.force', 'Force saving of unknown i-doit settings', null, new InputOption('force', 'f', InputOption::VALUE_NONE, 'Force saving of unknown i-doit settings')));

        parent::configure();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        return new CollectionStep('Set system settings', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new SetSettingsStep($this->getValue('setting.map'), (bool)$this->getValue('setting.force'))
        ]);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = parent::execute($input, $output);

        return $result;
    }
}
