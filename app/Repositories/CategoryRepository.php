<?php

namespace App\Repositories;

use App\Models\Category;
use Framework\Database;

class CategoryRepository implements CategoryRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /** @return Category[] */
    public function all(): array
    {
        $rows = $this->database->run("SELECT * FROM categories ORDER BY name")->fetchAll();
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = $this->fromDbRow($row);
        }
        return $categories;
    }

    public function find(int $id): ?Category
    {
        $row = $this->database->run("SELECT * FROM categories WHERE id = :id", ["id" => $id])->fetch();
        if (!$row) {
            return null;
        }
        return $this->fromDbRow($row);
    }

    /** @return Category[] */
    public function findCarCategories(int $carId): array
    {
        $rows = $this->database->run(
            "SELECT categories.* FROM categories
             JOIN car_categories ON categories.id = car_categories.category_id
             WHERE car_categories.car_id = :carId
             ORDER BY categories.name",
            ["carId" => $carId]
        )->fetchAll();
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = $this->fromDbRow($row);
        }
        return $categories;
    }

    public function insert(Category $category): Category
    {
        $this->database->run(
            "INSERT INTO categories (name) VALUES (:name)",
            ["name" => $category->name]
        );
        $category->id = $this->database->getLastID();
        return $category;
    }

    public function update(Category $category): Category
    {
        $this->database->run(
            "UPDATE categories SET name = :name WHERE id = :id",
            ["name" => $category->name, "id" => $category->id]
        );
        return $category;
    }

    public function delete(Category $category): bool
    {
        $stmt = $this->database->run("DELETE FROM categories WHERE id = :id", ["id" => $category->id]);
        return $stmt->rowCount() > 0;
    }

    private function fromDbRow(mixed $row): Category
    {
        $category = new Category();
        $category->id = $row->id;
        $category->name = $row->name;
        return $category;
    }
}
