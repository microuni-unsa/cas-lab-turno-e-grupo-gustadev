<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_module_system;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class for purging category entries in a certain status.
 *
 * @package idoit\Console\Command\Idoit
 */
class CategoryCleanupCommand extends AbstractCommand
{
    const NAME = 'system-categorycleanup';

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
        return 'Purges optionally category entries that are in the state unfinished, archived or deleted';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption(
            'categoryStatus',
            null,
            InputOption::VALUE_REQUIRED,
            'Use to start cleaning up the specified status:' . "\n<info>" . C__RECORD_STATUS__BIRTH . "</info> for '<comment>unfinished</comment>' category entries" .
            "\n<info>" . C__RECORD_STATUS__ARCHIVED . "</info> for '<comment>archived</comment>' category entries" . "\n<info>" . C__RECORD_STATUS__DELETED .
            "</info> for '<comment>deleted</comment>' category entries"
        ));

        return $definition;
    }

    /**
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [
            'system-categorycleanup -u admin -p admin -i 1 --categoryStatus 3'
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
        $output->writeln('Starting cleanup... ');

        $mapping = [
            C__RECORD_STATUS__BIRTH    => 'unfinished',
            C__RECORD_STATUS__ARCHIVED => 'archived',
            C__RECORD_STATUS__DELETED  => 'deleted'
        ];

        $categoryStatus = (int)$input->getOption('categoryStatus');

        if (!isset($mapping[$categoryStatus])) {
            $output->writeln('<error>Please provide a valid category status to clean up</error>');

            return Command::SUCCESS;
        }

        try {
            $count = (new isys_module_system())->cleanup_categories($categoryStatus);

            $output->writeln('Unused category entries with status <info>' . $mapping[$categoryStatus] . '</info> deleted Total of <comment>' . $count . '</comment>.');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
