<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\AsyncController;
use App\Controllers\CarController;
use App\Controllers\HomeController;
use Framework\Router;
use Framework\RouteProviderInterface;
use Framework\ServiceContainer;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @throws \Exception
     */
    public function register(Router $router, ServiceContainer $container): void
    {
        $homeController = $container->get(HomeController::class);
        $router->addRoute('GET', '/', [$homeController, "index"]);
        $router->addRoute('GET', '/about', [$homeController, "about"]);

        $carController = $container->get(CarController::class);
        $router->addRoute('GET', '/cars', [$carController, 'index']);
        $router->addRoute('GET', '/cars/create', [$carController, 'create']);
        $router->addRoute('POST', '/cars', [$carController, 'store']);
        $router->addRoute('GET', '/cars/(?<id>\d+)', [$carController, 'show']);
        $router->addRoute('GET', '/cars/(?<id>\d+)/edit', [$carController, 'edit']);
        $router->addRoute('POST', '/cars/(?<id>\d+)/edit', [$carController, 'update']);
        $router->addRoute('GET', '/cars/(?<id>\d+)/delete', [$carController, 'deleteConfirm']);
        $router->addRoute('POST', '/cars/(?<id>\d+)/delete', [$carController, 'delete']);

        $apiController = $container->get(ApiController::class);
        $router->addRoute('GET', '/api/cars', [$apiController, 'cars']);
        $router->addRoute('GET', '/api/cars/(?<id>\d+)', [$apiController, 'car']);

        $asyncController = $container->get(AsyncController::class);
        $router->addRoute('GET', '/async/cars', [$asyncController, 'index']);
        $router->addRoute('GET', '/async/cars/(?<id>\d+)', [$asyncController, 'show']);
    }
}
