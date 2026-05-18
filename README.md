# Classic Cars

A small PHP web application built on the **Maestro** framework (custom MVC). It serves the same data in two formats depending on how you access it:

| URL         | Returns   | Who uses it   |
| ----------- | --------- | ------------- |
| `/cars`     | HTML page | A browser     |
| `/api/cars` | JSON      | An API client |

This distinction — same data, different representation — is the core concept this app is built to illustrate.

---

## Local development

### Prerequisites

| Tool       | Minimum version | Check                  |
| ---------- | --------------- | ---------------------- |
| PHP        | 8.2             | `php -v`               |
| Composer   | 2.x             | `composer -V`          |
| SQLite CLI | any             | `sqlite3 --version`    |

PHP must have the `pdo_sqlite` extension enabled. Verify with:

```bash
php -m | grep pdo
```

You should see both `pdo` and `pdo_sqlite` in the output. On most systems these are enabled by default. On Ubuntu/Debian you can install them with `sudo apt install php-sqlite3`.

### Installation

1. **Clone the repository**

   ```bash
   git clone <repo-url> classic-cars
   cd classic-cars
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Seed the database** — runs all SQL files in order and creates `database.sqlite`:

   ```bash
   cat database/0_reset.sql database/1_makes.sql database/2_categories.sql database/3_cars.sql | sqlite3 database.sqlite
   ```

   Re-run this command at any time to reset the database back to the original seed data.

4. **Start the development server**

   ```bash
   php maestro serve
   ```

5. Open `http://localhost:8888` in your browser.

---

## Project structure

```text
app/
  Controllers/
    ApiBaseController.php <- Abstract base: builds the JSON envelope
    CarController.php     <- HTML CRUD for /cars
    ApiController.php     <- JSON API for /api/cars
    HomeController.php
  Models/
    Car.php
    Make.php
    Category.php
  Repositories/
    CarRepository.php + CarRepositoryInterface.php
    MakeRepository.php + MakeRepositoryInterface.php
    CategoryRepository.php + CategoryRepositoryInterface.php
  views/
    cars/                 <- Twig templates for the HTML pages
    partials/base.html.twig
src/                      <- Framework core (do not modify)
database/
  0_reset.sql
  1_makes.sql
  2_categories.sql
  3_cars.sql
```

---

## How the dual HTML / JSON response works

Visit `/cars` in your browser → you get a styled HTML table.  
Visit `/api/cars` in your browser → you get raw JSON.

The same `CarRepository` provides the data in both cases. The **controller** decides what to do with it:

- `CarController::index()` passes the data to a Twig template → HTML
- `ApiController::cars()` serialises the data to an array and calls `responseFactory->json()` → JSON

This pattern — one data source, multiple representations — is the foundation of every REST API.

### JSON response envelope

Every JSON response is wrapped in a consistent envelope with three top-level keys:

| Key     | Purpose                                                     |
| ------- | ----------------------------------------------------------- |
| `meta`  | Information about the response (resource name, item count)  |
| `links` | URLs the client can follow without hardcoding them (HATEOAS)|
| `data`  | The actual payload — a single object or an array of objects |

Collection (`GET /api/cars`):

```json
{
  "meta":  { "resource": "cars", "total": 12 },
  "links": { "self": "/api/cars" },
  "data":  [ { "id": 1, "model": "Mustang", "year": 1965 }, "..." ]
}
```

Single resource (`GET /api/cars/1`):

```json
{
  "meta":  { "resource": "cars" },
  "links": { "self": "/api/cars/1", "collection": "/api/cars" },
  "data":  { "id": 1, "model": "Mustang", "year": 1965, "..." }
}
```

Error (`GET /api/cars/999`):

```json
{
  "meta":  { "resource": "cars" },
  "links": { "self": "/api/cars/999", "collection": "/api/cars" },
  "error": { "status": 404, "message": "Car not found" }
}
```

The envelope is built in `ApiBaseController`. Every API controller extends it and calls `$this->apiJson()` or `$this->apiError()` — the shape is always identical, regardless of which resource is returned.

### URL design: HTML routes vs API routes

The two sets of routes look different on purpose.

HTML routes include path segments like `/edit` and `/delete`:

```text
GET  /makes/1/edit
POST /makes/1/edit
GET  /makes/1/delete
POST /makes/1/delete
```

This is necessary because **HTML forms only support GET and POST**. A browser cannot send a `PUT` or `DELETE` request. Without the `/edit` segment you would have two conflicting `GET /makes/1` routes — one to show the make, one to show the edit form. The extra path segment is the only way to tell them apart.

API routes do not have this limitation. An API client (fetch, curl, Postman) can send any HTTP verb, so the verb itself carries the intent. The URL should only identify the *resource*; the method names the *action*:

| Verb     | URL             | What it does   |
| -------- | --------------- | -------------- |
| `GET`    | `/api/makes`    | List all makes |
| `POST`   | `/api/makes`    | Create a make  |
| `GET`    | `/api/makes/1`  | Show one make  |
| `PUT`    | `/api/makes/1`  | Update a make  |
| `DELETE` | `/api/makes/1`  | Delete a make  |

Adding `/edit` or `/delete` to an API URL would be redundant — `DELETE /api/makes/1` already says everything. This separation — URL identifies the resource, verb identifies the operation — is what makes an API RESTful.

---

## Assignment

The current app only has full CRUD (Create, Read, Update, Delete) implemented for **Cars**. The **Makes** and **Categories** already have models, repositories, and seed data in place — but no controllers, routes, or views yet.

Your task is to implement these two missing features, following the exact same pattern used for Cars.

### Part 1 — Makes

HTML routes for the browser:

| Route                      | Controller method | What it does                    |
| -------------------------- | ----------------- | ------------------------------- |
| `GET /makes`               | `index`           | List all makes                  |
| `GET /makes/create`        | `create`          | Show the create form            |
| `POST /makes`              | `store`           | Save a new make                 |
| `GET /makes/{id}`          | `show`            | Show a single make and its cars |
| `GET /makes/{id}/edit`     | `edit`            | Show the edit form              |
| `POST /makes/{id}/edit`    | `update`          | Save changes                    |
| `GET /makes/{id}/delete`   | `deleteConfirm`   | Confirmation page               |
| `POST /makes/{id}/delete`  | `delete`          | Delete the make                 |

JSON API routes:

| Route                  | What it returns                            |
| ---------------------- | ------------------------------------------ |
| `GET /api/makes`       | Array of all makes                         |
| `GET /api/makes/{id}`  | A single make, with its cars nested inside |

Fields a Make has: `id`, `name`, `country`, `description`

---

### Part 2 — Categories

HTML routes for the browser (same CRUD pattern as Makes):

| Route                          | Description                |
| ------------------------------ | -------------------------- |
| `GET /categories`              | List all categories        |
| `GET /categories/create`       | Create form                |
| `POST /categories`             | Store new category         |
| `GET /categories/{id}`         | Show category and its cars |
| `GET /categories/{id}/edit`    | Edit form                  |
| `POST /categories/{id}/edit`   | Update                     |
| `GET /categories/{id}/delete`  | Confirmation               |
| `POST /categories/{id}/delete` | Delete                     |

JSON API routes:

| Route                       | What it returns                                 |
| --------------------------- | ----------------------------------------------- |
| `GET /api/categories`       | Array of all categories                         |
| `GET /api/categories/{id}`  | A single category, with its cars nested inside  |

Fields a Category has: `id`, `name`

---

### Checklist

Work through this list for each feature. Every step maps to something that already exists in the Cars implementation.

- [ ] Create `MakeController.php` in `app/Controllers/` (look at `CarController.php` as a reference)
- [ ] Register the routes in `app/RouteProvider.php`
- [ ] Register the controller in `app/ServiceProvider.php`
- [ ] Create views in `app/views/makes/` (look at `app/views/cars/` as a reference)
- [ ] Add the JSON endpoints to `ApiController.php`
- [ ] Add a "Makes" link to the navbar in `app/views/partials/base.html.twig`
- [ ] Repeat all steps for Categories

### Things to think about

- What should happen when you try to delete a Make that still has cars assigned to it?
- The `MakeRepository` already has `insert`, `update`, and `delete` methods. You only need to wire them up.
- For the `show` page of a Make, you will need to fetch the cars belonging to that make. Look at how `CarRepository` works and think about where to add a `findMakeCars(int $makeId)` method.
- The JSON response for `GET /api/makes/{id}` should look something like:

  ```json
  {
    "id": 1,
    "name": "Ford",
    "country": "USA",
    "description": "Founded in 1903...",
    "cars": [
      { "id": 1, "model": "Mustang", "year": 1965 },
      { "id": 2, "model": "Thunderbird", "year": 1957 }
    ]
  }
  ```
