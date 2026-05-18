<?php

namespace App\Repositories;

use App\Models\Make;
use Framework\Database;

class MakeRepository implements MakeRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /** @return Make[] */
    public function all(): array
    {
        $rows = $this->database->run("SELECT * FROM makes ORDER BY name")->fetchAll();
        $makes = [];
        foreach ($rows as $row) {
            $makes[] = $this->fromDbRow($row);
        }
        return $makes;
    }

    public function find(int $id): ?Make
    {
        $row = $this->database->run("SELECT * FROM makes WHERE id = :id", ["id" => $id])->fetch();
        if (!$row) {
            return null;
        }
        return $this->fromDbRow($row);
    }

    public function insert(Make $make): Make
    {
        $this->database->run(
            "INSERT INTO makes (name, country, description) VALUES (:name, :country, :description)",
            ["name" => $make->name, "country" => $make->country, "description" => $make->description]
        );
        $make->id = $this->database->getLastID();
        return $make;
    }

    public function update(Make $make): Make
    {
        $this->database->run(
            "UPDATE makes SET name = :name, country = :country, description = :description WHERE id = :id",
            ["name" => $make->name, "country" => $make->country, "description" => $make->description, "id" => $make->id]
        );
        return $make;
    }

    public function delete(Make $make): bool
    {
        $stmt = $this->database->run("DELETE FROM makes WHERE id = :id", ["id" => $make->id]);
        return $stmt->rowCount() > 0;
    }

    private function fromDbRow(mixed $row): Make
    {
        $make = new Make();
        $make->id = $row->id;
        $make->name = $row->name;
        $make->country = $row->country;
        $make->description = $row->description;
        return $make;
    }
}
