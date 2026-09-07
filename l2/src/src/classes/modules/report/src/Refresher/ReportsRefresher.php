<?php

namespace idoit\Module\Report\Refresher;

class ReportsRefresher
{
    private ?Statistics $statistics = null;

    public function __construct()
    {
        $this->statistics = new Statistics();
    }

    /**
     * @param int|null $reportId
     *
     * @return void
     * @throws \Exception
     */
    public function refresh(?int $reportId = null): void
    {
        $query = "SELECT * FROM isys_report WHERE %s";
        $conditions = [
            "isys_report__querybuilder_data IS NOT NULL",
            "isys_report__querybuilder_data != ''"
        ];

        if (is_numeric($reportId)) {
            $reportId = (int)$reportId;
            $conditions[] = "isys_report__id = {$reportId}";
        }

        $dao = \isys_application::instance()->container->get('cmdb_dao');
        $result = $dao->retrieve(sprintf($query, implode(' AND ', $conditions)));

        while ($row = $result->get_row()) {
            $startTimeIndexCreation = microtime(true);

            try {
                $success = (Report::factory($row['isys_report__id']))->refresh();
            } catch (\Throwable $e) {
                $success = false;
            }

            $this->statistics->addReport(
                $row['isys_report__id'],
                $row['isys_report__title'],
                number_format(microtime(true) - $startTimeIndexCreation, 2),
                $success
            );
        }
    }

    /**
     * @return Statistics
     */
    public function getStatistics(): Statistics
    {
        return $this->statistics;
    }
}
