<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetTenantSettingCommand extends AbstractCommand
{
    const NAME = 'tenant-set-settings';

    public function getCommandName()
    {
        return self::NAME;
    }

    public function getCommandDescription()
    {
        return 'This command enables you to set tenant related settings by providing a settings list based on json';
    }

    public function getCommandDefinition()
    {
        return new InputDefinition([
            new InputOption('settings', 's', InputOption::VALUE_REQUIRED, 'Map of settings to be set as json like {"system.base.uri": "https://i-doit.com/", [...]}'),
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Force saving of unknown i-doit settings')
        ]);
    }

    public function isConfigurable()
    {
        return true;
    }

    public function getCommandUsages()
    {
        return [
            '-s \'{"system.base.uri": "https://i-doit.com/"}\'',
            '-s \'{"system.base.uri": "https://i-doit.com/"}\' -f',
        ];
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $settingsMap = $input->getOption('settings');
        $force = $input->getOption('force');

        if (!\isys_format_json::is_json_array($settingsMap)) {
            $output->writeln("You need to pass a valid JSON object (key: value) as <info>settings</info> option, like: <comment>-s '{\"system.base.uri\": \"https://i-doit.com/\"}'</comment>");
            return Command::FAILURE;
        }

        $map = \isys_format_json::decode($settingsMap, true);
        $definitions = \isys_tenantsettings::getRawDefinition();

        foreach ($map as $setting => $value) {
            if (!$definitions[$setting]) {
                if (!$force) {
                    $output->writeln("Tenant setting <info>{$setting}</info> does not exist and will be skipped...");
                    continue;
                }

                $output->writeln("Non-existing tenant setting <info>{$setting}</info> forcefully set to <comment>{$value}</comment>");
            } else {
                $output->writeln("Tenant setting <info>{$setting}</info> set to <comment>{$value}</comment>");
            }

            \isys_tenantsettings::set($setting, $value);
        }

        return Command::SUCCESS;
    }
}
