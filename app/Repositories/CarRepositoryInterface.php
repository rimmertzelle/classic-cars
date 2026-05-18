<?php

namespace App\Repositories;

use App\Models\Car;

interface CarRepositoryInterface
{
    /** @return Car[] */
    public function all(): array;
    public function find(int $id): ?Car;
    public function insert(Car $car): Car|null;
    public function update(Car $car): bool;
    public function delete(Car $car): bool;
}
