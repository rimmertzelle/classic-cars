<?php

namespace App\Controllers;

use App\Models\Car;
use App\Repositories\CarRepositoryInterface;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;

class ApiController extends ApiBaseController
{
    private CarRepositoryInterface $carRepository;

    public function __construct(ResponseFactory $responseFactory, CarRepositoryInterface $carRepository)
    {
        parent::__construct($responseFactory);
        $this->carRepository = $carRepository;
    }

    public function cars(Request $request): Response
    {
        $cars = $this->carRepository->all();
        $data = array_map([$this, 'carToArray'], $cars);
        return $this->apiJson('cars', $data, $request->path, total: count($data));
    }

    public function car(Request $request): Response
    {
        $id = (int)$request->get('id');
        $car = $this->carRepository->find($id);
        if ($car === null) {
            return $this->apiError('cars', 'Car not found', $request->path, 404, '/api/cars');
        }
        return $this->apiJson('cars', $this->carToArray($car), $request->path, '/api/cars');
    }

    /** @return array<string, mixed> */
    private function carToArray(Car $car): array
    {
        return [
            'id' => $car->id,
            'model' => $car->model,
            'year' => $car->year,
            'color' => $car->color,
            'engine' => $car->engine,
            'description' => $car->description,
            'make' => $car->make ? [
                'id' => $car->make->id,
                'name' => $car->make->name,
                'country' => $car->make->country,
            ] : null,
            'categories' => array_map(
                fn($cat) => ['id' => $cat->id, 'name' => $cat->name],
                $car->categories
            ),
        ];
    }
}
