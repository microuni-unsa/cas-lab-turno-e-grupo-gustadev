<?php

namespace idoit\Console\Command\Notification;

use Exception;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Logger;
use idoit\Component\Settings\Environment;
use idoit\Console\Command\AbstractCommand;
use isys_notification;
use isys_notifications_dao;
use Monolog\Level;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HandleNotificationsCommand extends AbstractCommand
{
    const NAME = 'notifications-send';

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
        return 'Sends out e-mails for notifications defined in the notification add-on';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        // @see ID-8949 Add two new options.
        $definition->addOption(new InputOption('notification-ids', null, InputOption::VALUE_REQUIRED, 'Pass specific notification IDs to be sent <comment>1,2,3</comment>'));
        $definition->addOption(new InputOption('notification-type-ids', null, InputOption::VALUE_REQUIRED, 'Pass specific notification type IDs to be sent <comment>1,2,3</comment>'));

        return $definition;
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
        return [
            'php console.php notifications-send',
            'php console.php notifications-send -u<user> -p<password>',
            'php console.php notifications-send -u<user> -p<password> --notification-ids 1',
            'php console.php notifications-send -u<user> -p<password> --notification-ids 1,2,3',
            'php console.php notifications-send -u<user> -p<password> --notification-type-ids 4',
            'php console.php notifications-send -u<user> -p<password> --notification-type-ids 4,5,6',
        ];
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

        switch ($output->getVerbosity()) {
            default:
            case OutputInterface::VERBOSITY_NORMAL:
                $debugLevel = Level::Warning->value;
                break;

            case OutputInterface::VERBOSITY_VERBOSE:
                $debugLevel = Level::Notice->value;
                break;

            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $debugLevel = Level::Info->value;
                break;

            case OutputInterface::VERBOSITY_DEBUG:
                $debugLevel = Level::Debug->value;
                break;
        }

        // Start logging:
        $logger = Logger::factory(
            'notifications',
            BASE_DIR . '/log/notifications.log',
            $debugLevel
        );

        $output->writeln('Begin to notify...');

        $idoitUri = Environment::getBaseUri();
        if (!$idoitUri) {
            $output->writeln('<error>Tenant Setting "i-doit address (URL)" not set!</error>');
        }

        // @see ID-8949 Get allowed notifications and notification types.
        $notificationIds = array_filter(array_map('intval', explode(',', $input->getOption('notification-ids'))));
        $notificationTypeIds = array_filter(array_map('intval', explode(',', $input->getOption('notification-type-ids'))));

        // Get database component:
        $database = $this->container->get('database');
        $language = $this->container->get('language');

        try {
            $output->writeln('Iterating through each notification type...');

            $l_dao = new isys_notifications_dao($database, $logger);

            // Fetch all notification types:
            $types = $l_dao->get_type();

            // @see ID-8949 Get allowed types to only iterate them.
            $allowedTypes = [];
            if (count($notificationIds)) {
                $notifications = $l_dao->get_notifications(null, ['id' => $notificationIds]);

                foreach ($notifications as $notification) {
                    $allowedTypes[] = $notification['type'];
                }
            }

            // Iterate through each notification type:
            foreach ($types as $type) {
                $notificationTypeId = $type['id'];
                $notificationTypeTitle = $language->get($type['title']);

                if (count($allowedTypes) && !in_array($notificationTypeId, $allowedTypes)) {
                    continue;
                }

                // @see ID-8949 Skip notification types, that where not specifically passed.
                if (count($notificationTypeIds) && !in_array($type['id'], $notificationTypeIds)) {
                    $output->writeln("Skip notification type #{$notificationTypeId} '<comment>{$notificationTypeTitle}</comment>'.", OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }

                $output->writeln("Handling notification type #{$notificationTypeId} '<comment>{$notificationTypeTitle}</comment>'.");
                $logger->info("Handling notification type #{$notificationTypeId} '{$notificationTypeTitle}'.");

                /**
                 * Use callback to notify:
                 *
                 * @var $l_callback isys_notification
                 */
                $l_callback = new $type['callback']($l_dao, $database, $logger, $output);

                // @see ID-8949 Set allowed notifications.
                if (count($notificationIds)) {
                    $l_callback->setAllowedNotifications($notificationIds);
                }

                try {
                    $l_callback->set_channels(isys_notification::C__CHANNEL__EMAIL);
                    $l_callback->init($type);
                    $l_callback->notify();
                } catch (Exception $e) {
                    $output->writeln([
                        '  Skipping the notification type - there was an error:',
                        '  <error>' . $e->getMessage() . '</error>'
                    ]);
                }

                unset($l_callback);
            }

            $l_dao->apply_update();

            $output->writeln('Everything done.');
            return Command::SUCCESS;
        } catch (PHPMailerException $e) {
            $output->writeln('<error>There was a problem while sending notification emails!</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } //try/catch
    }
}
