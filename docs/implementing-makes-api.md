# Implementing the Makes API

This guide walks through adding `GET /api/makes` and `GET /api/makes/{id}` to the existing API.
Every step mirrors what is already in place for Cars — use those files as your reference.

---

## Step 1 — Add a `findMakeCars()` method to `CarRepository`

The single-make endpoint needs the cars that belong to a make. Add the method to the interface first, then implement it in the repository.

`app/Repositories/CarRepositoryInterface.php` — add:

```php
/** @return Car[] */
public function findMakeCars(int $makeId): array;
```

`app/Repositories/CarRepository.php` — implement it:

```php
public function findMakeCars(int $makeId): array
{
    $rows = $this->database->run(
        "SELECT cars.*, makes.name AS make_name, makes.country AS make_country,
                makes.description AS make_description
         FROM cars
         JOIN makes ON cars.make_id = makes.id
         WHERE cars.make_id = :make_id
         ORDER BY cars.model",
        ["make_id" => $makeId]
    )->fetchAll();

    return array_map([$this, 'fromDbRow'], $rows);
}
```

---

## Step 2 — Inject `MakeRepository` into `ApiController`

`app/Controllers/ApiController.php` — extend the constructor:

```php
public function __construct(
    ResponseFactory $responseFactory,
    private CarRepositoryInterface $carRepository,
    private MakeRepositoryInterface $makeRepository,
) {
    parent::__construct($responseFactory);
}
```

---

## Step 3 — Add the two API methods and a serialiser

Still in `app/Controllers/ApiController.php`, add alongside `cars()` and `car()`:

```php
public function makes(Request $request): Response
{
    $makes = $this->makeRepository->all();
    $data = array_map([$this, 'makeToArray'], $makes);
    return $this->apiJson('makes', $data, $request->path, total: count($data));
}

public function make(Request $request): Response
{
    $id = (int)$request->get('id');
    $make = $this->makeRepository->find($id);
    if ($make === null) {
        return $this->apiError('makes', 'Make not found', $request->path, 404, '/api/makes');
    }
    return $this->apiJson('makes', $this->makeToArray($make), $request->path, '/api/makes');
}

/** @return array<string, mixed> */
private function makeToArray(Make $make): array
{
    $cars = $this->carRepository->findMakeCars($make->id);
    return [
        'id'          => $make->id,
        'name'        => $make->name,
        'country'     => $make->country,
        'description' => $make->description,
        'cars'        => array_map(
            fn($car) => ['id' => $car->id, 'model' => $car->model, 'year' => $car->year],
            $cars
        ),
    ];
}
```

Also add the `Make` import at the top of the file:

```php
use App\Models\Make;
use App\Repositories\MakeRepositoryInterface;
```

---

## Step 4 — Register the routes

`app/RouteProvider.php` — add next to the existing car API routes:

```php
$router->addRoute('GET', '/api/makes', [$apiController, 'makes']);
$router->addRoute('GET', '/api/makes/(?<id>\d+)', [$apiController, 'make']);
```

---

## Step 5 — Update the service container

`app/ServiceProvider.php` — pass `$makeRepository` to the `ApiController` constructor:

```php
$apiController = new ApiController($responseFactory, $carRepository, $makeRepository);
```

---

## Expected responses

`GET /api/makes`:

```json
{
  "meta":  { "resource": "makes", "total": 6 },
  "links": { "self": "/api/makes" },
  "data":  [ { "id": 1, "name": "Ford", "country": "USA", "description": "..." }, "..." ]
}
```

`GET /api/makes/1`:

```json
{
  "meta":  { "resource": "makes" },
  "links": { "self": "/api/makes/1", "collection": "/api/makes" },
  "data": {
    "id": 1,
    "name": "Ford",
    "country": "USA",
    "description": "Founded in 1903...",
    "cars": [
      { "id": 1, "model": "Mustang", "year": 1965 },
      { "id": 2, "model": "Thunderbird", "year": 1957 }
    ]
  }
}
```

`GET /api/makes/999`:

```json
{
  "meta":  { "resource": "makes" },
  "links": { "self": "/api/makes/999", "collection": "/api/makes" },
  "error": { "status": 404, "message": "Make not found" }
}
```
