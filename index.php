<?php
include('./api/UserController.php');
include('./api/InventoryController.php');
use UserController as UserController;
use InventoryController as InventoryController;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, array $controller){
        if(substr($path, -1) != '/'){
            $path = $path . '/';
        }

        $path = '/papi/api/' . $path;

        array_push($this->routes, [
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
        ]);
    }

    public function dispatch(string $path){
        if(substr($path, -1) != '/'){
            $path = $path . '/';
        }
        foreach ($this->routes as $route){
            if($path == $route['path'] && $route['method'] == strtoupper($_SERVER['REQUEST_METHOD'])){
                [$class, $function] = $route['controller'];
                $controllerInstance = new $class;
                $controllerInstance->$function();
            }
        }
    }
}

$router = new Router();

$router->add('POST', 'user', [UserController::class, 'create']);
$router->add('POST', 'inventory', [InventoryController::class, 'create']);
$router->add('PUT', 'inventory', [InventoryController::class, 'update']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($path);