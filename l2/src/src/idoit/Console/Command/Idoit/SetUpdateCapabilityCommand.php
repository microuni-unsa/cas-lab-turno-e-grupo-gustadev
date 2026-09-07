<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use isys_update_config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DialogStatusCommand
 *
 * @package idoit\Console\Command\Idoit
 * @see     ID-9404
 */
class SetUpdateCapabilityCommand extends AbstractCommand
{
    const NAME = 'idoit:set-update-capability';

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
        return 'Dis- and enable the i-doit update capability';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('status', InputArgument::REQUIRED, 'Either <comment>enable</comment> or <comment>disable</comment> the update capability'));

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
            'enable -u<user> -p<password>',
            'disable -u<user> -p<password>',
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
        global $g_enable_gui_update;

        $status = ($input->getArgument('status'));

        if (!in_array($status, ['enable', 'disable'], true)) {
            $output->writeln("<error>You need to provide either 'enable' or 'disable' as option! You provided '{$status}'.</error>");

            return Command::FAILURE;
        }

        $enableGuiUpdate = $status === 'enable' ? 1 : 0;

        // We do not need to proceed, if the value is already set.
        if (isset($g_enable_gui_update) && $g_enable_gui_update == $enableGuiUpdate) {
            $output->writeln('The GUI update status was already set, no need to update.');

            return Command::SUCCESS;
        }

        $output->writeln("The GUI update will be set to <info>{$status}</info> (<comment>{$enableGuiUpdate}</comment>)");

        $configDir = BASE_DIR . 'src';
        $updater = new isys_update_config();

        $output->writeln('Backup the current <info>config.inc.php</info>.');

        // Creating config backup file.
        $backupPath = $updater->backup($configDir);

        if ($backupPath === false) {
            $output->writeln('<error>The backup failed - aborting!</error>');

            return Command::FAILURE;
        }

        $backupPath = str_replace('\\', '/', $backupPath);

        $output->writeln("Backup completed <comment>{$backupPath}</comment>!");
        $output->writeln('Create new <info>config.inc.php</info>...');

        // Write new config file.
        $config = $updater->parseAndUpdateConfig(BASE_DIR . 'setup', ['config.admin.enable_gui_update' => $enableGuiUpdate]);
        $updater->write('<' . substr($config, 1), $configDir);

        $output->writeln('All done <comment>:)</comment>');

        return Command::SUCCESS;
    }
}
