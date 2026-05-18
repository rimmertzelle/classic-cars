<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\CarController;
use App\Controllers\HomeController;
use App\Repositories\CarRepository;
use App\Repositories\CarRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\CategoryRepositoryInterface;
use App\Repositories\MakeRepository;
use App\Repositories\MakeRepositoryInterface;
use Exception;
use Framework\Database;
use Framework\ResponseFactory;
use Framework\ServiceContainer;
use Framework\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Exception
     */
    public function register(ServiceContainer $container): void
    {
        $responseFactory = $container->get(ResponseFactory::class);
        $database = $container->get(Database::class);

        $makeRepository = new MakeRepository($database);
        $container->set(MakeRepositoryInterface::class, $makeRepository);

        $categoryRepository = new CategoryRepository($database);
        $container->set(CategoryRepositoryInterface::class, $categoryRepository);

        $carRepository = new CarRepository($database, $categoryRepository);
        $container->set(CarRepositoryInterface::class, $carRepository);

        $homeController = new HomeController($responseFactory);
        $container->set(HomeController::class, $homeController);

        $carController = new CarController($responseFactory, $carRepository, $makeRepository, $categoryRepository);
        $container->set(CarController::class, $carController);

        $apiController = new ApiController($responseFactory, $carRepository);
        $container->set(ApiController::class, $apiController);
    }
}
