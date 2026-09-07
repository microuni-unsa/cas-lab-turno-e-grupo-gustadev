<?php

namespace idoit\Console\Command\Contract;

use DateTime;
use idoit\Console\Command\AbstractCommand;
use isys_cmdb_dao_category_s_contract;
use isys_event_manager;
use isys_factory_cmdb_dialog_dao;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtendContractsCommand extends AbstractCommand
{
    const NAME = 'extend-contracts';

    private array $unitCache;

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
        return 'Automatically extend the runtime of not-cancelled contracts';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition([
            new InputOption('simulate', null, InputOption::VALUE_NONE, 'Simulate the contract extension')
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
        return [
            'php console.php extend-contracts',
            'php console.php extend-contracts -u<user> -p<password>',
            'php console.php extend-contracts -u<user> -p<password> --simulate'
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = $this->container->get('database');
        $language = $this->container->get('language');
        $locale = $this->container->get('locales');

        $isSimulation = $input->getOption('simulate');

        $periodUnits = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $database)->get_data();

        foreach ($periodUnits as $unit) {
            $this->unitCache[$unit['isys_guarantee_period_unit__const']] = (int)$unit['isys_guarantee_period_unit__id'];
        }

        try {
            $contractDao = isys_cmdb_dao_category_s_contract::instance($database);

            $conditions = [
                ' AND isys_obj__status = ' . $contractDao->convert_sql_int(C__RECORD_STATUS__NORMAL),
                ' AND isys_contract_end_type__const = \'C__DIALOG__NOTICE\'',
                ' AND isys_cats_contract_list__runtime_period >= 1',
                ' AND isys_cats_contract_list__runtime_period_unit >= 1'
            ];

            $result = $contractDao->get_data(null, null, implode($conditions), null, C__RECORD_STATUS__NORMAL);

            $contractsCount = count($result);

            $output->writeln($language->get_in_text("Looking for contracts where 'LC__CATS__CONTRACT__RUNTIME_PERIOD' is set and 'LC__CMDB__CATS__CONTRACT__END_TYPE' equals 'LC__DIALOG__NOTICE'."));
            $output->writeln("Found <comment>{$contractsCount} contracts</comment> to be processed.");

            while ($row = $result->get_row()) {
                $contractId = $row['isys_obj__id'];
                $contractTitle = $row['isys_obj__title'];

                $output->writeln("Processing '<info>{$contractTitle}</info>' (#{$contractId})...");

                if (! $this->isContractApplicable($row, $contractDao, $output)) {
                    $output->writeln(' > Skip');
                    continue;
                }

                $modifyString = $this->getModifyString((int)$row['isys_cats_contract_list__runtime_period'], (int)$row['isys_cats_contract_list__runtime_period_unit']);

                $oldEndDate = $row['isys_cats_contract_list__end_date'];
                $newEndDate = (new DateTime($row['isys_cats_contract_list__end_date']))->modify($modifyString);
                $newEndDateString = $newEndDate->format('Y-m-d');

                $output->writeln(" > Changing contract end date from <comment>{$oldEndDate}</comment> to <comment>{$newEndDateString}</comment> ({$modifyString})");

                // This logic will determine the 'biggest' possible unit (1 year instead of 365 days, 3 months instead of 91 days).
                $oldRuntime = (int)$row['isys_cats_contract_list__runtime'];
                $oldRuntimeUnit = (int)$row['isys_cats_contract_list__runtime_unit'];
                [$newRuntime, $newRuntimeUnit] = $this->determineNewRuntime(new DateTime($row['isys_cats_contract_list__start_date']), $newEndDate);

                $newData = [
                    'start_date' => $row['isys_cats_contract_list__start_date'],
                ];

                $logbookChanges = [
                    'isys_cmdb_dao_category_s_contract::end_date' => [
                        'from' => $locale->fmt_date($oldEndDate),
                        'to'   => $locale->fmt_date($newEndDateString),
                    ]
                ];

                if ($oldRuntime !== $newRuntime || $oldRuntimeUnit !== $newRuntimeUnit) {
                    $newData['run_time'] = $newRuntime;
                    $newData['run_time_unit'] = $newRuntimeUnit;

                    $logbookChanges['isys_cmdb_dao_category_s_contract::run_time'] = [
                        'from' => $oldRuntime,
                        'to'   => $newRuntime,
                    ];

                    $unitMapping = [
                        'C__GUARANTEE_PERIOD_UNIT_MONTH' => $language->get('LC__UNIVERSAL__MONTHS'),
                        'C__GUARANTEE_PERIOD_UNIT_DAYS' => $language->get('LC__UNIVERSAL__DAYS'),
                        'C__GUARANTEE_PERIOD_UNIT_WEEKS' => $language->get('LC__UNIVERSAL__WEEKS'),
                        'C__GUARANTEE_PERIOD_UNIT_YEARS' => $language->get('LC__UNIVERSAL__YEARS')
                    ];

                    $logbookChanges['isys_cmdb_dao_category_s_contract::run_time_unit'] = [
                        'from' => $unitMapping[array_search($oldRuntimeUnit, $this->unitCache)],
                        'to'   => $unitMapping[array_search($newRuntimeUnit, $this->unitCache)],
                    ];
                } else {
                    $newData['end_date'] = $newEndDateString;
                }

                if ($isSimulation) {
                    $output->writeln(' > Due to "simulation" mode no actual changes will be processed!');
                    continue;
                }

                $success = $contractDao->save_data($row['isys_cats_contract_list__id'], $newData);

                if (!$success) {
                    $output->writeln(' > <error>The changed date could not be saved</error>');
                    continue;
                }

                $output->writeln(' > Writing changes to logbook', OutputInterface::VERBOSITY_VERBOSE);

                isys_event_manager::getInstance()
                    ->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                        'Updated contract runtime via "extend-contracts" command',
                        $contractId,
                        $row['isys_obj__isys_obj_type__id'],
                        $language->get('LC__CMDB__CATS__CONTRACT'),
                        serialize($logbookChanges),
                        'Updated contract runtime via "extend-contracts" command',
                        null,
                        null,
                        null,
                        count($logbookChanges)
                    );

                $output->writeln(' > <info>DONE</info>');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        return Command::FAILURE;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return array
     */
    private function determineNewRuntime(DateTime $startDate, DateTime $endDate): array
    {
        $diff = $startDate->diff($endDate);

        if ($diff->d > 0) {
            if ($diff->days % 7 === 0) {
                return [$diff->days / 7, $this->unitCache['C__GUARANTEE_PERIOD_UNIT_WEEKS']];
            }

            return [$diff->days, $this->unitCache['C__GUARANTEE_PERIOD_UNIT_DAYS']];
        }

        if ($diff->m > 0) {
            return [$diff->m + $diff->y * 12, $this->unitCache['C__GUARANTEE_PERIOD_UNIT_MONTH']];
        }

        if ($diff->y > 0) {
            return [$diff->y, $this->unitCache['C__GUARANTEE_PERIOD_UNIT_YEARS']];
        }

        // Always fallback to days.
        return [$diff->days, $this->unitCache['C__GUARANTEE_PERIOD_UNIT_DAYS']];
    }

    /**
     * @param int $period
     * @param int $periodUnit
     *
     * @return string
     * @throws \Exception
     */
    private function getModifyString(int $period, int $periodUnit): string
    {
        switch (array_search($periodUnit, $this->unitCache)) {
            default:
            case 'C__GUARANTEE_PERIOD_UNIT_DAYS':
                return "+ {$period} days";

            case 'C__GUARANTEE_PERIOD_UNIT_WEEKS':
                return "+ {$period} weeks";

            case 'C__GUARANTEE_PERIOD_UNIT_MONTH':
                return "+ {$period} months";

            case 'C__GUARANTEE_PERIOD_UNIT_YEARS':
                return "+ {$period} years";
        }
    }

    /**
     * @param array                             $categoryData
     * @param isys_cmdb_dao_category_s_contract $contractDao
     * @param OutputInterface                   $output
     *
     * @return bool
     * @throws \Exception
     */
    private function isContractApplicable(array $categoryData, isys_cmdb_dao_category_s_contract $contractDao, OutputInterface $output): bool
    {
        $expirationDate = null;
        $endDate = $categoryData['isys_cats_contract_list__end_date'];
        $periodTypeId = $categoryData["isys_cats_contract_list__isys_contract_notice_period_type__id"];

        if (empty($endDate) || strtotime($endDate) === false) {
            $output->writeln(' > No end date.', OutputInterface::VERBOSITY_VERBOSE);
            return false;
        }

        if ($periodTypeId != defined_or_default('C__CONTRACT__ON_CONTRACT_END')) {
            $output->writeln(' > Cancellation period is not set to "on contract end".', OutputInterface::VERBOSITY_VERBOSE);
            return false;
        }

        $expirationDate = $contractDao->calculate_noticeperiod(
            $endDate,
            $categoryData['isys_cats_contract_list__notice_period'],
            $categoryData['isys_cats_contract_list__notice_period_unit__id']
        );

        if ($expirationDate === null) {
            $output->writeln(' > No cancellation date.', OutputInterface::VERBOSITY_VERBOSE);
            return false;
        }

        // '%r' will return a sign ('' or '-') and '%a' the amount of days, so values like '-2' or '123'.
        $difference = (int) (new DateTime())->diff(new DateTime($expirationDate))->format('%r%a');

        // The cancellation date needs to lie in the past.
        if ($difference >= 0) {
            $output->writeln(' > Cancellation date needs to be in the past.', OutputInterface::VERBOSITY_VERBOSE);
            return false;
        }

        $output->writeln(" > Cancellation date was {$expirationDate}.", OutputInterface::VERBOSITY_VERBOSE);

        return true;
    }
}
