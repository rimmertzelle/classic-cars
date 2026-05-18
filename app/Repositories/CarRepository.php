<?php

namespace App\Repositories;

use App\Models\Car;
use App\Models\Make;
use Framework\Database;

class CarRepository implements CarRepositoryInterface
{
    private Database $database;

    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(Database $database, CategoryRepositoryInterface $categoryRepository)
    {
        $this->database = $database;
        $this->categoryRepository = $categoryRepository;
    }

    /** @return Car[] */
    public function all(): array
    {
        $rows = $this->database->run(
            "SELECT cars.*,
                    makes.name AS make_name,
                    makes.country AS make_country,
                    makes.description AS make_description
             FROM cars
             JOIN makes ON cars.make_id = makes.id
             ORDER BY makes.name, cars.model"
        )->fetchAll();
        $cars = [];
        foreach ($rows as $row) {
            $cars[] = $this->fromDbRow($row);
        }
        return $cars;
    }

    public function find(int $id): ?Car
    {
        $row = $this->database->run(
            "SELECT cars.*,
                    makes.name AS make_name,
                    makes.country AS make_country,
                    makes.description AS make_description
             FROM cars
             JOIN makes ON cars.make_id = makes.id
             WHERE cars.id = :id",
            ["id" => $id]
        )->fetch();
        if (!$row) {
            return null;
        }
        return $this->fromDbRow($row);
    }

    public function insert(Car $car): Car|null
    {
        $stmt = $this->database->run(
            "INSERT INTO cars (make_id, model, year, color, engine, description)
             VALUES (:make_id, :model, :year, :color, :engine, :description)",
            [
                "make_id" => $car->makeId,
                "model" => $car->model,
                "year" => $car->year,
                "color" => $car->color,
                "engine" => $car->engine,
                "description" => $car->description,
            ]
        );
        if ($stmt->rowCount() === 0) {
            return null;
        }
        $car->id = $this->database->getLastID();
        $this->syncCategories($car);
        return $car;
    }

    public function update(Car $car): bool
    {
        $stmt = $this->database->run(
            "UPDATE cars SET make_id = :make_id, model = :model, year = :year,
                             color = :color, engine = :engine, description = :description
             WHERE id = :id",
            [
                "id" => $car->id,
                "make_id" => $car->makeId,
                "model" => $car->model,
                "year" => $car->year,
                "color" => $car->color,
                "engine" => $car->engine,
                "description" => $car->description,
            ]
        );
        $this->syncCategories($car);
        return $stmt->rowCount() > 0;
    }

    public function delete(Car $car): bool
    {
        $stmt = $this->database->run("DELETE FROM cars WHERE id = :id", ["id" => $car->id]);
        return $stmt->rowCount() > 0;
    }

    private function syncCategories(Car $car): void
    {
        $this->database->run("DELETE FROM car_categories WHERE car_id = :car_id", ["car_id" => $car->id]);
        $stmt = $this->database->prepare(
            "INSERT INTO car_categories (car_id, category_id) VALUES (:car_id, :category_id)"
        );
        foreach ($car->categories as $category) {
            $stmt->execute(["car_id" => $car->id, "category_id" => $category->id]);
        }
    }

    private function fromDbRow(mixed $row): Car
    {
        $make = new Make();
        $make->id = $row->make_id;
        $make->name = $row->make_name;
        $make->country = $row->make_country;
        $make->description = $row->make_description;

        $car = new Car();
        $car->id = $row->id;
        $car->makeId = $row->make_id;
        $car->make = $make;
        $car->model = $row->model;
        $car->year = $row->year;
        $car->color = $row->color;
        $car->engine = $row->engine;
        $car->description = $row->description;
        $car->categories = $this->categoryRepository->findCarCategories($car->id);
        return $car;
    }
}
