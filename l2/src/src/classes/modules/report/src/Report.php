<?php

namespace idoit\Module\Report;

use idoit\Component\Provider\Factory;
use idoit\Module\Report\Protocol\Worker;
use idoit\Module\Report\Validate\Query;
use isys_report;
use isys_report_dao;

/**
 * Report
 *
 * @package     idoit\Module\Report
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class Report
{
    use Factory;

    /**
     * @var \isys_component_dao
     */
    private $dao;

    /**
     * Report id
     *
     * @var int
     */
    private $id;

    /**
     * Report type (e.g. 'c' for custom)
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * Report SQL Query
     *
     * @var string
     */
    private $query;

    /**
     * Tenant ID
     *
     * @var int
     */
    private $tenantId;

    /**
     * @var string
     */
    private $datetime;

    /**
     * @var string
     */
    private $lastEdited;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * Is report executed in html context?
     *
     * If not, don't generate any html.
     *
     * @var string
     */
    private $htmlContext = true;

    /**
     * @var bool
     */
    private $compressedMultivalueResults = false;

    /**
     * Query execution as a generator function
     */
    public function execute()
    {
        if (!empty($this->query)) {
            try {
                if (Query::validate($this->query)) {
                    /**
                     * Increase max execution time for this report
                     * Default is 5x of php's default (3000)
                     */
                    set_time_limit(\isys_tenantsettings::get('report.max_execution_time', 3000));

                    if (stripos($this->query, 'LIMIT')) {
                        $matches = [];
                        preg_match('/LIMIT [0-9,\s]*(?=\;)/i', trim($this->query), $matches);
                        $this->query = preg_replace('/LIMIT [0-9,\s]*(?=\;)/i', '', trim($this->query));
                    }

                    if (strpos($this->query, '{')) {
                        // Remove all placeholders from query like ,'{', isys_obj__id, '}'
                        $this->query = preg_replace("/\,\s*\'\s*\{\'\,\s*[\w]+\,.\'\}\'/", '', $this->query);
                    }

                    // Execute report.
                    $reportData = isys_report_dao::instance()->query($this->query, null, true);

                    if (!is_array($reportData['headers']) || !count($reportData['headers']) || !$this->worker) {
                        return;
                    }

                    // @see ID-9797 Use 'reformatResult' in order to unify the results.
                    $reportRows = isys_report::reformatResult($reportData, $this->htmlContext, $this->compressedMultivalueResults);

                    foreach ($reportRows as $row) {
                        // @see ID-10021 Remove '__id__' field.
                        unset($row['__id__']);

                        foreach ($row as &$cell) {
                            // @see ID-10016 Replace '&nbsp;' with actual whitespaces and then trim the content.
                            // @see ID-11591 Also replace '&amp;' with actual '&'.
                            $cell = trim(str_replace(['&nbsp;', '&amp;'], [' ', '&'], $cell));
                        }

                        $this->worker->work($row);
                    }
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @param $bool
     *
     * @return $this
     */
    public function enableHtmlContext($bool = true)
    {
        $this->htmlContext = $bool;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @param int $tenantId
     *
     * @return $this
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param string $datetime
     *
     * @return $this
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastEdited()
    {
        return $this->lastEdited;
    }

    /**
     * @param string $lastEdited
     *
     * @return $this
     */
    public function setLastEdited($lastEdited)
    {
        $this->lastEdited = $lastEdited;

        return $this;
    }

    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param Worker $worker
     *
     * @return $this
     */
    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompressedMultivalueResults()
    {
        return $this->compressedMultivalueResults;
    }

    /**
     * @param bool $compressedMultivalueResults
     */
    public function setCompressedMultivalueResults($compressedMultivalueResults)
    {
        $this->compressedMultivalueResults = $compressedMultivalueResults;
        return $this;
    }

    /**
     * Report constructor.
     *
     * @param \isys_component_dao $dao
     * @param  string             $query
     */
    public function __construct(\isys_component_dao $dao, $query, $title = null, $reportId = null, $reportType = null)
    {
        $this->dao = $dao;
        $this->query = $query;

        $this->title = $title;
        $this->id = $reportId;
        $this->type = $reportType;
    }
}
