<?php

namespace idoit\Module\Report\Container;

use idoit\Module\Report\SqlQuery\Structure\ReportQuery;

class ReportContainer
{
    /**
     * @var ReportQuery
     */
    private ReportQuery $reportQuery;

    /**
     * @return ReportQuery
     */
    public function getReportQuery(): ReportQuery
    {
        return $this->reportQuery;
    }

    /**
     * @param ReportQuery $reportQuery
     *
     * @return $this
     */
    public function setReportQuery(ReportQuery $reportQuery): ReportContainer
    {
        $this->reportQuery = $reportQuery;
        return $this;
    }
}
