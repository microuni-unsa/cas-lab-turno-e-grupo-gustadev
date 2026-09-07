<?php

namespace idoit\Console\Command\Logbook;

use Exception;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Console\Command\AbstractCommand;
use isys_component_dao_archive;
use isys_component_dao_logbook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveCommand extends AbstractCommand
{
    const NAME = 'logbook:archive';

    public function getCommandAliases()
    {
        return [
            'logbook-archive'
        ];
    }

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Archive Logbook entries that precede a certain interval (settings are defined in the GUI)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition([
            new InputOption('batch', null, InputOption::VALUE_REQUIRED, 'Configure the batch size', 1000),
        ]);
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    public function isHidden(): bool
    {
        return !FeatureManager::isFeatureActive('logbook-archive-ui');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $g_db_system;

        if (!FeatureManager::isFeatureActive('logbook-archive-ui')) {
            $output->writeln('Command disabled, please check the feature management.');
            return Command::FAILURE;
        }

        $batch = (int)$input->getOption('batch');

        // Get daos, because now we are logged in.
        $daoLogbook = new isys_component_dao_logbook($this->container->get('database'));

        $output->writeln([
            'Logbook archiving-handler initialized - <comment>' . date("Y-m-d H:i:s") . '</comment>',
            ''
        ]);

        // Check status and add to logbook.
        try {
            $settings = $daoLogbook->getArchivingSettings();

            if ($settings['dest'] == 0) {
                $database = $this->container->get('database');
                $output->writeln('<info>Using local database</info>');
            } else {
                try {
                    $output->writeln('Using remote database <info>' . $settings["host"] . '</info>');

                    $database = \isys_component_database::get_database(
                        $g_db_system["type"],
                        $settings["host"],
                        $settings["port"],
                        $settings["user"],
                        $settings["pass"],
                        $settings["db"]
                    );
                } catch (\Exception $e) {
                    throw new \Exception('Logbook archiving: Failed to connect to ' . $settings["host"]);
                }
            }

            (new isys_component_dao_archive($database))
                ->setOutput($output)
                ->archive($daoLogbook, (int)($settings['interval'] ?? 90), $batch);

            $output->writeln([
                '<comment>Memory peak: ' . (memory_get_peak_usage(true) / 1024 / 1024) . ' MB</comment>',
                '',
                'Finished the process - <comment>' . date("Y-m-d H:i:s") . '</comment>'
            ]);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
