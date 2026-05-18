<?php

namespace App\Models;

class Car
{
    public int $id;

    public int $makeId;

    public ?Make $make = null;

    public string $model;

    public int $year;

    public string $color;

    public string $engine;

    public string $description;

    /** @var Category[] */
    public array $categories = [];
}
