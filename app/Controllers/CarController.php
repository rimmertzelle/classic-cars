<?php

namespace App\Controllers;

use App\Models\Car;
use App\Repositories\CarRepositoryInterface;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\MakeRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class CarController
{
    private ResponseFactory $responseFactory;

    private CarRepositoryInterface $carRepository;

    private MakeRepositoryInterface $makeRepository;

    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        ResponseFactory $responseFactory,
        CarRepositoryInterface $carRepository,
        MakeRepositoryInterface $makeRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->carRepository = $carRepository;
        $this->makeRepository = $makeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function index(): Response
    {
        $cars = $this->carRepository->all();
        return $this->responseFactory->view("cars/index.html.twig", ["cars" => $cars]);
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->responseFactory->notFound();
        }
        return $this->responseFactory->view("cars/show.html.twig", ["car" => $car]);
    }

    public function create(): Response
    {
        return $this->responseFactory->view("cars/create.html.twig", [
            "makes" => $this->makeRepository->all(),
            "categories" => $this->categoryRepository->all(),
        ]);
    }

    public function store(Request $request): Response
    {
        $model = $request->get('model');
        $makeId = $request->get('make_id');
        $year = $request->get('year');
        $color = $request->get('color') ?? '';
        $engine = $request->get('engine') ?? '';
        $description = $request->get('description') ?? '';

        $errors = [];

        if ($model === null || trim($model) === '') {
            $errors['model'] = "Model is required.";
        }

        if (!is_numeric($makeId) || (int)$makeId <= 0) {
            $errors['make_id'] = "Make is required.";
        }

        if (!is_numeric($year) || (int)$year < 1886 || (int)$year > (int)date('Y') + 1) {
            $errors['year'] = "A valid year is required.";
        }

        if (!empty($errors)) {
            $car = new Car();
            $car->model = $model ?? '';
            $car->makeId = (int)$makeId;
            $car->year = (int)$year;
            $car->color = $color;
            $car->engine = $engine;
            $car->description = $description;
            return $this->responseFactory->view("cars/create.html.twig", [
                "errors" => $errors,
                "car" => $car,
                "makes" => $this->makeRepository->all(),
                "categories" => $this->categoryRepository->all(),
            ]);
        }

        $car = new Car();
        $car->model = $model;
        $car->makeId = (int)$makeId;
        $car->year = (int)$year;
        $car->color = $color;
        $car->engine = $engine;
        $car->description = $description;
        $car->categories = $this->resolveCategories($request->getAll('categories'));

        $car = $this->carRepository->insert($car);
        if ($car === null) {
            return $this->responseFactory->internalError();
        }
        return $this->responseFactory->redirect('/cars/' . $car->id);
    }

    public function edit(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->responseFactory->notFound();
        }
        return $this->responseFactory->view("cars/edit.html.twig", [
            "car" => $car,
            "makes" => $this->makeRepository->all(),
            "categories" => $this->categoryRepository->all(),
        ]);
    }

    public function update(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->responseFactory->notFound();
        }

        $car->model = $request->get('model') ?? $car->model;
        $car->makeId = (int)($request->get('make_id') ?? $car->makeId);
        $car->year = (int)($request->get('year') ?? $car->year);
        $car->color = $request->get('color') ?? $car->color;
        $car->engine = $request->get('engine') ?? $car->engine;
        $car->description = $request->get('description') ?? $car->description;
        $car->categories = $this->resolveCategories($request->getAll('categories'));

        if (!$this->carRepository->update($car)) {
            return $this->responseFactory->internalError();
        }
        return $this->responseFactory->redirect('/cars/' . $car->id);
    }

    public function deleteConfirm(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->responseFactory->notFound();
        }
        return $this->responseFactory->view("cars/delete.html.twig", ["car" => $car]);
    }

    public function delete(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->responseFactory->notFound();
        }
        $this->carRepository->delete($car);
        return $this->responseFactory->redirect('/cars');
    }

    /** @param string[]|null $ids */
    private function resolveCategories(?array $ids): array
    {
        $categories = [];
        if ($ids) {
            foreach ($ids as $id) {
                $category = $this->categoryRepository->find((int)$id);
                if ($category) {
                    $categories[] = $category;
                }
            }
        }
        return $categories;
    }
}
