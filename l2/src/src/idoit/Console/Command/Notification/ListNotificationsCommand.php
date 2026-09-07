<?php

namespace idoit\Console\Command\Notification;

use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Logger;
use idoit\Console\Command\AbstractCommand;
use isys_notifications_dao;
use Monolog\Handler\NullHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListNotificationsCommand extends AbstractCommand
{
    const NAME = 'notifications-list';

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
        return 'Lists all notification types and notifications for later usage';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition();
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

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        if (!FeatureManager::isFeatureActive('notification-command')) {
            $this->setHidden(true);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!FeatureManager::isFeatureActive('notification-command')) {
            $output->writeln('Command disabled, please check the feature management.');
            return Command::FAILURE;
        }

        $database = $this->container->get('database');
        $language = $this->container->get('language');

        try {
            $logger = new Logger('list-notifications', [new NullHandler()]);

            $l_dao = new isys_notifications_dao($database, $logger);

            $types = $l_dao->get_type();
            $output->writeln(['', 'List of <info>notification types</info>:']);

            foreach ($types as $type) {
                $id = str_pad('#' . $type['id'], 6, ' ', STR_PAD_LEFT);
                $title = $language->get($type['title']);

                $output->writeln("{$id} '<comment>{$title}</comment>'");
            }

            $notifications = $l_dao->get_notifications();
            $output->writeln(['', 'List of <info>notifications</info>:']);

            foreach ($notifications as $notification) {
                $id = str_pad('#' . $notification['id'], 6, ' ', STR_PAD_LEFT);
                $title = $language->get($notification['title']);
                $type = $language->get($types[$notification['type']]['title']);

                $output->writeln("{$id} '<comment>{$title}</comment>' (type '<comment>{$type}</comment>' #{$notification['type']})");
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
