<?php

namespace idoit\Module\Report\Dto;

use DateTime;

class Report
{
    public const MAP = [
        'id' => 'isys_report__id',
        'title' => 'isys_report__title',
        'description' => 'isys_report__description',
        'query' => 'isys_report__query',
        'queryRow' => 'isys_report__query_row',
        'mandator' => 'isys_report__mandator',
        'user' => 'isys_report__user',
        'dateTime' => 'isys_report__datetime',
        'lastEdited' => 'isys_report__last_edited',
        'type' => 'isys_report__type',
        'userSpecific' => 'isys_report__user_specific',
        'queryBuilderData' => 'isys_report__querybuilder_data',
        'reportCategory' => 'isys_report__isys_report_category__id',
        'emptyValues' => 'isys_report__empty_values',
        'displayRelations' => 'isys_report__display_relations',
        'categoryReport' => 'isys_report__category_report',
        'const' => 'isys_report__const',
        'compressedMultivalueResults' => 'isys_report__compressed_multivalue_results',
        'showHtml' => 'isys_report__show_html',
        'keepDescriptionFormat' => 'isys_report__keep_description_format',
        'imported' => 'isys_report__imported'
    ];

    public function __construct(
        private ?int $id = null,
        private ?string $title = null,
        private ?string $description = null,
        private ?string $query = null,
        private ?string $queryRow = null,
        private ?int $mandator = null,
        private ?int $user = null,
        private ?DateTime $dateTime = null,
        private ?DateTime $lastEdited = null,
        private ?string $type = null,
        private ?bool $userSpecific = false,
        private ?string $queryBuilderData = null,
        private ?int $reportCategory = null,
        private ?bool $emptyValues = false,
        private ?bool $displayRelations = false,
        private ?bool $categoryReport = false,
        private ?string $const = null,
        private ?bool $compressedMultivalueResults = false,
        private ?bool $showHtml = false,
        private ?bool $keepDescriptionFormat = false,
        private ?DateTime $imported = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getQueryRow(): ?string
    {
        return $this->queryRow;
    }

    public function getMandator(): ?int
    {
        return $this->mandator;
    }

    public function getUser(): ?int
    {
        return $this->user;
    }

    public function getDateTime(): ?DateTime
    {
        return $this->dateTime;
    }

    public function getLastEdited(): ?DateTime
    {
        return $this->lastEdited;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUserSpecific(): ?bool
    {
        return $this->userSpecific;
    }

    public function getQueryBuilderData(): ?string
    {
        return $this->queryBuilderData;
    }

    public function getReportCategory(): ?int
    {
        return $this->reportCategory;
    }

    public function getEmptyValues(): ?bool
    {
        return $this->emptyValues;
    }

    public function getDisplayRelations(): ?bool
    {
        return $this->displayRelations;
    }

    public function getCategoryReport(): ?bool
    {
        return $this->categoryReport;
    }

    public function getConst(): ?string
    {
        return $this->const;
    }

    public function getCompressedMultivalueResults(): ?bool
    {
        return $this->compressedMultivalueResults;
    }

    public function getShowHtml(): ?bool
    {
        return $this->showHtml;
    }

    public function getKeepDescriptionFormat(): ?bool
    {
        return $this->keepDescriptionFormat;
    }

    public function getImported(): ?DateTime
    {
        return $this->imported;
    }
}
