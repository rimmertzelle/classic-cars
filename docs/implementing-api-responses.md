# Implementing API Responses

This guide explains the infrastructure added to produce JSON responses. The HTML controllers already used `ResponseFactory::view()` — for the API layer, two things were added: a `json()` method on `ResponseFactory`, and a new base class that wraps every response in a consistent envelope.

---

## Step 1 — Add `json()` to `ResponseFactory`

`src/ResponseFactory.php` — add alongside `view()`, `notFound()`, etc.:

```php
public function json(mixed $data, int $statusCode = 200): Response
{
    $response = new Response();
    $response->responseCode = $statusCode;
    $response->body = json_encode($data);
    $response->header = "Content-Type: application/json";
    return $response;
}
```

This is the only place in the application that sets `Content-Type: application/json`. Every API response goes through here, so the header is always correct.

---

## Step 2 — Create `ApiBaseController`

`app/Controllers/ApiBaseController.php`:

```php
<?php

namespace App\Controllers;

use Framework\Response;
use Framework\ResponseFactory;

abstract class ApiBaseController
{
    public function __construct(protected ResponseFactory $responseFactory)
    {
    }

    protected function apiJson(
        string $resource,
        mixed $data,
        string $selfLink,
        ?string $collectionLink = null,
        ?int $total = null,
    ): Response {
        $meta = ['resource' => $resource];
        if ($total !== null) {
            $meta['total'] = $total;
        }

        $links = ['self' => $selfLink];
        if ($collectionLink !== null) {
            $links['collection'] = $collectionLink;
        }

        return $this->responseFactory->json([
            'meta'  => $meta,
            'links' => $links,
            'data'  => $data,
        ]);
    }

    protected function apiError(
        string $resource,
        string $message,
        string $selfLink,
        int $status,
        ?string $collectionLink = null,
    ): Response {
        $links = ['self' => $selfLink];
        if ($collectionLink !== null) {
            $links['collection'] = $collectionLink;
        }

        return $this->responseFactory->json([
            'meta'  => ['resource' => $resource],
            'links' => $links,
            'error' => [
                'status'  => $status,
                'message' => $message,
            ],
        ], $status);
    }
}
```

`ApiController` extends this class instead of instantiating `ResponseFactory` directly.

---

## The envelope structure

Every response has the same three top-level keys:

| Key     | Always present | Purpose |
|---------|---------------|---------|
| `meta`  | yes           | Resource name; optionally `total` on collections |
| `links` | yes           | `self` (current URL); optionally `collection` on single-resource responses |
| `data`  | success only  | The actual payload — array for collections, object for single resources |
| `error` | error only    | `status` (HTTP code) and `message` |

### Successful collection (`GET /api/cars`)

```json
{
  "meta":  { "resource": "cars", "total": 12 },
  "links": { "self": "/api/cars" },
  "data":  [ { "id": 1, "model": "Mustang", ... }, "..." ]
}
```

### Successful single resource (`GET /api/cars/1`)

```json
{
  "meta":  { "resource": "cars" },
  "links": { "self": "/api/cars/1", "collection": "/api/cars" },
  "data":  { "id": 1, "model": "Mustang", ... }
}
```

### Error response (`GET /api/cars/999`)

```json
{
  "meta":  { "resource": "cars" },
  "links": { "self": "/api/cars/999", "collection": "/api/cars" },
  "error": { "status": 404, "message": "Car not found" }
}
```

---

## Why a base class?

Without `ApiBaseController`, every controller method would construct the envelope manually:

```php
// without the base class — repeated in every method
return $this->responseFactory->json([
    'meta'  => ['resource' => 'cars', 'total' => count($data)],
    'links' => ['self' => $request->path],
    'data'  => $data,
]);
```

Any mistake (wrong key name, missing `total`, wrong status code) produces an inconsistent response. By moving this into `apiJson()` and `apiError()`, the shape of every response is enforced in one place. A controller method only has to decide *what* to return — the base class handles *how* to format it.

---

## How `ApiController` uses both methods

```php
// collection — passes total:
return $this->apiJson('cars', $data, $request->path, total: count($data));

// single resource — passes collection link:
return $this->apiJson('cars', $this->carToArray($car), $request->path, '/api/cars');

// not found — passes status and collection link:
return $this->apiError('cars', 'Car not found', $request->path, 404, '/api/cars');
```
