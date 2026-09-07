<?php

namespace idoit\Console\Command;

use isys_cmdb_dao_relation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateRelationsCommand extends AbstractCommand
{
    const NAME = 'system-objectrelations';

    /**
     * @var OutputInterface
     */
    private $output;

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
        return 'Regenerates all object relation names';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('categoryConstant', null, InputOption::VALUE_REQUIRED, 'Category constant (e.g. C__CATG__IP)'));

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
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        try {
            $this->regenerate_relations($input->getOption('categoryConstant'));
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * This rebuilds the locations
     *
     * @param $categoryConstant
     *
     * @throws \isys_exception_database
     * @throws \isys_exception_general
     */
    private function regenerate_relations($categoryConstant)
    {
        $dao = isys_cmdb_dao_relation::instance($this->container->get('database'));
        $this->output->writeln('Rebuilding relation objects. This can take a while.', true);

        $this->output->writeln('Removing unassigned relation objects.');
        $l_stats = $dao->delete_dead_relations();
        $l_dead_relation_objects = $l_stats[isys_cmdb_dao_relation::C__DEAD_RELATION_OBJECTS];

        if ($l_dead_relation_objects) {
            $this->output->writeln('(' . $l_dead_relation_objects . ') unassigned relation objects deleted.');
        } else {
            $this->output->writeln('No unassigned relation objects found');
        }

        $selectedCategory = [];

        if ($categoryConstant !== null) {
            $this->output->writeln('Using ' . $categoryConstant);

            $typeGlobal = $dao->convert_sql_int(C__CMDB__CATEGORY__TYPE_GLOBAL);
            $typeSpecific = $dao->convert_sql_int(C__CMDB__CATEGORY__TYPE_SPECIFIC);
            $constant = $dao->convert_sql_text($categoryConstant);

            $sql = "SELECT isysgui_catg__id AS id, {$typeGlobal} AS type
                FROM isysgui_catg
                WHERE isysgui_catg__const = {$constant}

                UNION

                SELECT isysgui_cats__id AS id, {$typeSpecific} AS type
                FROM isysgui_cats
                WHERE isysgui_cats__const = {$constant};";

            $categoryData = $dao->retrieve($sql)->get_row();

            $selectedCategory[$categoryData['type']] = [$categoryData['id'] => true];
        }

        $dao->regenerate_relations($selectedCategory);
        $this->output->writeln("Relation objects were successfully renewed.");
    }
}
