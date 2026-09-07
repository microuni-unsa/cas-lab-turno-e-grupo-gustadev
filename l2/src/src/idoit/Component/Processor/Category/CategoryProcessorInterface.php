<?php

namespace idoit\Component\Processor\Category;

/**
 * Category processor interface.
 */
interface CategoryProcessorInterface
{
    public function supports(string $categoryConstant): bool;

    public function readById(int|array $id, int|array|null $status = null): array;

    public function readByObject(int|array $id, int|array|null $status = null): array;

    public function save(int|null $id, int $objectId, array $data): int;

    public function archive(int|array $id): void;

    public function delete(int|array $id): void;

    public function purge(int|array $id): void;

    public function restore(int|array $id): void;

    public function isSingleValue(): bool;

    public function isMultiValue(): bool;
}
