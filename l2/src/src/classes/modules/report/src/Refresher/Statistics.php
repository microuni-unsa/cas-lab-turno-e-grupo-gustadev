<?php

namespace idoit\Module\Report\Refresher;

use idoit\Module\Report\Dto\ReportStatistic;

class Statistics
{
    /**
     * @var ReportStatistic[]
     */
    private array $reportStatistics = [];

    /**
     * @var float|int
     */
    private float $totalTime = 0;

    /**
     * @param int    $id
     * @param string $title
     * @param float  $time
     * @param bool   $success
     *
     * @return $this
     */
    public function addReport(int $id, string $title, float $time, bool $success): static
    {
        $this->reportStatistics[$id] = new ReportStatistic($id, $title, $time, $success);
        return $this;
    }

    /**
     * @return ReportStatistic[]
     */
    public function getReportStatistics(): array
    {
        return $this->reportStatistics;
    }

    /**
     * @param int $id
     *
     * @return ReportStatistic
     */
    public function getReportStatistic(int $id)
    {
        return $this->reportStatistics[$id];
    }

    /**
     * @return float
     */
    public function getTotalTime(): float
    {
        return array_sum(array_map(fn ($item) => $item->getTime(), $this->reportStatistics));
    }

    /**
     * @return int
     */
    public function getCountRefreshedReports(): int
    {
        return count($this->reportStatistics);
    }
}
