<?php

namespace idoit\Console\Command\Idoit;

use Exception;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class FeatureManagerCommand extends AbstractCommand
{
    const NAME = 'idoit:feature-manager';
    const AUTH_DOMAIN = self::AUTH_DOMAIN_SYSTEM;

    /**
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * @return string[]
     */
    public function getCommandAliases()
    {
        return ['idoit:fm'];
    }

    /**
     * @return string
     */
    public function getCommandDescription()
    {
        return 'With this command it will be possible to activate a set of features in i-doit.';
    }

    /**
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('unset-cloud', null, InputOption::VALUE_NONE, 'Deactivate if application is cloud instance.'));
        $definition->addOption(new InputOption('set-cloud', null, InputOption::VALUE_NONE, 'Activate if application is cloud instance.'));
        $definition->addOption(new InputOption('enable', 'e', InputOption::VALUE_REQUIRED, 'Enable features.'));
        $definition->addOption(new InputOption('disable', 'd', InputOption::VALUE_REQUIRED, 'Disable features.'));
        $definition->addOption(new InputOption('replace', 'r', InputOption::VALUE_REQUIRED, 'Replace current features.'));
        $definition->addOption(new InputOption('cloudable', 'c', InputOption::VALUE_NONE, 'Only cloudable features will be considered.'));
        $definition->addOption(new InputOption('noncloudable', 'f', InputOption::VALUE_NONE, 'Only non-cloudable features will be considered.'));
        $definition->addOption(new InputOption('wizard', null, InputOption::VALUE_NONE, 'Interactive mode to activate or deactivate features.'));
        $definition->addOption(new InputOption('list', 'l', InputOption::VALUE_NONE, 'Shows all available features.'));

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
            '--unset-cloud',
            '--set-cloud',
            '-e "archive-logbook-command,restore-logbook-ui,jdisc,ldap" or --enable="archive-logbook-command,restore-logbook-ui,jdisc,ldap"',
            '-d "archive-logbook-command,restore-logbook-ui,jdisc,ldap" or --disable="archive-logbook-command,restore-logbook-ui,jdisc,ldap"',
            '-r "archive-logbook-command,restore-logbook-ui,jdisc,ldap" or --disable="archive-logbook-command,restore-logbook-ui,jdisc,ldap"',
            '-c or --cloudable',
            '-f or --noncloudable',
            '--wizard (interactive mode)'
        ];
    }

    /**
     * @param FeatureManager  $featureManager
     * @param OutputInterface $output
     *
     * @return void
     */
    private function showFeatureList(FeatureManager $featureManager, OutputInterface $output): void
    {
        $featureList = $featureManager->getFeatures();

        foreach ($featureList as $feature) {
            $name = $feature->getName();
            $description = $feature->getDescription();

            $output->writeln("Id: <info>{$name}</info>, Description: <comment>{$description}</comment>");
        }
    }

    /**
     * @param FeatureManager  $featureManager
     * @param bool|null       $cloudable
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function processWizard(FeatureManager $featureManager, ?bool $cloudable, InputInterface $input, OutputInterface $output): bool
    {
        $features = $featureManager->getFeatures();
        $helper = $this->getHelper('question');
        $featureAnswers = [];

        foreach ($features as $feature) {
            $name = $feature->getName();
            $description = $feature->getDescription();

            if ($cloudable !== null && $feature->isCloudable() !== $cloudable) {
                continue;
            }

            $question = new ChoiceQuestion("<question>Do you want to activate {$name}? {$description}</question>", ['disable', 'activate']);
            $answer = $helper->ask($input, $output, $question);
            $featureAnswers[$name] = $answer === 'disable' ? false : true;
        }

        $newFeatureSet = array_filter($featureAnswers, fn ($item) => $item);

        if (empty($newFeatureSet)) {
            $message = "No features have been found";

            if ($cloudable !== null && $cloudable) {
                $message = "No features have been found which are usable for the cloud.";
            }

            if ($cloudable !== null && !$cloudable) {
                $message = "No features have been found which are non cloud able.";
            }

            $output->writeln($message);
        }

        return $featureManager->replaceFeatures(array_keys($newFeatureSet));
    }

    /**
     * @return void
     * @throws Exception
     */
    private function checkConfigurationFiles(): void
    {
        global $g_absdir;

        $templateFile = '/setup/config_template.inc.php';
        $configFile = '/src/config.inc.php';

        if (!file_exists($g_absdir . $templateFile)) {
            throw new Exception('Config template ' . $templateFile . ' does not exist.');
        }

        if (!is_writable(dirname($g_absdir . $configFile))) {
            throw new Exception('Folder ' . dirname($configFile) . ' is not writeable.');
        }

        if (file_exists($g_absdir . $configFile) && !is_writable($g_absdir . $configFile)) {
            throw new Exception('Config file ' . dirname($configFile) . ' is not writeable.');
        }
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
        if (!\isys_application::instance()->container->has('idoit.component.feature-manager')) {
            // Service does not exist so we just give feedback that it was successfull
            return Command::SUCCESS;
        }

        try {
            $this->checkConfigurationFiles();

            $featureManager = \isys_application::instance()->container->get('idoit.component.feature-manager');

            $enableFeatures = $input->getOption('enable') ?? null;
            $disableFeatures = $input->getOption('disable') ?? null;
            $replaceFeatures = $input->getOption('replace') ?? null;
            $filterCloudable = $input->getOption('cloudable');
            $filterNonCloudable = $input->getOption('noncloudable');
            $setCloud = $input->getOption('set-cloud');
            $unsetCloud = $input->getOption('unset-cloud');
            $showFeatureList = $input->getOption('list');
            $wizard = $input->getOption('wizard');
            $success = false;

            if ($setCloud && $unsetCloud) {
                $output->writeln("<error>It is not possible to activate and deactivate the application as a cloud instance at the same time.</error>");
                return Command::FAILURE;
            }


            if ($setCloud) {
                // @see ID-10989 Notify the user.
                $output->writeln("<info>Warning: Activating this feature will disable login requirement in commands. The system user will be originator of all CMDB-related changes instead.</info>");
                $featureManager->setCloud(true);
            }

            if ($unsetCloud) {
                $featureManager->setCloud(false);
            }

            if ($showFeatureList) {
                $this->showFeatureList($featureManager, $output);

                return Command::SUCCESS;
            }

            $allFeatures = $featureManager->getFeatureNameList();
            $cloudable = null;

            if ($filterCloudable) {
                $cloudable = true;
            } elseif ($filterNonCloudable) {
                $cloudable = false;
            }

            if ($enableFeatures === 'all') {
                $success = $featureManager->enableFeatures($allFeatures, $cloudable);
            } elseif ($enableFeatures !== null) {
                $enableFeatures = explode(',', str_replace(' ', '', $enableFeatures));
                $success = $featureManager->enableFeatures($enableFeatures, $cloudable);
            }

            if ($disableFeatures === 'all') {
                $success = $featureManager->disableFeatures($allFeatures, $cloudable);
            } elseif ($disableFeatures !== null) {
                $disableFeatures = explode(',', str_replace(' ', '', $disableFeatures));
                $success = $featureManager->disableFeatures($disableFeatures, $cloudable);
            }

            if ($replaceFeatures === 'all') {
                $success = $featureManager->replaceFeatures($allFeatures, $cloudable);
            } elseif ($replaceFeatures !== null) {
                $replaceFeatures = explode(',', str_replace(' ', '', $replaceFeatures));
                $success = $featureManager->replaceFeatures($replaceFeatures, $cloudable);
            }

            if ($wizard) {
                $success = $this->processWizard($featureManager, $cloudable, $input, $output);
            }
        } catch (Exception $e) {
            $io = new SymfonyStyle($input, $output);
            $io->error("Something went wrong with the following message: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
