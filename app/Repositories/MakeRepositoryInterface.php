<?php

namespace App\Repositories;

use App\Models\Make;

interface MakeRepositoryInterface
{
    /** @return Make[] */
    public function all(): array;
    public function find(int $id): ?Make;
    public function insert(Make $make): Make;
    public function update(Make $make): Make;
    public function delete(Make $make): bool;
}
