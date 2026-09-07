<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use idoit\Module\License\LicenseServiceFactory;
use isys_application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddonLicenseCheckCommand extends AbstractCommand
{
    const NAME = 'addon-license-check';
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
        return 'This command checks if a specific add-on is licensed.';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('addon', 'a', InputOption::VALUE_REQUIRED, 'Add-on to be checked'));

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
     * @return array|string[]
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon = $input->getOption('addon');

        if (empty($addon)) {
            $output->writeln("<comment>Please define add-on by specifying '-a' or '--addon'.</comment>");
            return Command::FAILURE;
        }

        global $g_license_token;

        $licenseService = LicenseServiceFactory::createDefaultLicenseService(
            isys_application::instance()->container->get('database_system'),
            $g_license_token
        );

        $addonList = $licenseService->getLicensedAddOns();
        if (isset($addonList[$addon]) && $addonList[$addon]['licensed']) {
            $output->writeln("<info>Add-on '{$addon}' is licensed.</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<error>Add-on '{$addon}' is not licensed.</error>");
        return Command::FAILURE;
    }
}
