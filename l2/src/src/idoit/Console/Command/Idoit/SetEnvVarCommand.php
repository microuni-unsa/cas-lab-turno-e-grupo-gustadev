<?php

namespace idoit\Console\Command\Idoit;

use idoit\Component\Settings\Environment;
use idoit\Console\Command\AbstractCommand;
use isys_cache;
use isys_component_signalcollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetEnvVarCommand extends AbstractCommand
{
    const NAME = 'idoit:set-env-var';
    const AUTH_DOMAIN = self::AUTH_DOMAIN_SYSTEM;

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
        return 'With this command it will be possible to set environmental variables for i-doit.';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('key-pair', 'k', InputOption::VALUE_REQUIRED, 'Comma separated list of key pairs which will be included as environmental variable.'));
        $definition->addOption(new InputOption('show-variables', 's', InputOption::VALUE_NONE, 'Show all defined environmental variables.'));
        $definition->addOption(new InputOption('list-variables', 'l', InputOption::VALUE_NONE, 'Show all possible environmental variables.'));

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
        return [
            '-k "IDOIT_APP_URL=localhost" or --key-pairs="IDOIT_APP_URL=localhost"',
            '-s or --show-variables',
            '-l or --list-variables'
        ];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keyPair = $input->getOption('key-pair');
        $show = $input->getOption('show-variables');
        $list = $input->getOption('list-variables');
        $envFile = \isys_application::instance()->app_path . '/src/.env';
        $allowedVariables = Environment::getAllowedVariables();

        if ($list) {
            foreach ($allowedVariables as $key => $description) {
                $output->writeln("<info>{$key}</info> => <comment>{$description}</comment>");
            }

            return Command::SUCCESS;
        }

        if ($show) {
            if (file_exists($envFile)) {
                $data = file_get_contents($envFile);
                $output->writeln('<info>'.$data.'</info>');
                return Command::SUCCESS;
            }

            $output->writeln('<comment>No environment file found.</comment>');
            return Command::FAILURE;
        }

        if (!is_writable(dirname($envFile))) {
            $output->writeln('<error>Folder ' . dirname($envFile) . ' is not writeable.</error>');
            return Command::FAILURE;
        }

        if (!file_exists($envFile)) {
            try {
                touch($envFile);
            } catch (\Throwable $e) {
                $output->writeln('<error>Missing right to create files in ' . dirname($envFile) . '.</error>');
                return Command::FAILURE;
            }
        }

        if (!$this->validate($keyPair, $output)) {
            return Command::FAILURE;
        }

        $keyPair = str_replace(',', "\n", $keyPair);

        file_put_contents($envFile, $keyPair);
        $this->clear_cache();

        return Command::SUCCESS;
    }

    private function clear_cache()
    {
        global $g_dirs;

        // Removing isys_cache values
        isys_cache::keyvalue()->flush();

        $l_deleted = $l_undeleted = 0;

        // Deleting contents of the temp directory.
        isys_glob_delete_recursive($g_dirs["temp"], $l_deleted, $l_undeleted, (ENVIRONMENT === 'development'));

        isys_component_signalcollection::get_instance()->emit('system.afterFlushSystemCache');
    }

    /**
     * @param string $keyPairs
     *
     * @return bool
     */
    private function validate(string $keyPairs, OutputInterface $output): bool
    {
        $keyPairs = explode(',', $keyPairs);
        $allowedVariables = Environment::getAllowedVariables();
        $possibleKeys = array_keys($allowedVariables);

        foreach ($keyPairs as $value) {
            [$key, ] = explode('=', $value);
            if (!in_array($key, $possibleKeys)) {
                $output->writeln("<comment>The key <error>{$key}</error> is not allowed.</comment>");
                return false;
            }
        }

        return true;
    }
}
