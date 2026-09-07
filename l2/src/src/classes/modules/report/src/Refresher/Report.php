<?php

namespace idoit\Module\Report\Refresher;

use DateTime;
use idoit\Module\Report\Dto\Report as ReportDto;
use isys_application;
use isys_cmdb_dao_category_property;
use isys_component_database;

class Report
{
    public function __construct(
        private isys_component_database $database,
        private ?int $reportId = null,
        private ?ReportDto $report = null,
        private array $values = [],
    ) {
    }

    /**
     * @param int|null $reportId
     *
     * @return static
     * @throws \DateMalformedStringException
     */
    public static function factory(?int $reportId = null, ?array $values = []): static
    {
        $obj = new static(\isys_application::instance()->container->get('database'), $reportId);
        $obj->load();
        $obj->setValues($values);

        return $obj;
    }

    /**
     * @return void
     * @throws \DateMalformedStringException
     */
    public function load(): void
    {
        if (!$this->reportId) {
            $this->report = new ReportDto();
            return;
        }

        $result = $this->database->query("SELECT * FROM isys_report WHERE isys_report__id = {$this->reportId}");
        $data = $this->database->fetch_row_assoc($result);

        $this->report = new ReportDto(
            $data['isys_report__id'],
            $data['isys_report__title'],
            $data['isys_report__description'],
            $data['isys_report__query'],
            $data['isys_report__query_row'],
            $data['isys_report__mandator'],
            $data['isys_report__user'],
            new DateTime($data['isys_report__datetime']),
            new DateTime($data['isys_report__last_edited']),
            $data['isys_report__type'],
            $data['isys_report__user_specific'],
            $data['isys_report__querybuilder_data'],
            $data['isys_report__isys_report_category__id'],
            $data['isys_report__empty_values'],
            $data['isys_report__display_relations'],
            $data['isys_report__category_report'],
            $data['isys_report__const'],
            $data['isys_report__compressed_multivalue_results'],
            $data['isys_report__show_html'],
            $data['isys_report__keep_description_format'],
            ($data['isys_report__imported'] !== null ? new DateTime($data['isys_report__imported']) : null)
        );
    }

    /**
     * @return bool
     */
    private function save(): bool
    {
        try {
            $reflection = new \ReflectionClass($this->report);
            $properties = $reflection->getProperties();
            $values = [];
            foreach ($properties as $property) {
                $propKey = $property->getName();

                if ($propKey === 'id' || !isset($this->values[$propKey])) {
                    continue;
                }

                $type = $property->getType()->getName();

                if ($type === 'DateTime' && !$this->values[$propKey] instanceof DateTime) {
                    $this->values[$propKey] = new DateTime($this->values[$propKey]);
                }

                $values[] = $this->buildUpdateField($propKey, $type, $this->values[$propKey]);
            }

            if (empty($values)) {
                return false;
            }

            if ($this->reportId) {
                $this->database->query(
                    sprintf(
                        "Update isys_report SET %s WHERE isys_report__id = %d",
                        implode(',', $values),
                        $this->reportId
                    )
                );
                return true;
            }

            $this->database->query(
                sprintf(
                    "INSERT INTO isys_report SET %s",
                    implode(',', $values)
                )
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $key
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     * @throws \Exception
     */
    private function buildUpdateField(string $key, string $type, mixed $value): string
    {
        $cmdbDao = isys_application::instance()->container->get('cmdb_dao');
        $dbField = ReportDto::MAP[$key];
        $escapedValue = 'NULL';

        if ($value !== null) {
            $escapedValue = match ($type) {
                'string' => $cmdbDao->convert_sql_text($value),
                'int' => $cmdbDao->convert_sql_int($value),
                'bool' => $cmdbDao->convert_sql_boolean($value),
                'DateTime' => $cmdbDao->convert_sql_datetime($value->format('Y-m-d H:i:s'))
            };
        }

        return "{$dbField} = {$escapedValue}";
    }

    /**
     * @return bool
     * @throws \idoit\Exception\JsonException
     * @throws \isys_exception_database
     */
    public function refresh(): bool
    {
        $dao = isys_cmdb_dao_category_property::instance($this->database);
        if ($this->report->getQueryBuilderData()) {
            $this->values['query'] = $dao->reset()
                    ->prepareEnvironmentForReportById($this->reportId)
                    ->create_property_query_for_report() . '';
        }

        return $this->save();
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addValue(string $key, mixed $value): static
    {
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values): static
    {
        $this->values = $values;

        return $this;
    }
}
