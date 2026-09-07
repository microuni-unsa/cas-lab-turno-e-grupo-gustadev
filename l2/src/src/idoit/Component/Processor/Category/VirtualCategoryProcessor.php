<?php

namespace idoit\Component\Processor\Category;

use idoit\Exception\Exception;

/**
 * Category processor component to establish a unified internal API to apply CRUD operations on categories.
 */
class VirtualCategoryProcessor implements CategoryProcessorInterface
{
    public function supports(string $categoryConstant): bool
    {
        return true;
    }

    public function readById(int|array $id, int|array|null $status = null): array
    {
        throw new Exception('Virtual categories can not read data, as they have none.');
    }

    public function readByObject(int|array $id, int|array|null $status = null): array
    {
        throw new Exception('Virtual categories can not read data, as they have none.');
    }

    public function archive(int|array $id): void
    {
        throw new Exception('Virtual categories are not able to be archived.');
    }

    public function delete(int|array $id): void
    {
        throw new Exception('Virtual categories are not able to be deleted.');
    }

    public function purge(int|array $id): void
    {
        throw new Exception('Virtual categories are not able to be purged.');
    }

    public function restore(int|array $id): void
    {
        throw new Exception('Virtual categories are not able to be restored.');
    }

    public function save(int|null $id, int $objectId, array $data): int
    {
        throw new Exception('Virtual categories are not able to save data.');
    }

    public function isSingleValue(): bool
    {
        return true;
    }

    public function isMultiValue(): bool
    {
        return false;
    }
}
