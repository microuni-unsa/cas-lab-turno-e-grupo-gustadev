<?php

namespace idoit\Module\Report\Dto;

class ReportStatistic
{
    public function __construct(private int $id, private string $title, private float $time, private bool $success)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
