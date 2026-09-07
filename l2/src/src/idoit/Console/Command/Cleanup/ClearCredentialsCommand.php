<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_contact_dao_person;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCredentialsCommand extends AbstractCommand
{
    const NAME = 'clear-credentials';

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
        return 'It removes both attributes <comment>username</comment> and <comment>password</comment> from the users "login" category';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition([
            new InputOption('object', null, InputOption::VALUE_REQUIRED, 'The object id of the user'),
            new InputOption('include-username', null, InputOption::VALUE_NONE, 'Clear the username, additionally to the password')
        ]);
    }

    /**
     * Checks if a command can have a config file via --config
     *
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
            '--object 123',
            '--object 123 --include-username',
            '--object 123,456,789 --include-username',
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
        try {
            $personObjectIds = array_filter(array_unique(explode(',', $input->getOption('object'))), fn ($id) => is_numeric($id));
            $clearUsername = $input->getOption('include-username');

            if (count($personObjectIds) === 0) {
                $output->writeln("Please provide an object ID by passing <info>--object 123</info>");

                return Command::FAILURE;
            }

            foreach ($personObjectIds as $personObjectId) {
                $personObjectId = (int)$personObjectId;

                $output->writeln("Looking for person with object ID <info>{$personObjectId}</info>");

                $personDao = isys_contact_dao_person::instance($this->container->get('database'));

                $personData = $personDao
                    ->get_data_by_id($personObjectId)
                    ->get_row();

                if (!$personData) {
                    $output->writeln('<error>Person with this object ID not found</error>');

                    continue;
                }

                $output->writeln("Person '<info>{$personData['isys_cats_person_list__title']}</info>' found.");
                $output->writeln('Clear credentials for this person.');

                if ($clearUsername) {
                    $output->writeln('Also <info>clear</info> the username.');

                    $sql = "UPDATE isys_cats_person_list
                        SET isys_cats_person_list__user_pass = NULL, isys_cats_person_list__title = NULL
                        WHERE isys_cats_person_list__isys_obj__id = {$personObjectId}
                        LIMIT 1;";
                } else {
                    $output->writeln('<info>Do not clear</info> the username.');

                    $sql = "UPDATE isys_cats_person_list
                        SET isys_cats_person_list__user_pass = NULL
                        WHERE isys_cats_person_list__isys_obj__id = {$personObjectId}
                        LIMIT 1;";
                }

                $personDao->update($sql);
                $personDao->apply_update();
            }

            $output->writeln('Clearing done.');
        } catch (\Exception $e) {
            $output->writeln('<error>There was an error while clearing credentials.</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
