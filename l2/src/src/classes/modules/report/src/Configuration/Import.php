<?php

namespace idoit\Module\Report\Configuration;

use isys_application;
use isys_cmdb_dao;
use isys_cmdb_dao_category_property;
use isys_library_xml;

class Import extends AbstractConfiguration
{
    /**
     * @var isys_cmdb_dao
     */
    private isys_cmdb_dao $dao;

    /**
     * @param string $xmlContent
     *
     * @throws \Exception
     */
    public function __construct(string $xmlContent)
    {
        $this->setXml($xmlContent);
        $this->dao = isys_cmdb_dao_category_property::instance(isys_application::instance()->container->get('database'));
    }

    /**
     * @param isys_library_xml $report
     *
     * @return array
     */
    private function extractValues(isys_library_xml $report, $table)
    {
        $data = [];

        $fieldMap = array_filter($this->getFieldMap(), function ($item) use ($table) {
            return $item[self::FIELDMAP_TABLE] === $table;
        });

        /**
         * @var $value isys_library_xml
         */
        foreach ($report as $key => $value) {
            if (!isset($fieldMap[$key])) {
                continue;
            }
            $count = 0;
            $value = strval($value);
            $fieldInfo = $fieldMap[$key];
            $nullable = $fieldInfo[self::FIELDMAP_NULLABLE];
            $dbField = $fieldInfo[self::FIELDMAP_DBFIELD];
            $cData = $fieldInfo[self::FIELDMAP_CDATA];
            $isNumeric = is_numeric($value);
            $isNullable = $fieldInfo[self::FIELDMAP_NULLABLE];

            if (is_numeric($value)) {
                $data[$key] = "{$dbField} = {$this->dao->convert_sql_int($value)}";
                continue;
            }
            $value = trim($value);

            if (is_string($value)) {
                if ($value === '' && $isNullable) {
                    $data[$key] = "{$dbField} = NULL";
                    continue;
                }

                if ($key === 'const' || $key === 'title') {
                    $count = $this->countEntries($table, "{$dbField} = {$this->dao->convert_sql_text($value)}");
                    if ($count > 0) {
                        $counter = 1;
                        do {
                            $newValue = $value . '_' . $counter++;
                            $condition = "{$dbField} = {$this->dao->convert_sql_text($newValue)}";
                        } while (($count = $this->countEntries($table, $condition)) > 0);
                        $value = $newValue;
                    }
                }

                $data[$key] = "{$dbField} = {$this->dao->convert_sql_text($value)}";
                continue;
            }
        }
        return $data;
    }

    /**
     * @param string $table
     * @param array  $data
     * @param string $condition
     *
     * @return string
     */
    private function getUpdateQuery(string $table, array $data, string $condition)
    {
        $pattern = "UPDATE {$table} SET %s WHERE {$condition}";
        return sprintf($pattern, implode(', ', $data));
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return string
     */
    private function getCreateQuery(string $table, array $data)
    {
        $pattern = "INSERT INTO $table SET %s";
        return sprintf($pattern, implode(', ', $data));
    }

    /**
     * @param string $table
     * @param string $condition
     *
     * @return \isys_component_dao_result|int|null
     * @throws \isys_exception_database
     */
    private function getEntryId(string $table, string $condition)
    {
        $result = $this->dao->retrieve("SELECT {$table}__id FROM {$table} WHERE {$condition}");
        return $result->get_row_value($table . '__id');
    }

    /**
     * @param string $table
     * @param string $condition
     *
     * @return int
     * @throws \isys_exception_database
     */
    private function countEntries(string $table, string $condition)
    {
        $result = $this->dao->retrieve("SELECT {$table}__id FROM {$table} WHERE {$condition}");
        return $result->count();
    }

    /**
     * @return bool
     * @throws ImportException
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    public function importReport()
    {
        $xml = $this->getXml();
        $data = [];

        if (!isset($xml->report)) {
            throw ImportException::expectedNodeMissing('report');
        }

        foreach ($xml->report as $report) {
            $updateCondition = null;
            $updateReportQuery = null;
            $reportId = null;
            $count = 0;
            $data = $this->extractValues($report, 'isys_report');
            $data['user'] = 'isys_report__user = ' . $this->dao->convert_sql_id(
                isys_application::instance()->container->get('session')->get_user_id()
            );

            $this->dao->update($this->getCreateQuery('isys_report', $data));
            $reportId = $this->dao->get_last_insert_id();

            if (!strpos($data['querybuilder_data'], "= ''")) {
                // Update Query
                $query = isys_cmdb_dao_category_property::instance(isys_application::instance()->container->get('database'))
                        ->reset()
                        ->prepareEnvironmentForReportById($reportId)
                        ->create_property_query_for_report() . '';

                $this->dao->update($this->getUpdateQuery('isys_report', [
                        'isys_report__query = ' . $this->dao->convert_sql_text($query)
                    ], 'isys_report__id = ' . $this->dao->convert_sql_id($reportId)));
            }

            $data = $this->extractValues($report, 'isys_report_category');

            if (strpos($data['category__title'], '= NULL') !== false
                && strpos($data['category__const'], '= NULL') !== false
            ) {
                continue;
            }

            if (strpos($data['category__title'], '= NULL') === false
                && $reportCategoryId = $this->getEntryId('isys_report_category', $data['category__title'])
            ) {
                $updateReportQuery = $this->getUpdateQuery(
                    'isys_report',
                    [
                        'isys_report__isys_report_category__id = ' . $this->dao->convert_sql_id($reportCategoryId)
                    ],
                    'isys_report__id = ' . $this->dao->convert_sql_id($reportId)
                );
            }

            if (strpos($data['category__const'], '= NULL') === false
                && $reportCategoryId = $this->getEntryId('isys_report_category', $data['category__const'])
            ) {
                $updateReportQuery = $this->getUpdateQuery(
                    'isys_report',
                    [
                        'isys_report__isys_report_category__id = ' . $this->dao->convert_sql_id($reportCategoryId)
                    ],
                    'isys_report__id = ' . $this->dao->convert_sql_id($reportId)
                );
            }

            if ($updateReportQuery === null) {
                $reportCategoryQuery = $this->getCreateQuery('isys_report_category', $data);
                $this->dao->update($reportCategoryQuery);
                $reportCategoryId = $this->dao->get_last_insert_id();

                $updateReportQuery = $this->getUpdateQuery(
                    'isys_report',
                    [
                        'isys_report__isys_report_category__id = ' . $this->dao->convert_sql_id($reportCategoryId)
                    ],
                    'isys_report__id = ' . $this->dao->convert_sql_id($reportId)
                );
            }
            $this->dao->update($updateReportQuery);
        }
        return $this->dao->apply_update();
    }
}
