<?php

namespace App\Repositories;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    /** @return Category[] */
    public function all(): array;
    public function find(int $id): ?Category;

    /** @return Category[] */
    public function findCarCategories(int $carId): array;
    public function insert(Category $category): Category;
    public function update(Category $category): Category;
    public function delete(Category $category): bool;
}
