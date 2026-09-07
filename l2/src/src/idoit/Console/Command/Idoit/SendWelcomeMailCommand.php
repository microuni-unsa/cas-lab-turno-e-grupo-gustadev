<?php

namespace idoit\Console\Command\Idoit;

use idoit\Component\PasswordReset\PasswordResetService;
use idoit\Component\PasswordReset\UserInfo;
use idoit\Component\Settings\Environment;
use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendWelcomeMailCommand extends AbstractCommand
{
    const NAME = 'send-welcome-mail';

    public function getCommandName()
    {
        return self::NAME;
    }

    public function getCommandDescription()
    {
        return 'This command sends welcome e-mail with all required information to enable user to access his instance and set his initial password.';
    }

    public function getCommandDefinition()
    {
        return new InputDefinition([
            new InputOption('subject', 's', InputOption::VALUE_OPTIONAL, 'Subject of welcome mail to be sent', PasswordResetService::SUBJECT_INITIAL_PASSWORD),
            new InputOption('ttl', 'r', InputOption::VALUE_OPTIONAL, 'TTL of password recovery link', PasswordResetService::TOKEN_INITIAL_TTL),
            new InputOption('email', 'e', InputOption::VALUE_REQUIRED, 'Recovery e-mail of user documented in login category'),
        ]);
    }

    public function isConfigurable()
    {
        return false;
    }

    public function getCommandUsages()
    {
        return [
            '-s "Set your new password for your i-doit instance" -e "mail@i-doit.com" -r "48 hours"',
            '--subject "Set your new password for your i-doit instance" --email "mail@i-doit.com" --ttl "48 hours"',
            '--email "mail@i-doit.com"',
        ];
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \isys_exception_dao
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $subject = $input->getOption('subject');
            $recoveryEmail = $input->getOption('email');
            $tenantId = $input->getOption('tenantId');
            $ttl = $input->getOption('ttl');
            $isRecoveryMailValid = $recoveryEmail ? filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL) !== false : true;
            $isSmtpConfigured = \isys_settings::get('system.email.smtp-host');

            if (!is_string($subject)) {
                throw new \Exception('Option --subject has to be a string.');
            }

            if (!is_string($ttl)) {
                throw new \Exception('Option --ttl has to be time string link \'48 hours\'.');
            }

            if (!$recoveryEmail || !$isRecoveryMailValid) {
                throw new \Exception('Option --email is not set or invalid.');
            }

            if (!$isSmtpConfigured) {
                throw new \Exception('System-level configuration for smtp is missing. Please configure smtp by executing:

./console.php system-set-settings -u {ADMIN_USER} -p {ADMIN_PASSWORD} -s "{ \
    \"system.email.smtp-host\": \"smtp.myhost.com\", \
    \"system.email.port\": \"25\", \
    \"system.email.smtp-auto-tls\": \"0\", \
    \"system.email.username\": \"smtp-user\", \
    \"system.email.password\": \"smtp-password\" \
}" -f');
            }

            if (!Environment::get('IDOIT_APP_URL')) {
                throw new \Exception('URL for link generation is missing. Please configure URL to continue:

./console.php idoit:set-env-var \
            -u {ADMIN_USER} -p {ADMIN_PASSWORD} \
            -k "IDOIT_APP_URL=https://url-to-idoit.com"
            ');
            }

            /** @var PasswordResetService $passwordResetService */
            $passwordResetService = \isys_application::instance()->container->get('reset_password');
            $db = \isys_application::instance()->container->get('database');
            $dbSystem = \isys_application::instance()->container->get('database_system');

            // Get user information
            $user = $db->query("SELECT * FROM isys_cats_person_list
                        WHERE isys_cats_person_list__password_reset_email = '{$db->escape_string($recoveryEmail)}'")
                ->fetch_assoc();

            // Check if user exists
            if ($user === null) {
                throw new \Exception("User with recovery mail '{$recoveryEmail}' not found.");
            }

            // Get tenant information
            $tenant = $dbSystem->query("SELECT isys_mandator__id, isys_mandator__title FROM isys_mandator WHERE isys_mandator__id = '{$dbSystem->escape_string($tenantId)}'")
                ->fetch_assoc();

            // Check if tenant exists
            if ($tenant === null) {
                throw new \Exception("Unable to find tenant with id '{$tenantId}'.");
            }

            // Define user information
            $userInfo = new UserInfo(
                $user['isys_cats_person_list__isys_obj__id'],
                $user['isys_cats_person_list__title'],
                $tenant['isys_mandator__id'],
                $tenant['isys_mandator__title']
            );

            // Send e-mail
            $passwordResetService->requestInitialPasswordSet($userInfo, $recoveryEmail, $ttl, $subject);
            $io->success("Welcome e-mail successfully sent to user with recovery e-mail '{$recoveryEmail}'.");

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
    }
}
