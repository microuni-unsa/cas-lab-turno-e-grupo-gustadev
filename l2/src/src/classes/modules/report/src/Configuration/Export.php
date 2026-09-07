<?php

namespace idoit\Module\Report\Configuration;

use Exception;
use isys_report_dao;

class Export extends AbstractConfiguration
{
    /**
     * @var array
     */
    private array $reportIds = [];

    /**
     * @param array $reportIds
     */
    public function __construct(array $reportIds)
    {
        $this->reportIds = $reportIds;
    }

    /**
     * @param array $report
     */
    private function buildReportNode(array $report)
    {
        $reportNode = $this->getXml()->addChild('report');

        foreach ($this->getFieldMap() as $exportField => $fieldInfo) {
            $value = $report[$fieldInfo[self::FIELDMAP_DBFIELD]] ?? '';

            if ($fieldInfo[self::FIELDMAP_CDATA]) {
                $node = $reportNode->addChild($exportField);
                $node->addCData($value);
                continue;
            }

            $reportNode->addChild($exportField, $value);
        }
    }

    /**
     * @return false
     * @throws \Exception
     */
    public function createExport()
    {
        if (empty($this->reportIds)) {
            return false;
        }

        try {
            $dao = isys_report_dao::instance();
            $reports = $dao->get_reports(null, $this->reportIds, null, true, false);

            $this->setXml('<isys></isys>');

            foreach ($reports as $report) {
                $this->buildReportNode($report);
            }
        } catch (Exception $e) {
            throw ExportException::couldNotBuildExport($e->getMessage());
        }

        return true;
    }

    /**
     * Create download
     */
    public function output()
    {
        header("Content-Type: text/xml");
        header("Expires: " . date("D, d M Y H:i:s") . " GMT");
        header("Content-Disposition: attachment; filename=" . date("YmdHis") . "-report-export.xml");
        header("Pragma: no-cache");

        echo $this->getXml()->asXML();
        die;
    }
}
