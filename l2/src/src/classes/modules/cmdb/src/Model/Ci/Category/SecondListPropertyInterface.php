<?php

namespace idoit\Module\Cmdb\Model\Ci\Category;

interface SecondListPropertyInterface
{
    public static function requestData(?array $parameters = []): string;

    public static function preparationData(?array $parameters = []): array;

    public static function preselectionData(?array $parameters = []): array;

    public function isApplicable(string $className, string $method): bool;
}
